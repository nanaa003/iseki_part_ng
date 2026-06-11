<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('role:1') for admin, ->middleware('role:2') for leader
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userTypeId = (string) auth()->user()->Id_Type_User;

        if (!in_array($userTypeId, $roles)) {
            // Redirect to their proper dashboard
            if (auth()->user()->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
            if (auth()->user()->isLeader()) {
                return redirect()->route('leader.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
            if (auth()->user()->isArea()) {
                return redirect()->route('area.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
            // Fallback
            return redirect()->route('login');
        }

        return $next($request);
    }
}
