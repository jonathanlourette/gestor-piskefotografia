@php
    /** @var \App\Domains\Gallery\Gallery $gallery */
    /** @var \Illuminate\Database\Eloquent\Collection $gallery->photos */
@endphp

@extends('admin::layouts.admin')

@section('title', 'Fotos — ' . $gallery->title)

@section('content')
<div x-data="photoManager()" class="d-flex flex-column gap-4">

    {{-- ====================================================================== --}}
    {{-- HEADER                                                                 --}}
    {{-- ====================================================================== --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 bg-white rounded-4 p-4 border border-secondary-subtle shadow-sm">
        <div class="d-flex align-items-start gap-3">
            <a href="{{ route('admin.gallery.index') }}"
               class="btn btn-light rounded-3 px-3 border border-secondary-subtle text-secondary mt-1"
               title="Voltar para Galerias"
               style="transition: all 0.2s ease;"
               onmouseover="this.style.transform='translateY(-1px)'"
               onmouseout="this.style.transform='translateY(0)'">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="d-flex align-items-center gap-3 mb-1 flex-wrap">
                    <h1 class="fw-bolder mb-0 text-dark" style="font-size: 1.5rem; letter-spacing: -0.02em;">{{ $gallery->title }}</h1>
                    <span class="badge bg-{{ $gallery->status->badgeColor() }}-subtle text-{{ $gallery->status->badgeColor() }}-emphasis border border-{{ $gallery->status->badgeColor() }}-subtle rounded-pill px-3 py-2 fw-bold">
                        {{ $gallery->status->label() }}
                    </span>
                </div>
                <p class="text-body-secondary mb-0">
                    {{ $gallery->customer_name }}
                    <span class="mx-2 text-secondary-subtle">·</span>
                    <span x-text="photos.length + ' foto' + (photos.length !== 1 ? 's' : '')"></span>
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <template x-if="galleryStatus === 'draft' && photos.length > 0">
                <button type="button"
                        @click="activateGallery()"
                        class="btn btn-dark btn-lg rounded-4 px-4 fw-semibold shadow-sm d-flex align-items-center gap-2"
                        style="transition: all 0.2s ease;"
                        onmouseover="this.style.transform='translateY(-1px)'"
                        onmouseout="this.style.transform='translateY(0)'">
                    <i class="bi bi-rocket-takeoff"></i>
                    Publicar Galeria
                </button>
            </template>
            <a href="{{ route('admin.gallery.edit', $gallery->id) }}"
               class="btn btn-light btn-lg rounded-4 px-4 fw-semibold shadow-sm d-flex align-items-center gap-2"
               style="transition: all 0.2s ease;"
               onmouseover="this.style.transform='translateY(-1px)'"
               onmouseout="this.style.transform='translateY(0)'">
                <i class="bi bi-pencil"></i>
                Editar
            </a>
        </div>
    </div>

    {{-- ====================================================================== --}}
    {{-- SHARE CARD (only when active)                                          --}}
    {{-- ====================================================================== --}}
    @if($gallery->status === \App\Domains\Gallery\Enums\GalleryStatusEnum::ACTIVE)
    <div class="bg-white rounded-4 border border-success-subtle shadow-sm p-4">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="d-flex align-items-center justify-content-center bg-success-subtle rounded-circle flex-shrink-0" style="width: 44px; height: 44px;">
                <i class="bi bi-share-fill text-success"></i>
            </div>
            <div>
                <h6 class="fw-bold text-dark mb-0">Galeria Publicada</h6>
                <p class="text-body-secondary small mb-0">Compartilhe o link e a senha com o cliente</p>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-7">
                <label class="form-label small fw-semibold text-secondary text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Link de Acesso</label>
                <div class="d-flex gap-2">
                    <input type="text" readonly
                           value="{{ route('gallery.login', $gallery->uuid) }}"
                           class="form-control form-control-sm bg-body-tertiary border-0 rounded-3 font-monospace text-secondary shadow-none"
                           id="gallery-share-link">
                    <button type="button"
                            class="btn btn-sm btn-dark rounded-3 px-3 shadow-sm flex-shrink-0"
                            title="Copiar Link"
                            onclick="navigator.clipboard.writeText(document.getElementById('gallery-share-link').value); this.innerHTML='<i class=\'bi bi-check-lg\'></i>'; setTimeout(() => { this.innerHTML='<i class=\'bi bi-clipboard\'></i>'; }, 2000);">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-secondary text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Senha de Acesso</label>
                <div class="d-flex gap-2">
                    <input type="text" readonly
                           value="Use o botão Editar para redefinir"
                           class="form-control form-control-sm bg-body-tertiary border-0 rounded-3 text-body-secondary shadow-none"
                           id="gallery-share-password">
                    <a href="{{ route('admin.gallery.edit', $gallery->id) }}"
                       class="btn btn-sm btn-outline-dark rounded-3 px-3 shadow-sm flex-shrink-0"
                       title="Editar Senha">
                        <i class="bi bi-key"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ====================================================================== --}}
    {{-- UPLOAD AREA                                                            --}}
    {{-- ====================================================================== --}}
    <div class="bg-white rounded-4 border border-secondary-subtle shadow-sm overflow-hidden">
        <div class="px-4 pt-4 pb-2">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-cloud-upload me-2 text-secondary"></i>Enviar Fotos
            </h5>
        </div>

        <!-- Drop Zone -->
        <div class="px-4 pb-4">
            <div class="rounded-4 text-center py-5 px-3 position-relative transition-all"
                 style="border: 2px dashed; cursor: pointer; transition: all 0.25s ease;"
                 :class="dragOver ? 'border-dark bg-body-tertiary' : 'border-secondary-subtle'"
                 :style="dragOver ? 'background-color: rgba(13,110,253,0.04);' : ''"
                 @dragover.prevent="dragOver = true"
                 @dragleave.prevent="dragOver = false"
                 @drop.prevent="handleDrop($event)"
                 @click="$refs.fileInput.click()">

                <input type="file"
                       x-ref="fileInput"
                       accept=".jpg,.jpeg,.png"
                       multiple
                       class="d-none"
                       @change="handleFileSelect($event)">

                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                     :class="dragOver ? 'bg-primary-subtle' : 'bg-body-tertiary'"
                     style="width: 80px; height: 80px; transition: background-color 0.25s ease;">
                    <i class="bi bi-cloud-arrow-up fs-2" :class="dragOver ? 'text-primary' : 'text-secondary'"></i>
                </div>
                <p class="text-body-secondary mb-1 fs-5">
                    Arraste fotos aqui ou
                    <span class="text-decoration-underline fw-semibold text-dark">clique para selecionar</span>
                </p>
                <p class="text-body-secondary small mb-0">JPG ou PNG · Máximo 15 MB cada</p>
            </div>
        </div>

        <!-- Files Selected — Confirm Upload -->
        <template x-if="files.length > 0 && !uploading">
            <div class="px-4 pb-4">
                <div class="d-flex align-items-center justify-content-between bg-body-tertiary rounded-4 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle" style="width: 44px; height: 44px;">
                            <i class="bi bi-images text-secondary fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark" x-text="files.length + ' foto' + (files.length !== 1 ? 's' : '') + ' selecionada' + (files.length !== 1 ? 's' : '')"></div>
                            <div class="text-body-secondary small" x-text="formatSize(files.reduce((s, f) => s + f.size, 0)) + ' total'"></div>
                        </div>
                    </div>
                    <button type="button"
                            @click="startUpload()"
                            class="btn btn-dark rounded-4 px-4 fw-semibold shadow-sm d-flex align-items-center gap-2"
                            style="transition: all 0.2s ease;"
                            onmouseover="this.style.transform='translateY(-1px)'"
                            onmouseout="this.style.transform='translateY(0)'">
                        <i class="bi bi-upload"></i>
                        Iniciar Upload
                    </button>
                </div>
            </div>
        </template>

        <!-- Upload Progress -->
        <template x-if="uploading">
            <div class="px-4 pb-4">
                <div class="bg-body-tertiary rounded-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="fw-semibold text-dark" x-text="'Foto ' + currentFile + ' de ' + totalFiles"></span>
                            <span class="text-body-secondary ms-2" x-text="currentFileName"></span>
                        </div>
                        <span class="fw-bold text-dark" x-text="uploadProgress + '%'"></span>
                    </div>
                    <div class="progress bg-white rounded-pill" style="height: 10px;">
                        <div class="progress-bar rounded-pill"
                             role="progressbar"
                             :style="`width: ${uploadProgress}%; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); transition: width 0.3s ease;`"
                             :aria-valuenow="uploadProgress" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- ====================================================================== --}}
    {{-- PHOTO GRID                                                             --}}
    {{-- ====================================================================== --}}
    <template x-if="photos.length > 0">
        <div>
            <!-- Selection Bar -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <button type="button"
                            @click="toggleSelectAll()"
                            class="btn btn-sm btn-light rounded-3 px-3 border border-secondary-subtle text-secondary fw-medium"
                            style="transition: all 0.2s ease;"
                            :class="selectedPhotos.length === photos.length && photos.length > 0 ? 'bg-primary-subtle text-primary border-primary-subtle' : ''">
                        <template x-if="selectedPhotos.length === photos.length && photos.length > 0">
                            <i class="bi bi-check2-square me-1"></i>
                        </template>
                        <template x-if="selectedPhotos.length !== photos.length || photos.length === 0">
                            <i class="bi bi-square me-1"></i>
                        </template>
                        <span x-text="selectedPhotos.length === photos.length && photos.length > 0 ? 'Desmarcar Todas' : 'Selecionar Todas'"></span>
                    </button>
                </div>
                <template x-if="selectedPhotos.length > 0">
                    <span class="text-body-secondary fw-medium" x-text="selectedPhotos.length + ' selecionada' + (selectedPhotos.length !== 1 ? 's' : '')"></span>
                </template>
            </div>

            <!-- Grid -->
            <div class="row g-3">
                <template x-for="photo in photos" :key="photo.id">
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="position-relative rounded-4 overflow-hidden border bg-white shadow-sm transition-all"
                             style="cursor: pointer; transition: all 0.25s ease;"
                             :class="selectedPhotos.includes(photo.id) ? 'border-primary shadow' : 'border-secondary-subtle'"
                             @mouseenter="$el.style.transform = 'translateY(-2px)'"
                             @mouseleave="$el.style.transform = 'translateY(0)'">

                            <!-- Thumbnail -->
                            <div class="ratio rounded-top" style="--bs-aspect-ratio: 75%;">
                                <img :src="photo.temporary_url"
                                     :alt="photo.original_name"
                                     class="w-100 h-100 object-fit-cover"
                                     loading="lazy"
                                     style="transition: transform 0.3s ease;"
                                     @mouseenter="$el.style.transform = 'scale(1.05)'"
                                     @mouseleave="$el.style.transform = 'scale(1)'">
                            </div>

                            <!-- Checkbox Overlay (top-left) -->
                            <div class="position-absolute top-0 start-0 p-2" style="z-index: 2;">
                                <button type="button"
                                        @click.stop="toggleSelect(photo.id)"
                                        class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center border"
                                        style="width: 32px; height: 32px; transition: all 0.2s ease;"
                                        :class="selectedPhotos.includes(photo.id)
                                            ? 'btn-primary border-primary text-white shadow-sm'
                                            : 'btn-light border-secondary-subtle text-secondary'"
                                        :title="selectedPhotos.includes(photo.id) ? 'Desmarcar' : 'Selecionar'">
                                    <i class="bi" :class="selectedPhotos.includes(photo.id) ? 'bi-check-lg' : 'bi-square'"></i>
                                </button>
                            </div>

                            <!-- Cover Star Badge (top-right) -->
                            <template x-if="photo.s3_path === coverPath">
                                <div class="position-absolute top-0 end-0 p-2" style="z-index: 2;">
                                    <span class="d-flex align-items-center justify-content-center bg-warning rounded-circle shadow-sm"
                                          style="width: 30px; height: 30px;" title="Foto de Capa">
                                        <i class="bi bi-star-fill text-white" style="font-size: 0.8rem;"></i>
                                    </span>
                                </div>
                            </template>

                            <!-- Photo Info -->
                            <div class="px-3 py-2">
                                <div class="text-truncate fw-medium text-dark small" :title="photo.original_name" x-text="photo.original_name"></div>
                                <div class="text-body-secondary" style="font-size: 0.75rem;" x-text="formatSize(photo.size_bytes)"></div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex border-top border-secondary-subtle">
                                <button type="button"
                                        @click.stop="setCover(photo.id)"
                                        class="btn btn-sm flex-fill rounded-0 text-secondary fw-medium py-2 border-end border-secondary-subtle"
                                        :class="photo.s3_path === coverPath ? 'text-warning' : ''"
                                        :title="photo.s3_path === coverPath ? 'Esta é a capa' : 'Definir como Capa'"
                                        style="transition: background-color 0.15s ease;"
                                        @mouseenter="$el.style.backgroundColor = 'rgba(0,0,0,0.03)'"
                                        @mouseleave="$el.style.backgroundColor = ''">
                                    <i class="bi" :class="photo.s3_path === coverPath ? 'bi-star-fill' : 'bi-star'"></i>
                                    <span class="ms-1" style="font-size: 0.75rem;">Capa</span>
                                </button>
                                <button type="button"
                                        @click.stop="deleteSingle(photo.id)"
                                        class="btn btn-sm flex-fill rounded-0 text-secondary fw-medium py-2"
                                        title="Remover Foto"
                                        style="transition: background-color 0.15s ease;"
                                        @mouseenter="$el.style.backgroundColor = 'rgba(220,53,69,0.06)'; $el.style.color = '#dc3545'"
                                        @mouseleave="$el.style.backgroundColor = ''; $el.style.color = ''">
                                    <i class="bi bi-trash3"></i>
                                    <span class="ms-1" style="font-size: 0.75rem;">Remover</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <!-- Empty State -->
    <template x-if="photos.length === 0 && !uploading">
        <div class="bg-body-tertiary rounded-5 py-5 px-4 text-center border border-light">
            <div class="py-5 my-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-white shadow-sm rounded-circle mb-4" style="width: 96px; height: 96px;">
                    <i class="bi bi-image fs-1 text-secondary"></i>
                </div>
                <h2 class="fw-bold text-dark mb-3">Nenhuma foto enviada ainda</h2>
                <p class="text-body-secondary fs-5 mb-0 mx-auto" style="max-width: 500px;">
                    Use a área acima para enviar as fotos da galeria. Arraste ou selecione os arquivos.
                </p>
            </div>
        </div>
    </template>

    {{-- ====================================================================== --}}
    {{-- BOTTOM ACTION BAR (sticky)                                             --}}
    {{-- ====================================================================== --}}
    <template x-if="selectedPhotos.length > 0">
        <div class="position-fixed bottom-0 start-0 end-0 bg-white border-top shadow-lg" style="z-index: 1040; margin-left: 260px;">
            <div class="d-flex align-items-center justify-content-between px-4 py-3"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center bg-primary-subtle rounded-circle" style="width: 40px; height: 40px;">
                        <i class="bi bi-check2-all text-primary"></i>
                    </div>
                    <div>
                        <span class="fw-bold text-dark" x-text="selectedPhotos.length + ' foto' + (selectedPhotos.length !== 1 ? 's' : '') + ' selecionada' + (selectedPhotos.length !== 1 ? 's' : '')"></span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <template x-if="selectedPhotos.length === 1">
                        <button type="button"
                                @click="setCover(selectedPhotos[0])"
                                class="btn btn-outline-dark rounded-4 px-4 fw-semibold d-flex align-items-center gap-2"
                                style="transition: all 0.2s ease;"
                                onmouseover="this.style.transform='translateY(-1px)'"
                                onmouseout="this.style.transform='translateY(0)'">
                            <i class="bi bi-star"></i>
                            Definir Capa
                        </button>
                    </template>
                    <button type="button"
                            @click="deleteSelected()"
                            class="btn btn-outline-danger rounded-4 px-4 fw-semibold d-flex align-items-center gap-2"
                            style="transition: all 0.2s ease;"
                            onmouseover="this.style.transform='translateY(-1px)'"
                            onmouseout="this.style.transform='translateY(0)'">
                        <i class="bi bi-trash3"></i>
                        Remover Selecionadas
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- ====================================================================== --}}
    {{-- ACTIVATE GALLERY MODAL                                                 --}}
    {{-- ====================================================================== --}}
    <div class="modal fade" id="activateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-5 overflow-hidden">
                <div class="bg-success pt-2"></div>
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-5 mt-2">
                    <div>
                        <h4 class="modal-title fw-bold text-dark mb-1">Publicar Galeria</h4>
                        <p class="text-secondary fs-6 mb-0">O link de acesso será gerado e a galeria ficará disponível para o cliente.</p>
                    </div>
                    <button type="button" class="btn-close bg-light rounded-circle p-2" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body px-5 py-4">
                    <div class="bg-success-subtle rounded-4 p-4 border border-success-subtle">
                        <label class="small text-success-emphasis fw-semibold text-uppercase d-block mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Link de Acesso</label>
                        <div class="d-flex gap-2">
                            <input type="text" readonly
                                   x-ref="galleryLink"
                                   class="form-control form-control-sm bg-white border-0 rounded-3 font-monospace text-secondary shadow-none">
                            <button type="button"
                                    @click="copyLink()"
                                    class="btn btn-sm btn-dark rounded-3 px-3 shadow-sm"
                                    title="Copiar Link">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <div class="mt-3">
                            <label class="small text-success-emphasis fw-semibold text-uppercase d-block mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Senha de Acesso</label>
                            <div class="d-flex gap-2">
                                <input type="text" readonly
                                       x-ref="galleryPassword"
                                       class="form-control form-control-sm bg-white border-0 rounded-3 font-monospace text-secondary shadow-none">
                                <button type="button"
                                        @click="copyPassword()"
                                        class="btn btn-sm btn-dark rounded-3 px-3 shadow-sm"
                                        title="Copiar Senha">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-5 gap-2 bg-white">
                    <button type="button" class="btn btn-light btn-lg rounded-4 px-4 fw-semibold" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function photoManager() {
        return {
            files: [],
            uploading: false,
            currentFile: 0,
            totalFiles: 0,
            uploadProgress: 0,
            currentFileName: '',
            deleting: false,
            selectedPhotos: [],
        photos: @json($photosJson ? json_decode($photosJson, true) : []),
            galleryStatus: '{{ $gallery->status->value }}',
            coverPath: @json($gallery->cover_photo_path),
            galleryId: {{ $gallery->id }},
            dragOver: false,
            activatedLink: '',
            activatedPassword: '',

            // ── File Handling ──────────────────────────────────────────────

            handleDrop(e) {
                this.dragOver = false;
                const droppedFiles = Array.from(e.dataTransfer.files).filter(
                    f => f.type === 'image/jpeg' || f.type === 'image/png'
                );
                if (droppedFiles.length === 0) {
                    this.showToast('warning', 'Nenhum arquivo válido encontrado. Use JPG ou PNG.');
                    return;
                }
                this.files = droppedFiles;
            },

            handleFileSelect(e) {
                const selected = Array.from(e.target.files);
                if (selected.length === 0) return;
                this.files = selected;
                e.target.value = '';
            },

            // ── Sequential Upload ──────────────────────────────────────────

            async startUpload() {
                if (this.files.length === 0) return;

                this.uploading = true;
                this.totalFiles = this.files.length;
                this.currentFile = 0;
                this.uploadProgress = 0;

                for (let i = 0; i < this.files.length; i++) {
                    this.currentFile = i + 1;
                    this.currentFileName = this.files[i].name;
                    this.uploadProgress = Math.round(((i) / this.files.length) * 100);

                    try {
                        const photo = await this.uploadFile(this.files[i]);
                        this.photos.push(photo);
                    } catch (error) {
                        console.error('Upload failed:', this.files[i].name, error);
                        this.showToast('error', `Erro ao enviar "${this.files[i].name}": ${error}`);
                    }
                }

                this.uploadProgress = 100;
                this.files = [];
                this.uploading = false;
                this.showToast('success', `${this.totalFiles} foto(s) enviada(s) com sucesso!`);
            },

            uploadFile(file) {
                return new Promise((resolve, reject) => {
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('gallery_id', this.galleryId);

                    const xhr = new XMLHttpRequest();

                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const fileProgress = Math.round((e.loaded / e.total) * 100);
                            const baseProgress = ((this.currentFile - 1) / this.totalFiles) * 100;
                            const fileWeight = (1 / this.totalFiles) * 100;
                            this.uploadProgress = Math.round(baseProgress + (fileWeight * fileProgress / 100));
                        }
                    });

                    xhr.addEventListener('load', () => {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success && response.photo) {
                                    resolve(response.photo);
                                } else {
                                    reject(response.message || 'Erro desconhecido no upload.');
                                }
                            } catch {
                                reject('Resposta inválida do servidor.');
                            }
                        } else {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                reject(response.message || `Erro ${xhr.status}`);
                            } catch {
                                reject(`Erro HTTP ${xhr.status}`);
                            }
                        }
                    });

                    xhr.addEventListener('error', () => {
                        reject('Erro de conexão. Tente novamente.');
                    });

                    xhr.open('POST', '{{ route("admin.gallery.photos.store", $gallery->id) }}');
                    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.send(formData);
                });
            },

            // ── Selection ──────────────────────────────────────────────────

            toggleSelect(photoId) {
                const idx = this.selectedPhotos.indexOf(photoId);
                if (idx > -1) {
                    this.selectedPhotos.splice(idx, 1);
                } else {
                    this.selectedPhotos.push(photoId);
                }
            },

            toggleSelectAll() {
                if (this.selectedPhotos.length === this.photos.length) {
                    this.selectedPhotos = [];
                } else {
                    this.selectedPhotos = this.photos.map(p => p.id);
                }
            },

            // ── Delete ─────────────────────────────────────────────────────

            deleteSingle(photoId) {
                if (!confirm('Tem certeza que deseja remover esta foto?')) return;
                this.selectedPhotos = [photoId];
                this.deleteSelected();
            },

            async deleteSelected() {
                if (this.selectedPhotos.length === 0) return;

                const count = this.selectedPhotos.length;
                if (!confirm(`Tem certeza que deseja remover ${count} foto${count !== 1 ? 's' : ''}?`)) {
                    this.selectedPhotos = [];
                    return;
                }

                this.deleting = true;

                try {
                    const response = await fetch('{{ route("admin.gallery.photos.destroy", $gallery->id) }}', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ photo_ids: this.selectedPhotos }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        const deletedIds = [...this.selectedPhotos];
                        this.photos = this.photos.filter(p => !deletedIds.includes(p.id));
                        this.selectedPhotos = [];
                        this.showToast('success', data.message || `${count} foto(s) removida(s)!`);
                    } else {
                        this.showToast('error', data.message || 'Erro ao remover fotos.');
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    this.showToast('error', 'Erro de conexão ao remover fotos.');
                } finally {
                    this.deleting = false;
                }
            },

            // ── Set Cover ──────────────────────────────────────────────────

            async setCover(photoId) {
                try {
                    const response = await fetch('{{ route("admin.gallery.photos.setCover", $gallery->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ photo_id: photoId }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        const photo = this.photos.find(p => p.id === photoId);
                        if (photo) {
                            this.coverPath = photo.s3_path;
                        }
                        this.showToast('success', data.message || 'Capa definida com sucesso!');
                    } else {
                        this.showToast('error', data.message || 'Erro ao definir capa.');
                    }
                } catch (error) {
                    console.error('Set cover error:', error);
                    this.showToast('error', 'Erro de conexão ao definir capa.');
                }
            },

            // ── Activate Gallery ───────────────────────────────────────────

            async activateGallery() {
                if (!confirm('Publicar galeria? O link será gerado e a galeria ficará acessível para o cliente.')) return;

                try {
                    const response = await fetch('{{ route("admin.gallery.activate", $gallery->id) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.galleryStatus = 'active';
                        this.activatedLink = data.url || '';
                        this.activatedPassword = data.password || '';

                        this.$nextTick(() => {
                            if (this.$refs.galleryLink) {
                                this.$refs.galleryLink.value = this.activatedLink;
                            }
                            if (this.$refs.galleryPassword) {
                                this.$refs.galleryPassword.value = this.activatedPassword;
                            }
                            const modal = new bootstrap.Modal(document.getElementById('activateModal'));
                            modal.show();
                        });

                        this.showToast('success', 'Galeria publicada com sucesso!');
                    } else {
                        this.showToast('error', data.message || 'Erro ao ativar galeria.');
                    }
                } catch (error) {
                    console.error('Activate error:', error);
                    this.showToast('error', 'Erro de conexão ao ativar galeria.');
                }
            },

            // ── Clipboard ──────────────────────────────────────────────────

            copyLink() {
                navigator.clipboard.writeText(this.activatedLink).then(() => {
                    this.showToast('success', 'Link copiado!');
                });
            },

            copyPassword() {
                navigator.clipboard.writeText(this.activatedPassword).then(() => {
                    this.showToast('success', 'Senha copiada!');
                });
            },

            // ── Utilities ──────────────────────────────────────────────────

            formatSize(bytes) {
                if (bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
            },

            showToast(type, message) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type, message },
                }));
            },
        };
    }
</script>
@endsection
