<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            $request->session()->flash('message', [
                'type'    => 'error',
                'message' => __('Could not update address due to an error.'),
            ]);
            Log::error('Could not update address due to an error.', $e);
            return back();
        }

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Address has been updated.'),
        ]);
        return back();
    }
}
