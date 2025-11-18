<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\PlantingEvent;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PlantingEventController extends Controller
{
    public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(PlantingEvent::class, 'plantingEvent');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PlantingEvent::class);

        $perPage = $request->integer('per_page', 10);

        $query = PlantingEvent::query()
            ->with('campaign')
            ->setUpQuery();        // this applies search + sort based on request params

        return Inertia::render('PlantingEvent/Index', [
            'tableData' => $query->paginate($perPage)->withQueryString(),
            'dataColumns' => PlantingEvent::getDataColumns(),
            'treeData'    => Tree::with('species:id,latin_name,common_name')
                ->select('id', 'species_id', 'lat', 'lon', 'address')
                ->get(),
            'campaignData' => Campaign::query()
                ->select('id', 'name', 'sponsor', 'start_date', 'end_date')
                ->get(),
            'userData' => User::with('roles:id,name')
                ->select(['id', 'first_name', 'last_name'])
                ->get(),
        ]);
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "tree_id" => 'required|exists:trees,id',
            "campaign_id" => 'nullable|exists:campaigns,id',
            "planted_by" => 'nullable|exists:users,id',
            "planted_at" => 'nullable|date',
            "method" => 'nullable|string',
            "notes" => 'nullable|string',
        ]);

        PlantingEvent::create($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Planting Event has been created.'),
        ]);

        return redirect()->route('plantingEvents.index');
    }


    public function update(Request $request, PlantingEvent $plantingEvent): RedirectResponse
    {
        $validated = $request->validate([
            "name" => 'required|string',
            "sponsor" => 'nullable|string',
            "start_date" => 'nullable|date',
            "end_date" => 'nullable|date',
            "notes" => 'nullable|string',
        ]);

        $plantingEvent->update($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('PlantingEvent has been updated.'),
        ]);

        return redirect()->route('plantingEvents.index');
    }


    public function destroy(Request $request, PlantingEvent $plantingEvent): RedirectResponse
    {

        // 1. Authorize 
        $this->authorize('delete', $plantingEvent);

        // 2. Delete
        $plantingEvent->delete();

        // 3. Flash
        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('PlantingEvent has been deleted.')
        ]);

        return redirect()->route('plantingEvents.index');
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        // Extract the IDs from the incoming payload
        $ids = collect($request->input('plantingEvents'))
            ->pluck('id')
            ->filter()        // remove nulls just in case
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No planting events selected.'),
            ]);

            return redirect()->route('plantingEvents.index');
        }

        DB::transaction(function () use ($ids) {
            // Load the models we’re going to operate on
            $itemList = PlantingEvent::whereIn('id', $ids)->get();

            // Optional sanity check: if some IDs were not found
            // decide what to do (ignore, error, etc.)
            // if (count($itemList) !== count($ids)) {
            //     throw new \RuntimeException('Some selected plantingEvents do not exist.');
            // }

            foreach ($itemList as $item) {
                $this->authorize('delete', $item);
                $item->delete();
            }
        });

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Planting Events have been deleted.'),
        ]);

        return redirect()->route('plantingEvents.index');
    }
}
