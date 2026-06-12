@php
    /** @var \App\Domains\Order\Order $order */

    $productCounts = $order->items->countBy('product_id');
    $productPosition = [];
    $itemsPayload = $order->items->map(function ($item) use ($productCounts, &$productPosition) {
        $productPosition[$item->product_id] = ($productPosition[$item->product_id] ?? 0) + 1;

        $name = $item->product->name;
        if ($productCounts[$item->product_id] > 1) {
            $name .= ' · Pacote '.$productPosition[$item->product_id].' de '.$productCounts[$item->product_id];
        }

        return [
            'id' => $item->id,
            'name' => $name,
            'photo_limit' => $item->photoLimit(),
            'uploaded_count' => $item->photos->count(),
            'photos' => $item->photos->map(fn ($p) => ['id' => $p->id, 'original_name' => $p->original_name, 'url' => $p->thumbnail_url]),
        ];
    });
@endphp

@extends('site::layouts.site')

@section('title', 'Enviar Fotos — Piske Memórias')
@section('header_solid', true)

@section('content')
    <div x-data="orderUpload({
        orderId: {{ $order->id }},
        items: {{ $itemsPayload->toJson() }}
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
                    <span class="text-body-secondary fs-5" x-text="totalUploaded + ' de ' + totalRequired + ' fotos'"></span>
                </div>
                <div class="progress bg-body-tertiary rounded-pill" style="height: 12px;">
                    <div class="progress-bar rounded-pill transition-all" role="progressbar"
                         :style="'width: ' + progressPercent + '%; background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);'"
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
                             <span class="text-body-secondary fs-5" x-text="item.uploaded_count + ' de ' + item.photo_limit + ' fotos enviadas'"></span>
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
                                 x-on:dragover.prevent="item.dragover = true"
                                 x-on:dragleave.prevent="item.dragover = false"
                                 x-on:drop.prevent="handleDrop($event, item)"
                                 x-on:click="document.getElementById('file-input-' + item.id).click()">
                                <input type="file"
                                       :id="'file-input-' + item.id"
                                       accept="image/*"
                                       multiple
                                       class="d-none"
                                       x-on:change="handleFileSelect($event, item)">
                                <div class="bg-body-tertiary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-cloud-upload fs-2 text-secondary"></i>
                                </div>
                                 <p class="text-body-secondary mb-0 fs-5" x-text="'Selecione até ' + (item.photo_limit - item.uploaded_count - item.queue.length) + ' foto(s)'"></p>
                                <p class="text-body-secondary small mt-2 mb-0">Qualquer formato · Máximo 20MB cada</p>
                            </div>
                        </div>
                    </template>

                    <!-- Miniaturas: fotos enviadas + uploads em andamento -->
                    <div x-show="item.photos.length > 0 || item.queue.length > 0" class="px-4 pb-4" style="display: none;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 0.75rem;">
                                <!-- Fotos já enviadas -->
                                <template x-for="photo in item.photos" :key="'photo-' + photo.id">
                                    <div class="position-relative">
                                        <div class="rounded-3 overflow-hidden border border-secondary-subtle bg-body-tertiary" style="aspect-ratio: 1;">
                                            <img :src="photo.url" :alt="photo.original_name" class="w-100 h-100" style="object-fit: cover;" loading="lazy" decoding="async">
                                        </div>
                                        <button type="button"
                                                class="btn btn-danger rounded-circle position-absolute d-flex align-items-center justify-content-center p-0 shadow"
                                                style="width: 28px; height: 28px; top: -9px; right: -9px;"
                                                :disabled="photo.removing"
                                                x-on:click="removePhoto(item, photo)"
                                                aria-label="Remover foto">
                                            <span class="spinner-border spinner-border-sm" x-show="photo.removing" style="display: none; width: 13px; height: 13px;"></span>
                                            <i class="bi bi-trash3-fill" x-show="!photo.removing" style="font-size: 0.78rem;"></i>
                                        </button>
                                        <div class="text-truncate text-center text-secondary mt-1" style="font-size: 0.65rem;" x-text="photo.original_name"></div>
                                    </div>
                                </template>

                                <!-- Uploads em andamento / com erro -->
                                <template x-for="entry in item.queue" :key="'queue-' + entry.id">
                                    <div class="position-relative">
                                        <div class="rounded-3 overflow-hidden border position-relative bg-body-tertiary"
                                             :class="entry.status === 'error' ? 'border-danger' : 'border-secondary-subtle'"
                                             style="aspect-ratio: 1;">
                                            <template x-if="entry.status === 'pending'">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-1">
                                                    <div class="spinner-border spinner-border-sm text-secondary" role="status" style="width: 1.2rem; height: 1.2rem;">
                                                        <span class="visually-hidden">Na fila...</span>
                                                    </div>
                                                    <span class="text-secondary fw-medium" style="font-size: 0.65rem;">Na fila</span>
                                                </div>
                                            </template>
                                            <template x-if="entry.status === 'uploading'">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-1 bg-dark bg-opacity-50">
                                                     <span class="text-white fw-bold small" x-text="entry.progress + '%'"></span>
                                                     <div class="progress rounded-pill" style="height: 5px; width: 70%; background: rgba(255, 255, 255, 0.3);">
                                                         <div class="progress-bar bg-white rounded-pill"
                                                              :style="'width: ' + entry.progress + '%'"></div>
                                                     </div>
                                                </div>
                                            </template>
                                            <template x-if="entry.status === 'processing'">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-1 bg-dark bg-opacity-50">
                                                    <div class="spinner-border spinner-border-sm text-white" role="status" style="width: 1.2rem; height: 1.2rem;">
                                                        <span class="visually-hidden">Processando...</span>
                                                    </div>
                                                    <span class="text-white fw-medium" style="font-size: 0.65rem;">Processando...</span>
                                                </div>
                                            </template>
                                            <template x-if="entry.status === 'error'">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-1 bg-danger bg-opacity-75">
                                                    <i class="bi bi-x-lg text-white fw-bold" style="font-size: 1.5rem; -webkit-text-stroke: 1.5px #fff;"></i>
                                                    <button type="button"
                                                            class="btn btn-sm btn-light rounded-pill px-2 py-0"
                                                            style="font-size: 0.55rem;"
                                                            x-on:click="retryUpload(item, entry)">
                                                        <i class="bi bi-arrow-clockwise"></i> Repetir
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                        <template x-if="entry.status === 'error'">
                                            <button type="button"
                                                    class="btn btn-danger rounded-circle position-absolute d-flex align-items-center justify-content-center p-0 shadow"
                                                    style="width: 28px; height: 28px; top: -9px; right: -9px;"
                                                    x-on:click="removeQueueEntry(item, entry)"
                                                    aria-label="Descartar">
                                                <i class="bi bi-x-lg" style="font-size: 0.78rem;"></i>
                                            </button>
                                        </template>
                                        <div class="text-truncate text-center mt-1 text-secondary"
                                             style="font-size: 0.65rem;"
                                             x-text="entry.fileName"></div>
                                        <template x-if="entry.status === 'error' && entry.errorMessage">
                                            <div class="text-danger fw-medium text-center"
                                                 style="font-size: 0.55rem; line-height: 1.2;"
                                                 x-text="entry.errorMessage"></div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Finalize Button -->
            <div class="d-flex flex-column flex-md-row gap-3 mt-2 mb-5">
                <button
                    type="button"
                    class="btn btn-lg rounded-4 fw-semibold px-5 flex-grow-1"
                    :class="allComplete ? 'btn-dark' : 'btn-outline-dark'"
                    :disabled="finalizing || !allComplete"
                    x-on:click="finalizeOrder()"
                >
                    <span x-show="!finalizing">
                        <i class="bi bi-check2-all me-2"></i>Finalizar Envio
                    </span>
                    <span x-show="finalizing" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2"></span>Finalizando...
                    </span>
                </button>
                <a href="{{ route('site.landing.index') }}" class="btn btn-light btn-lg rounded-4 fw-semibold text-secondary">
                    Voltar à Página Inicial
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Mapa não-reativo para guardar File objects crus (proxy do Alpine quebra o FormData)
        // Ainda necessário porque o upload envia o File original diretamente ao backend.
        var rawFiles = new Map();

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
                maxConcurrentUploads: 1,
                finalizing: false,

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
                    var hasErrors = this.items.some(function(item) {
                        return item.queue.some(function(e) { return e.status === 'error'; });
                    });
                    if (hasErrors) return false;
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
                    const remaining = item.photo_limit - item.uploaded_count - item.queue.length;

                    if (remaining <= 0) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'error', message: 'Todas as vagas deste pacote já foram preenchidas.' }
                        }));
                        return;
                    }

                    if (files.length > remaining) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'error', message: 'Você selecionou ' + files.length + ' fotos, mas só restam ' + remaining + ' vaga(s). Apenas as primeiras ' + remaining + ' fotos serão enviadas.' }
                        }));
                    }

                    var toUpload = files.slice(0, remaining);
                    toUpload.forEach(file => {
                        const entryId = '' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                        const entry = {
                            id: entryId,
                            fileName: file.name,
                            status: 'pending',
                            progress: 0,
                            errorMessage: '',
                        };
                        rawFiles.set(entryId, file);
                        item.queue.push(entry);
                        this.pendingJobs.push({ item, entryId });
                    });

                    this.pumpUploadQueue();
                },

                pumpUploadQueue() {
                    while (this.activeUploads < this.maxConcurrentUploads && this.pendingJobs.length > 0) {
                        const job = this.pendingJobs.shift();
                        var entry = null;
                        for (var i = 0; i < job.item.queue.length; i++) {
                            if (job.item.queue[i].id === job.entryId) {
                                entry = job.item.queue[i];
                                break;
                            }
                        }
                        if (!entry || entry.status === 'success') { continue; }
                        var rawFile = rawFiles.get(job.entryId);
                        if (!rawFile) { continue; }
                        this.activeUploads++;
                        entry.status = 'uploading';
                        entry.progress = 0;
                        this.uploadFile(job.item, entry, rawFile);
                    }
                },

                removeQueueEntry(item, entry) {
                    const index = item.queue.findIndex(e => e.id === entry.id);
                    if (index !== -1) {
                        item.queue.splice(index, 1);
                    }
                    rawFiles.delete(entry.id);
                },

                retryUpload(item, entry) {
                    if (!rawFiles.has(entry.id)) return;
                    entry.status = 'pending';
                    entry.progress = 0;
                    entry.errorMessage = '';
                    this.pendingJobs.push({ item: item, entryId: entry.id });
                    this.pumpUploadQueue();
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
                    var self = this;
                    const formData = new FormData();
                    formData.append('order_item_id', item.id);
                    formData.append('photo', file);

                    const xhr = new XMLHttpRequest();

                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percent = Math.round((e.loaded / e.total) * 100);
                            if (percent !== entry.progress) {
                                entry.progress = percent;
                            }
                        }
                    });

                    xhr.upload.addEventListener('load', function() {
                        entry.status = 'processing';
                    });

                    xhr.addEventListener('load', function() {
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
                                    self.removeQueueEntry(item, entry);
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
                                 var msg = response.message || ('Erro ' + xhr.status);
                                 if (msg === 'validation.uploaded') {
                                     msg = 'Falha no envio. Tente novamente.';
                                 }
                                 entry.errorMessage = msg;
                             } catch {
                                 if (xhr.status === 413) {
                                     entry.errorMessage = 'Arquivo muito grande.';
                                 } else {
                                     entry.errorMessage = 'Erro ' + xhr.status;
                                 }
                             }
                        }
                    });

                    xhr.addEventListener('error', function() {
                        entry.status = 'error';
                        entry.errorMessage = 'Erro de conexão. Tente novamente.';
                    });

                    xhr.addEventListener('loadend', function() {
                        self.activeUploads--;
                        self.pumpUploadQueue();
                    });

                    xhr.open('POST', '{{ route("site.order.uploadPhoto", $order->id) }}');
                    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.send(formData);
                },

                async finalizeOrder() {
                    if (this.finalizing || !this.allComplete) return;
                    this.finalizing = true;
                    try {
                        const response = await fetch('{{ route("site.order.finalize", $order->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });
                        const data = await response.json();
                        if (data.success && data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            this.finalizing = false;
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'error', message: data.message || 'Erro ao finalizar pedido.' }
                            }));
                        }
                    } catch {
                        this.finalizing = false;
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'error', message: 'Erro de conexão. Tente novamente.' }
                        }));
                    }
                },
            };
        }
    </script>
@endpush
