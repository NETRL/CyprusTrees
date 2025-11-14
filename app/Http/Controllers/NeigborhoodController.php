<?php

namespace App\Http\Controllers;

use App\Http\Requests\Neighborhood\StoreNeighborhoodRequest;
use App\Http\Requests\Neighborhood\UpdateNeighborhoodRequest;
use App\Models\Neighborhood;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->setUpQuery();        // this applies search + sort based on request params

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
        $neighborhood->update($request->validated());

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

        // 2. Delete
        $neighborhood->delete();

        // 3. Flash
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

            // Optional sanity check: if some IDs were not found
            // decide what to do (ignore, error, etc.)
            // if (count($neighborhoodList) !== count($ids)) {
            //     throw new \RuntimeException('Some selected neighborhood do not exist.');
            // }

            foreach ($neighborhoodList as $neighborhood) {
                $this->authorize('delete', $neighborhood);
                $neighborhood->delete();
            }
        });

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Neighborhoods have been deleted.'),
        ]);

        return redirect()->route('neighborhoods.index');
    }
}
