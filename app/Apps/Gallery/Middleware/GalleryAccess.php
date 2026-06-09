<?php

declare(strict_types=1);

namespace App\Apps\Gallery\Middleware;

use App\Domains\Gallery\Enums\GalleryStatusEnum;
use App\Domains\Gallery\Gallery;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GalleryAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $uuid = $request->route('uuid');

        $gallery = Gallery::where('uuid', $uuid)->first();

        if (! $gallery) {
            abort(404, 'Galeria não encontrada.');
        }

        if ($gallery->status !== GalleryStatusEnum::ACTIVE) {
            abort(404, 'Galeria não encontrada.');
        }

        if ($gallery->expires_at !== null && $gallery->expires_at->isPast()) {
            abort(410, 'Esta galeria expirou.');
        }

        $sessionKey = "gallery_auth_{$uuid}";
        if (! session($sessionKey)) {
            return redirect()->route('gallery.login', ['uuid' => $uuid]);
        }

        // Share gallery with the request for controllers
        $request->attributes->set('gallery', $gallery);

        return $next($request);
    }
}
