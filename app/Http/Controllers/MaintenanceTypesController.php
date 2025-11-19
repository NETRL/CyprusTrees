<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceTypesController extends Controller
{
     public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(MaintenanceType::class, 'maintenanceType');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', MaintenanceType::class);

        $perPage = $request->integer('per_page', 10);

        $query = MaintenanceType::query()
            ->withCount('events')
            ->setUpQuery();        // this applies search + sort based on request params

        return Inertia::render('MaintenanceType/Index', [
            'tableData' => $query->paginate($perPage)->withQueryString(),
            'dataColumns' => MaintenanceType::getDataColumns(),
        ]);
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name" => 'required|string|max:60',
        ]);

        MaintenanceType::create($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item type has been created.'),
        ]);

        return redirect()->route('maintenanceTypes.index');
    }


    public function update(Request $request, MaintenanceType $maintenanceType): RedirectResponse
    {
        $validated = $request->validate([
            "name" => 'required|string|max:60',
        ]);

        $maintenanceType->update($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item type has been updated.'),
        ]);

        return redirect()->route('maintenanceTypes.index');
    }


    public function destroy(Request $request, MaintenanceType $maintenanceType): RedirectResponse
    {

        // 1. Authorize 
        $this->authorize('delete', $maintenanceType);

        // 2. Delete
        $maintenanceType->delete();

        // 3. Flash
        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('Item type has been deleted.')
        ]);

        return redirect()->route('maintenanceTypes.index');
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        // Extract the IDs from the incoming payload
        $ids = collect($request->input('maintenanceTypes'))
            ->pluck('type_id')
            ->filter()        // remove nulls just in case
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No item selected.'),
            ]);

            return redirect()->route('maintenanceTypes.index');
        }

        DB::transaction(function () use ($ids) {
            // Load the models we’re going to operate on
            $itemList = MaintenanceType::whereIn('type_id', $ids)->get();

            // Optional sanity check: if some IDs were not found
            // decide what to do (ignore, error, etc.)
            // if (count($itemList) !== count($ids)) {
            //     throw new \RuntimeException('Some selected tree maintenanceTypes do not exist.');
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

        return redirect()->route('maintenanceTypes.index');
    }
}
