<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(Campaign::class, 'campaign');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Campaign::class);

        $perPage = $request->integer('per_page', 10);

        $query = Campaign::query()
            ->withCount('plantingEvents')
            ->setUpQuery();        // this applies search + sort based on request params

        return Inertia::render('Campaign/Index', [
            'tableData' => $query->paginate($perPage)->withQueryString(),
            'dataColumns' => Campaign::getDataColumns(),
        ]);
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name" => 'required|string',
            "sponsor" => 'nullable|string',
            "start_date" => 'nullable|date',
            "end_date" => 'nullable|date',
            "notes" => 'nullable|string',
        ]);

        Campaign::create($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Campaign has been created.'),
        ]);

        return redirect()->route('campaigns.index');
    }


    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
         $validated = $request->validate([
            "name" => 'required|string',
            "sponsor" => 'nullable|string',
            "start_date" => 'nullable|date',
            "end_date" => 'nullable|date',
            "notes" => 'nullable|string',
        ]);

        $campaign->update($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Campaign has been updated.'),
        ]);

        return redirect()->route('campaigns.index');
    }


    public function destroy(Request $request, Campaign $campaign): RedirectResponse
    {

        // 1. Authorize 
        $this->authorize('delete', $campaign);

        // 2. Delete
        $campaign->delete();

        // 3. Flash
        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('Campaign has been deleted.')
        ]);

        return redirect()->route('campaigns.index');
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        // Extract the IDs from the incoming payload
        $ids = collect($request->input('campaigns'))
            ->pluck('id')
            ->filter()        // remove nulls just in case
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No campaign selected.'),
            ]);

            return redirect()->route('campaigns.index');
        }

        DB::transaction(function () use ($ids) {
            // Load the models we’re going to operate on
            $itemList = Campaign::whereIn('id', $ids)->get();

            // Optional sanity check: if some IDs were not found
            // decide what to do (ignore, error, etc.)
            // if (count($itemList) !== count($ids)) {
            //     throw new \RuntimeException('Some selected campaigns do not exist.');
            // }

            foreach ($itemList as $item) {
                $this->authorize('delete', $item);
                $item->delete();
            }
        });

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Campaigns have been deleted.'),
        ]);

        return redirect()->route('campaigns.index');
    }
}
