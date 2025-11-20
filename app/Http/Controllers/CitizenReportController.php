<?php

namespace App\Http\Controllers;

use App\Models\CitizenReport;
use App\Models\ReportType;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CitizenReportController extends Controller
{
    public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(CitizenReport::class, 'citizenReport');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CitizenReport::class);

        $perPage = $request->integer('per_page', 10);

        $query = CitizenReport::query()
            ->with('type')
            ->setUpQuery();        // this applies search + sort based on request params

        return Inertia::render('CitizenReport/Index', [
            'tableData' => $query->paginate($perPage)->withQueryString(),
            'dataColumns' => CitizenReport::getDataColumns(),
            'typeData' => ReportType::select(['id', 'name'])->get(),
            'treeData' => Tree::with('species:id,latin_name,common_name')
                ->select('id', 'species_id', 'lat', 'lon', 'address')
                ->get(),
            'userData' => User::with('roles:id,name')
                ->select(['id', 'first_name', 'last_name'])
                ->get(),
            'reportStatus' => CitizenReport::getReportStatusOptions(),
        ]);
    }


    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            "report_type_id" => 'required|integer|exists:report_types,id',
            "created_by"    => 'nullable|integer|exists:users,id',
            "created_at"    => 'nullable|date',
            "tree_id"       => 'required|integer|exists:trees,id',
            'lat'           => ['nullable', 'numeric', 'between:-90,90'],
            'lon'           => ['nullable', 'numeric', 'between:-180,180'],
            "description"   => 'nullable|string|max:5000',
            "status"        => 'nullable|string|max:20',
            "photo_url"     => 'nullable|string|max:255',
            "created_at"    => 'nullable|date',
            "resolved_at"   => 'nullable|date',
        ]);

        CitizenReport::create($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item type has been created.'),
        ]);

        return redirect()->route('citizenReports.index');
    }


    public function update(Request $request, CitizenReport $citizenReport): RedirectResponse
    {
        $validated = $request->validate([
            "report_type_id" => 'required|integer|exists:report_types,id',
            "created_by"    => 'nullable|integer|exists:users,id',
            "created_at"    => 'nullable|date',
            "tree_id"       => 'required|integer|exists:trees,id',
            'lat'           => ['nullable', 'numeric', 'between:-90,90'],
            'lon'           => ['nullable', 'numeric', 'between:-180,180'],
            "description"   => 'nullable|string|max:5000',
            "status"        => 'nullable|string|max:20',
            "photo_url"     => 'nullable|string|max:255',
            "created_at"    => 'nullable|date',
            "resolved_at"   => 'nullable|date',
        ]);

        $citizenReport->update($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item type has been updated.'),
        ]);

        return redirect()->route('citizenReports.index');
    }


    public function destroy(Request $request, CitizenReport $citizenReport): RedirectResponse
    {

        // 1. Authorize 
        $this->authorize('delete', $citizenReport);

        // 2. Delete
        $citizenReport->delete();

        // 3. Flash
        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('Item type has been deleted.')
        ]);

        return redirect()->route('citizenReports.index');
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        // Extract the IDs from the incoming payload
        $ids = collect($request->input('citizenReports'))
            ->pluck('report_id')
            ->filter()        // remove nulls just in case
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No item selected.'),
            ]);

            return redirect()->route('citizenReports.index');
        }

        DB::transaction(function () use ($ids) {
            // Load the models we’re going to operate on
            $itemList = CitizenReport::whereIn('report_id', $ids)->get();

            // Optional sanity check: if some IDs were not found
            // decide what to do (ignore, error, etc.)
            // if (count($itemList) !== count($ids)) {
            //     throw new \RuntimeException('Some selected tree citizenReports do not exist.');
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

        return redirect()->route('citizenReports.index');
    }
}
