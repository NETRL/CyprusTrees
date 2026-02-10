<?php

namespace App\Http\Controllers\Gis;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessGeoJsonRevision;
use App\Models\GisLayer;
use App\Models\GisRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use function Symfony\Component\Clock\now;

class GisRevisionsController extends Controller
{
    public function store(Request $request, GisLayer $layer)
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'import_mode' => ['required', Rule::in([GisRevision::MODE_REPLACE, GisRevision::MODE_APPEND])],
            'file' => ['required', 'file', 'max:51200'], // 50MB
        ]);

        if (!$request->hasFile('file')) {
            return back()->with('message', ['type' => 'error', 'message' => 'No file received.']);
        }

        $file = $data['file'];
        $ext = strtolower($file->getClientOriginalExtension());

        // accept only geojson/json
        if (!in_array($ext, ['geojson', 'json'], true)) {
            return back()->with('message', [
                'type' => 'error',
                'message' => 'Only .geojson or .json (GeoJSON) files are accepted.',
            ]);
        }

        $storedPath = $file->store("gis/layers/{$layer->id}", 'local');
        
        // optional monotonic revision_no per layer
        $nextNo = (int) (GisRevision::query()->where('layer_id', $layer->id)->max('revision_no') ?? 0) + 1;

        $revision = GisRevision::create([
            'layer_id' => $layer->id,
            'imported_by' => $request->user()?->id,
            'revision_no' => $nextNo,
            'label' => $data['label'] ?: ("Revision #{$nextNo}"),
            'import_mode' => $data['import_mode'],
            'status' => GisRevision::STATUS_QUEUED,
            'original_name' => $file->getClientOriginalName(),
            'original_ext' => $ext,
            'stored_path' => $storedPath,
            'is_included' => true, // default; activation logic may adjust
        ]);

        ProcessGeoJsonRevision::dispatch($revision->id);

        return back()->with('message', [
            'type' => 'success',
            'message' => 'Import queued.'
        ]);
    }

    public function toggleIncluded(Request $request, GisLayer $layer, GisRevision $revision)
    {
        abort_unless($revision->layer_id === $layer->id, 404);

        $data = $request->validate([
            'is_included' => ['required', 'boolean'],
        ]);

        // Don’t include non-completed revisions
        if ($revision->status !== GisRevision::STATUS_COMPLETED && $data['is_included'] === true) {
            return back()->with('message', [
                'type' => 'error',
                'message' => 'Only completed revisions can be included.',
            ]);
        }

        $revision->update(['is_included' => (bool)$data['is_included']]);

        return back()->with('message', [
            'type' => 'success',
            'message' => $data['is_included'] ? 'Revision included.' : 'Revision excluded.',
        ]);
    }


    /**
     * “Activate (replace)” = this revision becomes the baseline:
     * - mark all other revisions is_included=false
     * - mark this revision true
     * - set layer.active_revision_id
     */
    public function activateReplace(Request $request, GisLayer $layer, GisRevision $revision)
    {
        abort_unless($revision->layer_id === $layer->id, 404);

        if ($revision->status !== GisRevision::STATUS_COMPLETED) {
            return back()->with('message', [
                'type' => 'error',
                'message' => 'Only completed revisions can be activated.',
            ]);
        }

        DB::transaction(function () use ($layer, $revision) {
            GisRevision::query()
                ->where('layer_id', $layer->id)
                ->where('id', '!=', $revision->id)
                ->update(['is_included' => false]);

            $revision->update([
                'is_included' => true,
                'activated_at' => now(),
            ]);

            $layer->update([
                'active_revision_id' => $revision->id,
            ]);
        });

        return back()->with('message', [
            'type' => 'success',
            'message' => 'Activated as baseline (replace).',
        ]);
    }

    public function archive(Request $request, GisLayer $layer, GisRevision $revision)
    {
        abort_unless($revision->layer_id === $layer->id, 404);

        DB::transaction(function () use ($revision, $layer) {
            $revision->update([
                'status' => GisRevision::STATUS_ARCHIVED,
                'is_included' => false,
                'archived_at' => now(),
            ]);

            // if it was active unset layer pointer (maybe choose a new one?)
            if ($layer->active_revision_id === $revision->id) {
                $layer->update(['active_revision_id' => null]);
            }
        });

        return back()->with('message', [
            'type' => 'success',
            'message' => 'Revision archived.',
        ]);
    }

    public function purge(Request $request, GisLayer $layer, GisRevision $revision)
    {
        abort_unless($revision->layer_id === $layer->id, 404);

        // dont purge included or active
        if ($revision->is_included || $layer->active_revision_id === $revision->id) {
            return back()->with('message', [
                'type' => 'error',
                'message' => 'Cannot purge an included/active revision. Exclude it first.',
            ]);
        }

        DB::transaction(function () use ($revision) {
            // cascade will delete gis_features
            $revision->features()->delete(); // optional, FK cascade may handle on hard delete
            $revision->forceDelete();             // hard delete
            // $revision->delete();             // soft delete (recommended first)
        });

        return back()->with('message', [
            'type' => 'success',
            'message' => 'Revision removed.',
        ]);
    }
}
