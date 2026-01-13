<?php

namespace App\Http\Controllers;

use App\Helpers\GeometryHelper;
use App\Http\Requests\Neighborhood\StoreNeighborhoodRequest;
use App\Http\Requests\Neighborhood\UpdateNeighborhoodRequest;
use App\Models\Neighborhood;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class NeigborhoodController extends Controller
{
    public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(Neighborhood::class, 'neighborhood');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Neighborhood::class);

        $perPage = $request->integer('per_page', 10);

        $query = Neighborhood::query()
            ->withCount('trees')   // needed to show `trees_count`
            ->orderBy('id', 'desc')
            ->setUpQuery();

        return Inertia::render('Neighborhood/Index', [
            'tableData' => $query->paginate($perPage)->withQueryString(),
            'dataColumns' => Neighborhood::getDataColumns(),
        ]);
    }

    public function store(StoreNeighborhoodRequest $request): RedirectResponse
    {
        Neighborhood::create($request->validated());

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Neighborhood has been created.'),
        ]);

        return redirect()->route('neighborhoods.index');
    }

    public function update(UpdateNeighborhoodRequest $request, Neighborhood $neighborhood): RedirectResponse
    {
        $oldRef = $neighborhood->geom_ref;
        $data   = $request->validated();

        $neighborhood->update($data);

        $newRef = $neighborhood->geom_ref;

        // If geom_ref changed, rename associated GeoJSON file if it exists
        if ($oldRef && $newRef && $oldRef !== $newRef) {

            $oldPath = $neighborhood->geojsonPath($oldRef);
            $newPath = $neighborhood->geojsonPath($newRef);

            if (file_exists($oldPath)) {

                // Ensure directory exists
                if (!is_dir(dirname($newPath))) {
                    mkdir(dirname($newPath), 0775, true);
                }

                rename($oldPath, $newPath);

                // OPTIONAL: re-generate PostGIS geom (keeps DB consistent)
                GeometryHelper::updateSpatialData($neighborhood);
            }
        }

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Nneighborhood has been updated.'),
        ]);

        return redirect()->route('neighborhoods.index');
    }

    public function destroy(Request $request, Neighborhood $neighborhood): RedirectResponse
    {

        // 1. Authorize 
        $this->authorize('delete', $neighborhood);

        // 2. Remove associated GeoJSON file
        $this->removeNeighborhoodGeojson($neighborhood, clearGeom: false);

        // 3. Delete
        $neighborhood->delete();

        // 4. Flash
        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('Neighborhood has been deleted.')
        ]);

        return redirect()->route('neighborhoods.index');
    }


    public function massDestroy(Request $request): RedirectResponse
    {
        // Extract the IDs from the incoming payload
        $ids = collect($request->input('neighborhoods'))
            ->pluck('id')
            ->filter()        // remove nulls just in case
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No neighborhoods selected.'),
            ]);

            return redirect()->route('neighborhoods.index');
        }

        DB::transaction(function () use ($ids) {
            // Load the models we’re going to operate on
            $neighborhoodList = Neighborhood::whereIn('id', $ids)->get();

            foreach ($neighborhoodList as $neighborhood) {
                $this->authorize('delete', $neighborhood);
                $this->removeNeighborhoodGeojson($neighborhood, clearGeom: false);
                $neighborhood->delete();
            }
        });

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Neighborhoods have been deleted.'),
        ]);

        return redirect()->route('neighborhoods.index');
    }

    public function uploadFile(Request $request)
    {

        $validated = $request->validate([
            'neighborhood_id'   => ['required', 'exists:neighborhoods,id'],
            'geojson_file'      => ['required', 'file', 'mimes:json,geojson'],
        ]);


        $neighborhood = Neighborhood::findOrFail($validated['neighborhood_id']);
        $file         = $validated['geojson_file'];

        // Ensure geom_ref exists
        if (!$neighborhood->geom_ref) {
            // Example derivation; adjust to your naming scheme
            $base = $neighborhood->name ?? ('neighborhood-' . $neighborhood->id);
            $neighborhood->geom_ref = Str::upper(Str::slug($base, '_'));
            $neighborhood->save();
        }

        $geomRef  = $neighborhood->geom_ref;
        $filename = $geomRef . '.json';
        $dir      = base_path('geojson-data');

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file->move($dir, $filename);

        // Now use your existing logic to populate geom

        GeometryHelper::updateSpatialData($neighborhood);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('GeoJSON uploaded and spatial data updated.'),
        ]);
        return redirect()->route('neighborhoods.index');
    }

    public function removeFile(Request $request)
    {
        $validated = $request->validate([
            'neighborhood_id' => ['required', 'exists:neighborhoods,id'],
        ]);

        $neighborhood = Neighborhood::findOrFail($validated['neighborhood_id']);

        $this->authorize('update', $neighborhood);

        $this->removeNeighborhoodGeojson($neighborhood, clearGeom: true);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('GeoJSON file removed and geometry cleared.'),
        ]);

        return redirect()->route('neighborhoods.index');
    }

    protected function removeNeighborhoodGeojson(Neighborhood $neighborhood, bool $clearGeom = true): void
    {
        $geomRef = $neighborhood->geom_ref;

        if (! $geomRef) {
            return; // nothing to do
        }

        $path = $neighborhood->geojsonPath($geomRef);

        if (file_exists($path)) {
            if (! @unlink($path)) {
                Log::warning("Failed to delete GeoJSON file for {$geomRef}: {$path}");
            }
        }

        if ($clearGeom) {
            // Only clear geom if we’re keeping the model
            $neighborhood->geom = null;
            $neighborhood->save();
        }
    }
}
