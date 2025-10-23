<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    public function update(Request $request)
    {

        $validated = $request->validate([
            'country' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
        ]);

        $user = $request->user();

        try {
            if ($user->address) {
                $user->address->update($validated);
            } else {
                $user->address()->create($validated);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Could not update address due to an error.')->withInput();
        }
        return back()->with('success', 'Your address has been updated successfully!');
    }
}
