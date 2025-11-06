<?php

namespace App\Http\Controllers;

use App\Models\Tree;
use App\Models\TreeTag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TreeTagController extends Controller
{
    public function index(): Response{
        return Inertia::render('Tree/Tag/Index', [
            'treeTags' => TreeTag::query()->paginate(15),
        ]);
    }
}
