<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Tree;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
        $treeId  = $request->integer('tree_id');

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
}
