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

              <!-- Photos List -->
              @if($order->photosCount() > 0)
                   <div class="bg-white rounded-4 p-4 border border-secondary-subtle shadow-sm mt-4">
                      <h5 class="fw-bold text-dark mb-3">Fotos Recebidas ({{ $order->photosCount() }})</h5>

                     <div class="table-responsive">
                         <table class="table table-borderless align-middle mb-0">
                             <thead class="bg-light">
                                 <tr>
                                     <th class="ps-3 py-2 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem;">Produto</th>
                                     <th class="py-2 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem;">Fotos</th>
                                     <th class="pe-3 py-2 text-end text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem;">Ações</th>
                                 </tr>
                             </thead>
                              <tbody>
                                  @foreach($order->items as $item)
                                      @if($item->photos->count() > 0)
                                          @php
                                              $itemPhotos = $item->photos->take(3);
                                              $hasMorePhotos = $item->photos->count() > 3;
                                              $itemPhotoUrls = [];
                                              foreach($item->photos as $photo) {
                                                  $itemPhotoUrls[] = [
                                                      'url' => $photo->temporary_url,
                                                      'name' => $photo->original_name
                                                  ];
                                              }
                                          @endphp
                                         <tr class="border-bottom border-light">
                                             <td class="ps-3 py-3">
                                                 <div class="fw-bold text-dark">
                                                      {{ $item->product->name }}
                                                 </div>
                                                 <div class="mt-1">
                                                     <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-2 px-2 py-1 fw-medium small">
                                                         {{ $item->photos->count() }}/{{ $item->photoLimit() }} fotos
                                                     </span>
                                                 </div>
                                             </td>
                                             <td class="py-3">
                                                 <div class="d-flex gap-2 align-items-center">
                                                     @foreach($itemPhotos as $photo)
                                                         <a href="{{ $photo->temporary_url }}" target="_blank"
                                                            class="text-decoration-none d-flex-shrink-0">
                                                             <img src="{{ $photo->thumbnail_url }}"
                                                                  alt="{{ $photo->original_name }}"
                                                                  class="object-fit-cover rounded-3 border border-secondary-subtle"
                                                                  style="width:48px;height:48px;transition:transform 0.2s ease;"
                                                                  onmouseover="this.style.transform='scale(1.05)'"
                                                                  onmouseout="this.style.transform='scale(1)'">
                                                         </a>
                                                     @endforeach
                                                     @if($hasMorePhotos)
                                                         <span class="text-body-secondary fw-semibold small">...</span>
                                                     @endif
                                                 </div>
                                             </td>
                                             <td class="pe-3 py-3 text-end">
                                                  <button type="button"
                                                          class="btn btn-outline-dark btn-sm rounded-3 px-3 fw-semibold d-flex align-items-center gap-2"
                                                          :disabled="downloadingItemId !== null"
                                                          data-urls='@json($itemPhotoUrls)'
                                                          @click="downloadItemPhotos($el, {{ $item->id }})"
                                                          style="transition: all 0.2s ease;"
                                                          onmouseover="if(!this.disabled) this.style.transform='translateY(-1px)'"
                                                          onmouseout="this.style.transform='translateY(0)'">
                                                      <i class="bi bi-download"></i> Baixar
                                                  </button>
                                             </td>
                                         </tr>
                                     @endif
                                 @endforeach
                             </tbody>
                         </table>
                     </div>
                 </div>
             @endif

        </div>

        <!-- Right Column: Actions -->
        <div class="col-lg-4">
            
         <!-- Update Status -->
         <div class="bg-white rounded-4 p-4 border border-secondary-subtle shadow-sm mb-4">
             <h5 class="fw-bold text-dark mb-3">Atualizar Status</h5>
             
             <!-- Status atual como texto -->
             <div class="mb-3 p-3 rounded-3 bg-{{ $order->status->color() }}-subtle border border-{{ $order->status->color() }}-subtle">
                 <span class="small text-{{ $order->status->color() }}-emphasis fw-semibold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Status Atual</span>
                 <span class="fw-bold text-{{ $order->status->color() }}-emphasis fs-5">{{ $order->status->label() }}</span>
             </div>

             <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                 @csrf
                 @method('PUT')
                 <div class="mb-3">
                     <label class="form-label fw-semibold text-dark fs-6">Novo Status</label>
                     <select name="status" class="form-select form-select-lg bg-body-tertiary border-0 rounded-3 shadow-none" required
                             style="transition: box-shadow 0.2s ease;"
                             onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'" 
                             onblur="this.style.boxShadow='none'">
                         <option value="" disabled selected>Selecione o novo status...</option>
                         @foreach($statusOptions as $value => $label)
                             <option value="{{ $value }}">{{ $label }}</option>
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

          <!-- Delete Order -->
          <div class="bg-white rounded-4 p-4 border border-danger-subtle shadow-sm mt-4">
              <h5 class="fw-bold text-danger mb-2">Zona de Perigo</h5>
              <p class="text-secondary small mb-3">A exclusão é permanente e remove todas as fotos do pedido.</p>
              <button type="button"
                      class="btn btn-outline-danger w-100 rounded-3 fw-semibold"
                      @click="deleteOrder()">
                  <i class="bi bi-trash3 me-2"></i>Excluir Pedido
              </button>
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
                downloadingItemId: null,
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

                async downloadItemPhotos(button, itemId) {
                    if (this.downloadingItemId) return;

                    const urlsJson = button.dataset.urls;
                    if (!urlsJson) return;

                    const photos = JSON.parse(urlsJson);
                    if (photos.length === 0) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'warning', message: 'Nenhuma foto encontrada.' } }));
                        return;
                    }

                    this.downloadingItemId = itemId;
                    this.downloadTotal = photos.length;
                    this.downloadProgress = 0;

                    const originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Baixando 1 de ' + photos.length + '...';

                    for (let i = 0; i < photos.length; i++) {
                        try {
                            await this.downloadSinglePhotoFromData(photos[i]);
                            this.downloadProgress = i + 1;
                            if (i < photos.length - 1) {
                                button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Baixando ' + (i + 1) + ' de ' + photos.length + '...';
                                await new Promise(resolve => setTimeout(resolve, 1000));
                            }
                        } catch (error) {
                            console.error('Erro:', error);
                            window.open(photos[i].url, '_blank');
                        }
                    }

                    this.downloadingItemId = null;
                    button.innerHTML = '<i class="bi bi-check-lg me-1"></i>Concluído!';
                    button.classList.remove('btn-outline-dark');
                    button.classList.add('btn-outline-success');

                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                        button.classList.remove('btn-outline-success');
                        button.classList.add('btn-outline-dark');
                    }, 2000);

                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: photos.length + ' foto(s) baixada(s)!' } }));
                },

                async downloadSinglePhotoFromData(photo) {
                    const url = photo.url;
                    const filename = photo.name;

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
                },

                async deleteOrder() {
                    if (!await window.confirmModal('Tem certeza que deseja excluir este pedido? Esta ação é irreversível e todas as fotos serão removidas.')) {
                        return;
                    }

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("admin.orders.delete", $order->id) }}';
                    form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '"><input type="hidden" name="_method" value="DELETE">';
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    </script>
@endsection