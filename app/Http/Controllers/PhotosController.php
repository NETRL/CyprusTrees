<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PhotosController extends Controller
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

        $query = Photo::query()
            ->setUpQuery();

        return Inertia::render('Photo/Index', [
            'tableData' => $query->paginate($perPage)->withQueryString(),
            'dataColumns' => Photo::getDataColumns(),
        ]);
    }
}
