<?php

declare(strict_types=1);

namespace App\Core\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->is_admin) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
