<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Initial page load via Inertia
        $kpis = $this->buildKpis($request);

        return Inertia::render('Dashboard', [
            'kpis' => $kpis,
            'filters' => $this->normalizeFilters($request),
        ]);
    }

    public function kpis(Request $request)
    {
        // JSON endpoint for refetching KPIs on filter changes
        return response()->json([
            'kpis' => $this->buildKpis($request),
            'filters' => $this->normalizeFilters($request),
        ]);
    }

    private function normalizeFilters(Request $request): array
    {
        // v1: last N months + topN neighborhoods
        $months = (int) $request->integer('months', 12);
        $months = max(1, min($months, 36));

        $topN = (int) $request->integer('topN', 10);
        $topN = max(3, min($topN, 25));

        return [
            'months' => $months,
            'topN'   => $topN,
        ];
    }

    private function buildKpis(Request $request): array
    {
        $filters = $this->normalizeFilters($request);

        // Cache per filter set (and per-tenant for multi-tenant)
        $cacheKey = 'dash:kpis:' . md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($filters) {
            return [
                'owner_distribution'     => $this->kpiOwnerDistribution(),
                'reports_timeline'       => $this->kpiReportsTimeline($filters['months']),
                'dbh_distribution'       => $this->kpiDbhDistribution(),
                'health_by_neighborhood' => $this->kpiHealthByNeighborhood($filters['topN']),
            ];
        });
    }

    private function kpiOwnerDistribution(): array
    {
        $rows = DB::select("
            SELECT
              CASE owner_type
                WHEN 'public'  THEN 'Public'
                WHEN 'private' THEN 'Private'
                ELSE 'Unknown'
              END AS name,
              COUNT(*)::int AS value
            FROM trees
            GROUP BY 1
            ORDER BY value DESC
        ");

        return array_map(fn($r) => ['name' => $r->name, 'value' => (int)$r->value], $rows);
    }

    private function kpiReportsTimeline(int $months): array
    {
        $rows = DB::select("
            WITH months AS (
              SELECT date_trunc('month', d)::date AS m
              FROM generate_series(
                date_trunc('month', now()) - (? - 1) * interval '1 month',
                date_trunc('month', now()),
                interval '1 month'
              ) d
            ),
            created AS (
              SELECT date_trunc('month', created_at)::date AS m, COUNT(*)::int AS c
              FROM citizen_reports
              GROUP BY 1
            ),
            resolved AS (
              SELECT date_trunc('month', resolved_at)::date AS m, COUNT(*)::int AS c
              FROM citizen_reports
              WHERE status = 'resolved' AND resolved_at IS NOT NULL
              GROUP BY 1
            )
            SELECT
              months.m,
              COALESCE(created.c,0)  AS created_count,
              COALESCE(resolved.c,0) AS resolved_count
            FROM months
            LEFT JOIN created USING (m)
            LEFT JOIN resolved USING (m)
            ORDER BY months.m
        ", [$months]);

        $xAxis = [];
        $created = [];
        $resolved = [];

        foreach ($rows as $r) {
            $xAxis[] = date('Y-m', strtotime($r->m));
            $created[] = (int) $r->created_count;
            $resolved[] = (int) $r->resolved_count;
        }

        return [
            'xAxisData' => $xAxis,
            'seriesData' => [
                ['name' => 'New Reports', 'data' => $created],
                ['name' => 'Resolved',    'data' => $resolved],
            ],
        ];
    }

    private function kpiDbhDistribution(): array
    {
        $rows = DB::select("
            SELECT bucket AS name, COUNT(*)::int AS value
            FROM (
              SELECT CASE
                WHEN dbh_cm IS NULL THEN 'Unknown'
                WHEN dbh_cm < 10 THEN '<10'
                WHEN dbh_cm < 25 THEN '10–25'
                WHEN dbh_cm < 50 THEN '25–50'
                WHEN dbh_cm < 80 THEN '50–80'
                ELSE '>80'
              END AS bucket
              FROM trees
            ) x
            GROUP BY 1
            ORDER BY
              CASE bucket
                WHEN '<10' THEN 1
                WHEN '10–25' THEN 2
                WHEN '25–50' THEN 3
                WHEN '50–80' THEN 4
                WHEN '>80' THEN 5
                ELSE 99
              END
        ");

        return [
            'xAxisData' => array_map(fn($r) => $r->name, $rows),
            'seriesData' => array_map(fn($r) => (int)$r->value, $rows),
        ];
    }

    private function kpiHealthByNeighborhood(int $topN): array
    {
        // fixed order & labels from enum
        $statusOrder = ['excellent', 'good', 'fair', 'poor', 'critical', 'dead'];
        $statusLabels = [
            'excellent' => 'Excellent',
            'good'      => 'Good',
            'fair'      => 'Fair',
            'poor'      => 'Poor',
            'critical'  => 'Critical',
            'dead'      => 'Dead',
        ];

        // Top N neighborhoods by at-risk count (poor/critical/dead), then by total
        $rows = DB::select("
    WITH base AS (
      SELECT
        COALESCE(n.name, 'Unassigned') AS neighborhood,
        COALESCE(t.health_status, 'fair') AS hs
      FROM trees t
      LEFT JOIN neighborhoods n ON n.id = t.neighborhood_id
    ),
    top_nb AS (
      SELECT neighborhood
      FROM base
      GROUP BY neighborhood
      ORDER BY
        SUM(CASE WHEN hs IN ('poor','critical','dead') THEN 1 ELSE 0 END) DESC,
        COUNT(*) DESC
      LIMIT ?
    )
    SELECT
      neighborhood,
      hs,
      COUNT(*)::int AS c
    FROM base
    WHERE neighborhood IN (SELECT neighborhood FROM top_nb)
    GROUP BY 1,2
    ORDER BY neighborhood, hs
", [$topN]);


        // Pivot server-side to your stacked bar contract
        $neighborhoods = [];
        $index = [];

        // collect neighborhoods in stable order (as returned)
        foreach ($rows as $r) {
            if (!isset($index[$r->neighborhood])) {
                $index[$r->neighborhood] = count($neighborhoods);
                $neighborhoods[] = $r->neighborhood;
            }
        }

        $series = [];
        foreach ($statusOrder as $k) {
            $series[$k] = array_fill(0, count($neighborhoods), 0);
        }

        foreach ($rows as $r) {
            if (!isset($series[$r->hs])) continue;
            $i = $index[$r->neighborhood] ?? null;
            if ($i === null) continue;
            $series[$r->hs][$i] = (int) $r->c;
        }

        return [
            'xAxisData' => $neighborhoods,
            'seriesData' => array_map(fn($k) => [
                'key'  => $k,
                'name' => $statusLabels[$k] ?? $k,
                'data' => $series[$k],
            ], $statusOrder),
        ];
    }
}
