<?php

namespace App\Http\Controllers\Gis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gis\UpsertGisLayerRequest;
use App\Models\GisLayer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class GisLayersCrudController extends Controller
{
    public function index(Request $request)
    {

        $this->authorize('viewAny', GisLayer::class);

        $perPage = $request->integer('per_page', 10);

        $query = GisLayer::query()
            ->with([])
            ->setUpQuery();

        $tableData = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Gis/Layers/Index', [
            'tableData' => $tableData,
            'dataColumns' => GisLayer::getDataColumns(),
        ]);
    }

    public function store(UpsertGisLayerRequest $request)
    {
        GisLayer::create($request->validated());

        return redirect()->back()
            ->with('message', [
                'type' => 'success',
                'message' => 'Layer created.',
            ]);
    }

    public function update(UpsertGisLayerRequest $request, GisLayer $layer)
    {
        // prevent key changes after creation
        if ($request->input('key') !== $layer->key) {
            throw ValidationException::withMessages([
                'key' => 'Key cannot be changed.',
            ]);
        }


        $layer->update($request->validated());

        return back()->with('message', [
            'type' => 'success',
            'message' => 'Layer updated.',
        ]);
    }

    public function destroy(Request $request, GisLayer $layer)
    {

        $this->authorize('delete', $layer);
        // Soft-delete layer (revisions/features cascade on hard delete only)
        // $layer->delete();
        $layer->forceDelete();

        return redirect()->route('gisLayers.index')->with('message', [
            'type' => 'success',
            'message' => 'Layer deleted.',
        ]);
    }
}
