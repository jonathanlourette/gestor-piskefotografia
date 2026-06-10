@php
    /** @var \App\Domains\Order\Order $order */
@endphp

@extends('site::layouts.site')

@section('title', 'Enviar Fotos — Piske Memórias')
@section('header_solid', true)

@section('content')
    <div x-data="orderUpload({
        orderId: {{ $order->id }},
        items: {{ $order->items->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->product->name,
            'photo_limit' => $item->product->photo_limit,
            'uploaded_count' => $item->photos->count(),
            'photos' => $item->photos->map(fn($p) => ['id' => $p->id, 'original_name' => $p->original_name, 'url' => $p->temporary_url])
        ])->toJson() }}
    })" class="container-fluid px-4 px-lg-5 py-5">
        <div class="col-lg-8 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="fw-bolder mb-2 text-dark display-4" style="letter-spacing: -0.02em;">Enviar Fotos</h1>
                <p class="text-body-secondary mb-0 fs-5">Pedido <span class="font-monospace bg-body-tertiary px-3 py-2 rounded-4 fw-bold text-dark">#{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</span></p>
            </div>

            <!-- Global Progress -->
            <div class="bg-white rounded-4 border border-secondary-subtle p-4 mb-5 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold text-dark fs-5">Progresso Geral</span>
                    <span class="text-body-secondary fs-5" x-text="`${totalUploaded} de ${totalRequired} fotos`"></span>
                </div>
                <div class="progress bg-body-tertiary rounded-pill" style="height: 12px;">
                    <div class="progress-bar rounded-pill transition-all" role="progressbar"
                         :style="`width: ${progressPercent}%; background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);`"
                         :aria-valuenow="progressPercent" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <!-- Items with Upload -->
            <template x-for="item in items" :key="item.id">
                <div class="bg-white rounded-4 border border-secondary-subtle mb-4 overflow-hidden shadow-sm">
                    <!-- Item Header -->
                    <div class="px-4 pt-4 pb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark mb-1 fs-4" x-text="item.name"></h5>
                            <span class="text-body-secondary fs-5" x-text="`${item.uploaded_count} de ${item.photo_limit} fotos enviadas`"></span>
                        </div>
                        <template x-if="item.uploaded_count >= item.photo_limit">
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill px-3 py-2 fw-medium">
                                <i class="bi bi-check-lg me-1"></i>Completo
                            </span>
                        </template>
                    </div>

                    <!-- Upload Zone (hidden when complete) -->
                    <template x-if="item.uploaded_count < item.photo_limit">
                        <div class="px-4 pb-3">
                            <!-- Drop Zone -->
                            <div class="rounded-4 text-center py-5 px-3 border-dashed position-relative transition-all"
                                 style="border-style: dashed !important; border-width: 2px; cursor: pointer;"
                                 :class="item.dragover ? 'border-dark bg-body-tertiary' : 'border-secondary-subtle'"
                                 @dragover.prevent="item.dragover = true"
                                 @dragleave.prevent="item.dragover = false"
                                 @drop.prevent="handleDrop($event, item)"
                                 @click="document.getElementById('file-input-' + item.id).click()">
                                <input type="file"
                                       :id="'file-input-' + item.id"
                                       accept="image/jpeg,image/png"
                                       multiple
                                       class="d-none"
                                       @change="handleFileSelect($event, item)">
                                <div class="bg-body-tertiary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-cloud-upload fs-2 text-secondary"></i>
                                </div>
                                <p class="text-body-secondary mb-0 fs-5">Arraste fotos aqui ou <span class="text-decoration-underline fw-semibold text-dark">clique para selecionar</span></p>
                                <p class="text-body-secondary small mt-2 mb-0">JPG ou PNG · Máximo 15MB cada</p>
                            </div>
                        </div>
                    </template>

                    <!-- Miniaturas: fotos enviadas + uploads em andamento -->
                    <template x-if="item.photos.length > 0 || item.queue.length > 0">
                        <div class="px-4 pb-4">
                            <div class="d-flex flex-wrap gap-3">
                                <!-- Fotos já enviadas -->
                                <template x-for="photo in item.photos" :key="'photo-' + photo.id">
                                    <div class="position-relative" style="width: 96px;">
                                        <div class="rounded-3 overflow-hidden border border-secondary-subtle bg-body-tertiary" style="width: 96px; height: 96px;">
                                            <img :src="photo.url" :alt="photo.original_name" class="w-100 h-100" style="object-fit: cover;" loading="lazy">
                                        </div>
                                        <button type="button"
                                                class="btn btn-danger rounded-circle position-absolute d-flex align-items-center justify-content-center p-0 shadow"
                                                style="width: 28px; height: 28px; top: -9px; right: -9px;"
                                                :disabled="photo.removing"
                                                @click="removePhoto(item, photo)"
                                                aria-label="Remover foto">
                                            <span class="spinner-border spinner-border-sm" x-show="photo.removing" style="display: none; width: 13px; height: 13px;"></span>
                                            <i class="bi bi-trash3-fill" x-show="!photo.removing" style="font-size: 0.78rem;"></i>
                                        </button>
                                        <div class="text-truncate text-center text-secondary mt-1" style="font-size: 0.65rem;" x-text="photo.original_name"></div>
                                    </div>
                                </template>

                                <!-- Uploads em andamento / com erro -->
                                <template x-for="entry in item.queue" :key="'queue-' + entry.id">
                                    <div class="position-relative" style="width: 96px;">
                                        <div class="rounded-3 overflow-hidden border position-relative bg-body-tertiary"
                                             :class="entry.status === 'error' ? 'border-danger' : 'border-secondary-subtle'"
                                             style="width: 96px; height: 96px;">
                                            <img :src="entry.previewUrl" :alt="entry.file.name" class="w-100 h-100" style="object-fit: cover;"
                                                 x-show="entry.status !== 'error'"
                                                 x-on:error="$el.style.visibility = 'hidden'">
                                            <template x-if="entry.status === 'pending'">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-1" style="background: rgba(255, 255, 255, 0.65);">
                                                    <i class="bi bi-clock text-secondary fs-5"></i>
                                                    <span class="text-secondary fw-medium" style="font-size: 0.65rem;">Na fila</span>
                                                </div>
                                            </template>
                                            <template x-if="entry.status === 'uploading'">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-1" style="background: rgba(15, 15, 35, 0.55);">
                                                    <span class="spinner-border spinner-border-sm text-white" x-show="entry.progress >= 100"></span>
                                                    <span class="text-white fw-bold small" x-show="entry.progress < 100" x-text="`${entry.progress}%`"></span>
                                                    <div class="progress rounded-pill" style="height: 5px; width: 70%; background: rgba(255, 255, 255, 0.3);">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-white rounded-pill"
                                                             :style="`width: ${entry.progress}%; transition: width 0.2s ease;`"></div>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="entry.status === 'error'">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(220, 53, 69, 0.75);">
                                                    <i class="bi bi-x-lg text-white fw-bold" style="font-size: 2rem; -webkit-text-stroke: 1.5px #fff;"></i>
                                                </div>
                                            </template>
                                        </div>
                                        <template x-if="entry.status === 'error'">
                                            <button type="button"
                                                    class="btn btn-danger rounded-circle position-absolute d-flex align-items-center justify-content-center p-0 shadow"
                                                    style="width: 28px; height: 28px; top: -9px; right: -9px;"
                                                    @click="removeQueueEntry(item, entry)"
                                                    aria-label="Descartar">
                                                <i class="bi bi-x-lg" style="font-size: 0.78rem;"></i>
                                            </button>
                                        </template>
                                        <div class="text-truncate text-center mt-1"
                                             :class="entry.status === 'error' ? 'text-danger fw-medium' : 'text-secondary'"
                                             style="font-size: 0.65rem;"
                                             x-text="entry.status === 'error' ? entry.errorMessage : entry.file.name"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Finalize Button -->
            <form action="{{ route('site.order.finalize', $order->id) }}" method="POST">
                @csrf
                <div class="d-flex flex-column flex-md-row gap-3 mt-2 mb-5">
                    <button
                        type="submit"
                        class="btn btn-lg rounded-4 fw-semibold px-5 flex-grow-1"
                        :class="allComplete ? 'btn-dark' : 'btn-outline-dark'"
                        :disabled="!allComplete"
                    >
                        <i class="bi bi-check2-all me-2"></i>Finalizar Envio
                    </button>
                    <a href="{{ route('site.landing.index') }}" class="btn btn-light btn-lg rounded-4 fw-semibold text-secondary">
                        Voltar à Página Inicial
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function orderUpload(initialState) {
            return {
                orderId: initialState.orderId,
                items: initialState.items.map(item => ({
                    ...item,
                    photos: item.photos.map(photo => ({ ...photo, removing: false })),
                    queue: [],
                    dragover: false,
                })),
                pendingJobs: [],
                activeUploads: 0,
                maxConcurrentUploads: 2,

                get totalUploaded() {
                    return this.items.reduce((sum, item) => sum + item.uploaded_count, 0);
                },

                get totalRequired() {
                    return this.items.reduce((sum, item) => sum + item.photo_limit, 0);
                },

                get progressPercent() {
                    if (this.totalRequired === 0) return 0;
                    return Math.round((this.totalUploaded / this.totalRequired) * 100);
                },

                get allComplete() {
                    return this.items.every(item => item.uploaded_count >= item.photo_limit);
                },

                handleFileSelect(event, item) {
                    const files = Array.from(event.target.files);
                    this.processFiles(files, item);
                    event.target.value = '';
                },

                handleDrop(event, item) {
                    item.dragover = false;
                    const files = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
                    this.processFiles(files, item);
                },

                processFiles(files, item) {
                    const remaining = item.photo_limit - item.uploaded_count;
                    const toUpload = files.slice(0, remaining);

                    if (files.length > remaining) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'warning', message: `Limite de ${item.photo_limit} fotos: apenas ${remaining} foto(s) serão enviadas.` }
                        }));
                    }

                    toUpload.forEach(file => {
                        const entry = {
                            id: Date.now() + Math.random(),
                            file: file,
                            previewUrl: URL.createObjectURL(file),
                            status: 'pending',
                            progress: 0,
                            errorMessage: '',
                        };
                        item.queue.push(entry);
                        // Entry reativa para a UI atualizar; File cru para o FormData (proxy quebra o envio)
                        this.pendingJobs.push({ item, entry: item.queue[item.queue.length - 1], file });
                    });

                    this.pumpUploadQueue();
                },

                pumpUploadQueue() {
                    while (this.activeUploads < this.maxConcurrentUploads && this.pendingJobs.length > 0) {
                        const job = this.pendingJobs.shift();
                        this.activeUploads++;
                        job.entry.status = 'uploading';
                        this.uploadFile(job.item, job.entry, job.file);
                    }
                },

                removeQueueEntry(item, entry) {
                    item.queue = item.queue.filter(e => e.id !== entry.id);
                    setTimeout(() => URL.revokeObjectURL(entry.previewUrl), 1000);
                },

                async removePhoto(item, photo) {
                    if (!await window.confirmModal('Deseja realmente remover esta foto do pedido?')) {
                        return;
                    }

                    photo.removing = true;

                    try {
                        const response = await fetch('{{ route("site.order.removePhoto", $order->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ photo_id: photo.id }),
                        });

                        const data = await response.json();

                        if (data.success) {
                            item.photos = item.photos.filter(p => p.id !== photo.id);
                            item.uploaded_count--;
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'success', message: data.message || 'Foto removida com sucesso!' }
                            }));
                        } else {
                            photo.removing = false;
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'error', message: data.message || 'Erro ao remover a foto.' }
                            }));
                        }
                    } catch {
                        photo.removing = false;
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'error', message: 'Erro de conexão. Tente novamente.' }
                        }));
                    }
                },

                uploadFile(item, entry, file) {
                    const formData = new FormData();
                    formData.append('order_item_id', item.id);
                    formData.append('photo', file);

                    const xhr = new XMLHttpRequest();

                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            entry.progress = Math.round((e.loaded / e.total) * 100);
                        }
                    });

                    xhr.addEventListener('load', () => {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    entry.status = 'success';
                                    item.uploaded_count++;
                                    if (response.photo) {
                                        item.photos.push({
                                            id: response.photo.id,
                                            original_name: response.photo.original_name,
                                            url: response.photo.url,
                                            removing: false,
                                        });
                                    }
                                    this.removeQueueEntry(item, entry);
                                } else {
                                    entry.status = 'error';
                                    entry.errorMessage = response.message || 'Erro no upload.';
                                }
                            } catch {
                                entry.status = 'error';
                                entry.errorMessage = 'Resposta inválida do servidor.';
                            }
                        } else {
                            entry.status = 'error';
                            try {
                                const response = JSON.parse(xhr.responseText);
                                entry.errorMessage = response.message || `Erro ${xhr.status}`;
                            } catch {
                                entry.errorMessage = `Erro ${xhr.status}`;
                            }
                        }
                    });

                    xhr.addEventListener('error', () => {
                        entry.status = 'error';
                        entry.errorMessage = 'Erro de conexão. Tente novamente.';
                    });

                    // Libera a vaga na fila ao terminar (sucesso, erro ou abort)
                    xhr.addEventListener('loadend', () => {
                        this.activeUploads--;
                        this.pumpUploadQueue();
                    });

                    xhr.open('POST', '{{ route("site.order.uploadPhoto", $order->id) }}');
                    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.send(formData);
                },
            };
        }
    </script>
@endpush
