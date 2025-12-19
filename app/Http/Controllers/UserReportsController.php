<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Models\CitizenReport;
use App\Models\ReportType;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserReportsController extends Controller
{

    public function index(Request $request): Response
    {

        $perPage = $request->integer('per_page', 10);

        $report_id = $request->string('report_id')->trim()->toString();

        $query = CitizenReport::query()
            ->where('created_by', auth()->id())
            ->with(['type', 'tree.species:id,common_name,latin_name', 'photo'])
            ->orderBy('created_at', 'desc')
            ->setUpQuery();        // this applies search + sort based on request params

        if ($report_id) {
            $query->where('report_id', $report_id);
        }

        $tableData = $query->paginate($perPage)->withQueryString();

        $tableData->getCollection()->transform(function ($e) {
            return [
                ...$e->toArray(),
                'type_label' => $e->type ? ($e->type->name) : "-",
            ];
        });

        return Inertia::render('User/Report/Index', [
            'tableData' => $tableData,
            'dataColumns' => CitizenReport::getDataColumns(),
            'reportStatus' => CitizenReport::getReportStatusOptions(),
        ]);
    }
}
