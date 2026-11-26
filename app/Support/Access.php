<?php

namespace App\Support;

use App\Models\ClassUnlock;
use App\Models\Result;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Who may see what.
 *   admin   – everything, every batch
 *   teacher – only the batches assigned to them (students, results, class locks)
 *   student – only the Reading/Writing classes unlocked for their batch, and their own results
 */
class Access
{
    /** Every batch that has students, A–Z. */
    public static function allBatches(): array
    {
        return User::where('role', 'student')->whereNotNull('batch')
            ->distinct()->orderBy('batch')->pluck('batch')->all();
    }

    /** Batches this staff member works with. */
    public static function batchesFor(User $user): array
    {
        if ($user->isAdmin()) {
            return array_values(array_unique(array_merge(
                self::allBatches(),
                \App\Models\TeacherBatch::distinct()->pluck('batch')->all()
            )));
        }
        if ($user->isTeacher()) {
            return $user->batches()->orderBy('batch')->pluck('batch')->all();
        }

        return [];
    }

    /**
     * The batch a teacher/admin is taking class with now (chosen after login, kept in the session).
     * Lock / unlock, time and "show answers" on the class pages work on this batch.
     */
    public static function currentBatch(?User $user): ?string
    {
        if (! $user?->isStaff()) {
            return null;
        }
        $batches = self::batchesFor($user);
        $chosen = session('class_batch');

        return in_array($chosen, $batches, true) ? $chosen : ($batches[0] ?? null);
    }

    /** A teacher with several batches who has not picked one yet this session. */
    public static function mustChooseBatch(?User $user): bool
    {
        return $user?->isTeacher() && count(self::batchesFor($user)) > 1
            && ! in_array(session('class_batch'), self::batchesFor($user), true);
    }

    public static function ownsBatch(User $user, string $batch): bool
    {
        return $user->isAdmin() || in_array(User::normalizeBatch($batch), self::batchesFor($user), true);
    }

    /** Limit a student query to what this staff member may see. */
    public static function scopeStudents(Builder $q, User $user): Builder
    {
        $q->where('role', 'student');

        return $user->isAdmin() ? $q : $q->whereIn('batch', self::batchesFor($user) ?: ['__none__']);
    }

    /** Limit a results query the same way. */
    public static function scopeResults(Builder $q, User $user): Builder
    {
        return $user->isAdmin() ? $q : $q->where('module', $user->courseKey())->whereHas('user', fn ($u) => self::scopeStudents($u, $user));
    }

    public static function canSeeResult(User $user, Result $result): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ($user->isTeacher()) {
            return $result->module === $user->courseKey() && $result->user && $result->user->isStudent() && in_array($result->user->batch, self::batchesFor($user), true);
        }

        return $result->user_id === $user->id;
    }

    /** Class keys ("reading:class-3") unlocked for a batch. */
    public static function unlockedFor(?string $batch): array
    {
        $batch = User::normalizeBatch($batch);
        if ($batch === '') {
            return [];
        }

        return ClassUnlock::where('batch', $batch)->get()->map(fn ($u) => $u->module.':'.$u->test_slug)->all();
    }

    /** Has a teacher turned on "Show answers" for this class in the student's batch? */
    public static function answersShown(?User $user, string $module, string $slug): bool
    {
        return $user && $user->isStudent() && ClassUnlock::where('batch', $user->batch)
            ->where('module', $module)->where('test_slug', $slug)->whereNotNull('answers_shown_at')->exists();
    }

    /** Staff open every class; a student only what is unlocked for their batch. Other modules stay open. (Homework has its own page.) */
    public static function canOpen(?User $user, string $module, string $slug): bool
    {
        if (! in_array($module, Tests::MODULES, true) || $user?->isStaff()) {
            return true;
        }

        return $user && in_array("{$module}:{$slug}", self::unlockedFor($user->batch), true);
    }

    /** Has this student already handed in the homework of a class? */
    public static function homeworkDone(?User $user, string $module, string $slug): bool
    {
        if (! $user) {
            return false;
        }
        $results = Result::where('user_id', $user->id)->where('module', $module)
            ->where('test_slug', $slug)->where('kind', 'homework')->get(['part']);
        // handed in as a whole (older results), or every exercise handed in one by one
        return $results->contains(fn ($r) => $r->part === null)
            || count(self::homeworkPartsDone($user, $module, $slug)) >= max(1, Tests::homeworkCount($module, $slug));
    }

    /** Homework exercises (tab numbers) this student has already handed in one by one. */
    public static function homeworkPartsDone(?User $user, string $module, string $slug): array
    {
        return $user ? Result::where('user_id', $user->id)->where('module', $module)->where('test_slug', $slug)
            ->where('kind', 'homework')->whereNotNull('part')->distinct()->pluck('part')->map(fn ($p) => (int) $p)->all() : [];
    }

    /** Is the class itself (not only its homework) open for this user? */
    public static function classOpen(?User $user, string $module, string $slug): bool
    {
        if (! in_array($module, Tests::MODULES, true) || $user?->isStaff()) {
            return true;
        }

        return $user && in_array("{$module}:{$slug}", self::unlockedFor($user->batch), true);
    }

    /** The homework set for a student's batch on this class, if any. */
    public static function homeworkFor(?User $user, string $module, string $slug): ?\App\Models\HomeworkAssignment
    {
        if (! $user?->isStudent() || ! $user->batch) {
            return null;
        }

        return \App\Models\HomeworkAssignment::where('batch', $user->batch)
            ->where('module', $module)->where('test_slug', $slug)->first();
    }

    /** Every homework set for a student's batch, keyed "module:slug". */
    public static function homeworkList(?User $user): \Illuminate\Support\Collection
    {
        if (! $user?->isStudent() || ! $user->batch) {
            return collect();
        }

        return \App\Models\HomeworkAssignment::where('batch', $user->batch)->get()
            ->keyBy(fn ($h) => $h->module.':'.$h->test_slug);
    }
}
