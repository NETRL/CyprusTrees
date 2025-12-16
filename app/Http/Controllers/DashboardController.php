<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
  public function index(Request $request)
  {
    $filters = $this->normalizeFilters($request);

    return Inertia::render('Dashboard', [
      'snapshot' => $this->buildSnapshotKpis(),            // NOT date filtered
      'activity' => $this->buildActivityKpis($filters),    // date filtered
      'filters'  => $filters,
    ]);
  }

  private function normalizeFilters(Request $request): array
  {
    // months: 0=all time else 3..36
    $months = (int) $request->integer('months', 12);
    $months = max(0, min($months, 36));

    // internal control for neighborhood chart readability
    $topN = (int) $request->integer('topN', 10);
    $topN = max(3, min($topN, 25));

    [$start, $end] = $this->computeRange($months);

    return [
      'months'    => $months,
      'topN'      => $topN,
      'startDate' => $start->toDateString(),
      'endDate'   => $end->toDateString(),
    ];
  }

  private function computeRange(int $months): array
  {
    if ($months === 0) {
      $minCreated = DB::table('citizen_reports')->whereNotNull('created_at')->min('created_at');
      $minMaint   = DB::table('maintenance_events')->whereNotNull('performed_at')->min('performed_at');
      $minAssess  = DB::table('health_assessments')->whereNotNull('assessed_at')->min('assessed_at');

      $mins = array_filter([$minCreated, $minMaint, $minAssess]);
      $min  = $mins ? min($mins) : now()->subMonths(11)->startOfMonth();

      return [Carbon::parse($min)->startOfMonth(), now()->endOfMonth()];
    }

    return [now()->subMonths($months - 1)->startOfMonth(), now()->endOfMonth()];
  }

  /** --------------------------
   * Snapshot KPIs
   * ------------------------- */

  private function buildSnapshotKpis(): array
  {
    // Cache snapshot separately (changes less frequently)
    return Cache::remember('dash:snapshot:v1', now()->addMinutes(30), function () {
      return [
        'owner_distribution'     => $this->kpiOwnerDistributionSnapshot(),
        'dbh_distribution'       => $this->kpiDbhDistributionSnapshot(),
        'health_by_neighborhood' => $this->kpiHealthByNeighborhoodSnapshot(10),
        'co2'                    => $this->kpiCo2Snapshot(),
        'co2_by_neighborhood'    => $this->kpiCo2ByNeighborhood(12),
        'co2_by_dbh_bins'        => $this->kpiCo2ByDbhBins(),
      ];
    });
  }

  private function kpiOwnerDistributionSnapshot(): array
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

  private function kpiDbhDistributionSnapshot(): array
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
      'xAxisData'  => array_map(fn($r) => $r->name, $rows),
      'seriesData' => array_map(fn($r) => (int)$r->value, $rows),
    ];
  }

  private function kpiHealthByNeighborhoodSnapshot(int $topN): array
  {
    $statusOrder = ['excellent', 'good', 'fair', 'poor', 'critical', 'dead', 'unknown'];
    $statusLabels = [
      'excellent' => 'Excellent',
      'good'      => 'Good',
      'fair'      => 'Fair',
      'poor'      => 'Poor',
      'critical'  => 'Critical',
      'dead'      => 'Dead',
      'unknown'   => 'Unknown',
    ];

    $rows = DB::select("
            WITH base AS (
              SELECT
                COALESCE(n.name, 'Unassigned') AS neighborhood,
                COALESCE(t.health_status, 'unknown') AS hs
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
            SELECT neighborhood, hs, COUNT(*)::int AS c
            FROM base
            WHERE neighborhood IN (SELECT neighborhood FROM top_nb)
            GROUP BY 1,2
            ORDER BY neighborhood, hs
        ", [$topN]);

    $neighborhoods = [];
    $index = [];
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
      $series[$r->hs][$i] = (int)$r->c;
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

  private function kpiCo2Snapshot(): array
  {
    $row = DB::selectOne("
        SELECT
          SUM(
            0.15 * POWER(dbh_cm, 2.4) * 0.5 * 3.67 *
            CASE
              WHEN EXTRACT(YEAR FROM age(now(), planted_at)) < 5  THEN 0.08
              WHEN EXTRACT(YEAR FROM age(now(), planted_at)) < 20 THEN 0.04
              ELSE 0.015
            END
          ) AS annual_co2_kg,
          COUNT(*) FILTER (WHERE dbh_cm IS NOT NULL AND planted_at IS NOT NULL) AS eligible,
          COUNT(*) AS total
        FROM trees
    ");

    $annualKg = (float) ($row->annual_co2_kg ?? 0);
    $eligible = (int) $row->eligible;
    $total    = (int) $row->total;

    return [
      'annual_co2_kg' => round($annualKg, 2),
      'annual_co2_t'  => round($annualKg / 1000, 2),
      'coverage_pct'  => $total > 0 ? round(($eligible / $total) * 100, 1) : 0,
      'eligible'      => $eligible,
      'total'         => $total,
    ];
  }

  private function kpiCo2ByNeighborhood(int $topN = 12): array
  {
    $rows = DB::select("
        SELECT
          COALESCE(n.name, 'Unassigned') AS neighborhood,
          (
            SUM(
              0.15 * POWER(t.dbh_cm, 2.4) * 0.5 * 3.67 *
              CASE
                WHEN EXTRACT(YEAR FROM age(now(), t.planted_at)) < 5  THEN 0.08
                WHEN EXTRACT(YEAR FROM age(now(), t.planted_at)) < 20 THEN 0.04
                ELSE 0.015
              END
            ) / 1000.0
          ) AS co2_t_per_year
        FROM trees t
        LEFT JOIN neighborhoods n ON n.id = t.neighborhood_id
        WHERE t.dbh_cm IS NOT NULL AND t.planted_at IS NOT NULL
        GROUP BY 1
        ORDER BY co2_t_per_year DESC
        LIMIT ?
    ", [$topN]);

    return [
      'xAxisData'  => array_map(fn($r) => $r->neighborhood, $rows),
      'seriesData' => array_map(fn($r) => round((float)$r->co2_t_per_year, 2), $rows),
    ];
  }

  private function kpiCo2ByDbhBins(): array
  {
    $rows = DB::select("
        WITH base AS (
          SELECT
            CASE
              WHEN dbh_cm IS NULL THEN 'Unknown'
              WHEN dbh_cm < 10 THEN '<10'
              WHEN dbh_cm < 25 THEN '10–25'
              WHEN dbh_cm < 50 THEN '25–50'
              WHEN dbh_cm < 80 THEN '50–80'
              ELSE '>80'
            END AS bucket,
            (
              0.15 * POWER(dbh_cm, 2.4) * 0.5 * 3.67 *
              CASE
                WHEN EXTRACT(YEAR FROM age(now(), planted_at)) < 5  THEN 0.08
                WHEN EXTRACT(YEAR FROM age(now(), planted_at)) < 20 THEN 0.04
                ELSE 0.015
              END
            ) AS annual_co2_kg
          FROM trees
          WHERE dbh_cm IS NOT NULL AND planted_at IS NOT NULL
        )
        SELECT
          bucket,
          ROUND(SUM(annual_co2_kg) / 1000.0, 2) AS co2_t_per_year
        FROM base
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
      'xAxisData'  => array_map(fn($r) => $r->bucket, $rows),
      'seriesData' => array_map(fn($r) => (float)$r->co2_t_per_year, $rows),
    ];
  }




  /** --------------------------
   * Activity KPIs (date-ranged)
   * ------------------------- */

  private function buildActivityKpis(array $filters): array
  {
    $cacheKey = 'dash:activity:v1:' . md5(json_encode($filters));

    return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($filters) {
      $start = Carbon::parse($filters['startDate'])->startOfDay();
      $end   = Carbon::parse($filters['endDate'])->endOfDay();

      return [
        'reports_timeline' => $this->kpiReportsTimelineNoZeroMonths($start, $end),
        'maintenance_summary'   => $this->kpiMaintenanceSummary($start, $end),
        'maintenance_timeline'  => $this->kpiMaintenanceTimeline($start, $end),
        'maintenance_by_type'   => $this->kpiMaintenanceByType($start, $end),
        'co2_yearly'       => $this->kpiCo2YearlyComparison(6),
      ];
    });
  }

  // No padded months; only months that have created/resolved > 0
  private function kpiReportsTimelineNoZeroMonths(Carbon $start, Carbon $end): array
  {
    $rows = DB::select("
            WITH created AS (
              SELECT date_trunc('month', created_at)::date AS m,
                     COUNT(*)::int AS created_count
              FROM citizen_reports
              WHERE created_at BETWEEN ? AND ?
              GROUP BY 1
            ),
            resolved AS (
              SELECT date_trunc('month', resolved_at)::date AS m,
                     COUNT(*)::int AS resolved_count
              FROM citizen_reports
              WHERE status = 'resolved'
                AND resolved_at IS NOT NULL
                AND resolved_at BETWEEN ? AND ?
              GROUP BY 1
            )
            SELECT
              COALESCE(c.m, r.m) AS m,
              COALESCE(c.created_count, 0)  AS created_count,
              COALESCE(r.resolved_count, 0) AS resolved_count
            FROM created c
            FULL OUTER JOIN resolved r ON r.m = c.m
            ORDER BY m
        ", [$start, $end, $start, $end]);

    $xAxis = [];
    $created = [];
    $resolved = [];

    foreach ($rows as $r) {
      $xAxis[]    = date('Y-m', strtotime($r->m));
      $created[]  = (int)$r->created_count;
      $resolved[] = (int)$r->resolved_count;
    }

    return [
      'xAxisData' => $xAxis,
      'seriesData' => [
        ['name' => 'New Reports', 'data' => $created],
        ['name' => 'Resolved',    'data' => $resolved],
      ],
    ];
  }

  private function kpiMaintenanceSummary(Carbon $start, Carbon $end): array
  {
    $startAt = $start->copy()->startOfMonth();
    $endEx   = $end->copy()->endOfDay()->addSecond();

    $row = DB::selectOne("
        SELECT
          COUNT(*)::int                            AS events,
          COUNT(DISTINCT tree_id)::int             AS trees,
          COALESCE(SUM(cost),0)::numeric(10,2)     AS total_cost
        FROM maintenance_events
        WHERE performed_at BETWEEN ? AND ?
    ", [$startAt->toDateTimeString(), $endEx->toDateTimeString()]);

    $events = (int) $row->events;
    $totalCost = (float) $row->total_cost;

    return [
      'events'          => $events,
      'trees'           => (int) $row->trees,
      'total_cost'      => $totalCost,
      'avg_cost_event'  => $events > 0 ? round($totalCost / $events, 2) : 0,
    ];
  }

  private function kpiMaintenanceTimeline(Carbon $start, Carbon $end): array
  {

    $startAt = $start->copy()->startOfMonth();
    $endEx   = $end->copy()->endOfMonth()->addDay()->startOfDay(); // month-safe end-exclusive

    $rows = DB::select("
        SELECT
          date_trunc('month', performed_at)::date AS m,
          COUNT(*)::int                           AS events,
          COALESCE(SUM(cost),0)::numeric(10,2)    AS cost
        FROM maintenance_events
        WHERE performed_at BETWEEN ? AND ?
        GROUP BY 1
        ORDER BY m
    ", [$startAt->toDateTimeString(), $endEx->toDateTimeString()]);

    $xAxis = [];
    $events = [];
    $costs = [];

    foreach ($rows as $r) {
      $xAxis[]  = date('Y-m', strtotime($r->m));
      $events[] = (int) $r->events;
      $costs[]  = (float) $r->cost;
    }

    return [
      'xAxisData' => $xAxis,
      'seriesData' => [
        ['name' => 'Events', 'data' => $events],
        ['name' => 'Cost',   'data' => $costs],
      ],
    ];
  }

  private function kpiMaintenanceByType(Carbon $start, Carbon $end): array
  {

    $startAt = $start->copy()->startOfMonth();
    $endEx   = $end->copy()->endOfMonth()->addDay()->startOfDay();

    $rows = DB::select("
        SELECT
          mt.name,
          COUNT(*)::int                        AS events,
          COALESCE(SUM(me.cost),0)::numeric    AS cost
        FROM maintenance_events me
        JOIN maintenance_types mt ON mt.type_id = me.type_id
        WHERE me.performed_at BETWEEN ? AND ?
        GROUP BY 1
        ORDER BY events DESC
    ", [$startAt->toDateTimeString(), $endEx->toDateTimeString()]);

    return [
      'xAxisData' => array_map(fn($r) => $r->name, $rows),
      'seriesData' => [
        [
          'name' => 'Events',
          'data' => array_map(fn($r) => (int)$r->events, $rows),
        ],
      ],
    ];
  }

  private function kpiCo2YearlyComparison(int $years = 6): array
  {
    $years = max(2, min($years, 30)); // safety

    $rows = DB::select("
        WITH years AS (
          SELECT generate_series(
            date_trunc('year', now()) - make_interval(years => ?),
            date_trunc('year', now()),
            interval '1 year'
          )::date AS y
        )
        SELECT
          EXTRACT(YEAR FROM y.y)::int AS year,
          SUM(
            0.15 * POWER(t.dbh_cm, 2.4) * 0.5 * 3.67 *
            CASE
              WHEN EXTRACT(YEAR FROM age(y.y, t.planted_at)) < 5  THEN 0.08
              WHEN EXTRACT(YEAR FROM age(y.y, t.planted_at)) < 20 THEN 0.04
              ELSE 0.015
            END
          ) AS annual_co2_kg
        FROM years y
        JOIN trees t
          ON t.dbh_cm IS NOT NULL
         AND t.planted_at IS NOT NULL
         AND t.planted_at <= y.y
        GROUP BY year
        ORDER BY year
    ", [$years - 1]);

    $xAxis = [];
    $valuesTons = [];

    foreach ($rows as $r) {
      $xAxis[] = (string) $r->year;
      $valuesTons[] = round(((float) $r->annual_co2_kg) / 1000, 2); // tons/year
    }

    $mean = count($valuesTons)
      ? round(array_sum($valuesTons) / count($valuesTons), 2)
      : 0;

    return [
      'xAxisData' => $xAxis,
      'seriesData' => [
        ['name' => 'Estimated CO₂ (t/year)', 'data' => $valuesTons],
        ['name' => 'Mean', 'data' => array_fill(0, count($valuesTons), $mean)],
      ],
      'mean' => $mean,
    ];
  }




  public function methodology()
  {
    // Plain text / markdown; easy to export later as PDF
    $text = $this->co2MethodologyText();

    // If you want download:
    // return response($text)
    //   ->header('Content-Type', 'text/markdown; charset=UTF-8')
    //   ->header('Content-Disposition', 'attachment; filename="methodology-co2.md"');

    return response($text)->header('Content-Type', 'text/plain; charset=UTF-8');
  }

  private function co2MethodologyText(): string
  {
    return <<<TXT
CO₂ ESTIMATION METHODOLOGY (Urban Trees Dashboard)

Metric name:
- Estimated annual CO₂ sequestration (kg CO₂ / year)

Scope:
- Values are model-based estimates derived from available tree attributes.
- This is NOT a verified carbon offset and should not be used to claim neutrality.

Eligibility:
- A tree is included only if:
  - DBH (dbh_cm) is present
  - planted_at is present
- Dashboard displays coverage: eligible trees / total trees.

Model:
1) Above-ground biomass (AGB, kg)
   AGB = 0.15 × DBH_cm ^ 2.4

2) Carbon content (kg C)
   C = AGB × 0.5

3) CO₂ equivalent (kg CO₂)
   CO₂_total = C × 3.67

4) Annual sequestration factor (by tree age)
   age = years between planted_at and the evaluation date
   growth_factor:
   - age < 5 years   : 0.08
   - 5–19 years      : 0.04
   - age >= 20 years : 0.015

Annual CO₂:
   Annual_CO₂ = CO₂_total × growth_factor

Aggregation:
- Neighborhood and DBH-bin charts sum Annual_CO₂ across eligible trees.

Limitations:
- Does not use species-specific wood density or height-based allometry.
- Assumes planted_at approximates age (may be inaccurate for legacy trees).
- Represents an estimate under average conditions; real sequestration varies by health, climate, pruning, mortality, and site conditions.
TXT;
  }
}
