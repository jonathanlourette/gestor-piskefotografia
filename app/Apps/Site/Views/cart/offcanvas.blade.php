<!-- Offcanvas do Carrinho -->
<div
    class="offcanvas offcanvas-end border-0 shadow-lg"
    tabindex="-1"
    id="cartOffcanvas"
    aria-labelledby="cartOffcanvasLabel"
    x-data="cartOffcanvas()"
    x-init="initCart()"
    @cart-updated.window="loadCartData()"
>
    <div class="offcanvas-header border-bottom border-light pb-4" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-white-10 rounded-4 d-flex justify-content-center align-items-center" style="width: 48px; height: 48px; background: rgba(255,255,255,0.1);">
                <i class="bi bi-cart3 fs-5 text-white"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold text-white mb-1" id="cartOffcanvasLabel">Seu Carrinho</h5>
                <p class="text-white-50 small mb-0" x-text="cartData.count + ' item(s)'"></p>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white fs-4 opacity-75-hover" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>

    <div class="offcanvas-body p-4 d-flex flex-column overflow-hidden">
        <!-- Empty State -->
        <div x-show="cartData.items.length === 0" class="text-center py-5 my-4">
            <div class="py-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-body-tertiary rounded-circle mb-4" style="width: 96px; height: 96px;">
                    <i class="bi bi-cart-x fs-1 text-secondary"></i>
                </div>
                <h2 class="fw-bold text-dark mb-3">Seu carrinho está vazio</h2>
                <p class="text-body-secondary fs-5 mb-4">
                    Adicione produtos para começar a criar suas memórias.
                </p>
                <a href="{{ route('site.landing.index') }}" class="btn btn-dark btn-lg rounded-4 px-5 fw-semibold shadow-sm" data-bs-dismiss="offcanvas">
                    Ver Produtos
                </a>
            </div>
        </div>

        <!-- Carrinho com itens -->
        <div x-show="cartData.items.length > 0" class="d-flex flex-column flex-grow-1 overflow-hidden">
            <!-- Lista de itens (scrollável) -->
            <div class="flex-grow-1 overflow-auto" style="-webkit-overflow-scrolling: touch;">
                <div class="d-flex flex-column gap-3 pb-3">
                    <template x-for="item in cartData.items" :key="item.product_id">
                        <div class="bg-body-tertiary rounded-4 p-4 border border-light">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold text-dark mb-2" x-text="item.name"></h6>
                                    <div class="d-flex flex-wrap gap-3 mb-3">
                                        <div class="text-secondary small">
                                            <i class="bi bi-images me-1"></i>
                                            <span x-text="item.photo_limit + ' foto(s)'"></span>
                                        </div>
                                        <div class="text-secondary small">
                                            <i class="bi bi-currency-dollar me-1"></i>
                                            <span x-text="formatPrice(item.price)"></span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="text-secondary small">Quantidade:</span>
                                        <span class="badge bg-dark rounded-pill px-3 py-2 fw-medium" x-text="item.quantity"></span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-dark fs-5 mb-3" x-text="formatPrice(item.price * item.quantity)"></div>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-light bg-white text-danger border-0 rounded-3 px-3 py-2"
                                        x-on:click="removeItem(item.product_id)"
                                        :disabled="isRemoving"
                                    >
                                        <i class="bi bi-trash3-fill" x-show="!isRemoving"></i>
                                        <span class="spinner-border spinner-border-sm" x-show="isRemoving" style="display: none;"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer fixo: resumo + finalizar -->
            <div class="border-top border-light pt-3 mt-2 bg-white" style="flex-shrink: 0;">
                <!-- Resumo e Total -->
                <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                    <div>
                        <span class="fw-semibold text-dark">Total</span>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold text-dark fs-5" x-text="formatPrice(cartData.total)"></span>
                    </div>
                </div>

                <!-- Botão limpar carrinho -->
                <button
                    type="button"
                    class="btn btn-light bg-body w-100 rounded-4 px-4 fw-medium small text-secondary mb-2"
                    x-on:click="clearCart()"
                    :disabled="isClearing"
                >
                    <i class="bi bi-trash3 me-1" x-show="!isClearing"></i>
                    <span x-show="!isClearing">Limpar Carrinho</span>
                    <span x-show="isClearing" style="display: none;">
                        Limpando... <span class="spinner-border spinner-border-sm align-middle"></span>
                    </span>
                </button>

                <!-- Botão Finalizar -->
                <a
                    href="{{ route('site.order.create') }}"
                    class="btn btn-dark btn-lg w-100 rounded-4 px-4 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2"
                    onclick="const oc = bootstrap.Offcanvas.getInstance(document.getElementById('cartOffcanvas')); if(oc) oc.hide();"
                >
                    <i class="bi bi-check-circle fs-5"></i>
                    Finalizar Pedido
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function cartOffcanvas() {
        return {
            cartData: {
                items: [],
                total: 0,
                count: 0
            },
            isRemoving: false,
            isClearing: false,

            async initCart() {
                await this.loadCartData();
            },

            async loadCartData() {
                try {
                    const response = await fetch('{{ route('site.cart.index') }}', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.cartData = data;
                        this.updateBadge(data.count);
                    }
                } catch (error) {
                    console.error('Erro ao carregar carrinho:', error);
                }
            },

            async removeItem(productId) {
                if (!await window.confirmModal('Deseja realmente remover este item do carrinho?')) {
                    return;
                }

                this.isRemoving = true;

                try {
                    const response = await fetch('{{ route('site.cart.remove') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({
                            product_id: productId
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.cartData = data;
                        this.updateBadge(data.count);

                        if (data.message) {
                            this.showNotification(data.message, 'success');
                        }
                    } else {
                        this.showNotification('Erro ao remover item.', 'error');
                    }
                } catch (error) {
                    console.error('Erro ao remover item:', error);
                    this.showNotification('Erro ao remover item.', 'error');
                } finally {
                    this.isRemoving = false;
                }
            },

            async clearCart() {
                if (!await window.confirmModal('Deseja realmente limpar todo o carrinho?')) {
                    return;
                }

                this.isClearing = true;

                try {
                    const response = await fetch('{{ route('site.cart.clear') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.cartData = data;
                        this.updateBadge(data.count);

                        if (data.message) {
                            this.showNotification(data.message, 'success');
                        }
                    } else {
                        this.showNotification('Erro ao limpar carrinho.', 'error');
                    }
                } catch (error) {
                    console.error('Erro ao limpar carrinho:', error);
                    this.showNotification('Erro ao limpar carrinho.', 'error');
                } finally {
                    this.isClearing = false;
                }
            },

            updateBadge(count) {
                // Atualiza badge no header via window.cartData para ser acessível globalmente
                if (window.cartData) {
                    window.cartData.count = count;
                }

                // Dispara evento customizado
                window.dispatchEvent(new CustomEvent('cart-badge-update', { detail: { count } }));
            },

            formatPrice(price) {
                return 'R$ ' + parseFloat(price).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },

            showNotification(message, type) {
                // Usa o sistema global de toasts via Alpine
                window.dispatchEvent(new CustomEvent('toast', { detail: { type, message } }));
            }
        }
    }
</script>