@php
    /** @var \App\Domains\Gallery\Gallery $gallery */
    /** @var string|null $coverUrl */
    $photoCount = $gallery->photos_count ?? 0;
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">

    <title>{{ $gallery->title }} — Piske Memórias</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --gallery-bg: #0a0a0a;
            --gallery-text: #ffffff;
            --gallery-text-muted: rgba(255, 255, 255, 0.5);
            --gallery-accent: #ffffff;
        }

        html { scroll-behavior: smooth; }

        body {
            background-color: var(--gallery-bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--gallery-text);
            margin: 0;
            padding: 0;
        }

        /* ===== HERO SECTION ===== */
        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            filter: blur(4px) brightness(0.3) saturate(0.8);
            transform: scale(1.1);
            transition: opacity 1s ease;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.7) 60%, #0a0a0a 100%);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #ffffff;
            max-width: 700px;
            padding: 0 2rem;
        }

        .hero-title {
            font-size: clamp(2.2rem, 7vw, 4.5rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1;
            margin-bottom: 1.25rem;
            text-transform: uppercase;
        }

        .hero-subtitle {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 0.25rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            font-weight: 400;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.8rem;
            margin-bottom: 2.5rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .btn-hero {
            background: #ffffff;
            color: #0a0a0a;
            border: none;
            padding: 1rem 3rem;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-hero:hover {
            background: #ffffff;
            color: #0a0a0a;
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(255, 255, 255, 0.15);
        }

        .hero-scroll {
            position: absolute;
            bottom: 2.5rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            color: rgba(255, 255, 255, 0.25);
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
            40% { transform: translateX(-50%) translateY(-8px); }
            60% { transform: translateX(-50%) translateY(-4px); }
        }

        /* ===== STICKY HEADER ===== */
        .gallery-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(10, 10, 10, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.3s ease;
        }

        .gallery-header.scrolled {
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.4);
        }

        .btn-download {
            background-color: transparent;
            color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-download:hover {
            background-color: #ffffff;
            color: #0a0a0a;
            border-color: #ffffff;
        }

        /* ===== PHOTO GRID — FLUSH / NO GAP ===== */
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0;
        }

        @media (min-width: 576px) { .photo-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 768px) { .photo-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (min-width: 1200px) { .photo-grid { grid-template-columns: repeat(5, 1fr); } }
        @media (min-width: 1600px) { .photo-grid { grid-template-columns: repeat(6, 1fr); } }

        .photo-item {
            position: relative;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            cursor: pointer;
            background: #111;
        }

        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94), filter 0.5s ease;
        }

        .photo-item:hover img {
            transform: scale(1.06);
            filter: brightness(1.1);
        }

        /* Dark overlay on hover */
        .photo-item::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 50%, rgba(0, 0, 0, 0.4) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .photo-item:hover::after {
            opacity: 1;
        }

        .photo-item .fav-btn {
            position: absolute;
            bottom: 0.75rem;
            right: 0.75rem;
            width: 36px;
            height: 36px;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: translateY(4px);
            transition: all 0.25s ease;
            color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            z-index: 2;
        }

        .photo-item:hover .fav-btn,
        .photo-item .fav-btn.active {
            opacity: 1;
            transform: translateY(0);
        }

        .photo-item .fav-btn.active {
            color: #ef4444;
        }

        .photo-item .fav-btn:hover {
            background: rgba(0, 0, 0, 0.7);
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* ===== LOADING SPINNER ===== */
        .loader {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 4rem 0;
            color: rgba(255, 255, 255, 0.3);
        }

        .loader .spinner-border {
            width: 1.5rem;
            height: 1.5rem;
            border-width: 2px;
            color: rgba(255, 255, 255, 0.3);
        }

        /* ===== LIGHTBOX ===== */
        .lightbox-backdrop {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.97);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lightbox-image-container {
            position: relative;
            max-width: 90vw;
            max-height: 85vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lightbox-image-container img {
            max-width: 100%;
            max-height: 85vh;
            object-fit: contain;
            transition: opacity 0.3s ease;
        }

        .lightbox-close {
            position: fixed;
            top: 0;
            right: 0;
            z-index: 10000;
            width: 56px;
            height: 56px;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1.2rem;
        }

        .lightbox-close:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
        }

        .lightbox-nav {
            position: fixed;
            top: 0;
            bottom: 0;
            z-index: 10000;
            width: 80px;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1.5rem;
        }

        .lightbox-nav:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.03);
        }

        .lightbox-nav.prev { left: 0; }
        .lightbox-nav.next { right: 0; }

        .lightbox-actions {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 10000;
            display: flex;
            justify-content: center;
            gap: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding: 0;
        }

        .lightbox-actions button {
            width: 56px;
            height: 56px;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1.1rem;
        }

        .lightbox-actions button:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
        }

        .lightbox-actions button.fav-active {
            color: #ef4444;
        }

        .lightbox-counter {
            position: fixed;
            top: 1.25rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            color: rgba(255, 255, 255, 0.35);
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.1em;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            padding: 6rem 1rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.3);
        }

        /* ===== BRAND FOOTER ===== */
        .brand-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.2);
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
        }

        .brand-footer a {
            color: rgba(255, 255, 255, 0.4);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .brand-footer a:hover {
            color: #ffffff;
        }

        /* ===== NO COVER FALLBACK ===== */
        .hero-bg-fallback {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #0a0a0a 100%);
        }

        /* ===== ALPINE CLOAK ===== */
        [x-cloak] { display: none !important; }
    </style>
</head>
<body>

<div
    x-data="galleryViewer()"
    x-init="init()"
    data-uuid="{{ $gallery->uuid }}"
    data-csrf="{{ csrf_token() }}"
    data-photos-url="{{ route('gallery.photos', $gallery->uuid) }}"
    data-favorite-url="{{ route('gallery.favorite', [$gallery->uuid, 0]) }}"
    data-download-url="{{ route('gallery.download', $gallery->uuid) }}"
    data-photo-count="{{ $photoCount }}"
    data-home-url="{{ route('site.landing.index') }}"
>

    <!-- ===== HERO SECTION ===== -->
    <section class="hero-section">
        @if($coverUrl)
            <div class="hero-bg" style="background-image: url('{{ $coverUrl }}');"></div>
        @else
            <div class="hero-bg hero-bg-fallback"></div>
        @endif
        <div class="hero-overlay"></div>

        <div class="hero-content">
            <h1 class="hero-title">{{ $gallery->title }}</h1>

            @if($gallery->customer_name)
                <p class="hero-subtitle">{{ $gallery->customer_name }}</p>
            @endif

            @if($gallery->created_at)
                <p class="hero-subtitle mb-3" style="font-size: 0.95rem; opacity: 0.6;">
                    {{ $gallery->created_at->format('M Y') }}
                </p>
            @endif

            <div class="hero-badge">
                <i class="bi bi-image"></i>
                <span>{{ $photoCount }} fotos</span>
            </div>

            <div>
                <a href="#gallery-grid" class="btn btn-hero">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Ver Fotos
                </a>
            </div>
        </div>

        <div class="hero-scroll">
            <i class="bi bi-chevron-down fs-4"></i>
        </div>
    </section>

    <!-- ===== STICKY GALLERY HEADER ===== -->
    <div id="gallery-grid" class="gallery-header" x-ref="galleryHeader">
        <div class="container-fluid px-3 px-md-4 px-lg-5 py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('assets/img/logo-piske.png') }}" alt="Piske" style="height: 28px;">
                    <span class="fw-semibold text-dark d-none d-sm-inline" style="font-size: 0.95rem;">{{ $gallery->title }}</span>
                </div>
                <a href="{{ route('gallery.download', $gallery->uuid) }}" class="btn-download d-flex align-items-center gap-2">
                    <i class="bi bi-download"></i>
                    <span class="d-none d-sm-inline">Baixar Todas</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ===== PHOTO GRID ===== -->
    <div class="container-fluid px-3 px-md-4 px-lg-5 py-4">
        @if($photoCount === 0)
            <div class="empty-state">
                <div class="d-inline-flex align-items-center justify-content-center bg-white shadow-sm rounded-circle mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-image fs-1 text-body-secondary"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Nenhuma foto ainda</h3>
                <p class="text-body-secondary">As fotos serão adicionadas em breve.</p>
            </div>
        @else
            <div class="photo-grid">
                <template x-for="(photo, index) in photos" :key="photo.id">
                    <div class="photo-item" x-on:click="openLightbox(index)">
                        <img
                            :src="photo.thumbnail_url"
                            :alt="photo.original_name || ('Foto ' + (index + 1))"
                            loading="lazy"
                            x-on:error="$el.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZTVlN2ViIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJzYW5zLXNlcmlmIiBmb250LXNpemU9IjE0IiBmaWxsPSIjOWNhM2FmIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+SW1hZ2VtPC90ZXh0Pjwvc3ZnPg=='"
                        >
                        <button
                            class="fav-btn"
                            :class="{ 'active': photo.is_favorited }"
                            x-on:click.stop="toggleFavorite(photo.id, index)"
                            :aria-label="photo.is_favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                        >
                            <i class="bi" :class="photo.is_favorited ? 'bi-heart-fill' : 'bi-heart'" style="font-size: 0.9rem;"></i>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Infinite scroll sentinel (always in DOM) -->
            <div x-ref="sentinel" style="height: 1px;"></div>

            <!-- Loading indicator -->
            <div class="loader" x-show="loading">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <span class="small">Carregando mais fotos...</span>
            </div>

            <!-- End of photos message -->
            <div x-show="!hasMore && photos.length > 0" class="text-center py-4">
                <span class="text-body-secondary small" x-text="'Todas as ' + totalPhotoCount + ' fotos foram carregadas'"></span>
            </div>
        @endif
    </div>

    <!-- ===== BRAND FOOTER ===== -->
    <footer class="brand-footer py-4 text-center">
        powered by <a href="{{ route('site.landing.index') }}">Piske Memórias</a>
    </footer>

    <!-- ===== LIGHTBOX ===== -->
    <template x-if="lightboxOpen">
        <div x-cloak>
            <div class="lightbox-backdrop" x-on:click.self="closeLightbox()" x-transition.opacity>
                <!-- Counter -->
                <div class="lightbox-counter" x-text="(lightboxIndex + 1) + ' / ' + photos.length"></div>

                <!-- Close -->
                <button class="lightbox-close" x-on:click="closeLightbox()" aria-label="Fechar">
                    <i class="bi bi-x-lg"></i>
                </button>

                <!-- Previous -->
                <button
                    class="lightbox-nav prev"
                    x-on:click="prevPhoto()"
                    x-show="lightboxIndex > 0"
                    aria-label="Foto anterior"
                >
                    <i class="bi bi-chevron-left"></i>
                </button>

                <!-- Next -->
                <button
                    class="lightbox-nav next"
                    x-on:click="nextPhoto()"
                    x-show="lightboxIndex < photos.length - 1"
                    aria-label="Próxima foto"
                >
                    <i class="bi bi-chevron-right"></i>
                </button>

                <!-- Image -->
                <div class="lightbox-image-container" x-on:click.stop>
                    <img
                        :src="lightboxPhoto ? lightboxPhoto.url : ''"
                        :alt="lightboxPhoto ? (lightboxPhoto.original_name || 'Foto') : ''"
                    >
                </div>

                <!-- Actions -->
                <div class="lightbox-actions" x-on:click.stop>
                    <button
                        :class="{ 'fav-active': lightboxPhoto && lightboxPhoto.is_favorited }"
                        x-on:click="lightboxPhoto && toggleFavorite(lightboxPhoto.id, lightboxIndex)"
                        :aria-label="lightboxPhoto && lightboxPhoto.is_favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                    >
                        <i class="bi" :class="lightboxPhoto && lightboxPhoto.is_favorited ? 'bi-heart-fill' : 'bi-heart'"></i>
                    </button>
                    <button
                        x-on:click="lightboxPhoto && downloadPhoto(lightboxPhoto.url)"
                        aria-label="Baixar foto"
                    >
                        <i class="bi bi-download"></i>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

@verbatim
<script>
    function galleryViewer() {
        return {
            photos: [],
            loading: false,
            nextCursor: null,
            hasMore: true,
            visitorToken: null,
            totalPhotoCount: 0,

            // Config (populated from data-* attributes)
            galleryUuid: '',
            csrfToken: '',
            photosUrl: '',
            favoriteUrlTemplate: '',
            downloadUrl: '',

            // Lightbox state
            lightboxOpen: false,
            lightboxIndex: 0,

            get lightboxPhoto() {
                return this.photos[this.lightboxIndex] || null;
            },

            init() {
                // Read PHP data from data-* attributes
                var el = this.$el;
                this.galleryUuid = el.dataset.uuid;
                this.csrfToken = el.dataset.csrf;
                this.photosUrl = el.dataset.photosUrl;
                this.favoriteUrlTemplate = el.dataset.favoriteUrl;
                this.downloadUrl = el.dataset.downloadUrl;
                this.totalPhotoCount = parseInt(el.dataset.photoCount, 10) || 0;

                this.loadVisitorToken();
                this.loadMore();
                this.setupInfiniteScroll();
                this.setupKeyboardNav();
            },

            /**
             * Build favorite URL for a specific photo ID.
             */
            getFavoriteUrl: function (photoId) {
                // Template contains "/favoritos/0" — replace "0" with real ID
                return this.favoriteUrlTemplate.replace(/\/favoritos\/\d+$/, '/favoritos/' + photoId);
            },

            /**
             * Carrega ou gera o token do visitante (cookie-based).
             */
            loadVisitorToken: function () {
                var token = this.getCookie('gallery_visitor_token');
                if (!token) {
                    token = 'v_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
                    this.setCookie('gallery_visitor_token', token, 365);
                }
                this.visitorToken = token;
            },

            /**
             * Carrega a próxima página de fotos via API JSON.
             */
            loadMore: async function () {
                if (this.loading || !this.hasMore) return;

                this.loading = true;

                try {
                    var params = new URLSearchParams({ visitor_token: this.visitorToken });
                    if (this.nextCursor) {
                        params.set('cursor', this.nextCursor);
                    }

                    var separator = this.photosUrl.indexOf('?') !== -1 ? '&' : '?';
                    var url = this.photosUrl + separator + params.toString();

                    var response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        if (response.status === 401 || response.status === 403) {
                            window.location.href = '/galeria/' + this.galleryUuid;
                            return;
                        }
                        throw new Error('Erro ao carregar fotos');
                    }

                    var data = await response.json();

                    if (data.photos && data.photos.length > 0) {
                        this.photos.push.apply(this.photos, data.photos);
                    }

                    this.nextCursor = data.next_cursor || null;
                    this.hasMore = data.has_more !== undefined ? data.has_more : !!this.nextCursor;
                } catch (error) {
                    console.error('Erro ao carregar fotos:', error);
                } finally {
                    this.loading = false;
                }
            },

            /**
             * Abre o lightbox no índice especificado.
             */
            openLightbox: function (index) {
                this.lightboxIndex = index;
                this.lightboxOpen = true;
                document.body.style.overflow = 'hidden';
                this.preloadAdjacent(index);
            },

            /**
             * Fecha o lightbox.
             */
            closeLightbox: function () {
                this.lightboxOpen = false;
                document.body.style.overflow = '';
            },

            /**
             * Navega para a próxima foto.
             */
            nextPhoto: function () {
                if (this.lightboxIndex < this.photos.length - 1) {
                    this.lightboxIndex++;
                    this.preloadAdjacent(this.lightboxIndex);
                    if (this.lightboxIndex >= this.photos.length - 3 && this.hasMore) {
                        this.loadMore();
                    }
                }
            },

            /**
             * Navega para a foto anterior.
             */
            prevPhoto: function () {
                if (this.lightboxIndex > 0) {
                    this.lightboxIndex--;
                    this.preloadAdjacent(this.lightboxIndex);
                }
            },

            /**
             * Pre-carrega as fotos adjacentes para navegação fluida.
             */
            preloadAdjacent: function (index) {
                if (index + 1 < this.photos.length) {
                    var nextImg = new Image();
                    nextImg.src = this.photos[index + 1].url;
                }
                if (index - 1 >= 0) {
                    var prevImg = new Image();
                    prevImg.src = this.photos[index - 1].url;
                }
            },

            /**
             * Alterna o favorito de uma foto.
             */
            toggleFavorite: async function (photoId, photoIndex) {
                try {
                    var url = this.getFavoriteUrl(photoId);

                    var response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ visitor_token: this.visitorToken }),
                    });

                    if (!response.ok) {
                        throw new Error('Erro ao favoritar');
                    }

                    var data = await response.json();

                    this.photos[photoIndex] = Object.assign(
                        {},
                        this.photos[photoIndex],
                        { is_favorited: data.is_favorited }
                    );
                } catch (error) {
                    console.error('Erro ao favoritar:', error);
                }
            },

            /**
             * Abre a foto em nova aba para download.
             */
            downloadPhoto: function (url) {
                if (url) {
                    window.open(url, '_blank');
                }
            },

            /**
             * Configura Intersection Observer para infinite scroll.
             */
            setupInfiniteScroll: function () {
                var self = this;

                var setupObserver = function () {
                    var sentinel = self.$refs.sentinel;
                    if (!sentinel) {
                        setTimeout(setupObserver, 200);
                        return;
                    }

                    var observer = new IntersectionObserver(
                        function (entries) {
                            if (entries[0].isIntersecting && self.hasMore && !self.loading) {
                                self.loadMore();
                            }
                        },
                        { rootMargin: '600px' }
                    );

                    observer.observe(sentinel);
                };

                setupObserver();
            },

            /**
             * Configura navegação por teclado no lightbox.
             */
            setupKeyboardNav: function () {
                var self = this;

                document.addEventListener('keydown', function (e) {
                    if (!self.lightboxOpen) return;

                    switch (e.key) {
                        case 'ArrowRight':
                            e.preventDefault();
                            self.nextPhoto();
                            break;
                        case 'ArrowLeft':
                            e.preventDefault();
                            self.prevPhoto();
                            break;
                        case 'Escape':
                            e.preventDefault();
                            self.closeLightbox();
                            break;
                    }
                });
            },

            // ===== Cookie Helpers =====

            getCookie: function (name) {
                var value = '; ' + document.cookie;
                var parts = value.split('; ' + name + '=');
                if (parts.length === 2) return parts.pop().split(';').shift();
                return null;
            },

            setCookie: function (name, value, days) {
                var d = new Date();
                d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
                document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
            },
        };
    }
</script>
@endverbatim
</body>
</html>
