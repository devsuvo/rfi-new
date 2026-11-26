<?php

namespace App\Support;

/** Catalog of the tests the admin panel manages (Reading + Writing only). */
class Tests
{
    public const MODULES = ['reading', 'writing'];

    /** @return array<string, array<string, string>> module => [slug => title] */
    public static function all(): array
    {
        $out = [];
        foreach (self::MODULES as $module) {
            foreach (config("modules.{$module}.classes", []) as $c) {
                $slug = $c['slug'] ?? basename($c['url'] ?? '');
                if ($slug) {
                    $out[$module][$slug] = $c['title'];
                }
            }
        }

        return $out;
    }

    public static function title(string $module, string $slug): string
    {
        return self::all()[$module][$slug] ?? $slug;
    }

    public static function exists(string $module, string $slug): bool
    {
        return isset(self::all()[$module][$slug]);
    }

    /** Number of "Homework …" tabs in a reading class sheet (0 = no homework). */
    public static function homeworkCount(string $module, string $slug): int
    {
        $file = resource_path("{$module}/{$slug}.txt");

        return $module === 'reading' && is_file($file)
            ? preg_match_all('/^= Homework\b/mi', file_get_contents($file))
            : 0;
    }

    /** Answer-box names of a reading class: only the homework tabs, or only the class tabs. */
    public static function readingFieldNames(string $slug, bool $homework): array
    {
        $names = [];
        foreach (self::readingFields($slug) as $group) {
            if (self::isHomeworkTab($group['name']) === $homework) {
                foreach ($group['fields'] as $f) {
                    $names[] = $f['name'];
                }
            }
        }

        return $names;
    }

    public static function isHomeworkTab(string $name): bool
    {
        return (bool) preg_match('/^homework\b/i', $name);
    }

    /**
     * Answer boxes on a reading page, grouped by tab.
     * @return array<int, array{name: string, fields: array<int, array{name: string, label: string, marks: int}>}>
     */
    public static function readingFields(string $slug): array
    {
        $file = resource_path("reading/{$slug}.txt");
        if (! is_file($file)) {
            return [];
        }
        $groups = [];
        foreach (ReadingSheet::load($file) as $pane) {
            preg_match_all('/name="(p\d+_q([\d_]+))(\[\])?"/', $pane['passage'].$pane['questions'], $m, PREG_SET_ORDER);
            $fields = [];
            foreach ($m as $hit) {
                $nums = explode('_', $hit[2]);
                $fields[$hit[1]] ??= ['name' => $hit[1], 'label' => implode(' & ', $nums), 'marks' => count($nums), 'multi' => ! empty($hit[3])];
            }
            if ($fields) {
                $groups[] = ['name' => $pane['name'], 'fields' => array_values($fields)];
            }
        }

        return $groups;
    }
}
