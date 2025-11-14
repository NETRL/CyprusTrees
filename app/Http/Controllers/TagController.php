<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TagController extends Controller
{

    public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(Tag::class, 'tag');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Tag::class);

        $perPage = $request->integer('per_page', 10);

        $query = Tag::query()
            ->withCount('trees')   // needed to show `trees_count`
            ->setUpQuery();        // this applies search + sort based on request params

        return Inertia::render('Tag/Index', [
            'tableData' => $query->paginate($perPage)->withQueryString(),
            'dataColumns' => Tag::getDataColumns(),
        ]);
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name" => 'required|string',
        ]);

        Tag::create($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Tag has been created.'),
        ]);

        return redirect()->route('tags.index');
    }


    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validate([
            "name" => 'required|string',
        ]);

        $tag->update($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Tag has been updated.'),
        ]);

        return redirect()->route('tags.index');
    }


    public function destroy(Request $request, Tag $tag): RedirectResponse
    {

        // 1. Authorize 
        $this->authorize('delete', $tag);

        // 2. Delete
        $tag->delete();

        // 3. Flash
        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('Tag has been deleted.')
        ]);

        return redirect()->route('tags.index');
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        // Extract the IDs from the incoming payload
        $ids = collect($request->input('tags'))
            ->pluck('id')
            ->filter()        // remove nulls just in case
            ->all();

        if (empty($ids)) {
            $request->session()->flash('message', [
                'type'    => 'info',
                'message' => __('No tree tag selected.'),
            ]);

            return redirect()->route('tags.index');
        }

        DB::transaction(function () use ($ids) {
            // Load the models we’re going to operate on
            $tagList = Tag::whereIn('id', $ids)->get();

            // Optional sanity check: if some IDs were not found
            // decide what to do (ignore, error, etc.)
            // if (count($tagList) !== count($ids)) {
            //     throw new \RuntimeException('Some selected tree tags do not exist.');
            // }

            foreach ($tagList as $tag) {
                $this->authorize('delete', $tag);
                $tag->delete();
            }
        });

        $request->session()->flash('message', [
            'type'    => 'success', // error, success, info
            'message' => __('Tree tags have been deleted.'),
        ]);

        return redirect()->route('tags.index');
    }
}
