<?php

declare(strict_types=1);

namespace App\Apps\Admin\Controllers;

use App\Domains\Product\Actions\CreateProductAction;
use App\Domains\Product\Actions\RemoveProductAction;
use App\Domains\Product\Actions\RetrieveProductAction;
use App\Domains\Product\Actions\RetrieveProductsAction;
use App\Domains\Product\Actions\UpdateProductAction;
use App\Domains\Product\Enums\ProductTypeEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends BaseAdminController
{
    public function index(RetrieveProductsAction $action): View
    {
        $products = $action->setData([])->perform();

        return view('admin::products.index', [
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        return view('admin::products.form', [
            'types' => ProductTypeEnum::options(),
            'product' => null,
        ]);
    }

    public function store(Request $request, CreateProductAction $action): RedirectResponse
    {
        try {
            $data = $request->all();

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }

            $action->setData($data)->perform();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Produto criado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('warning', $e->getMessage());
        }
    }

    public function edit(int $id, RetrieveProductAction $action): View
    {
        $product = $action->setData(['id' => $id])->perform();

        return view('admin::products.form', [
            'types' => ProductTypeEnum::options(),
            'product' => $product,
        ]);
    }

    public function update(Request $request, int $id, UpdateProductAction $action): RedirectResponse
    {
        try {
            $data = ['id' => $id] + $request->all();

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }

            $action->setData($data)->perform();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Produto atualizado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('warning', $e->getMessage());
        }
    }

    public function delete(int $id, RemoveProductAction $action): RedirectResponse
    {
        try {
            $action->setData(['id' => $id])->perform();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Produto removido com sucesso!');
        } catch (\Exception $e) {
            return back()->with('warning', $e->getMessage());
        }
    }
}
