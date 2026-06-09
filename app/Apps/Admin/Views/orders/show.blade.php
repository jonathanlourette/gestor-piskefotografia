@php
    /** @var \App\Domains\Order\Order $order */
    /** @var array $statusOptions */
@endphp

@extends('admin::layouts.admin')

@section('title', "Pedido #{$order->id} - Admin")

@section('content')
<div x-data="orderDetail()" class="d-flex flex-column gap-4">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start bg-white rounded-4 p-4 border border-secondary-subtle shadow-sm">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <h1 class="fw-bolder mb-0 text-dark" style="font-size: 1.75rem;">Pedido #{{ str_pad((string)$order->id, 5, '0', STR_PAD_LEFT) }}</h1>
                <span class="badge bg-{{ $order->status->color() }}-subtle text-{{ $order->status->color() }}-emphasis border border-{{ $order->status->color() }}-subtle rounded-pill px-3 py-2 fw-bold fs-6">
                    {{ $order->status->label() }}
                </span>
            </div>
            <p class="text-body-secondary mb-0 fs-6">
                Recebido em {{ $order->created_at->format('d/m/Y \à\s H:i') }}
                @if($order->updated_at->gt($order->created_at))
                    <span class="mx-2">•</span> Atualizado em {{ $order->updated_at->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>

         <!-- Tracking Link -->
         <div class="bg-primary-subtle rounded-4 p-3 border border-primary-subtle">
             <label class="small text-primary-emphasis fw-semibold text-uppercase d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Link de Rastreio</label>
             <div class="d-flex gap-2">
                 <input type="text" readonly 
                        value="{{ route('site.tracking.show', $order->uuid) }}" 
                        class="form-control form-control-sm bg-white border-0 rounded-3 font-monospace text-secondary shadow-none"
                        id="tracking-url">
                 <button type="button" @click="copyTracking()" 
                         class="btn btn-sm btn-dark rounded-3 px-3 shadow-sm" title="Copiar Link"
                         style="transition: transform 0.2s ease;"
                         onmouseover="this.style.transform='translateY(-1px)'"
                         onmouseout="this.style.transform='translateY(0)'">
                     <i class="bi bi-clipboard"></i>
                 </button>
             </div>
         </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Details -->
        <div class="col-lg-8">
            
            <!-- Customer Info -->
            <div class="bg-white rounded-4 p-4 border border-secondary-subtle shadow-sm mb-4">
                <h5 class="fw-bold text-dark mb-3">Dados do Cliente</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small text-secondary fw-semibold text-uppercase mb-1" style="font-size: 0.7rem;">Nome</label>
                        <div class="fw-medium text-dark">{{ $order->customer_name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-secondary fw-semibold text-uppercase mb-1" style="font-size: 0.7rem;">Telefone</label>
                        <div class="fw-medium text-dark">
                            <a href="tel:{{ $order->customer_phone }}" class="text-decoration-none text-dark hover-primary">{{ $order->customer_phone }}</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-4 p-4 border border-secondary-subtle shadow-sm">
                <h5 class="fw-bold text-dark mb-3">Itens do Pedido</h5>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 py-2 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem;">Produto</th>
                                <th class="py-2 text-end text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem;">Qtd.</th>
                                <th class="py-2 text-end text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem;">Preço Unit.</th>
                                <th class="py-2 text-end text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem;">Sub-total</th>
                                <th class="pe-3 py-2 text-center text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem;">Fotos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr class="border-bottom border-light">
                                    <td class="ps-3 py-3">
                                        <div class="fw-medium text-dark">{{ $item->product->name }}</div>
                                    </td>
                                    <td class="py-3 text-end text-body-secondary">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="py-3 text-end text-body-secondary">
                                        R$ {{ number_format($item->unit_price, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 text-end fw-bold text-dark">
                                        R$ {{ number_format($item->subtotal(), 2, ',', '.') }}
                                    </td>
                                    <td class="pe-3 py-3 text-center">
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-2 px-2 py-1 fw-medium small">
                                            {{ $item->photos->count() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="3" class="ps-3 py-3 text-end fw-bold text-dark">Total do Pedido</td>
                                <td class="pe-3 py-3 text-end fw-bold text-dark fs-5" colspan="2">
                                    R$ {{ number_format($order->total(), 2, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

             <!-- Photos Grid -->
             @if($order->photosCount() > 0)
                 <div class="bg-white rounded-4 p-4 border border-secondary-subtle shadow-sm">
                     <div class="d-flex justify-content-between align-items-center mb-3">
                         <h5 class="fw-bold text-dark mb-0">Fotos Recebidas ({{ $order->photosCount() }})</h5>
                         <button type="button" @click="downloadAllPhotos()" 
                                 class="btn btn-dark btn-sm rounded-4 px-4 fw-semibold shadow-sm d-flex align-items-center gap-2"
                                 :disabled="isDownloading"
                                 style="transition: all 0.2s ease;"
                                 onmouseover="if(!this.disabled) this.style.transform='translateY(-1px)'"
                                 onmouseout="this.style.transform='translateY(0)'">
                             <template x-if="!isDownloading">
                                 <span class="d-flex align-items-center gap-2">
                                     <i class="bi bi-download"></i> Baixar Todas
                                 </span>
                             </template>
                             <template x-if="isDownloading">
                                 <span class="d-flex align-items-center gap-2">
                                     <span class="spinner-border spinner-border-sm" role="status"></span>
                                     <span x-text="`Baixando ${downloadProgress} de ${downloadTotal}...`"></span>
                                 </span>
                             </template>
                         </button>
                     </div>
                     
                     @foreach($order->items as $item)
                         @if($item->photos->count() > 0)
                             <div class="mb-4">
                                 <div class="d-flex justify-content-between align-items-center mb-3">
                                     <h6 class="fw-bold text-dark mb-0">
                                         <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-2 px-2 py-1 me-2">{{ $item->photos->count() }}/{{ $item->product->photo_limit }}</span>
                                         {{ $item->product->name }}
                                         <span class="text-body-secondary fw-normal ms-1">× {{ $item->quantity }}</span>
                                     </h6>
                                     <button type="button" @click="downloadItemPhotos('item-{{ $item->id }}')" 
                                             class="btn btn-outline-dark btn-sm rounded-3 px-3 fw-semibold d-flex align-items-center gap-2"
                                             :disabled="isDownloading"
                                             style="transition: all 0.2s ease;"
                                             onmouseover="if(!this.disabled) this.style.transform='translateY(-1px)'"
                                             onmouseout="this.style.transform='translateY(0)'">
                                         <i class="bi bi-download"></i> Baixar {{ $item->product->name }}
                                     </button>
                                 </div>
                                 <div class="row g-3" id="item-{{ $item->id }}">
                                     @foreach($item->photos as $photo)
                                         <div class="col-6 col-md-4 col-lg-3">
                                             <a href="{{ $photo->temporary_url }}" target="_blank" class="text-decoration-none d-block group">
                                                 <div class="ratio ratio-1x1 rounded-4 overflow-hidden border border-secondary-subtle bg-body-tertiary shadow-sm" style="transition: all 0.2s ease;">
                                                     <img src="{{ $photo->temporary_url }}" 
                                                          alt="{{ $photo->original_name }}" 
                                                          class="object-fit-cover w-100 h-100 group-hover:scale-105"
                                                          loading="lazy"
                                                          style="transition: transform 0.2s ease-in-out;"
                                                          onmouseover="this.style.transform='scale(1.05)'" 
                                                          onmouseout="this.style.transform='scale(1)'">
                                                 </div>
                                                 <div class="small text-secondary mt-2 text-truncate" title="{{ $photo->original_name }}">
                                                     {{ $photo->original_name }}
                                                 </div>
                                             </a>
                                         </div>
                                     @endforeach
                                 </div>
                             </div>
                             @if(!$loop->last)
                                 <hr class="my-3 border-secondary-subtle">
                             @endif
                         @endif
                     @endforeach
                 </div>
             @endif

        </div>

        <!-- Right Column: Actions -->
        <div class="col-lg-4">
            
         <!-- Update Status -->
         <div class="bg-white rounded-4 p-4 border border-secondary-subtle shadow-sm mb-4">
             <h5 class="fw-bold text-dark mb-3">Atualizar Status</h5>
             <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                 @csrf
                 @method('PUT')
                 <div class="mb-3">
                     <label class="form-label fw-semibold text-dark fs-6">Novo Status</label>
                     <select name="status" class="form-select form-select-lg bg-body-tertiary border-0 rounded-3 shadow-none" required
                             style="transition: box-shadow 0.2s ease;"
                             onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'" 
                             onblur="this.style.boxShadow='none'">
                         @foreach($statusOptions as $value => $label)
                             <option value="{{ $value }}" {{ $order->status->value === $value ? 'selected' : '' }}>
                                 {{ $label }}
                             </option>
                         @endforeach
                     </select>
                 </div>
                 <button type="submit" class="btn btn-dark btn-lg w-100 rounded-4 fw-bold shadow-sm"
                         style="transition: all 0.2s ease;"
                         onmouseover="this.style.transform='translateY(-1px)'"
                         onmouseout="this.style.transform='translateY(0)'">
                     <i class="bi bi-arrow-repeat me-2"></i> Atualizar
                 </button>
             </form>
         </div>

         <!-- Internal Notes -->
         <div class="bg-white rounded-4 p-4 border border-secondary-subtle shadow-sm">
             <h5 class="fw-bold text-dark mb-3">Notas Internas</h5>
             <form action="{{ route('admin.orders.updateNotes', $order->id) }}" method="POST">
                 @csrf
                 @method('PUT')
                 <div class="mb-3">
                     <textarea name="notes" class="form-control bg-body-tertiary border-0 rounded-3 shadow-sm" 
                               rows="5" placeholder="Adicione observações sobre este pedido..."
                               style="transition: box-shadow 0.2s ease;"
                               onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'" 
                               onblur="this.style.boxShadow='none'">{{ $order->notes ?? '' }}</textarea>
                 </div>
                 <button type="submit" class="btn btn-outline-dark w-100 rounded-3 fw-semibold"
                         style="transition: all 0.2s ease;"
                         onmouseover="this.style.transform='translateY(-1px)'"
                         onmouseout="this.style.transform='translateY(0)'">
                     Salvar Notas
                 </button>
             </form>
         </div>

        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.orders.index', request()->only(['status', 'search'])) }}" 
           class="btn btn-light text-secondary fw-semibold rounded-3 px-4 border border-secondary-subtle">
            <i class="bi bi-arrow-left me-2"></i> Voltar para Lista
        </a>
    </div>

</div>

    <script>
        function orderDetail() {
            return {
                isDownloading: false,
                downloadProgress: 0,
                downloadTotal: 0,

                copyTracking() {
                    const urlInput = document.getElementById('tracking-url');
                    urlInput.select();
                    navigator.clipboard.writeText(urlInput.value).then(() => {
                        const btn = document.querySelector('[title="Copiar Link"]');
                        const originalHTML = btn.innerHTML;
                        btn.innerHTML = '<i class="bi bi-check-lg"></i>';
                        btn.classList.add('btn-success');
                        setTimeout(() => {
                            btn.innerHTML = originalHTML;
                            btn.classList.remove('btn-success');
                        }, 2000);
                    });
                },

                async downloadItemPhotos(containerId) {
                    const container = document.getElementById(containerId);
                    if (!container) return;

                    const photoLinks = container.querySelectorAll('a[href*="X-Amz-Algorithm"]');
                    if (photoLinks.length === 0) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'warning', message: 'Nenhuma foto encontrada.' } }));
                        return;
                    }

                    this.isDownloading = true;
                    this.downloadTotal = photoLinks.length;
                    this.downloadProgress = 0;

                    for (const link of photoLinks) {
                        try {
                            await this.downloadSinglePhoto(link);
                            this.downloadProgress++;
                            if (this.downloadProgress < this.downloadTotal) {
                                await new Promise(resolve => setTimeout(resolve, 500));
                            }
                        } catch (error) {
                            console.error('Erro ao baixar foto:', error);
                            this.downloadProgress++;
                        }
                    }

                    this.isDownloading = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: `${this.downloadTotal} foto(s) baixada(s) com sucesso!` } }));
                },

                async downloadAllPhotos() {
                    const photoLinks = document.querySelectorAll('a[href*="X-Amz-Algorithm"]');
                    if (photoLinks.length === 0) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'warning', message: 'Nenhuma foto encontrada para download.' } }));
                        return;
                    }

                    this.isDownloading = true;
                    this.downloadTotal = photoLinks.length;
                    this.downloadProgress = 0;

                    for (const link of photoLinks) {
                        try {
                            await this.downloadSinglePhoto(link);
                            this.downloadProgress++;
                            if (this.downloadProgress < this.downloadTotal) {
                                await new Promise(resolve => setTimeout(resolve, 500));
                            }
                        } catch (error) {
                            console.error('Erro ao baixar foto:', error);
                            this.downloadProgress++;
                        }
                    }

                    this.isDownloading = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: `${this.downloadTotal} foto(s) baixada(s) com sucesso!` } }));
                },

                async downloadSinglePhoto(link) {
                    const url = link.href;
                    const nameDiv = link.querySelector('.text-truncate');
                    const filename = nameDiv ? nameDiv.textContent.trim() : `foto.jpg`;

                    const response = await fetch(url);
                    const blob = await response.blob();
                    const blobUrl = URL.createObjectURL(blob);

                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(blobUrl);
                }
            }
        }
    </script>
@endsection