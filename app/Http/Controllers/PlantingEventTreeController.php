<?php

namespace App\Http\Controllers;

use App\Enums\PlantingEventStatus;
use App\Models\PlantingEvent;
use App\Models\PlantingEventTree;
use App\Models\Tree;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class PlantingEventTreeController extends Controller
{

    public function store(Request $request, PlantingEvent $plantingEvent)
    {

        // Prevent attaching trees to cancelled events
        if ($plantingEvent->status === PlantingEventStatus::CANCELLED->value) {
            abort(409, 'Cannot add trees to a cancelled planting event.');
        }

        $validated = $request->validate([
            'tree_id'          => 'required|integer|exists:trees,id',
            'planted_by'       => 'nullable|integer|exists:users,id',
            'planted_at'       => 'nullable|date',
            'planting_method'  => 'nullable|string|max:60',
            'notes'            => 'nullable|string|max:5000',
        ]);

        // Prevent duplicates (unique constraint exists, but fail gracefully)
        $exists = PlantingEventTree::query()
            ->where('planting_id', $plantingEvent->planting_id)
            ->where('tree_id', $validated['tree_id'])
            ->exists();

        if ($exists) {
            abort(409, 'This tree is already attached to the planting event.');
        }

        $item = PlantingEventTree::create([
            'planting_id'     => $plantingEvent->planting_id,
            'tree_id'         => $validated['tree_id'],
            'planted_by'      => $validated['planted_by'] ?? $request->user()->id,
            'planted_at'      => $validated['planted_at'] ?? now(),
            'planting_method' => $validated['planting_method'] ?? null,
            'notes'           => $validated['notes'] ?? null,
        ]);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item has been created.'),
        ]);

        return redirect()->back();
    }

    public function update(Request $request, PlantingEventTree $plantingEventTree)
    {
        // $this->authorize('update', $plantingEventTree->plantingEvent);

        $validated = $request->validate([
            'planted_by'       => 'nullable|integer|exists:users,id',
            'planted_at'       => 'nullable|date',
            'planting_method'  => 'nullable|string|max:60',
            'notes'            => 'nullable|string|max:5000',
        ]);

        $plantingEventTree->update($validated);

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item has been updated.'),
        ]);

        return redirect()->back();
    }

    public function destroy(Request $request, PlantingEventTree $plantingEventTree)
    {
        // $this->authorize('delete', $plantingEventTree->plantingEvent);

        // Optional safety: block removal from completed events unless admin
        if (
            $plantingEventTree->plantingEvent->status === PlantingEventStatus::COMPLETED->value &&
            ! $request->user()->can('forceDelete', $plantingEventTree)
        ) {
            abort(403, 'Cannot remove trees from a completed planting event.');
        }

        $plantingEventTree->delete();

        $request->session()->flash('message', [
            'type'    => 'success',
            'message' => __('Item has been deleted.'),
        ]);

        return redirect()->back();
    }
}
