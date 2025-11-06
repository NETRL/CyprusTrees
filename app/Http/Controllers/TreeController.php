<?php

namespace App\Http\Controllers;

use App\Models\Tree;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TreeController extends Controller
{

    public function __construct()
    {
        // automatically applies policy to all resource methods
        $this->authorizeResource(Tree::class, 'tree');
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Tree::class);

        $perPage = $request->integer('per_page', 25); // default 25

        return Inertia::render('Tree/Index', [
            'trees' => Tree::query()
                ->paginate($perPage)
                ->withQueryString(), // keep page/per_page in URL
        ]);
    }

    public function destroy(Request $request, Tree $tree): RedirectResponse
    {

        $tree->delete();

        $request->session()->flash('message', [
            'type' => 'success',
            'message' => __('Tree has been deleted.')
        ]);

        return redirect()->route('trees.index');
    }
}
