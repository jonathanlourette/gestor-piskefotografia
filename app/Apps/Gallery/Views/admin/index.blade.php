@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator|\App\Domains\Gallery\Gallery[] $galleries */
@endphp

@extends('admin::layouts.admin')

@section('title', 'Galerias')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-4">
            <div>
                <h1 class="fw-bolder mb-2 text-dark" style="letter-spacing: -0.02em;">Galerias</h1>
                <p class="text-body-secondary mb-0 fs-5">Gerencie as galerias de fotos dos clientes.</p>
            </div>
            <div>
                <a href="{{ route('admin.gallery.create') }}" class="btn btn-dark btn-lg rounded-4 px-4 fw-semibold shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg fs-5"></i> Nova Galeria
                </a>
            </div>
        </div>

        @if($galleries->isEmpty())
            <!-- Empty State Premium -->
            <div class="bg-body-tertiary rounded-5 py-5 px-4 text-center border border-light">
                <div class="py-5 my-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white shadow-sm rounded-circle mb-4" style="width: 96px; height: 96px;">
                        <i class="bi bi-images fs-1 text-secondary"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-3">Nenhuma galeria cadastrada</h2>
                    <p class="text-body-secondary fs-5 mb-4 mx-auto" style="max-width: 500px;">
                        Você ainda não possui galerias. Crie sua primeira galeria para começar a gerenciar os álbuns de fotos dos clientes.
                    </p>
                    <a href="{{ route('admin.gallery.create') }}" class="btn btn-dark btn-lg rounded-4 px-5 fw-semibold shadow-sm">
                        Criar Primeira Galeria
                    </a>
                </div>
            </div>
        @else
            <!-- Modern Table Container -->
            <div class="table-responsive rounded-4 border border-secondary-subtle bg-white">
                <table class="table table-borderless table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Título</th>
                            <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Cliente</th>
                            <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Fotos</th>
                            <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Status</th>
                            <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Data</th>
                            <th class="pe-4 py-3 text-end text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($galleries as $gallery)
                            <tr class="border-bottom border-light" style="transition: background-color 0.2s ease;">
                                <td class="ps-4 py-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($gallery->cover_photo_path)
                                            <img src="{{ asset($gallery->cover_photo_path) }}" alt="{{ $gallery->title }}" class="rounded-3" style="width: 48px; height: 48px; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="bg-primary-subtle text-primary-emphasis rounded-4 d-flex justify-content-center align-items-center" style="width: 48px; height: 48px; display: none;">
                                                <i class="bi bi-images fs-5"></i>
                                            </div>
                                        @else
                                            <div class="bg-primary-subtle text-primary-emphasis rounded-4 d-flex justify-content-center align-items-center" style="width: 48px; height: 48px;">
                                                <i class="bi bi-images fs-5"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark fs-6">{{ $gallery->title }}</h6>
                                            <span class="text-secondary small font-monospace bg-body-tertiary px-2 py-1 rounded">UUID: {{ substr($gallery->uuid, 0, 8) }}...</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <div class="fw-semibold text-dark">{{ $gallery->customer_name }}</div>
                                    <div class="text-secondary small">{{ $gallery->customer_email }}</div>
                                </td>
                                <td class="py-4">
                                    <span class="fw-semibold text-dark fs-6">{{ $gallery->photos_count }}</span>
                                    <span class="text-secondary small">foto{{ $gallery->photos_count != 1 ? 's' : '' }}</span>
                                </td>
                                <td class="py-4">
                                    <span class="badge bg-{{ $gallery->status->badgeColor() }}-subtle text-{{ $gallery->status->badgeColor() }}-emphasis border border-{{ $gallery->status->badgeColor() }}-subtle rounded-pill px-3 py-2 fw-medium fs-7">
                                        {{ $gallery->status->label() }}
                                    </span>
                                </td>
                                <td class="py-4 text-body-secondary fs-6">
                                    {{ $gallery->created_at->format('d/m/Y') }}
                                </td>
                                <td class="pe-4 py-4 text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('admin.gallery.photos.index', $gallery->id) }}"
                                           class="btn btn-sm btn-light bg-body-tertiary text-primary hover-primary border-0 rounded-3 px-3 py-2 shadow-sm"
                                           title="Fotos">
                                            <i class="bi bi-image-fill"></i>
                                        </a>
                                        <a href="{{ route('admin.gallery.edit', $gallery->id) }}"
                                           class="btn btn-sm btn-light bg-body-tertiary text-primary hover-primary border-0 rounded-3 px-3 py-2 shadow-sm"
                                           title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        @if($gallery->status === \App\Domains\Gallery\Enums\GalleryStatusEnum::DRAFT)
                                            <form action="{{ route('admin.gallery.activate', $gallery->id) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-light bg-body-tertiary text-success border-0 rounded-3 px-3 py-2 shadow-sm"
                                                        title="Ativar">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.gallery.destroy', $gallery->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              x-on:submit="if(!confirm('Tem certeza que deseja excluir esta galeria? Esta ação não pode ser desfeita.')) $event.preventDefault()">
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

            @if($galleries->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-secondary small">
                        Mostrando de {{ $galleries->firstItem() }} a {{ $galleries->lastItem() }} de {{ $galleries->total() }} registros
                    </div>
                    <div>
                        {{ $galleries->links() }}
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection