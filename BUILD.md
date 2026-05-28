# Piske Memórias — Plano Arquitetural (Cenário B — DDD-lite Completo)

> Sistema de Pedidos e Upload de Fotos para revelação digital → impressa.

---

## 📋 Decisões do Projeto

| Decisão | Resposta |
|---------|----------|
| **Arquitetura** | Cenário B — DDD-lite completo com Actions |
| **Pagamento** | Fora do site (combinado à parte via WhatsApp/presencial) |
| **Carrinho** | Sim, múltiplos itens por pedido (armazenado em sessão) |
| **Portal cliente** | Link único UUID para acompanhamento (sem login) |
| **Landing page** | Redesign premium SaaS-style (Bootstrap 5 + Alpine.js) |
| **Produtos** | Dinâmicos via painel admin, seed com 6 produtos iniciais |
| **Variações** | Não — preço fixo por produto, sem variação de tamanho/material |
| **photo_limit** | Definido no cadastro de cada produto pelo admin |
| **Upload** | S3, progressivo (1 foto por request), max 15MB, JPG+PNG |
| **Notificação** | Apenas no painel admin (sem email/WhatsApp automático) |
| **Auth** | 1 único admin (piskefotografia@gmail.com) |
| **Infra** | VPS próprio |
| **S3 Provider** | A decidir (interface S3 genérica — compatível com AWS/R2/MinIO) |
| **Assets** | CDN (Bootstrap 5 + Alpine.js + Bootstrap Icons) |

---

## 🏗️ Estrutura de Diretórios

```
app/
├── Apps/
│   ├── Site/                               # Mini App Público (Landing + Fluxo Cliente)
│   │   ├── Controllers/
│   │   │   ├── BaseController.php
│   │   │   ├── LandingController.php       # landing page, catálogo
│   │   │   ├── CartController.php          # carrinho (sessão)
│   │   │   ├── OrderController.php         # formulário dados + upload + confirmação
│   │   │   └── TrackingController.php      # link único de acompanhamento
│   │   ├── Views/
│   │   │   ├── layouts/
│   │   │   │   └── site.blade.php          # layout da landing (Bootstrap 5)
│   │   │   ├── landing/
│   │   │   │   └── index.blade.php         # hero + sobre + produtos + outros produtos + footer
│   │   │   ├── order/
│   │   │   │   ├── create.blade.php        # dados do cliente (nome + telefone)
│   │   │   │   ├── upload.blade.php        # upload progressivo de fotos
│   │   │   │   └── confirmation.blade.php  # confirmação + link de rastreio
│   │   │   └── tracking/
│   │   │       └── show.blade.php          # página de acompanhamento do pedido
│   │   ├── Providers/
│   │   │   └── SiteServiceProvider.php     # registra rotas + views
│   │   └── routes.php                      # rotas públicas
│   │
│   └── Admin/                              # Mini App Administrativo
│       ├── Controllers/
│       │   ├── BaseController.php
│       │   ├── DashboardController.php     # painel, contadores
│       │   ├── ProductController.php       # CRUD produtos
│       │   └── OrderController.php         # gerenciar pedidos, status, ver fotos
│       ├── Views/
│       │   ├── layouts/
│       │   │   └── admin.blade.php         # layout admin (sidebar Bootstrap)
│       │   ├── dashboard/
│       │   │   └── index.blade.php
│       │   ├── products/
│       │   │   ├── index.blade.php
│       │   │   └── form.blade.php
│       │   └── orders/
│       │       ├── index.blade.php
│       │       └── show.blade.php
│       ├── Providers/
│       │   └── AdminServiceProvider.php
│       └── routes.php
│
├── Domains/
│   ├── Product/
│   │   ├── Product.php                     # Model Eloquent
│   │   ├── Actions/
│   │   │   ├── CreateProductAction.php
│   │   │   ├── RetrieveProductAction.php
│   │   │   ├── RetrieveProductsAction.php
│   │   │   ├── UpdateProductAction.php
│   │   │   └── RemoveProductAction.php
│   │   └── Enums/
│   │       └── ProductTypeEnum.php         # pacote_fotos, quadro, ima, album
│   │
│   ├── Order/
│   │   ├── Order.php                       # Model
│   │   ├── OrderItem.php                   # Model (pivot com product)
│   │   ├── OrderPhoto.php                  # Model (fotos enviadas)
│   │   ├── Actions/
│   │   │   ├── CreateOrderAction.php       # cria pedido + itens via sessão do carrinho
│   │   │   ├── UploadOrderPhotoAction.php  # 1 foto por request → S3
│   │   │   ├── UpdateOrderStatusAction.php # muda status do pedido
│   │   │   ├── RetrieveOrderAction.php
│   │   │   └── RetrieveOrdersAction.php
│   │   └── Enums/
│   │       └── OrderStatusEnum.php         # enviado, revelando, concluido
│   │
│   └── User/
│       └── User.php                        # Model admin (usa tabela users padrão)
│
├── Support/
│   ├── Action.php                          # Classe abstrata base (DTO injection via setData/perform)
│   └── Data.php                            # DTO genérico (get, set, validate)
│
├── Integrations/
│   └── Storage/
│       └── S3StorageService.php            # wrapper para operações S3
│
└── Core/
    ├── Console/
    ├── Exceptions/
    │   └── Handler.php
    ├── Middlewares/
    │   └── RedirectIfAdmin.php             # middleware auth admin
    └── Providers/
        └── AppServiceProvider.php
```

---

## 🗄️ Banco de Dados

### Tabela: `products`

```sql
CREATE TABLE products (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name                VARCHAR(255) NOT NULL,
    slug                VARCHAR(255) NOT NULL UNIQUE,
    description         TEXT NULLABLE,
    price               DECIMAL(10,2) NOT NULL,
    photo_limit         INT UNSIGNED NOT NULL,
    type                ENUM('pacote_fotos','quadro','ima','album') NOT NULL,
    image_path          VARCHAR(500) NULLABLE,
    active              TINYINT(1) NOT NULL DEFAULT 1,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL
);
```

### Tabela: `orders`

```sql
CREATE TABLE orders (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid                CHAR(36) NOT NULL UNIQUE,
    customer_name       VARCHAR(255) NOT NULL,
    customer_phone      VARCHAR(20) NOT NULL,
    status              ENUM('enviado','revelando','concluido') NOT NULL DEFAULT 'enviado',
    notes               TEXT NULLABLE,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL
);
```

### Tabela: `order_items`

```sql
CREATE TABLE order_items (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id            BIGINT UNSIGNED NOT NULL,
    product_id          BIGINT UNSIGNED NOT NULL,
    quantity            INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price          DECIMAL(10,2) NOT NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);
```

### Tabela: `order_photos`

```sql
CREATE TABLE order_photos (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_item_id       BIGINT UNSIGNED NOT NULL,
    s3_path             VARCHAR(500) NOT NULL,
    original_name       VARCHAR(255) NOT NULL,
    size_bytes          BIGINT UNSIGNED NULLABLE,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE
);
```

### Tabela: `users` (padrão Laravel)

Usada apenas para o admin. Seed com `piskefotografia@gmail.com`.

### Tabelas padrão Laravel

- `sessions` (driver database, já configurado no .env)
- `cache` / `cache_locks` (driver database)
- `jobs` (queue database)
- `password_reset_tokens`
- `failed_jobs`

---

## 🔀 Rotas

### Site (público) — `app/Apps/Site/routes.php`

```
GET  /                          → LandingController@index          (landing page)
GET  /carrinho                  → CartController@index             (ver carrinho)
POST /carrinho/adicionar        → CartController@add              (adicionar item)
POST /carrinho/remover          → CartController@remove           (remover item)
POST /carrinho/limpar           → CartController@clear            (limpar carrinho)
GET  /pedido/criar              → OrderController@create           (form dados cliente)
POST /pedido                    → OrderController@store            (criar pedido)
GET  /pedido/{id}/upload        → OrderController@upload           (tela de upload)
POST /pedido/{id}/foto          → OrderController@uploadPhoto      (AJAX: 1 foto → S3)
POST /pedido/{id}/finalizar     → OrderController@finalize         (finalizar pedido)
GET  /pedido/{id}/confirmacao   → OrderController@confirmation     (tela confirmação)
GET  /rastreio/{uuid}           → TrackingController@show          (acompanhamento público)
```

### Admin (autenticado) — `app/Apps/Admin/routes.php`

```
GET  /admin/login               → AuthController@showLogin
POST /admin/login               → AuthController@login
POST /admin/logout              → AuthController@logout

GET  /admin                     → DashboardController@index
GET  /admin/produtos            → ProductController@index
GET  /admin/produtos/criar      → ProductController@create
POST /admin/produtos            → ProductController@store
GET  /admin/produtos/{id}/editar → ProductController@edit
PUT  /admin/produtos/{id}       → ProductController@update
DELETE /admin/produtos/{id}     → ProductController@delete

GET  /admin/pedidos             → OrderController@index
GET  /admin/pedidos/{id}        → OrderController@show
PUT  /admin/pedidos/{id}/status → OrderController@updateStatus
GET  /admin/pedidos/{id}/fotos  → OrderController@photos
```

---

## 🔄 Fluxo Detalhado do Cliente

### Passo 1: Landing Page (redesign premium SaaS)

- Hero com headline "Chega de fotos só no celular" + CTA "Fale com a gente!" → WhatsApp (mantido)
- Seção "Perguntas frequentes" — texto da página atual
- Seção "Tire suas memórias do digital" — Cards de pacotes de fotos (20/40/80) vindos do banco
  - Cada card = Product ativo do tipo `pacote_fotos`
  - Botão "Eu quero!" → adiciona ao carrinho (sessão)
- Seção "Outros Produtos" — Grid visual (Quadros, Imãs, Álbuns) vindos do banco
  - Cada card = Product ativo dos tipos `quadro`, `ima`, `album`
  - Botão "Eu quero!" → adiciona ao carrinho
- Footer com contato, redes sociais, endereço

### Passo 2: Carrinho (offcanvas/sidebar, Alpine.js)

- Badge no header com contagem de itens
- Offcanvas Bootstrap com lista de itens adicionados
- Exibir: nome do produto, preço, quantidade, sub-total
- Botão remover item
- Botão limpar carrinho
- Total calculado
- Botão "Finalizar Pedido" → redireciona para Step 3

### Passo 3: Dados do Cliente

- Formulário: Nome + Telefone (validação client-side + server-side)
- Resumo do carrinho (somente leitura)
- Botão "Criar Pedido" → `CreateOrderAction`
- Action: gera UUID, cria Order + OrderItems a partir da sessão, limpa carrinho

### Passo 4: Upload de Fotos

- Para cada item do pedido:
  - Label: "Nome do Produto — 0/N fotos enviadas"
  - Área de drag-and-drop ou seleção de arquivos (input file com accept="image/jpeg,image/png")
  - Upload AJAX progressivo (1 foto por request via `POST /pedido/{id}/foto`)
  - Barra de progresso individual por foto (XMLHttpRequest.upload.onprogress)
  - Preview thumbnail após upload bem-sucedido
- Status geral: "X de Y fotos enviadas"
- Se o cliente recarregar a página, o sistema consulta fotos já enviadas no banco e preenche o progresso
- Validação server-side: max 15MB, apenas JPG/PNG, não exceder photo_limit do produto
- Botão "Finalizar Envio" → só habilita quando TODOS os itens têm fotos completas

### Passo 5: Confirmação

- "Pedido criado com sucesso!"
- Número do pedido
- Link único para acompanhamento: `memorias.piskefotografia.com.br/rastreio/{uuid}`
- Instruções: "Seu pedido foi recebido. Acompanhe pelo link acima."
- Botão "Copiar Link"

### Passo 6: Acompanhamento (link público, sem login)

- Acessível via `GET /rastreio/{uuid}`
- Status visual pipeline: Enviado → Revelando → Concluído (badges coloridos)
- Lista de produtos pedidos com quantidade
- Contato WhatsApp para dúvidas
- Mensagens contextuais por status:
  - Enviado: "Seu pedido foi recebido e aguarda processamento."
  - Revelando: "Suas fotos estão sendo reveladas! Em breve estarão prontas."
  - Concluído: "Seu pedido está pronto! Entre em contato para combinar a entrega."

---

## 🔄 Fluxo do Admin

### Login

- Tela de login simples em `/admin/login`
- Email: piskefotografia@gmail.com + senha
- Middleware `RedirectIfAdmin` protege todas as rotas `/admin/*`

### Dashboard (`/admin`)

- Contadores: Pedidos novos (enviado) | Em revelação | Concluídos | Total do mês
- Lista dos 10 últimos pedidos (nome, telefone, status, data, link para detalhe)

### Produtos (`/admin/produtos`)

- Lista com nome, tipo, preço, limite de fotos, status ativo/inativo
- Criar: nome, slug (auto-gerado), descrição, preço, limite de fotos, tipo (enum), imagem de capa, ativo
- Editar: mesmos campos
- Excluir: hard delete (com confirmação)

### Pedidos (`/admin/pedidos`)

- Lista com filtros: status (tabs), busca por nome/telefone
- Detalhe do pedido:
  - Dados do cliente (nome, telefone)
  - Lista de itens com quantidades e preços
  - Grid de fotos enviadas (thumbnails do S3)
  - Trocar status: dropdown Enviado → Revelando → Concluído
  - Campo de notas internas (textarea)
  - Link de rastreio copiável

---

## ⚙️ Detalhes Técnicos Críticos

### Carrinho (Sessão)

- Armazenado em `session('cart')` como array serializado
- Estrutura: `[{ product_id, name, price, photo_limit, quantity }]`
- Gerenciado via Alpine.js no front, AJAX para add/remove via `CartController`
- Sem persistência em banco — só vira `Order` quando o cliente finaliza

### Upload Progressivo para S3

- Cada foto enviada individualmente via AJAX `POST /pedido/{id}/foto`
- Fluxo: `UploadOrderPhotoAction` → valida tipo (jpg/png) e tamanho (max 15MB) → envia ao S3 via `S3StorageService` → salva path em `order_photos`
- Path no S3: `orders/{order_id}/{item_id}/{timestamp}_{original_name}`
- Barra de progresso via `XMLHttpRequest.upload.onprogress` (Alpine.js)
- Se o cliente recarregar, consulta fotos já enviadas e restaura o progresso

### Configuração S3

- Usar driver `s3` do Laravel Filesystem (compatível com qualquer provider S3)
- Configurar no `.env`: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_ENDPOINT` (para R2/MinIO)
- Disco configurado em `config/filesystems.php`
- Requer pacote Composer: `league/flysystem-aws-s3-v3`

### Link Único de Rastreio

- UUID v4 gerado no `CreateOrderAction`
- Rota pública: `GET /rastreio/{uuid}` → `TrackingController@show`
- Sem autenticação necessária

### CDN Assets

- Bootstrap 5 CSS + JS via CDN (jsdelivr ou cdnjs)
- Alpine.js via CDN
- Bootstrap Icons via CDN
- Remover Tailwind do `package.json` e `vite.config.js` (ou manter Vite mínimo para build futuro)

---

## 🌱 Seed Inicial

### Products

| name | slug | price | photo_limit | type | active |
|------|------|-------|-------------|------|--------|
| 20 Fotos | 20-fotos | 60.00 | 20 | pacote_fotos | true |
| 40 Fotos | 40-fotos | 120.00 | 40 | pacote_fotos | true |
| 80 Fotos | 80-fotos | 250.00 | 80 | pacote_fotos | true |
| Polaroid Imã | polaroid-ima | 15.00 | 1 | ima | true |
| Quadro | quadro | 15.00 | 1 | quadro | true |
| Álbum | album | 15.00 | 20 | album | true |

### Admin User

- Email: `piskefotografia@gmail.com`
- Password: `password` (alterar no primeiro login)
- Name: Admin

---

## 📦 Pacotes Composer Necessários

- `league/flysystem-aws-s3-v3` — driver S3 do Laravel

---

## 📐 Fases de Implementação (Ordem de Execução)

### Fase 1 — Setup da Estrutura DDD
- Mover infraestrutura Laravel para `app/Core/` (Exceptions, Providers, Console, Middlewares)
- Criar `app/Support/Action.php` e `app/Support/Data.php` (DTO base)
- Configurar namespaces PSR-4 no `composer.json`
- Criar Mini Apps: `app/Apps/Site/` e `app/Apps/Admin/` com ServiceProviders
- Configurar `bootstrap/app.php` para carregar os ServiceProviders
- Remover Tailwind do package.json e vite.config.js
- Adicionar Bootstrap 5, Alpine.js e Bootstrap Icons via CDN nos layouts

### Fase 2 — Auth Admin
- Migration `users` (padrão Laravel)
- Tela de login admin (`/admin/login`)
- Middleware `RedirectIfAdmin`
- Seed do usuário admin (piskefotografia@gmail.com)

### Fase 3 — Domínio Product
- Migration `products`
- Model `Product` (com PHPDoc, casts para ProductTypeEnum)
- Enum `ProductTypeEnum` (pacote_fotos, quadro, ima, album)
- Actions: Create, Retrieve, RetrieveAll, Update, Remove
- Seeder `ProductSeeder` com os 6 produtos

### Fase 4 — Admin Products (CRUD)
- `ProductController` no Mini App Admin
- Views: index (lista com filtros), form (criar/editar)
- Layout admin com sidebar Bootstrap

### Fase 5 — Domínio Order
- Migrations: `orders`, `order_items`, `order_photos`
- Models: `Order`, `OrderItem`, `OrderPhoto` (com relacionamentos)
- Enum `OrderStatusEnum` (enviado, revelando, concluido)
- Actions: CreateOrder (do carrinho), UploadOrderPhoto (1 foto → S3), UpdateOrderStatus, RetrieveOrder, RetrieveOrders
- `S3StorageService` em Integrations

### Fase 6 — Carrinho (Sessão)
- `CartController` no Mini App Site
- Métodos: index (ver), add (adicionar), remove (remover), clear (limpar)
- Lógica Alpine.js no front para gerenciar estado do carrinho
- Offcanvas Bootstrap para exibição do carrinho

### Fase 7 — Landing Page (Redesign Premium)
- Layout `site.blade.php` com Bootstrap 5 premium SaaS-style
- View `landing/index.blade.php` com:
  - Hero (headline + CTA WhatsApp)
  - Seção "Perguntas" (texto da landing atual)
  - Seção "Sobre" (texto da landing atual)
  - Seção "Pacotes de Fotos" (cards dinâmicos do banco, tipo pacote_fotos)
  - Seção "Outros Produtos" (grid dinâmico, tipos quadro/ima/album)
  - Footer (contato, redes sociais, endereço)
- Seguir skill `create-frontend` para padrão visual premium SaaS

### Fase 8 — Fluxo do Cliente (Dados + Upload + Confirmação)
- View `order/create.blade.php` — formulário nome + telefone + resumo carrinho
- View `order/upload.blade.php` — upload progressivo com Alpine.js
  - Drag-and-drop area
  - Barra de progresso por foto
  - Preview thumbnails
  - Contador "X de N fotos"
- View `order/confirmation.blade.php` — confirmação + link UUID

### Fase 9 — Tracking (Link Público)
- `TrackingController` no Mini App Site
- View `tracking/show.blade.php` — status pipeline visual
- Acesso via UUID sem autenticação

### Fase 10 — Admin Orders (Gerenciamento)
- `OrderController` no Mini App Admin
- Views: index (lista com filtros/tabs por status), show (detalhe completo)
- Troca de status via dropdown
- Grid de fotos com thumbnails S3
- Campo de notas internas

### Fase 11 — Dashboard Admin
- `DashboardController` com contadores
- Cards: Pedidos novos | Em revelação | Concluídos | Total mês
- Lista dos últimos 10 pedidos

### Fase 12 — Polish
- Validações server-side completas em todas as Actions
- Empty states emocionais (seguir skill `create-frontend`)
- Toasts de feedback (sucesso/erro) via Alpine.js
- Responsividade mobile-first
- Testes manuais do fluxo completo

---

## 🎨 Design System (Seguir skill `create-frontend`)

- **Zero jQuery** — tudo em Alpine.js
- **Estilo Vercel/Stripe** — sombras etéreas, bordas ultraleves, tipografia enorme
- **Cores** — usar variantes `-subtle` do Bootstrap
- **Cards** — `rounded-4` ou `rounded-5`
- **Empty states** — ícones grandes, CTAs pulsantes, respiro vertical enorme
- **Tabelas** — headers `text-uppercase fs-7 fw-semibold`, bordas `border-secondary-subtle`
- **Inputs** — `form-control-lg`, `bg-body-tertiary`, `border-0`, `shadow-none`, `rounded-4`
- **Botões** — `btn-dark btn-lg rounded-4 fw-semibold`

---

## 🛡️ Skills do Projeto a Seguir

Toda implementação DEVE seguir rigorosamente as skills do projeto:

| Skill | Quando usar |
|-------|-------------|
| `convert-laravel-structure` | Fase 1 — conversão da estrutura para DDD modular |
| `create-model` | Fases 3 e 5 — criação de Models (Product, Order, OrderItem, OrderPhoto, User) |
| `create-actions` | Fases 3 e 5 — criação de Actions (padrão Command com DTO) |
| `create-controller` | Fases 4, 6, 8, 9, 10, 11 — Controllers magros com injeção de Actions |
| `create-frontend` | Fases 7, 8, 9 — views Blade premium SaaS |
| `create-route` | Todas as fases — rotas agrupadas por controller, FQCN, nomeadas |
| `create-integration` | Fase 5 — S3StorageService com interface Contract-Based |

---

## 📌 Notas Importantes

- O sistema NÃO processa pagamento — apenas gerencia pedidos e upload
- O admin é notificado de novos pedidos apenas ao acessar o painel
- As fotos ficam armazenadas no S3 permanentemente (ou até delete manual)
- O UUID do pedido é público mas não adivinhável (UUID v4)
- O carrinho não persiste entre sessões do navegador (sessão PHP)
- O `photo_limit` de cada produto é editável pelo admin a qualquer momento
- O preço no `order_items` é um snapshot do preço na hora da compra (não muda se o admin alterar o produto depois)
