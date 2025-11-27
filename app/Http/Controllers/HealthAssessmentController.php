<?php

namespace App\Http\Controllers;

use App\Enums\HealthStatus;
use App\Models\HealthAssessment;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

class HealthAssessmentController extends Controller
{
    public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(HealthAssessment::class, 'healthAssessment');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', HealthAssessment::class);

        $perPage = $request->integer('per_page', 10);

        $query = HealthAssessment::query()
            ->with('tree')
            ->setUpQuery();        // this applies search + sort based on request params

        return Inertia::render('HealthAssessment/Index', [
            'tableData'     => $query->paginate($perPage)->withQueryString(),
            'dataColumns'   => HealthAssessment::getDataColumns(),
            'treeData'      => Tree::with('species:id,latin_name,common_name')
                ->select(['id', 'species_id', 'lat', 'lon', 'address'])
                ->get(),
            'userData'      => User::with('roles:id,name')
                ->select(['id', 'first_name', 'last_name'])
                ->get(),
            'healthStatus' => HealthAssessment::getHealthStatusOptions(),
        ]);
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tree_id' => ['required', 'integer', 'exists:trees,id'],
            'assessed_by' => ['nullable', 'integer', 'exists:users,id'],
            'assessed_at' => ['nullable', 'date'],
            'health_status' => ['nullable', new Enum(HealthStatus::class)],
            'pests_diseases' => ['nullable', 'string', 'max:5000'],
            'risk_score' => ['nullable', 'numeric', 'between:0,1'],
            'actions_recommended' => ['nullable', 'string', 'max:5000'],
        ]);

        HealthAssessment::create($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item type has been created.'),
        ]);

        return redirect()->route('healthAssessments.index');
    }


    public function update(Request $request, HealthAssessment $healthAssessment): RedirectResponse
    {
        $validated = $request->validate([
            'tree_id' => ['required', 'integer', 'exists:trees,id'],
            'assessed_by' => ['nullable', 'integer', 'exists:users,id'],
            'assessed_at' => ['nullable', 'date'],
            'health_status' => ['nullable', new Enum(HealthStatus::class)],
            'pests_diseases' => ['nullable', 'string', 'max:5000'],
            'risk_score' => ['nullable', 'numeric', 'between:0,1'],
            'actions_recommended' => ['nullable', 'string', 'max:5000'],
        ]);

        $healthAssessment->update($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item type has been updated.'),
        ]);

        return redirect()->route('healthAssessments.index');
    }


    public function destroy(Request $request, HealthAssessment $healthAssessment): RedirectResponse
    {

        // 1. Authorize 
        $this->authorize('delete', $healthAssessment);

        // 2. Delete
        $healthAssessment->delete();

        // 3. Flash
        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('Item type has been deleted.')
        ]);

        return redirect()->route('healthAssessments.index');
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        // Extract the IDs from the incoming payload
        $ids = collect($request->input('healthAssessments'))
            ->pluck('assessment_id')
            ->filter()        // remove nulls just in case
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No item selected.'),
            ]);

            return redirect()->route('healthAssessments.index');
        }

        DB::transaction(function () use ($ids) {
            // Load the models we’re going to operate on
            $itemList = HealthAssessment::whereIn('assessment_id', $ids)->get();

            // Optional sanity check: if some IDs were not found
            // decide what to do (ignore, error, etc.)
            // if (count($itemList) !== count($ids)) {
            //     throw new \RuntimeException('Some selected tree healthAssessments do not exist.');
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

        return redirect()->route('healthAssessments.index');
    }
}
