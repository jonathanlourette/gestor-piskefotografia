@php
    /** @var \App\Domains\Product\Product|null $product */
    /** @var array<string, string> $types */
@endphp

@extends('admin::layouts.admin')

@section('title', $product ? 'Editar Produto' : 'Novo Produto')

@section('content')
    <div x-data="productForm()" class="container-fluid">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-4">
            <div>
                <h1 class="fw-bolder mb-2 text-dark" style="letter-spacing: -0.02em;">
                    {{ $product ? 'Editar Produto' : 'Novo Produto' }}
                </h1>
                <p class="text-body-secondary mb-0 fs-5">
                    {{ $product ? 'Atualize as informações do produto.' : 'Cadastre um novo produto para o catálogo.' }}
                </p>
            </div>
            <div>
                <a href="{{ route('admin.products.index') }}" class="btn btn-light btn-lg rounded-4 px-4 fw-semibold shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>

        <div class="bg-white rounded-4 border border-secondary-subtle p-4 p-md-5 shadow-sm" style="max-width: 900px;">
            <form action="{{ $product ? route('admin.products.update', $product->id) : route('admin.products.store') }}"
                  method="{{ $product ? 'POST' : 'POST' }}"
                  enctype="multipart/form-data"
                  @submit.prevent="submitForm($el)">

                @csrf
                @if($product)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <!-- Nome -->
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark fs-6 mb-2">
                            Nome do Produto <span class="text-danger">*</span>
                        </label>
                         <input type="text"
                                name="name"
                                value="{{ old('name', $product?->name ?? '') }}"
                                class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 px-4 py-3 {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                placeholder="Ex: Pacote Premium 50 Fotos"
                                required
                                style="transition: box-shadow 0.2s ease;"
                                onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'" 
                                onblur="this.style.boxShadow='none'">
                        @error('name')
                            <div class="invalid-feedback d-block mt-2 small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Tipo e Preço -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark fs-6 mb-2">
                            Tipo de Produto <span class="text-danger">*</span>
                        </label>
                         <select name="type"
                                 class="form-select form-select-lg bg-body-tertiary border-0 shadow-none rounded-4 px-4 py-3 {{ $errors->has('type') ? 'is-invalid' : '' }}"
                                 required
                                 style="transition: box-shadow 0.2s ease;"
                                 onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'" 
                                 onblur="this.style.boxShadow='none'">
                            <option value="" disabled selected>Selecione o tipo...</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}"
                                        {{ old('type', $product?->type->value ?? '') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback d-block mt-2 small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark fs-6 mb-2">
                            Preço (R$) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                             <span class="input-group-text bg-body-tertiary border-0 rounded-4-start ps-4 text-secondary fw-medium">R$</span>
                             <input type="number"
                                    name="price"
                                    value="{{ old('price', $product?->price ?? '') }}"
                                    step="0.01"
                                    min="0"
                                    class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4-end px-4 py-3 {{ $errors->has('price') ? 'is-invalid' : '' }}"
                                    placeholder="0,00"
                                    required
                                    style="transition: box-shadow 0.2s ease;"
                                    onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'" 
                                    onblur="this.style.boxShadow='none'">
                        </div>
                        @error('price')
                            <div class="invalid-feedback d-block mt-2 small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Limite de Fotos -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark fs-6 mb-2">
                            Limite de Fotos <span class="text-danger">*</span>
                        </label>
                         <input type="number"
                                name="photo_limit"
                                value="{{ old('photo_limit', $product?->photo_limit ?? '') }}"
                                min="1"
                                class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 px-4 py-3 {{ $errors->has('photo_limit') ? 'is-invalid' : '' }}"
                                placeholder="Ex: 50"
                                required
                                style="transition: box-shadow 0.2s ease;"
                                onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'" 
                                onblur="this.style.boxShadow='none'">
                        @error('photo_limit')
                            <div class="invalid-feedback d-block mt-2 small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Ativo -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark fs-6 mb-2">
                            Status
                        </label>
                         <div class="bg-body-tertiary border-0 shadow-sm rounded-4 px-4 py-3 d-flex align-items-center gap-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="active" value="0">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="active"
                                       value="1"
                                       id="activeSwitch"
                                       {{ old('active', $product?->active ?? true) ? 'checked' : '' }}
                                       style="width: 3.5rem; height: 2rem;">
                            </div>
                            <label for="activeSwitch" class="form-check-label mb-0 fw-medium text-dark">
                                Produto ativo no catálogo
                            </label>
                        </div>
                    </div>

                    <!-- Imagem de Capa -->
                    <div class="col-12">
                        <div x-data="{
                            hasImage: {{ $product && $product->image_path ? 'true' : 'false' }},
                            removeImage: false,
                            previewUrl: {{ $product && $product->image_path ? "'" . asset($product->image_path) . "'" : 'null' }}
                        }">
                            <label class="form-label fw-semibold text-dark fs-6 mb-2">
                                Imagem de Capa
                            </label>

                            <!-- Current Image Preview -->
                            @if($product && $product->image_path)
                                <div x-show="hasImage && !removeImage" class="mb-3 p-3 bg-body-tertiary rounded-4 d-flex align-items-center gap-3">
                                    <img :src="previewUrl"
                                         alt="{{ $product->name }}"
                                         class="rounded-3"
                                         style="width: 120px; height: 120px; object-fit: cover;">
                                    <div>
                                        <button type="button"
                                                class="btn btn-outline-danger btn-sm rounded-3 fw-medium"
                                                @click="removeImage = true; $nextTick(() => { document.querySelector('input[name=\'remove_image\']')?.dispatchEvent(new Event('input')); })">
                                            <i class="bi bi-trash me-1"></i>Remover imagem
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <!-- File Input -->
                            <div x-show="!hasImage || removeImage">
                                <input type="file"
                                       name="image"
                                       accept="image/jpeg,image/png,image/webp"
                                       class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 px-4 py-3 {{ $errors->has('image') ? 'is-invalid' : '' }}"
                                       style="transition: box-shadow 0.2s ease;"
                                       onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'"
                                       onblur="this.style.boxShadow='none'">

                                <div class="form-text text-secondary mt-2 small">
                                    JPG, PNG ou WebP. Máximo 5MB.
                                </div>

                                @error('image')
                                    <div class="invalid-feedback d-block mt-2 small">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Hidden field for removal -->
                            <input type="hidden"
                                   name="remove_image"
                                   value="{{ old('remove_image', '0') }}"
                                   x-model="removeImage ? '1' : '0'">
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark fs-6 mb-2">
                            Descrição
                        </label>
                         <textarea name="description"
                                   rows="4"
                                   class="form-control bg-body-tertiary border-0 shadow-none rounded-4 px-4 py-3 {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                   placeholder="Descreva os detalhes do produto..."
                                   style="transition: box-shadow 0.2s ease;"
                                   onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'" 
                                   onblur="this.style.boxShadow='none'">{{ old('description', $product?->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback d-block mt-2 small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top border-light">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-light btn-lg rounded-4 px-5 fw-semibold">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-dark btn-lg rounded-4 px-5 fw-semibold d-flex align-items-center gap-2" :disabled="isSubmitting">
                        <span x-show="!isSubmitting">
                            {{ $product ? 'Salvar Alterações' : 'Criar Produto' }}
                        </span>
                        <span x-show="isSubmitting" style="display: none;">
                            Salvando...
                            <span class="spinner-border spinner-border-sm align-middle"></span>
                        </span>
                        <i x-show="!isSubmitting" class="bi bi-check-lg fs-5"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function productForm() {
            return {
                isSubmitting: false,

                submitForm(form) {
                    if (form.checkValidity()) {
                        this.isSubmitting = true;
                        form.submit();
                    } else {
                        form.reportValidity();
                    }
                }
            }
        }
    </script>
@endsection