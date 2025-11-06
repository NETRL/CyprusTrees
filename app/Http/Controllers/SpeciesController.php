<?php

namespace App\Http\Controllers;

use App\Models\Species;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpeciesController extends Controller
{
    public function index(): Response{
        return Inertia::render('Tree/Species/Index', [
            'species' => Species::query()->paginate(15),
        ]);
    }
}
