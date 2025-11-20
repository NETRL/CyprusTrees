<?php

namespace App\Http\Controllers;

use App\Http\Requests\Species\StoreSpeciesRequest;
use App\Http\Requests\Species\UpdateSpeciesRequest;
use App\Models\Species;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SpeciesController extends Controller
{

    public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(Species::class, 'species');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Species::class);

        $perPage = $request->integer('per_page', 10);

        $query = Species::query()
            ->withCount('trees')   // needed to show `trees_count`
            ->setUpQuery();        // this applies search + sort based on request params
       
        return Inertia::render('Species/Index', [
            'tableData' => $query->paginate($perPage)->withQueryString(),
            'dataColumns' => Species::getDataColumns(),
            'droughtOptions' => Species::getDroughtToleranceOptions(),
            'canopyOptions' => Species::getCanopyClassOptions(),
        ]);
    }

    public function store(StoreSpeciesRequest $request): RedirectResponse
    {
        Species::create($request->validated());

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Species has been created.'),
        ]);

        return redirect()->route('species.index');
    }


    public function update(UpdateSpeciesRequest $request, Species $Species): RedirectResponse
    {
        $Species->update($request->validated());

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Species has been updated.'),
        ]);

        return redirect()->route('species.index');
    }




    public function destroy(Request $request, Species $species): RedirectResponse
    {

        // 1. Authorize 
        $this->authorize('delete', $species);

        // 2. Delete
        $species->delete();

        // 3. Flash
        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('Species has been deleted.')
        ]);

        return redirect()->route('species.index');
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        // Extract the IDs from the incoming payload
        $ids = collect($request->input('species'))
            ->pluck('id')
            ->filter()        // remove nulls just in case
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No species selected.'),
            ]);

            return redirect()->route('species.index');
        }

        DB::transaction(function () use ($ids) {
            // Load the models we’re going to operate on
            $speciesList = Species::whereIn('id', $ids)->get();

            // Optional sanity check: if some IDs were not found
            // decide what to do (ignore, error, etc.)
            // if (count($speciesList) !== count($ids)) {
            //     throw new \RuntimeException('Some selected species do not exist.');
            // }

            foreach ($speciesList as $species) {
                $this->authorize('delete', $species);
                $species->delete();
            }
        });

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Species have been deleted.'),
        ]);

        return redirect()->route('species.index');
    }
}
