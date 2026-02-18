<?php

namespace App\Http\Controllers;

use App\Services\UserEventsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserEventsController extends Controller
{
    public function index(Request $request, UserEventsService $svc): Response
    {
        $events = $svc->forUser($request->user()->id, now());

        return Inertia::render('User/Events/Index', [
            'events' => $events,
        ]);
    }
}
