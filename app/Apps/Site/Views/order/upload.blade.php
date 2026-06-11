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
                                       accept="image/jpeg,image/png"
                                       multiple
                                       class="d-none"
                                       x-on:change="handleFileSelect($event, item)">
                                <div class="bg-body-tertiary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-cloud-upload fs-2 text-secondary"></i>
                                </div>
                                 <p class="text-body-secondary mb-0 fs-5" x-text="'Selecione até ' + (item.photo_limit - item.uploaded_count - item.queue.length) + ' foto(s)'"></p>
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
                                    <div class="position-relative" style="width: 96px;">
                                        <div class="rounded-3 overflow-hidden border position-relative bg-body-tertiary"
                                             :class="entry.status === 'error' ? 'border-danger' : 'border-secondary-subtle'"
                                             style="width: 96px; height: 96px;">
                                            <img :src="entry.previewUrl" :alt="entry.file.name" class="w-100 h-100" style="object-fit: cover;"
                                                 decoding="async"
                                                 x-show="entry.previewUrl && entry.status !== 'error'"
                                                 x-on:error="$el.style.visibility = 'hidden'">
                                            <template x-if="entry.status === 'pending'">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-1" style="background: rgba(255, 255, 255, 0.65);">
                                                    <i class="bi bi-clock text-secondary fs-5"></i>
                                                    <span class="text-secondary fw-medium" style="font-size: 0.65rem;">Na fila</span>
                                                </div>
                                            </template>
                                            <template x-if="entry.status === 'uploading'">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-1" style="background: rgba(15, 15, 35, 0.55);">
                                                     <span class="text-white fw-bold small" x-text="entry.progress + '%'"></span>
                                                     <div class="progress rounded-pill" style="height: 5px; width: 70%; background: rgba(255, 255, 255, 0.3);">
                                                         <div class="progress-bar bg-white rounded-pill"
                                                              :style="'width: ' + entry.progress + '%'"></div>
                                                     </div>
                                                </div>
                                            </template>
                                            <template x-if="entry.status === 'processing'">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-1" style="background: rgba(15, 15, 35, 0.65);">
                                                    <span class="text-white fw-medium" style="font-size: 0.65rem;">Processando...</span>
                                                    <div class="progress rounded-pill" style="height: 5px; width: 70%; background: rgba(255, 255, 255, 0.3);">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-white rounded-pill" style="width: 100%;"></div>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="entry.status === 'error'">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-1" style="background: rgba(220, 53, 69, 0.75);">
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
        // Mapa não-reativo para guardar File objects crus (proxy do Alpine quebra o FormData)
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
                        const entryId = Date.now() + Math.random();
                        const entry = {
                            id: entryId,
                            fileName: file.name,
                            previewUrl: '',
                            status: 'pending',
                            progress: 0,
                            errorMessage: '',
                        };
                        // Guarda o File cru fora da reatividade do Alpine
                        rawFiles.set(entryId, file);
                        item.queue.push(entry);
                        const reactiveEntry = item.queue[item.queue.length - 1];
                        this.makePreview(file).then(url => { reactiveEntry.previewUrl = url; });
                        this.pendingJobs.push({ item, entry: reactiveEntry, file });
                    });

                    this.pumpUploadQueue();
                },

                /**
                 * Gera um preview minúsculo (192px) fora da thread principal.
                 * Usar o arquivo original direto força o navegador a decodificar a
                 * imagem inteira (vários megapixels) para um tile de 96px, travando
                 * as animações das barras de progresso.
                 */
                async makePreview(file) {
                    try {
                        const bitmap = await createImageBitmap(file, { resizeWidth: 192, resizeQuality: 'low' });
                        const canvas = document.createElement('canvas');
                        canvas.width = bitmap.width;
                        canvas.height = bitmap.height;
                        canvas.getContext('2d').drawImage(bitmap, 0, 0);
                        bitmap.close();

                        return await new Promise(resolve => {
                            canvas.toBlob(
                                blob => resolve(blob ? URL.createObjectURL(blob) : URL.createObjectURL(file)),
                                'image/jpeg',
                                0.7
                            );
                        });
                    } catch {
                        return URL.createObjectURL(file);
                    }
                },

                /**
                 * Redimensiona a foto original no navegador antes do upload.
                 * Fotos de 12MP+ (~5-15MB) são convertidas para JPEG 85% com
                 * no máximo 3000px, resultando em ~1-3MB. Evita erro 413 em
                 * produção onde o nginx tem client_max_body_size limitado.
                 */
                async resizeForUpload(file) {
                    if (file.type === 'image/png') {
                        // PNGs não redimensionamos (pode ter transparência)
                        return file;
                    }

                    try {
                        const bitmap = await createImageBitmap(file);
                        var maxDim = 4000;

                        if (bitmap.width <= maxDim && bitmap.height <= maxDim && file.size <= 4 * 1024 * 1024) {
                            bitmap.close();
                            return file; // Já é pequena o suficiente
                        }

                        var ratio = Math.min(maxDim / bitmap.width, maxDim / bitmap.height, 1);
                        var canvas = document.createElement('canvas');
                        canvas.width = Math.round(bitmap.width * ratio);
                        canvas.height = Math.round(bitmap.height * ratio);

                        var ctx = canvas.getContext('2d');
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
                        bitmap.close();

                        var blob = await new Promise(function(resolve) {
                            canvas.toBlob(function(b) { resolve(b); }, 'image/jpeg', 0.85);
                        });

                        return new File([blob], file.name.replace(/\.\w+$/, '.jpg'), { type: 'image/jpeg' });
                    } catch {
                        return file; // Fallback: envia original
                    }
                },

                /**
                 * Gera a miniatura (600px, JPEG) no navegador, fora da thread principal.
                 * Evita o GD no servidor single-thread, que segurava a requisição
                 * seguinte e deixava todo o fluxo síncrono.
                 */
                async makeThumbnail(file) {
                    try {
                        const bitmap = await createImageBitmap(file);
                        const maxSize = 600;

                        if (bitmap.width <= maxSize && bitmap.height <= maxSize) {
                            bitmap.close();
                            return null; // Servidor usa a própria foto como miniatura
                        }

                        const ratio = Math.min(maxSize / bitmap.width, maxSize / bitmap.height);
                        const canvas = document.createElement('canvas');
                        canvas.width = Math.round(bitmap.width * ratio);
                        canvas.height = Math.round(bitmap.height * ratio);

                        const ctx = canvas.getContext('2d');
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
                        bitmap.close();

                        return await new Promise(resolve => {
                            canvas.toBlob(blob => resolve(blob), 'image/jpeg', 0.82);
                        });
                    } catch {
                        return null; // Fallback: servidor gera com GD
                    }
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
                    const index = item.queue.findIndex(e => e.id === entry.id);
                    if (index !== -1) {
                        item.queue.splice(index, 1);
                    }
                    rawFiles.delete(entry.id);
                    setTimeout(() => { if (entry.previewUrl) URL.revokeObjectURL(entry.previewUrl); }, 1000);
                },

                retryUpload(item, entry) {
                    var rawFile = rawFiles.get(entry.id);
                    if (!rawFile) return;
                    entry.status = 'pending';
                    entry.progress = 0;
                    entry.errorMessage = '';
                    this.pendingJobs.push({ item: item, entry: entry, file: rawFile });
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

                async uploadFile(item, entry, file) {
                    const formData = new FormData();
                    formData.append('order_item_id', item.id);

                    // Redimensiona a foto original no frontend para evitar 413
                    const optimizedFile = await this.resizeForUpload(file);
                    formData.append('photo', optimizedFile);

                    const thumbnail = await this.makeThumbnail(optimizedFile);
                    if (thumbnail) {
                        formData.append('thumbnail', thumbnail, 'thumb.jpg');
                    }
                    // Se não gerou thumbnail, o backend usará a original como fallback

                    const xhr = new XMLHttpRequest();

                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const percent = Math.round((e.loaded / e.total) * 100);
                            // Só atualiza o estado reativo quando o % inteiro muda (evita re-renders excessivos)
                            if (percent !== entry.progress) {
                                entry.progress = percent;
                            }
                        }
                    });

                    // Bytes enviados: agora o servidor converte a miniatura e salva
                    xhr.upload.addEventListener('load', () => {
                        entry.status = 'processing';
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
