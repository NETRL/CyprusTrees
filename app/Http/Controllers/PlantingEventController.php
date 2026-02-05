<?php

namespace App\Http\Controllers;

use App\Enums\PlantingEventStatus;
use App\Jobs\ProcessPhotoUpload;
use App\Models\Campaign;
use App\Models\Neighborhood;
use App\Models\Photo;
use App\Models\PlantingEvent;
use App\Models\PlantingEventTree;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PlantingEventController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PlantingEvent::class, 'plantingEvent');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PlantingEvent::class);

        $perPage = $request->integer('per_page', 10);

        $query = PlantingEvent::query()
            ->with([
                'campaign:id,name,sponsor',
                'neighborhood:id,name',
                'assignedTo:id,first_name,last_name',
                'createdBy:id,first_name,last_name',
                'eventTrees' => function ($q) {
                    $q->with([
                        'tree' => function ($t) {
                            $t->with('species:id,latin_name,common_name');
                        },
                        'planter:id,first_name,last_name',
                    ]);
                    $q->orderBy('id', 'asc');
                },
            ])
            ->withCount(['eventTrees'])
            ->setUpQuery();


        $tableData = $query
            ->paginate($perPage)
            ->withQueryString();

        $tableData->getCollection()->transform(function ($e) {
            return [
                ...$e->toArray(),

                'location' => $e->getLocationAttribute(),

                'campaign_label' => $e->campaign
                    ? ($e->campaign_id . ' - ' . $e->campaign->name . ($e->campaign->sponsor ? ' (' . $e->campaign->sponsor . ')' : ''))
                    : '-',

                'neighborhood_label' => $e->neighborhood
                    ? ($e->neighborhood_id . ' - ' . $e->neighborhood->name)
                    : '-',

                'assigned_to_label' => $e->assignedTo
                    ? trim(($e->assignedTo->first_name ?? '') . ' ' . ($e->assignedTo->last_name ?? ''))
                    : '-',

                'created_by_label' => $e->creator
                    ? trim(($e->creator->first_name ?? '') . ' ' . ($e->creator->last_name ?? ''))
                    : '-',

                'trees_count' => (int) ($e->event_trees_count ?? 0),
                // children for expansion
                'planted_trees' => ($e->eventTrees ?? collect())->map(function ($pet) {
                    $tree = $pet->tree;

                    $treeLabel = $tree
                        ? ($tree->getKey() . ' - ' .
                            ($tree->species?->common_name ?? '-') .
                            ' (' . ($tree->species?->latin_name ?? '-') . ') ' .
                            ($tree->tags_label ?? '')
                        )
                        : (string) $pet->tree_id;

                    $plantedByLabel = $pet->plantedBy
                        ? trim(($pet->plantedBy->first_name ?? '') . ' ' . ($pet->plantedBy->last_name ?? ''))
                        : '-';

                    return [
                        'id'              => $pet->id,
                        'tree_id'         => $pet->tree_id,
                        'tree_label'      => $treeLabel,
                        'planted_by'      => $pet->planted_by,
                        'planted_by_label'   => $plantedByLabel,
                        'planted_at'      => $pet->planted_at,
                        'planting_method' => $pet->planting_method,
                        'notes'           => $pet->notes,
                        'tree_lat'        => $tree?->lat,
                        'tree_lon'        => $tree?->lon,
                    ];
                })->values(),
            ];
        });

        return Inertia::render('PlantingEvent/Index', [
            'tableData'    => $tableData,
            'dataColumns'  => PlantingEvent::getDataColumns(),
            'dateFilterable' => PlantingEvent::getDateFilterable(),
            'campaignData' => Campaign::query()
                ->select('id', 'name', 'sponsor', 'start_date', 'end_date')
                ->get(),

            'neighborhoodData' => Neighborhood::query()
                ->select('id', 'name')
                ->get(),

            'userData' => User::with('roles:id,name')
                ->select(['id', 'first_name', 'last_name'])
                ->get(),

            'statusOptions' => collect(PlantingEventStatus::cases())->map(fn($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campaign_id'       => 'nullable|integer|exists:campaigns,id',
            'neighborhood_id'   => 'nullable|integer|exists:neighborhoods,id',
            'assigned_to'       => 'nullable|integer|exists:users,id',
            'started_at'        => 'nullable|date',
            'completed_at'      => 'nullable|date|after_or_equal:started_at',
            'lat'               => 'nullable|numeric|between:-90,90',
            'lon'               => 'nullable|numeric|between:-180,180',
            'target_tree_count' => 'nullable|integer|min:0|max:100000',
            'status'            => 'required|string|in:' . implode(',', array_map(fn($c) => $c->value, PlantingEventStatus::cases())),
            'notes'             => 'nullable|string|max:5000',
        ]);

        $validated['created_by'] = $request->user()->id;

        PlantingEvent::query()->create($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Planting Event has been created.'),
        ]);

        return redirect()->route('plantingEvents.index');
    }

    public function update(Request $request, PlantingEvent $plantingEvent): RedirectResponse
    {
        $validated = $request->validate([
            'campaign_id'       => 'nullable|integer|exists:campaigns,id',
            'neighborhood_id'   => 'nullable|integer|exists:neighborhoods,id',
            'assigned_to'       => 'nullable|integer|exists:users,id',
            'started_at'        => 'nullable|date',
            'completed_at'      => 'nullable|date|after_or_equal:started_at',
            'lat'               => 'nullable|numeric|between:-90,90',
            'lon'               => 'nullable|numeric|between:-180,180',
            'target_tree_count' => 'nullable|integer|min:0|max:100000',
            'status'            => 'required|string|in:' . implode(',', array_map(fn($c) => $c->value, PlantingEventStatus::cases())),
            'notes'             => 'nullable|string|max:5000',
        ]);

        $plantingEvent->update($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Planting Event has been updated.'),
        ]);

        return redirect()->route('plantingEvents.index');
    }

    public function destroy(Request $request, PlantingEvent $plantingEvent): RedirectResponse
    {
        $this->authorize('delete', $plantingEvent);

        $plantingEvent->delete();

        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('Planting Event has been deleted.'),
        ]);

        return redirect()->route('plantingEvents.index');
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        $ids = collect($request->input('plantingEvents'))
            ->pluck('planting_id')
            ->filter()
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No planting events selected.'),
            ]);

            return redirect()->route('plantingEvents.index');
        }

        DB::transaction(function () use ($ids) {
            $items = PlantingEvent::query()->whereIn('planting_id', $ids)->get();

            foreach ($items as $item) {
                $this->authorize('delete', $item);
                $item->delete();
            }
        });

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Planting Events have been deleted.'),
        ]);

        return redirect()->route('plantingEvents.index');
    }


    public function storePhoto(Request $request, PlantingEvent $plantingEvent)
    {
        $validated = $request->validate([
            'tree_id' => ['required', 'exists:trees,id'],
            'caption' => ['nullable', 'string', 'max:255'],
            'source'  => ['nullable', 'in:camera,upload'],

            // match the existing PhotoController.store
            'photos'  => ['required', 'array', 'max:20'],
            'photos.*' => [
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:15360',
            ],
        ]);


        // Ensure this tree belongs to this planting event
        if (!$plantingEvent->eventTrees()->where('tree_id', $validated['tree_id'])->exists()) {
            return redirect()
                ->back()
                ->with('message', [
                    'type' => 'error',
                    'message' => __('Tree does not belong to this planting event.'),
                ]);
        }

        $photoCount = 0;
        $jobs = [];
        $createdPhotoIds = [];

        DB::transaction(function () use ($validated, $plantingEvent, &$jobs, &$photoCount, &$createdPhotoIds) {
            $treeId  = $validated['tree_id'];
            $caption = $validated['caption'] ?? null;
            $source  = $validated['source'] ?? 'upload';

            foreach ($validated['photos'] as $file) {
                // If extractCapturedAt is currently private in PhotoController, move it to a shared service/trait.
                // For now: store null or implement minimal extraction here.
                $capturedAt = null;

                $filename = uniqid('photo_', true) . '.jpg';
                $path = $file->storeAs('tree-photos', $filename, 'public');

                $photo = Photo::create([
                    'tree_id'     => $treeId,
                    'caption'     => $caption,
                    'url'         => null,
                    'captured_at' => $capturedAt,
                    'source'      => $source,
                    'path'        => $path,
                    'status'      => 'processing',
                ]);

                // Attach to planting event via pivot
                $plantingEvent->photos()->attach($photo->id);

                $createdPhotoIds[] = $photo->id;

                $jobs[] = new ProcessPhotoUpload($photo->id);
                $photoCount++;
            }
        });

        Bus::batch($jobs)->dispatch();

        return back()->with('message', [
            'type'    => 'success',
            'message' => trans_choice(
                '{1} :count photo uploaded, processing in background.|[2,*] :count photos uploaded, processing in background.',
                $photoCount,
                ['count' => $photoCount]
            ),
        ]);
    }
}
