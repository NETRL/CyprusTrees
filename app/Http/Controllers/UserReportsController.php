<?php

namespace App\Http\Controllers;

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

        $query = CitizenReport::query()
            ->where('created_by', auth()->id())
            ->with('type')
            ->setUpQuery();        // this applies search + sort based on request params

        return Inertia::render('User/Report/Index', [
            'tableData' => $query->paginate($perPage)->withQueryString(),
            'dataColumns' => CitizenReport::getDataColumns(),
            'typeData' => ReportType::select(['id', 'name'])->get(),
            'treeData' => Tree::with('species:id,latin_name,common_name')
                ->select('id', 'species_id', 'lat', 'lon', 'address')
                ->get(),
            'reportStatus' => CitizenReport::getReportStatusOptions(),
            'reportTypes' => ReportType::all(),
        ]);
    }
}
