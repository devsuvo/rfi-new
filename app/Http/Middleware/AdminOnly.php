<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Staff area guard. Usage: ->middleware('role:admin') or ->middleware('role:admin,teacher').
 * Also logs out a student whose access period has ended.
 */
class AdminOnly
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if ($user?->accessExpired()) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->withErrors(['email' => 'Your access ended on '.$user->access_until->format('d M Y').'. Please contact the office.']);
        }

        if ($roles) {
            abort_unless($user && in_array($user->role, $roles, true), 403, 'You do not have access to this page.');
        }

        // Students and teachers only see their own course (Reading for IELTS or Writing for IELTS).
        $name = $request->route()?->getName() ?? '';
        $module = self::moduleOf($request, $name);
        if ($user && $module && ! $user->canUseCourse($module)) {
            if ($request->expectsJson()) {
                abort(403, 'This is not part of your course.');
            }
            $home = $user->isTeacher() ? route('admin.panel', $user->courseKey()) : route($user->courseKey());

            return redirect($home)->with('locked', 'You are registered for '.\App\Models\User::COURSES[$user->courseKey()].' only.');
        }
        if ($user?->isTeacher()) {
            session(['panel' => $user->courseKey()]);
        }

        // Opening a Reading/Writing page switches the sidebar to that panel.
        if (preg_match('/^admin\.(reading|writing)\./', $name, $m)) {
            session(['panel' => $m[1]]);
        }

        return $next($request);
    }

    /** Which course (reading / writing / listening / speaking) a request belongs to, if any. */
    private static function moduleOf(Request $request, string $name): ?string
    {
        $route = $request->route();
        foreach (['module', 'panel'] as $p) {
            if ($m = $route?->parameter($p)) {
                return in_array($m, ['reading', 'writing', 'listening', 'speaking'], true) ? $m : null;
            }
        }
        if (preg_match('/^(?:admin\.)?(reading|writing|listening|speaking)(?:\.|$)/', $name, $m)) {
            return $m[1];
        }
        if (in_array($name, ['results.homework'], true)) {
            return 'reading';
        }

        return null;
    }
}
