<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tree\StoreTreeRequest;
use App\Http\Requests\Tree\UpdateTreeRequest;
use App\Models\Neighborhood;
use App\Models\Species;
use App\Models\Tree;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TreeController extends Controller
{

    public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(Tree::class, 'tree');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Tree::class);

        $perPage = $request->integer('per_page', 25); // default 25


        $query = Tree::query()
            ->with('species')
            ->withCount('photos')
            ->setUpQuery();

        return Inertia::render('Tree/Index', [
            'tableData' => $query->paginate($perPage)->withQueryString(),
            'speciesData' => Species::orderBy('common_name')
                ->get(['id', 'latin_name', 'common_name']),
            'neighborhoodData' => Neighborhood::orderBy('name')->get(['id', 'name', 'city']),
            'dataColumns' => Tree::getDataColumns()

        ]);
    }

    public function store(StoreTreeRequest $request): RedirectResponse
    {
        Tree::create($request->validated());

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Tree has been created.'),
        ]);

        return redirect()->route('trees.index');
    }


    public function update(UpdateTreeRequest $request, Tree $tree): RedirectResponse
    {
        $tree->update($request->validated());

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Tree has been updated.'),
        ]);

        return redirect()->route('trees.index');
    }


    public function destroy(Request $request, Tree $tree): RedirectResponse
    {

        // 1. Authorize 
        $this->authorize('delete', $tree);

        // 2. Delete
        $tree->delete();

        // 3. Flash
        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('Tree has been deleted.')
        ]);

        return redirect()->route('trees.index');
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        // Extract the IDs from the incoming payload
        $ids = collect($request->input('trees'))
            ->pluck('id')
            ->filter()        // remove nulls just in case
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No trees selected.'),
            ]);

            return redirect()->route('trees.index');
        }

        DB::transaction(function () use ($ids) {
            // Load the models we’re going to operate on
            $treesList = Tree::whereIn('id', $ids)->get();

            // Optional sanity check: if some IDs were not found
            // decide what to do (ignore, error, etc.)
            // if (count($treesList) !== count($ids)) {
            //     throw new \RuntimeException('Some selected trees do not exist.');
            // }

            foreach ($treesList as $trees) {
                $this->authorize('delete', $trees);
                $trees->delete();
            }
        });

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Trees have been deleted.'),
        ]);

        return redirect()->route('trees.index');
    }
}
