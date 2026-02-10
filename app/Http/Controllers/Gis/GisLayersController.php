<?php

namespace App\Http\Controllers\Gis;

use App\Http\Controllers\Controller;
use App\Models\GisLayer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GisLayersController extends Controller
{
    public function index(Request $request)
    {
        $selectedLayerId = $request->integer('layer_id');

        $layers = GisLayer::query()
            ->orderBy('display_name')
            ->get(['id', 'key', 'name', 'display_name', 'category', 'is_active', 'default_import_mode', 'active_revision_id', 'metadata']);

        $selectedLayer = null;
        $revisions = [];

        if ($selectedLayerId) {
            $selectedLayer = GisLayer::query()
                ->with(['activeRevision:id,layer_id,label,status,is_included,created_at',])
                ->findOrFail($selectedLayerId);

            $revisions = $selectedLayer->revisions()
                ->orderByDesc('created_at')
                ->limit(50)
                ->get([
                    'id',
                    'layer_id',
                    'revision_no',
                    'label',
                    'import_mode',
                    'status',
                    'is_included',
                    'original_name',
                    'features_imported',
                    'feature_counts',
                    'error',
                    'created_at',
                    'archived_at'
                ]);
        }

        return Inertia::render('Gis/DataImport/Index', [
            'layers' => $layers,
            'selectedLayerId' => $selectedLayerId,
            'selectedLayer' => $selectedLayer,
            'revisions' => $revisions,
        ]);
    }
}
