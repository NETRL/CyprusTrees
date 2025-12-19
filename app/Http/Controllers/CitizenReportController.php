<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Enums\TreeStatus;
use App\Jobs\ProcessPhotoUpload;
use App\Models\CitizenReport;
use App\Models\Photo;
use App\Models\ReportType;
use App\Models\Tree;
use App\Models\User;
use App\Notifications\CitizenReportStatusChanged;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
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
            ->with(['type', 'tree.species:id,common_name,latin_name', 'creator', 'photo'])
            ->setUpQuery();        // this applies search + sort based on request params


        $tableData = $query->paginate($perPage)->withQueryString();

        $tableData->getCollection()->transform(function ($e) {
            return [
                ...$e->toArray(),
                'tree_label' => $e->tree
                    ? ($e->tree_id . ' - ' . $e->tree->species?->common_name . ' (' . $e->tree->species?->latin_name . ') ' . ($e->tree->tags_label ?? ''))
                    : (string) $e->tree_id,

                'type_label' => $e->type ? ($e->type->name) : "-",

                'creator_label' => $e->creator
                    ? ($e->created_by . ' - ' . trim(($e->creator->first_name ?? '') . ' ' . ($e->creator->last_name ?? '')))
                    : '-',
            ];
        });

        return Inertia::render('CitizenReport/Index', [
            'tableData' => $tableData,
            'dataColumns' => CitizenReport::getDataColumns(),
            'typeData' => ReportType::select(['id', 'name'])->get(),
            'treeData' => Tree::with('species:id,latin_name,common_name')
                ->select('id', 'species_id', 'lat', 'lon', 'address')
                ->get(),
            'userData' => User::with('roles:id,name')
                ->select(['id', 'first_name', 'last_name'])
                ->get(),
            'reportStatus' => CitizenReport::getReportStatusOptions(),
            'reportTypes' => ReportType::all(),
        ]);
    }


    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            "report_type_id" => 'required|integer|exists:report_types,id',
            "created_at"    => 'nullable|date',
            "tree_id"       => 'required|integer|exists:trees,id',
            'lat'           => ['nullable', 'numeric', 'between:-90,90'],
            'lon'           => ['nullable', 'numeric', 'between:-180,180'],
            "description"   => 'nullable|string|max:5000',
            'photo'         => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:15360'],
            "resolved_at"   => 'nullable|date',
            "source"        => ['nullable', 'in:camera,upload'],

        ]);

        $photo = null;
        $report = null;

        DB::transaction(function () use ($request, &$validated, &$photo, &$report) {
            if ($request->hasFile('photo')) {
                // Store with .jpg extension to match job output
                $file = $validated['photo'];
                $filename = uniqid('photo_', true) . '.jpg';
                $path = $file->storeAs('tree-photos', $filename, 'public');

                $photo = Photo::create([
                    'tree_id'     => $validated['tree_id'],
                    'caption'     => $validated['description'] ?? null,
                    'url'         => null,
                    'captured_at' => $validated['created_at'],
                    'source'      => $validated['source'] ?? 'upload',
                    'path'        => $path,
                    'status'      => 'processing',
                ]);
                unset($validated['photo']);
            }

            $validated['created_by'] = auth()->id();
            $validated['status'] = ReportStatus::OPEN;
            if ($photo) {
                $validated['photo_id'] = $photo->id;
            }
            $report = CitizenReport::create($validated);

            if ($photo) {
                ProcessPhotoUpload::dispatch($photo->id);
            }
        });

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Thank you for your report! Our team will review it shortly.'),
        ]);

        return redirect()->back();
    }


    public function update(Request $request, CitizenReport $citizenReport): RedirectResponse
    {

        $validated = $request->validate([
            "report_id" => 'required|integer|exists:citizen_reports,report_id',
            "report_type_id" => 'required|integer|exists:report_types,id',
            "created_by"    => 'nullable|integer|exists:users,id',
            "created_at"    => 'nullable|date',
            "tree_id"       => 'required|integer|exists:trees,id',
            'lat'           => ['nullable', 'numeric', 'between:-90,90'],
            'lon'           => ['nullable', 'numeric', 'between:-180,180'],
            "description"   => 'nullable|string|max:5000',
            "status"        => 'nullable|string|max:20',
            "created_at"    => 'nullable|date',
            // "resolved_at"   => 'nullable|date',
        ]);

        // If you consider "resolved" a status, enforce resolved_at automatically:
        if (($validated['status'] ?? null) === ReportStatus::RESOLVED->value) {
            $validated['resolved_at'] = $validated['resolved_at'] ?? now();
        }

        $citizenReport->update($validated);

        if (
            $citizenReport->wasChanged('status') &&
            (string) $citizenReport->status === ReportStatus::RESOLVED->value &&
            (string) $citizenReport->getOriginal('status') !== ReportStatus::RESOLVED->value
        ) {
            $creator = $citizenReport->creator ?? User::find($citizenReport->created_by);
            if ($creator) {
                $creator->notify(new CitizenReportStatusChanged(
                    $citizenReport,
                    (string) $citizenReport->getOriginal('status'),
                    (string) $citizenReport->status
                ));
            }
        }


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
