<?php

declare(strict_types=1);

namespace App\Apps\Gallery\Controllers\Public;

use App\Domains\Gallery\Enums\GalleryStatusEnum;
use App\Domains\Gallery\Gallery;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GalleryAuthController extends BaseController
{
    /**
     * Show the gallery login form.
     */
    public function showLogin(string $uuid): View
    {
        $gallery = Gallery::where('uuid', $uuid)->first();

        if (! $gallery || $gallery->status !== GalleryStatusEnum::ACTIVE) {
            abort(404, 'Galeria não encontrada.');
        }

        return view('gallery::public.login', [
            'gallery' => $gallery,
        ]);
    }

    /**
     * Authenticate the visitor with the gallery password.
     */
    public function authenticate(Request $request, string $uuid)
    {
        $gallery = Gallery::where('uuid', $uuid)->first();

        if (! $gallery || $gallery->status !== GalleryStatusEnum::ACTIVE) {
            abort(404, 'Galeria não encontrada.');
        }

        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'A senha é obrigatória.',
        ]);

        if (! Hash::check($request->password, $gallery->password)) {
            return back()->with('error', 'Senha incorreta.');
        }

        // Set session for gallery authentication
        $sessionKey = "gallery_auth_{$uuid}";
        session([$sessionKey => true]);

        // Set visitor token cookie if not present
        $visitorToken = $request->cookie('gallery_visitor_token');
        if (! $visitorToken) {
            $visitorToken = Str::uuid()->toString();
        }

        return redirect()
            ->route('gallery.showcase', ['uuid' => $uuid])
            ->cookie('gallery_visitor_token', $visitorToken, 60 * 24 * 90);
    }
}
