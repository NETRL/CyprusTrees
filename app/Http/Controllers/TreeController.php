<?php

namespace App\Http\Controllers;

use App\Enums\OwnerType;
use App\Enums\TreeSex;
use App\Enums\TreeStatus;
use App\Http\Requests\Tree\StoreTreeRequest;
use App\Http\Requests\Tree\UpdateTreeRequest;
use App\Models\Neighborhood;
use App\Models\Species;
use App\Models\Tag;
use App\Models\Tree;
use App\Models\UserTree;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            ->with(['species', 'tags', 'neighborhood'])
            ->withCount('photos')
            ->orderBy('id', 'desc')
            ->setUpQuery();


        $tableData = $query->paginate($perPage)->withQueryString();


        $tableData->getCollection()->transform(function ($e) {
            return [
                ...$e->toArray(),
                'species_label' => $e->species
                    ? ($e->species?->id . ' - ' . $e->species?->common_name . ' (' . $e->species?->latin_name . ')')
                    : '-',
                'neighborhood_label' => $e->neighborhood
                    ? ($e->neighborhood?->name . ',' . $e->neighborhood?->city . ' (' . $e->neighborhood?->district . ')')
                    : '-',
                'location_label' => ($e->lat ?? '-') . ', ' . ($e->lon ?? '-'),

                'status_label' => $e->status ? TreeStatus::from($e->status)->label() : '-',
                'health_label' => $e->health_status ? Str::title(strtolower($e->health_status)) : '-',
                'sex_label' => $e->sex ? TreeSex::from($e->sex)->label() : '-',
                'owner_type_label' => $e->owner_type ? OwnerType::from($e->owner_type)->label() : '-',
            ];
        });

        return Inertia::render('Tree/Index', [
            'tableData' => $tableData,
            'speciesData' => Species::orderBy('common_name')
                ->get(['id', 'latin_name', 'common_name']),
            'neighborhoodData' => Neighborhood::orderBy('name')->get(['id', 'name', 'city']),
            'tagData' => Tag::all(),
            'dataColumns' => Tree::getDataColumns(),
            'treeSex' => Tree::getTreeSexOptions(),
            'healthStatus' => Tree::getHealthStatusOptions(),
            'treeStatus' => Tree::getTreeStatusOptions(),
            'ownerType' => Tree::getOwnerTypeOptions(),
        ]);
    }

    public function store(StoreTreeRequest $request): RedirectResponse
    {

        $data = $request->validated();

        $tagIdsPresent  = array_key_exists('tag_ids', $data);
        $tagIds         = $tagIdsPresent ? $data['tag_ids'] : null;
        unset($data['tag_ids']);

        $tree = Tree::create($data);

        if ($tagIdsPresent) {
            $tree->tags()->sync($tagIds);
        }

        UserTree::create([
            'user_id' => auth()->user()->id,
            'tree_id' => $tree->id,
        ]);


        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Tree has been created.'),
        ]);

        return redirect()->back()->with('flash_event', [
            'type' => 'tree.saved',
            'payload' => [
                'id' => $tree->id,
            ],
        ]);
    }


    public function update(UpdateTreeRequest $request, Tree $tree): RedirectResponse
    {
        $data = $request->validated();

        $tagIdsPresent  = array_key_exists('tag_ids', $data);
        $tagIds         = $tagIdsPresent ? $data['tag_ids'] : null;

        unset($data['tag_ids']);

        $tree->update($data);

        if ($tagIdsPresent) {
            $tree->tags()->sync($tagIds ?? []);
        }

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Tree has been updated.'),
        ]);

        return redirect()->back();
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
