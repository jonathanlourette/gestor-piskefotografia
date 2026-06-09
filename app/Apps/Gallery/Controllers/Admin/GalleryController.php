<?php

declare(strict_types=1);

namespace App\Apps\Gallery\Controllers\Admin;

use App\Apps\Admin\Controllers\BaseAdminController;
use App\Domains\Gallery\Actions\ActivateGalleryAction;
use App\Domains\Gallery\Actions\CreateGalleryAction;
use App\Domains\Gallery\Actions\UpdateGalleryAction;
use App\Domains\Gallery\Gallery;
use App\Integrations\Storage\Contract\StorageServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends BaseAdminController
{
    /**
     * Lista todas as galerias, ordenadas da mais recente para a mais antiga.
     */
    public function index(): View
    {
        $galleries = Gallery::latestFirst()->paginate(15);

        return view('gallery::admin.index', [
            'galleries' => $galleries,
        ]);
    }

    /**
     * Exibe o formulário de criação de uma nova galeria.
     */
    public function create(): View
    {
        return view('gallery::admin.form', [
            'gallery' => new Gallery,
        ]);
    }

    /**
     * Armazena uma nova galeria no banco de dados.
     */
    public function store(Request $request, CreateGalleryAction $action): RedirectResponse
    {
        try {
            $result = $action->setData($request->all())->perform();

            /** @var Gallery $gallery */
            $gallery = $result['gallery'];
            $plainPassword = $result['plain_password'];

            return redirect()
                ->route('admin.gallery.photos.index', $gallery->id)
                ->with('success', "Galeria criada com sucesso! Senha de acesso: {$plainPassword}")
                ->with('plain_password', $plainPassword);
        } catch (\Exception $e) {
            return back()->with('warning', $e->getMessage())->withInput();
        }
    }

    /**
     * Exibe o formulário de edição de uma galeria existente.
     */
    public function edit(int $id): View
    {
        $gallery = Gallery::findOrFail($id);

        return view('gallery::admin.form', [
            'gallery' => $gallery,
        ]);
    }

    /**
     * Atualiza uma galeria existente no banco de dados.
     */
    public function update(Request $request, int $id, UpdateGalleryAction $action): RedirectResponse
    {
        try {
            // SEGURANÇA: Usando o valor vindo da rota `$id` do lado esquerdo para
            // esmagar qualquer 'id' malicioso enviado no payload do request.
            $data = ['id' => $id] + $request->all();

            $action->setData($data)->perform();

            return back()->with('success', 'Galeria atualizada com sucesso!');
        } catch (\Exception $e) {
            return back()->with('warning', $e->getMessage())->withInput();
        }
    }

    /**
     * Remove uma galeria e todas as suas fotos do banco de dados e do S3.
     */
    public function destroy(int $id, StorageServiceInterface $storageService): RedirectResponse
    {
        try {
            $gallery = Gallery::findOrFail($id);

            // Deleta todas as fotos do S3
            foreach ($gallery->photos as $photo) {
                if ($photo->s3_path !== $photo->thumbnail_path) {
                    $storageService->delete($photo->thumbnail_path);
                }
                $storageService->delete($photo->s3_path);
            }

            // Deleta a galeria (cascade deletará as fotos do banco)
            $gallery->delete();

            return redirect()
                ->route('admin.gallery.index')
                ->with('success', 'Galeria removida com sucesso!');
        } catch (\Exception $e) {
            return back()->with('warning', $e->getMessage());
        }
    }

    /**
     * Ativa uma galeria para que ela fique acessível publicamente.
     */
    public function activate(int $id, Request $request, ActivateGalleryAction $action): RedirectResponse|JsonResponse
    {
        try {
            $gallery = $action->setData(['id' => $id])->perform();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Galeria ativada com sucesso!',
                    'url' => url("/galeria/{$gallery->uuid}"),
                    'password' => $gallery->password,
                ]);
            }

            return back()->with('success', "Galeria ativada com sucesso! Link de acesso: /galeria/{$gallery->uuid}");
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('warning', $e->getMessage());
        }
    }
}
