@php
    /** @var \App\Domains\Gallery\Gallery|null $gallery */
@endphp

@extends('admin::layouts.admin')

@section('title', $gallery?->id ? 'Editar Galeria' : 'Nova Galeria')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-4">
            <div>
                <h1 class="fw-bolder mb-2 text-dark" style="letter-spacing: -0.02em;">
                    {{ $gallery?->id ? 'Editar Galeria' : 'Nova Galeria' }}
                </h1>
                <p class="text-body-secondary mb-0 fs-5">
                    {{ $gallery?->id ? 'Atualize as informações da galeria.' : 'Cadastre uma nova galeria para o cliente.' }}
                </p>
            </div>
            <div>
                <a href="{{ route('admin.gallery.index') }}" class="btn btn-light btn-lg rounded-4 px-4 fw-semibold shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>

        <div class="bg-white rounded-4 border border-secondary-subtle p-4 p-md-5 shadow-sm" style="max-width: 900px;">
            <form action="{{ $gallery?->id ? route('admin.gallery.update', $gallery->id) : route('admin.gallery.store') }}"
                  method="POST">

                @csrf
                @if($gallery?->id)
                    @method('PUT')
                @endif

                <!-- Password Alert (from store) -->
                @if(session('plain_password'))
                    <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 mb-4" role="alert">
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">
                                <i class="bi bi-shield-lock-fill fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading fw-bold mb-2">Senha da Galeria Gerada</h5>
                                <p class="mb-3 text-body-secondary">A galeria foi criada com sucesso! Use a senha abaixo para acessar:</p>
                                <div class="d-flex gap-2 align-items-center">
                                    <code class="bg-body-tertiary border-0 rounded-3 px-4 py-2 fw-bold fs-5" style="letter-spacing: 2px;">{{ session('plain_password') }}</code>
                                    <button type="button"
                                            class="btn btn-outline-success rounded-3 px-3 py-2 fw-medium"
                                            onclick="navigator.clipboard.writeText('{{ session('plain_password') }}'); this.innerHTML = '<i class=\\'bi bi-check-fill me-1\\'></i>Copiado!'; setTimeout(() => { this.innerHTML = '<i class=\\'bi bi-clipboard me-1\\'></i>Copiar'; }, 2000);">
                                        <i class="bi bi-clipboard me-1"></i>Copiar
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                <div class="row g-4">
                    <!-- Título -->
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark fs-6 mb-2">
                            Título da Galeria <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="title"
                               value="{{ old('title', $gallery?->title ?? '') }}"
                               class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 px-4 py-3 {{ $errors->has('title') ? 'is-invalid' : '' }}"
                               placeholder="Ex: Casamento Ana e Pedro"
                               required
                               style="transition: box-shadow 0.2s ease;"
                               onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'"
                               onblur="this.style.boxShadow='none'">
                        @error('title')
                            <div class="invalid-feedback d-block mt-2 small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Nome do Cliente -->
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark fs-6 mb-2">
                            Nome do Cliente <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="customer_name"
                               value="{{ old('customer_name', $gallery?->customer_name ?? '') }}"
                               class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 px-4 py-3 {{ $errors->has('customer_name') ? 'is-invalid' : '' }}"
                               placeholder="Ex: Ana Silva"
                               required
                               style="transition: box-shadow 0.2s ease;"
                               onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'"
                               onblur="this.style.boxShadow='none'">
                        @error('customer_name')
                            <div class="invalid-feedback d-block mt-2 small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Email do Cliente -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark fs-6 mb-2">
                            Email do Cliente <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               name="customer_email"
                               value="{{ old('customer_email', $gallery?->customer_email ?? '') }}"
                               class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 px-4 py-3 {{ $errors->has('customer_email') ? 'is-invalid' : '' }}"
                               placeholder="exemplo@email.com"
                               required
                               style="transition: box-shadow 0.2s ease;"
                               onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'"
                               onblur="this.style.boxShadow='none'">
                        @error('customer_email')
                            <div class="invalid-feedback d-block mt-2 small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Telefone do Cliente -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark fs-6 mb-2">
                            Telefone do Cliente <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="customer_phone"
                               value="{{ old('customer_phone', $gallery?->customer_phone ?? '') }}"
                               class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 px-4 py-3 {{ $errors->has('customer_phone') ? 'is-invalid' : '' }}"
                               placeholder="(XX) XXXXX-XXXX"
                               x-data="{ phone: '{{ old('customer_phone', $gallery?->customer_phone ?? '') }}' }"
                               x-model="phone"
                               @input="phone = phone.replace(/\D/g, '').replace(/^(\d{2})(\d)/g, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2').substr(0, 15)"
                               required
                               style="transition: box-shadow 0.2s ease;"
                               onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'"
                               onblur="this.style.boxShadow='none'">
                        @error('customer_phone')
                            <div class="invalid-feedback d-block mt-2 small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Data de Expiração -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark fs-6 mb-2">
                            Data de Expiração
                        </label>
                        <input type="date"
                               name="expires_at"
                               value="{{ old('expires_at', $gallery?->expires_at?->format('Y-m-d') ?? '') }}"
                               class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 px-4 py-3 {{ $errors->has('expires_at') ? 'is-invalid' : '' }}"
                               style="transition: box-shadow 0.2s ease;"
                               onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'"
                               onblur="this.style.boxShadow='none'">
                        @error('expires_at')
                            <div class="invalid-feedback d-block mt-2 small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Senha -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark fs-6 mb-2">
                            Senha de Acesso
                        </label>
                        <input type="text"
                               name="password"
                               value="{{ old('password', $gallery?->password ?? '') }}"
                               class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 px-4 py-3 {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Deixe vazio para gerar automaticamente"
                               style="transition: box-shadow 0.2s ease;"
                               onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'"
                               onblur="this.style.boxShadow='none'">
                        <div class="form-text text-secondary mt-2 small">
                            <i class="bi bi-info-circle me-1"></i> Deixe vazio para gerar uma senha automática.
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block mt-2 small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Gallery Link (Edit mode, Active status) -->
                    @if($gallery && $gallery->status === \App\Domains\Gallery\Enums\GalleryStatusEnum::ACTIVE)
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark fs-6 mb-2">
                                Link da Galeria
                            </label>
                            <div class="bg-body-tertiary border-0 shadow-sm rounded-4 px-4 py-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-link-45deg fs-4 text-secondary"></i>
                                    <span class="font-monospace text-dark">{{ route('gallery.login', $gallery->uuid) }}</span>
                                </div>
                                <button type="button"
                                        class="btn btn-outline-dark btn-sm rounded-3 px-3 py-1 fw-medium"
                                                                                 onclick="navigator.clipboard.writeText('{{ route('gallery.login', $gallery->uuid) }}'); this.innerHTML = '<i class=\\'bi bi-check-fill me-1\\'></i>Copiado!'; setTimeout(() => { this.innerHTML = '<i class=\\'bi bi-clipboard me-1\\'></i>Copiar'; }, 2000);">
                                    <i class="bi bi-clipboard me-1"></i>Copiar
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top border-light">
                    <a href="{{ route('admin.gallery.index') }}" class="btn btn-light btn-lg rounded-4 px-5 fw-semibold">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-dark btn-lg rounded-4 px-5 fw-semibold d-flex align-items-center gap-2">
                        {{ $gallery?->id ? 'Salvar Galeria' : 'Criar Galeria' }}
                        <i class="bi bi-check-lg fs-5"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection