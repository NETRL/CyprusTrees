<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceEvent;
use App\Models\MaintenanceType;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceEventsController extends Controller
{
    public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(MaintenanceEvent::class, 'maintenanceEvent');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', MaintenanceEvent::class);

        $perPage = $request->integer('per_page', 10);

        $query = MaintenanceEvent::query()
            ->with('type')
            ->setUpQuery();        // this applies search + sort based on request params

        return Inertia::render('MaintenanceEvent/Index', [
            'tableData' => $query->paginate($perPage)->withQueryString(),
            'dataColumns' => MaintenanceEvent::getDataColumns(),
            'treeData' => Tree::with('species:id,latin_name,common_name')
                ->select('id', 'species_id', 'lat', 'lon', 'address')
                ->get(),
            'typeData' => MaintenanceType::select(['type_id', 'name'])->get(),
            'userData' => User::with('roles:id,name')
                ->select(['id', 'first_name', 'last_name'])
                ->get(),
        ]);
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "tree_id"       => 'required|integer|exists:trees,id',
            "type_id"       => 'required|integer|exists:maintenance_types,type_id',
            "performed_by"  => 'nullable|integer|exists:users,id',
            "performed_at"  => 'nullable|date',
            "quantity"      => 'nullable|numeric|min:0',
            "cost"          => 'nullable|numeric|min:0',
            "notes"          => 'nullable|string|max:5000',
        ]);

        MaintenanceEvent::create($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item type has been created.'),
        ]);

        return redirect()->route('maintenanceEvents.index');
    }


    public function update(Request $request, MaintenanceEvent $maintenanceEvent): RedirectResponse
    {
         $validated = $request->validate([
            "tree_id"       => 'required|integer|exists:trees,id',
            "type_id"       => 'required|integer|exists:maintenance_types,type_id',
            "performed_by"  => 'nullable|integer|exists:users,id',
            "performed_at"  => 'nullable|date',
            "quantity"      => 'nullable|numeric|min:0',
            "cost"          => 'nullable|numeric|min:0',
            "notes"          => 'nullable|string|max:5000',
        ]);

        $maintenanceEvent->update($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item type has been updated.'),
        ]);

        return redirect()->route('maintenanceEvents.index');
    }


    public function destroy(Request $request, MaintenanceEvent $maintenanceEvent): RedirectResponse
    {

        // 1. Authorize 
        $this->authorize('delete', $maintenanceEvent);

        // 2. Delete
        $maintenanceEvent->delete();

        // 3. Flash
        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('Item type has been deleted.')
        ]);

        return redirect()->route('maintenanceEvents.index');
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        // Extract the IDs from the incoming payload
        $ids = collect($request->input('maintenanceEvents'))
            ->pluck('type_id')
            ->filter()        // remove nulls just in case
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No item selected.'),
            ]);

            return redirect()->route('maintenanceEvents.index');
        }

        DB::transaction(function () use ($ids) {
            // Load the models we’re going to operate on
            $itemList = MaintenanceEvent::whereIn('event_id', $ids)->get();

            // Optional sanity check: if some IDs were not found
            // decide what to do (ignore, error, etc.)
            // if (count($itemList) !== count($ids)) {
            //     throw new \RuntimeException('Some selected tree maintenanceEvents do not exist.');
            // }

            foreach ($itemList as $item) {
                $this->authorize('delete', $item);
                $item->delete();
            }
        });

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Items have been deleted.'),
        ]);

        return redirect()->route('maintenanceEvents.index');
    }
}
