<?php

namespace App\Http\Controllers;

use App\Models\ReportType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportTypeController extends Controller
{
     public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(ReportType::class, 'reportType');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ReportType::class);

        $perPage = $request->integer('per_page', 10);

        $query = ReportType::query()
            ->withCount('reports') 
            ->setUpQuery();

        return Inertia::render('ReportType/Index', [
            'tableData' => $query->paginate($perPage)->withQueryString(),
            'dataColumns' => ReportType::getDataColumns(),
        ]);
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name" => 'required|string|max:60',
        ]);

        ReportType::create($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item has been created.'),
        ]);

        return redirect()->route('reportTypes.index');
    }


    public function update(Request $request, ReportType $reportType): RedirectResponse
    {
        $validated = $request->validate([
            "name" => 'required|string|max:60',
        ]);

        $reportType->update($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item has been updated.'),
        ]);

        return redirect()->route('reportTypes.index');
    }


    public function destroy(Request $request, ReportType $reportType): RedirectResponse
    {

        // 1. Authorize 
        $this->authorize('delete', $reportType);

        // 2. Delete
        $reportType->delete();

        // 3. Flash
        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('Item has been deleted.')
        ]);

        return redirect()->route('reportTypes.index');
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        // Extract the IDs from the incoming payload
        $ids = collect($request->input('reportTypes'))
            ->pluck('id')
            ->filter()        // remove nulls just in case
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No item selected.'),
            ]);

            return redirect()->route('reportTypes.index');
        }

        DB::transaction(function () use ($ids) {
            // Load the models we’re going to operate on
            $itemList = ReportType::whereIn('id', $ids)->get();

            // Optional sanity check: if some IDs were not found
            // decide what to do (ignore, error, etc.)
            // if (count($itemList) !== count($ids)) {
            //     throw new \RuntimeException('Some selected items do not exist.');
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

        return redirect()->route('reportTypes.index');
    }
}
