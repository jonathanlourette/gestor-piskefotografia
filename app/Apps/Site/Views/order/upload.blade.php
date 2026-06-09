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
            'photos' => $item->photos->map(fn($p) => ['id' => $p->id, 'original_name' => $p->original_name])
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

                    <!-- Uploaded Photos Preview -->
                    <template x-if="item.photos.length > 0">
                        <div class="px-4 pb-3">
                            <div class="d-flex flex-wrap gap-2">
                                <template x-for="photo in item.photos" :key="photo.id">
                                    <div class="position-relative rounded-3 overflow-hidden border border-secondary-subtle" style="width: 72px; height: 72px;">
                                        <div class="w-100 h-100 bg-body-tertiary d-flex align-items-center justify-content-center">
                                            <i class="bi bi-image text-secondary fs-4"></i>
                                        </div>
                                        <span class="position-absolute top-0 end-0 badge bg-success rounded-pill m-1" style="font-size: 0.6rem;">
                                            <i class="bi bi-check"></i>
                                        </span>
                                        <div class="position-absolute bottom-0 start-0 end-0 text-truncate px-1 text-center" style="font-size: 0.55rem; background: rgba(0,0,0,0.55); color: #fff;" x-text="photo.original_name"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Upload Zone (hidden when complete) -->
                    <template x-if="item.uploaded_count < item.photo_limit">
                        <div class="px-4 pb-4">
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

                            <!-- Upload Queue -->
                            <template x-if="item.queue && item.queue.length > 0">
                                <div class="mt-3">
                                    <template x-for="entry in item.queue" :key="entry.id">
                                        <div class="d-flex align-items-center gap-3 py-3 px-4 rounded-4 mb-2 bg-body-tertiary border border-light">
                                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-image text-secondary"></i>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="small text-truncate fw-medium text-dark fs-5" x-text="entry.file.name"></div>
                                                <template x-if="entry.status === 'uploading'">
                                                    <div class="progress mt-2 bg-white rounded-pill" style="height: 6px;">
                                                        <div class="progress-bar rounded-pill" role="progressbar"
                                                             :style="`width: ${entry.progress}%; background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);`"></div>
                                                    </div>
                                                </template>
                                            </div>
                                            <template x-if="entry.status === 'uploading'">
                                                <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                                            </template>
                                            <template x-if="entry.status === 'success'">
                                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                            </template>
                                            <template x-if="entry.status === 'error'">
                                                <i class="bi bi-x-circle-fill text-danger fs-5" :title="entry.errorMessage"></i>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
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
                    queue: [],
                    dragover: false,
                })),

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

                    toUpload.forEach(file => {
                        const entry = {
                            id: Date.now() + Math.random(),
                            file: file,
                            status: 'uploading',
                            progress: 0,
                            errorMessage: '',
                        };
                        item.queue.push(entry);
                        this.uploadFile(item, entry);
                    });
                },

                uploadFile(item, entry) {
                    const formData = new FormData();
                    formData.append('order_item_id', item.id);
                    formData.append('photo', entry.file);

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
                                        });
                                    }
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

                    xhr.open('POST', '{{ route("site.order.uploadPhoto", $order->id) }}');
                    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.send(formData);
                },
            };
        }
    </script>
@endpush
