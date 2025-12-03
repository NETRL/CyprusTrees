<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPhotoUpload;
use App\Models\Neighborhood;
use App\Models\Photo;
use App\Models\Species;
use App\Models\Tree;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PhotoController extends Controller
{
    public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(Photo::class, 'photo');
    }

    public function show(Photo $photo)
    {
        $this->authorize('view', $photo);
        return response()->json($photo);
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Photo::class);

        $perPage = $request->integer('per_page', 10);

        // --- Filters from request ---
        $treeId        = $request->filled('tree_id') ? (int) $request->input('tree_id') : null;
        $search         = $request->string('search')->trim()->toString(); // global text search
        $neighborhoodId = $request->integer('neighborhood_id');
        $speciesId      = $request->integer('species_id');
        $dateFrom       = $request->date('date_from'); // expects Y-m-d
        $dateTo         = $request->date('date_to');   // expects Y-m-d
        $sort           = $request->string('sort')->toString() ?: 'recent';

        $tree = null;

        // Base query: filters that should affect EVERYTHING (results + facets)
        $baseQuery = Photo::query()
            ->with([
                'tree',
                'tree.species',
                'tree.neighborhood',
            ]);

        // Tree mode
        if ($treeId) {
            $baseQuery->where('tree_id', $treeId);
            $tree = Tree::find($treeId);
        }

        // Global text search:
        // - caption
        // - tree address
        // - species name / latin name
        // - neighborhood name
        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('caption', 'like', "%{$search}%")
                    ->orWhereHas('tree', function ($qt) use ($search) {
                        $qt->where('address', 'like', "%{$search}%")
                            ->orWhereHas('species', function ($qs) use ($search) {
                                $qs->where('common_name', 'like', "%{$search}%")
                                    ->orWhere('latin_name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('neighborhood', function ($qn) use ($search) {
                                $qn->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        // Date range
        if ($dateFrom) {
            $baseQuery->whereDate('captured_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $baseQuery->whereDate('captured_at', '<=', $dateTo);
        }


        // MAIN RESULTS QUERY: base filters + selected species / neighborhood
        $resultsQuery = clone $baseQuery;

        // Filter: Neighborhood
        if ($neighborhoodId) {
            $resultsQuery->whereHas('tree', function ($qt) use ($neighborhoodId) {
                $qt->where('neighborhood_id', $neighborhoodId);
            });
        }

        // Filter: Species
        if ($speciesId) {
            $resultsQuery->whereHas('tree', function ($qt) use ($speciesId) {
                $qt->where('species_id', $speciesId);
            });
        }

        // Sort:
        // 1. recent      → captured_at desc
        // 2. oldest      → captured_at asc
        // 3. tree_id_asc → tree_id asc
        // 4. tree_id_desc→ tree_id desc
        switch ($sort) {
            case 'oldest':
                $resultsQuery->orderBy('captured_at', 'asc');
                break;
            case 'tree_id_asc':
                $resultsQuery->orderBy('tree_id', 'asc');
                break;
            case 'tree_id_desc':
                $resultsQuery->orderBy('tree_id', 'desc');
                break;
            case 'recent':
            default:
                $resultsQuery->orderBy('captured_at', 'desc');
                break;
        }

        $tableData = $resultsQuery
            ->paginate($perPage)
            ->appends($request->query());

        // SPECIES FACET: base filters + current neighborhood
        $speciesQuery = Species::query()
            ->leftJoin('trees', 'trees.species_id', '=', 'species.id')
            ->leftJoin('photos', 'photos.tree_id', '=', 'trees.id')
            ->leftJoin('neighborhoods', 'neighborhoods.id', '=', 'trees.neighborhood_id');

        // Same shared filters:
        if ($treeId) {
            $speciesQuery->where('photos.tree_id', $treeId);
        }
        if ($search !== '') {
            $speciesQuery->where(function ($q) use ($search) {
                $q->where('photos.id', $search)
                    ->orWhere('photos.caption', 'like', "%{$search}%")
                    ->orWhere('trees.address', 'like', "%{$search}%")
                    ->orWhere('species.common_name', 'like', "%{$search}%")
                    ->orWhere('species.latin_name', 'like', "%{$search}%")
                    ->orWhere('neighborhoods.name', 'like', "%{$search}%");
            });
        }
        if ($dateFrom) {
            $speciesQuery->whereDate('photos.captured_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $speciesQuery->whereDate('photos.captured_at', '<=', $dateTo);
        }

        // Apply neighborhood filter (other facet), but NOT species filter
        if ($neighborhoodId) {
            $speciesQuery->where('trees.neighborhood_id', $neighborhoodId);
        }

        $speciesOptions = $speciesQuery
            ->groupBy('species.id', 'species.common_name', 'species.latin_name')
            ->orderBy('species.common_name')
            ->get([
                'species.id',
                'species.common_name',
                'species.latin_name',
                DB::raw('COUNT(photos.id) as photos_count'),
            ]);

        // NEIGHBORHOOD FACET: base filters + current species
        $neighborhoodQuery = Neighborhood::query()
            ->leftJoin('trees', 'trees.neighborhood_id', '=', 'neighborhoods.id')
            ->leftJoin('photos', 'photos.tree_id', '=', 'trees.id');

        if ($treeId) {
            $neighborhoodQuery->where('photos.tree_id', $treeId);
        }
        if ($search !== '') {
            $neighborhoodQuery->where(function ($q) use ($search) {
                $q->where('photos.id', $search)
                    ->orWhere('photos.caption', 'like', "%{$search}%")
                    ->orWhere('trees.address', 'like', "%{$search}%")
                    ->orWhere('neighborhoods.name', 'like', "%{$search}%");
            });
        }
        if ($dateFrom) {
            $neighborhoodQuery->whereDate('photos.captured_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $neighborhoodQuery->whereDate('photos.captured_at', '<=', $dateTo);
        }

        // Apply species filter (other facet), but NOT neighborhood filter
        if ($speciesId) {
            $neighborhoodQuery->where('trees.species_id', $speciesId);
        }

        $neighborhoodOptions = $neighborhoodQuery
            ->groupBy('neighborhoods.id', 'neighborhoods.name')
            ->orderBy('neighborhoods.name')
            ->get([
                'neighborhoods.id',
                'neighborhoods.name',
                DB::raw('COUNT(photos.id) as photos_count'),
            ]);

        // Keep the last-used filters so Vue can pre-fill the form
        $filters = [
            'tree_id'         => $treeId,
            'search'          => $search,
            'neighborhood_id' => $neighborhoodId,
            'species_id'      => $speciesId,
            'date_from'       => $request->input('date_from'),
            'date_to'         => $request->input('date_to'),
            'sort'            => $sort,
        ];

        return Inertia::render('Photo/Index', [
            'tableData'           => $tableData,
            'selectedTree'        => $tree,
            'initialTreeId'       => $treeId,
            'filters'             => $filters,
            'speciesOptions'      => $speciesOptions,
            'neighborhoodOptions' => $neighborhoodOptions,
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'tree_id' => ['required', 'exists:trees,id'],
            'caption' => ['nullable', 'string', 'max:255'],
            'source'  => ['nullable', 'in:camera,upload'],
            'photos'  => ['required', 'array', 'max:20'],
            'photos.*' => [
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:15360',
            ],
        ]);

        $photoCount = 0;
        $jobs = [];


        DB::transaction(function () use ($validated, &$jobs, &$photoCount) {
            $treeId = $validated['tree_id'];
            $caption = $validated['caption'] ?? null;
            $source = $validated['source'] ?? 'upload';

            foreach ($validated['photos'] as $file) {
                $capturedAt = $this->extractCapturedAt($file);

                // Store with .jpg extension to match job output
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

                $jobs[] = new ProcessPhotoUpload($photo->id);
                $photoCount++;
            }
        });

        // Dispatch all jobs as a batch
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

    /**
     * Extract captured_at timestamp from EXIF data
     */
    private function extractCapturedAt($file): ?Carbon
    {
        try {
            $exif = @exif_read_data($file->getPathname());

            if (empty($exif['DateTimeOriginal'])) {
                return null;
            }

            return Carbon::createFromFormat('Y:m:d H:i:s', $exif['DateTimeOriginal']);
        } catch (\Throwable) {
            return null;
        }
    }


    public function update(Request $request, Photo $photo)
    {
        $validated = $request->validate([
            'caption'      => ['nullable', 'string', 'max:255'],
            'photo'        => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:15360'],
            'source'       => ['nullable', 'in:camera,upload'],
            'captured_at'  => ['nullable', 'date'],
        ]);

        // keep track of the old original path so we can delete it *after* everything succeeds
        $oldPath = $photo->path;

        $hasNewFile = $request->hasFile('photo');

        if ($hasNewFile) {
            /** @var \Illuminate\Http\UploadedFile $file */
            $file = $validated['photo'];

            $filename = uniqid('photo_', true) . '.jpg';
            $path     = $file->storeAs('tree-photos', $filename, 'public');

            // EXIF date, fallback to existing captured_at if EXIF missing
            $capturedAt = $photo->captured_at;
            try {
                $exif = @exif_read_data($file->getPathname());
                if (!empty($exif['DateTimeOriginal'])) {
                    $capturedAt = Carbon::createFromFormat('Y:m:d H:i:s', $exif['DateTimeOriginal']);
                }
            } catch (\Throwable $e) {
                // ignore EXIF errors, keep fallback
            }

            // set new path & reset URL/status so the job can take over
            $photo->path        = $path;
            $photo->url         = null;
            $photo->status      = 'processing';
            $photo->captured_at = $capturedAt;
            $photo->error_message = null; // clear previous error if any
        }

        // other fields (caption, source, manual captured_at override)
        if (array_key_exists('caption', $validated)) {
            $photo->caption = $validated['caption'];
        }

        if (!empty($validated['source'])) {
            $photo->source = $validated['source'];
        }

        if (!empty($validated['captured_at'])) {
            // allow manual override even if EXIF was used above
            $photo->captured_at = $validated['captured_at'];
        }

        $photo->save();

        // if a new file was uploaded, queue the same processing job as in store()
        if ($hasNewFile) {
            ProcessPhotoUpload::dispatch($photo->id);

            // delete old original file if it exists and differs from new path
            if ($oldPath && $oldPath !== $photo->path) {
                try {
                    Storage::disk('public')->delete($oldPath);
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete old photo', [
                        'path'  => $oldPath,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return back()->with('message', [
            'type'    => 'success',
            'message' => __('Photo updated successfully.'),
        ]);
    }

    public function destroy(Request $request, Photo $photo): RedirectResponse
    {
        $this->authorize('delete', $photo);

        // tree_id for redirect
        $treeId = $photo->tree_id;

        // Resolve relative path from URL, e.g. "/storage/tree-photos/xxx.jpg"
        $path = null;
        if ($photo->url) {
            $path = ltrim(str_replace('/storage/', '', parse_url($photo->url, PHP_URL_PATH)), '/');
        }

        // Delete file first (if we know where it is and it exists)
        if ($path && Storage::disk('public')->exists($path)) {
            try {
                $deleted = Storage::disk('public')->delete($path);

                if (! $deleted) {
                    // Couldn’t delete file: keep DB row, report error
                    Log::warning('Failed to delete photo file (delete() returned false)', [
                        'path' => $path,
                        'photo_id' => $photo->id,
                    ]);

                    return back()->with('message', [
                        'type' => 'error',
                        'message' => __('Could not delete the photo file. Please try again later.'),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to delete photo file (exception)', [
                    'path'  => $path,
                    'photo_id' => $photo->id,
                    'error' => $e->getMessage(),
                ]);

                return back()->with('message', [
                    'type' => 'error',
                    'message' => __('Could not delete the photo file. Please try again later.'),
                ]);
            }
        }

        // Delete from db
        try {
            $photo->delete();
        } catch (\Throwable $e) {
            Log::error('Failed to delete photo DB record', [
                'photo_id' => $photo->id,
                'error'    => $e->getMessage(),
            ]);

            return back()->with('message', [
                'type' => 'error',
                'message' => __('Could not delete the photo from the database.'),
            ]);
        }

        //  Success
        return redirect()
            ->route('photos.index', ['tree_id' => $treeId])
            ->with('message', [
                'type' => 'success',
                'message' => __('Photo has been deleted.'),
            ]);
    }



    public function massDestroy(Request $request): RedirectResponse
    {
        // Extract photo IDs (array of IDs)
        $ids = collect($request->input('photos'))
            ->filter()
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No photos selected.'),
            ]);

            return back();
        }

        // Fetch photos
        $photos = Photo::whereIn('id', $ids)->get();

        if ($photos->isEmpty()) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('Selected photos do not exist.'),
            ]);

            return back();
        }

        // DB deletion INSIDE transaction 
        DB::transaction(function () use ($photos) {
            foreach ($photos as $photo) {
                $this->authorize('delete', $photo);
                $photo->delete();  // delete row, but keep file path for later
            }
        });

        // File deletion OUTSIDE transaction 
        foreach ($photos as $photo) {
            if ($photo->url) {
                $path = ltrim(str_replace('/storage/', '', parse_url($photo->url, PHP_URL_PATH)), '/');

                try {
                    Storage::disk('public')->delete($path);
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete photo file (massDestroy)', [
                        'path'     => $path,
                        'photo_id' => $photo->id,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }
        }

        //  Flash & redirect 
        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Selected photos have been deleted.'),
        ]);

        return back();
    }
}
