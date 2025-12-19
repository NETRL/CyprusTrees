<?php

namespace App\Http\Controllers;

use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeTimezoneController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'timezone' => ['required', 'string', 'max:64'],
        ]);

        // strict validation against PHP timezone identifiers
        if (!in_array($validated['timezone'], DateTimeZone::listIdentifiers(), true)) {
            return response()->json(['message' => 'Invalid timezone'], 422);
        }

        $request->user()->update([
            'timezone' => $validated['timezone'],
        ]);

        return response()->noContent();
    }
}
