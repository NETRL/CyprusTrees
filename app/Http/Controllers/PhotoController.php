<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Tree;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Photo::class);

        $perPage = $request->integer('per_page', 10);

        if ($request->filled('tree_id')) {
            $treeId = (int) $request->input('tree_id');
        } else {
            $treeId = null;
        }
        $tableData = null;
        $tree      = null;

        if ($treeId) {
            $query = Photo::query()
                ->where('tree_id', $treeId)
                ->setUpQuery();

            $tableData = $query->paginate($perPage)->withQueryString();
            $tree      = Tree::find($treeId);
        }

        return Inertia::render('Photo/Index', [
            'tableData'      => $tableData,   // null if no tree chosen yet
            'selectedTree'   => $tree,
            'initialTreeId'  => $treeId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tree_id' => ['required', 'exists:trees,id'],
            'caption' => ['nullable', 'string', 'max:255'],
            'source'  => ['nullable', 'in:camera,upload'],
            'photos'  => ['required', 'array'],
            'photos.*' => ['image'], // max:5120
        ]);

        $treeId = $validated['tree_id'];
        $caption = $validated['caption'] ?? null;
        $source = $validated['source'] ?? 'upload';

        foreach ($validated['photos'] as $file) {
            // Store file under public disk
            $path = $file->store('tree-photos', 'public');
            $url  = Storage::disk('public')->url($path);

            // Try to read EXIF datetime
            $capturedAt = null;
            try {
                $exif = @exif_read_data($file->getPathname());
                if (!empty($exif['DateTimeOriginal'])) {
                    $capturedAt = Carbon::createFromFormat('Y:m:d H:i:s', $exif['DateTimeOriginal']);
                }
            } catch (\Throwable $e) {
                // silently ignore if EXIF missing/invalid
            }

            Photo::create([
                'tree_id'     => $treeId,
                'caption'     => $caption,     // same caption for all, or null
                'url'         => $url,
                'captured_at' => $capturedAt,
                'source'      => $source,
            ]);
        }

        return back()->with('message', [
            'type'    => 'success',
            'message' => __('Photos uploaded successfully.'),
        ]);
    }

    public function update(Request $request, Photo $photo)
    {
        $validated = $request->validate([
            'caption' => ['nullable', 'string', 'max:255'],
            'photo'   => ['nullable', 'image', 'max:5120'],
            'source'  => ['nullable', 'in:camera,upload'],
            'captured_at' => ['nullable', 'date'],
        ]);


        // Keep old path (if any) so we can delete it *after* new upload
        $oldPath = null;
        if ($photo->url) {
            // "/storage/tree-photos/xxx.jpg" → "tree-photos/xxx.jpg"
            $oldPath = ltrim(str_replace('/storage/', '', parse_url($photo->url, PHP_URL_PATH)), '/');
        }
        // If a new file is uploaded, store it first
        if ($request->hasFile('photo')) {
            $file = $validated['photo'];
            // Store new file
            $newPath = $file->store('tree-photos', 'public');
            $newUrl  = Storage::disk('public')->url($newPath);

            // Extract EXIF date if possible
            $capturedAt = $photo->captured_at; // fallback to existing
            try {
                $exif = @exif_read_data($file->getPathname());
                if (!empty($exif['DateTimeOriginal'])) {
                    $capturedAt = Carbon::createFromFormat('Y:m:d H:i:s', $exif['DateTimeOriginal']);
                }
            } catch (\Throwable $e) {
            }

            // Update model to new file
            $photo->url         = $newUrl;
            $photo->captured_at = $capturedAt;
        }

        // Other fields
        if (array_key_exists('caption', $validated)) {
            $photo->caption = $validated['caption'];
        }
        if (!empty($validated['source'])) {
            $photo->source = $validated['source'];
        }
        if (!empty($validated['captured_at'])) {
            // allow manual override
            $photo->captured_at = $validated['captured_at'];
        }

        $photo->save();

        // Now that everything succeeded, delete old file (if any)
        if ($oldPath) {
            try {
                Storage::disk('public')->delete($oldPath);
            } catch (\Throwable $e) {
                // log this, but don't break the request
                Log::warning('Failed to delete old photo', ['path' => $oldPath, 'error' => $e->getMessage()]);
            }
        }

        return back()->with('message', [
            'type' => 'success',
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
