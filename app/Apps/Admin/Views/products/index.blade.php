@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $products */
@endphp

@extends('admin::layouts.admin')

@section('title', 'Produtos')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-4">
            <div>
                <h1 class="fw-bolder mb-2 text-dark" style="letter-spacing: -0.02em;">Produtos</h1>
                <p class="text-body-secondary mb-0 fs-5">Gerencie o catálogo de produtos e pacotes fotográficos.</p>
            </div>
            <div>
                <a href="{{ route('admin.products.create') }}" class="btn btn-dark btn-lg rounded-4 px-4 fw-semibold shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg fs-5"></i> Novo Produto
                </a>
            </div>
        </div>

        @if($products->isEmpty())
            <!-- Empty State Premium -->
            <div class="bg-body-tertiary rounded-5 py-5 px-4 text-center border border-light">
                <div class="py-5 my-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white shadow-sm rounded-circle mb-4" style="width: 96px; height: 96px;">
                        <i class="bi bi-box-seam fs-1 text-secondary"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-3">Nenhum produto cadastrado</h2>
                    <p class="text-body-secondary fs-5 mb-4 mx-auto" style="max-width: 500px;">
                        Você ainda não possui produtos no catálogo. Adicione seu primeiro produto para começar a vender.
                    </p>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-dark btn-lg rounded-4 px-5 fw-semibold shadow-sm">
                        Criar Primeiro Produto
                    </a>
                </div>
            </div>
        @else
            <!-- Modern Table Container -->
            <div class="table-responsive rounded-4 border border-secondary-subtle bg-white">
                <table class="table table-borderless table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Nome</th>
                            <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Tipo</th>
                            <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Preço</th>
                            <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Limite de Fotos</th>
                            <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Status</th>
                            <th class="pe-4 py-3 text-end text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                         @foreach($products as $product)
                             <tr class="border-bottom border-light" style="transition: background-color 0.2s ease;">
                                 <td class="ps-4 py-4">
                                      <div class="d-flex align-items-center gap-3">
                                          @if($product->image_url)
                                              <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="rounded-3" style="width: 48px; height: 48px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                              <div class="bg-primary-subtle text-primary-emphasis rounded-4 d-flex justify-content-center align-items-center" style="width: 48px; height: 48px; display: none;">
                                                  <i class="bi bi-box-seam fs-5"></i>
                                              </div>
                                         @else
                                             <div class="bg-primary-subtle text-primary-emphasis rounded-4 d-flex justify-content-center align-items-center" style="width: 48px; height: 48px;">
                                                 <i class="bi bi-box-seam fs-5"></i>
                                             </div>
                                         @endif
                                         <div>
                                             <h6 class="mb-1 fw-bold text-dark fs-6">{{ $product->name }}</h6>
                                             <span class="text-secondary small font-monospace bg-body-tertiary px-2 py-1 rounded">#{{ str_pad((string) $product->id, 5, '0', STR_PAD_LEFT) }}</span>
                                         </div>
                                     </div>
                                 </td>
                                <td class="py-4">
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-2 fw-medium fs-7">
                                        {{ $product->type->label() }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    <span class="fw-semibold text-dark fs-6">R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                                </td>
                                <td class="py-4">
                                    <span class="text-body-secondary fs-6">{{ $product->photo_limit }} fotos</span>
                                </td>
                                <td class="py-4">
                                    @if($product->active)
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill px-3 py-2 fw-medium fs-7">
                                            <span class="d-inline-block bg-success rounded-circle me-1" style="width: 6px; height: 6px;"></span> Ativo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle rounded-pill px-3 py-2 fw-medium fs-7">
                                            <span class="d-inline-block bg-secondary rounded-circle me-1" style="width: 6px; height: 6px;"></span> Inativo
                                        </span>
                                    @endif
                                </td>
                                 <td class="pe-4 py-4 text-end">
                                     <div class="d-flex gap-2 justify-content-end">
                                         <a href="{{ route('admin.products.edit', $product->id) }}"
                                            class="btn btn-sm btn-light bg-body-tertiary text-primary hover-primary border-0 rounded-3 px-3 py-2 shadow-sm"
                                            title="Editar">
                                             <i class="bi bi-pencil-fill"></i>
                                         </a>
                                         <form action="{{ route('admin.products.delete', $product->id) }}"
                                               method="POST"
                                               class="d-inline"
                                               x-on:submit="if(!confirm('Tem certeza que deseja excluir este produto?')) $event.preventDefault()">
                                             @csrf
                                             @method('DELETE')
                                             <button type="submit"
                                                     class="btn btn-sm btn-light bg-body-tertiary text-danger border-0 rounded-3 px-3 py-2 shadow-sm"
                                                     title="Excluir">
                                                 <i class="bi bi-trash3-fill"></i>
                                             </button>
                                         </form>
                                     </div>
                                 </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-secondary small">
                        Mostrando de {{ $products->firstItem() }} a {{ $products->lastItem() }} de {{ $products->total() }} registros
                    </div>
                    <div>
                        {{ $products->links() }}
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection