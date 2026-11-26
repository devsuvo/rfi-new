<?php

namespace App\Support;

use App\Models\TestTime;
use App\Models\User;

/**
 * Time limits for a test.
 *   Default: each reading exercise gets 2 min per question (ReadingSheet), a @fulltest or a writing test one 60-min timer.
 *   A teacher can set their own minutes per part for a batch; students of that batch get those.
 */
class Timing
{
    public const FULL_TEST_MINUTES = 60;

    /**
     * Parts of a test with their default minutes.
     * @return array<string, array{name: string, minutes: int, count: int|null}>  part => info ("1", "2", … or "all")
     */
    public static function parts(string $module, string $slug): array
    {
        if ($module === 'reading') {
            $file = resource_path("reading/{$slug}.txt");
            $panes = is_file($file) ? ReadingSheet::load($file) : [];
            $parts = [];
            foreach ($panes as $i => $p) {
                if (! empty($p['minutes'])) {
                    $parts[(string) ($i + 1)] = ['name' => $p['name'], 'minutes' => (int) $p['minutes'], 'count' => $p['count'] ?? null];
                }
            }
            if ($parts) {
                return $parts;
            }
            $count = array_sum(array_column($panes, 'count'));

            return ['all' => ['name' => 'Whole test', 'minutes' => self::FULL_TEST_MINUTES, 'count' => $count ?: null]];
        }

        return ['all' => ['name' => 'Whole test', 'minutes' => self::FULL_TEST_MINUTES, 'count' => null]];
    }

    /** Teacher-set minutes for a batch: part => minutes. */
    public static function overrides(?string $batch, string $module, string $slug): array
    {
        if (! $batch) {
            return [];
        }

        return TestTime::where(['batch' => $batch, 'module' => $module, 'test_slug' => $slug])
            ->pluck('minutes', 'part')->map(fn ($m) => (int) $m)->all();
    }

    /** Minutes this user gets: part => minutes (defaults, replaced by their batch's teacher-set times). */
    public static function forUser(?User $user, string $module, string $slug): array
    {
        return self::forBatch($user?->isStudent() ? $user->batch : null, $module, $slug);
    }

    /** Minutes for a batch: part => minutes. */
    public static function forBatch(?string $batch, string $module, string $slug): array
    {
        $out = array_map(fn ($p) => $p['minutes'], self::parts($module, $slug));
        foreach (self::overrides($batch, $module, $slug) as $part => $min) {
            if (isset($out[$part])) {
                $out[$part] = $min;
            }
        }

        return $out;
    }
}
