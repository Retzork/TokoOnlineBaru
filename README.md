# TokoOnlineBaru — Web Programming III

Laravel 10 online store (TokoOnline). The admin backend was built in Web Programming II; Web Programming III adds the customer-facing storefront, week by week.

This README has two jobs:

1. **Progress tracker** — documents **finished weeks only**, with a step-by-step "How to check" guide per week.
2. **Onboarding context** — enough background for a new agent (or human) to understand the project and continue with the next week without re-exploring everything.

## Project context

- The full week-by-week plan lives in **`../project.md`** (workspace root, next to the app folder). It contains 12 weekly briefs; weeks must be done **in order** (each builds on the previous one). The module has **no Week 8** — numbering jumps from 7 to 9.
- Each brief lists tasks plus **"Known issues to fix"** — the module's sample code contains typos/inconsistencies that must be corrected, not copied.
- **Already done in WP II (do not rebuild):** tables `user`, `kategori`, `produk`, `foto_produk`; models `User`, `Kategori`, `Produk`, `FotoProduk`; backend controllers `BerandaController`, `LoginController`, `UserController`, `KategoriController`, `ProdukController`; admin views under `resources/views/backend/` (Matrix Admin template); helper `App\Helpers\ImageHelper::uploadAndResize()`.
- **WP III builds:** the public storefront using the E-Shop (Colorlib) template, themed as a traditional Indonesian snack store ("Jajanan Tradisional").
- **Customer feature design:** `docs/blueprint-customer.md` — `user` + `customer` table structure, their 1 : 0..1 relation and the Google-login data flow.

## Running the project

1. Start **MySQL** and **Mailpit** from Laragon (Apache optional).
2. Serve the app:

   ```
   php artisan serve
   ```

3. Open http://127.0.0.1:8000

| Area | URL | Credentials |
| --- | --- | --- |
| Storefront | http://127.0.0.1:8000 | — |
| Customer login (email/password) | http://127.0.0.1:8000/customer/login | `nailah22@gmail.com` / `customer123` |
| Admin backend | http://127.0.0.1:8000/backend/login | `kelompok3@gmail.com` / `kelompok123@` |
| Mailpit (mail catcher) | http://localhost:8025 | — |
| Database browser (phpMyAdmin) | http://localhost/phpmyadmin/ | auto-login as `adminer` (MySQL user with rights on `db_tokoonline` only) |
| Database browser (Adminer) | http://localhost/adminer/ | `adminer` / `adminer` |

- Database: `db_tokoonline` (MySQL, user `root`, empty password). Seeded users: `kelompok3@gmail.com` (Super Admin — the module's seeded `fauzi@gmail.com`, renamed at the group's request) and two regular users — passwords `kelompok123@` (kelompok3) / `P@55word` (others).
- `.env` mail settings point at Mailpit on `127.0.0.1:1025` (`MAIL_HOST=127.0.0.1` — the Laragon install does not resolve the docker hostname `mailpit`).
- Product images use `php artisan storage:link`; uploads live in `storage/app/public/img-produk/`.

## Conventions (follow these)

- Table names are **singular** (`user`, `kategori`, `produk`, `foto_produk`, `customer`, and later `order`, `order_item`).
- Indonesian naming for fields and UI text.
- Backend views extend `backend.v_layouts.app`; frontend views extend `v_layouts.app`. Content goes in `@section('content')`.
- Frontend layout: `resources/views/v_layouts/app.blade.php`. Frontend views are `v_<name>/<page>.blade.php` (e.g. `v_beranda/index.blade.php`).
- Asset links use `asset('frontend/...')`, `asset('image/...')`, `asset('storage/img-produk/...')`. Product photos have size variants: prefixes `thumb_sm_`, `thumb_md_`, `thumb_lg_`.
- Product `status`: `1` = published, `0` = blocked. Blocked products must never appear on the storefront.
- Every `route('...')` used in a view must exist in `routes/web.php`. All admin/backend routes stay behind `auth` middleware.

## Agent workflow (do this every week)

1. Read the current week's brief in `../project.md`.
2. Implement the tasks in order; also fix the week's "Known issues" instead of copying the module's bugs.
3. Verify: run the app, hit the pages the brief mentions, and check that referenced assets/routes return 200.
4. **Update this README**: add a section for the finished week (template below) and update the status table.

### Week section template

```markdown
### Week N — <Title> :white_check_mark:

Completed: <date>

**Goal:** <one line from the brief>

**What was done** — bullets with file paths

**Known issues from the module fixed** — from the brief's list

**How to check** — numbered, step-by-step browser walkthrough with expected results

**Files to inspect** — table: file → what changed

**Verified** — test results (status codes, assets checked)
```

## Status

| Week | Topic | Status |
| --- | --- | --- |
| 1 | Frontend Setup and Home Page | :white_check_mark: Done |
| 2 | Product Display (list, detail, category filter, all products, pagination) | :white_check_mark: Done |
| 3 | Customer Registration and Login with Google (Socialite) | :white_check_mark: Done |
| 4 | Account Management (frontend session + backend customer list) | :white_check_mark: Covered by Week 5 |
| 5 | Route Groups and Middleware (customer account page) | :white_check_mark: Done |
| 6 | Shopping Cart | :white_check_mark: Done |
| 7 | RajaOngkir Shipping Cost API (standalone test page) | :white_check_mark: Done |
| 9 | Ordering and Checkout Flow | :warning: Partial — checkout/history work; shipping still uses the old city-based flow |
| 10 | Order Management (backend) and Invoices | :white_check_mark: Done |
| 11 | Order Chart (admin dashboard) | Not started |
| 12 | Payment Gateway with Midtrans (Snap) | Not started |
| 13–15 | Group Project Presentation (no code) | — |

Detailed descriptions for unfinished weeks stay in `../project.md` — this README only documents finished work. Week 9's checkout → history path is present and covered by the Week 10 tests, but its shipping form still uses city IDs and a hardcoded origin, so a full Week 9 pass is still pending.

## Progress

### Week 1 — Frontend Setup and Home Page :white_check_mark:

Completed: 2026-10-15

**Goal:** install the E-Shop template as the public layout and render a home page at `/`.

**What was done**

- `public/frontend/` — E-Shop (Colorlib) template installed:
  - full template extracted to `public/frontend/eshop/`
  - `css/`, `fonts/`, `img/`, `js/` copied to `public/frontend/`
  - banner images `banner01.jpg`–`banner03.jpg` in `public/frontend/banner/`
- `public/image/logo.png` and `public/image/icon_univ_bsi.png` added (header/footer logo, favicon).
- `resources/views/v_layouts/app.blade.php` — public layout derived from the template's `index.html`:
  - all asset links use `asset('frontend/...')`
  - 3 home banner slides (`#home-slick`) use `asset('frontend/banner/bannerNN.jpg')` with captions "Jajanan Tradisional" / "Khas Makanan Indonesia" and button "Pesan Sekarang"
  - header/footer text themed for the store (traditional Indonesian snacks)
  - static product cards for now (moved to the beranda view in Week 2)
- `BerandaController::index()` — returns the 6 newest published products (`status = 1`, ordered by `updated_at` desc, paginate 6) and `judul` = "Halaman Beranda".
- `resources/views/v_beranda/index.blade.php` — extends `v_layouts.app`, content section is empty for now (filled in Week 2).
- `routes/web.php` — `/` redirects to route `beranda`; added `GET /beranda` → `BerandaController@index` (name `beranda`). All existing backend routes kept behind `auth`.

**Known issues from the module fixed**

- Footer logo used `./img/logo.png` → now `asset('image/logo.png')`.
- Typo "Halan Beranda" → "Halaman Beranda".

**How to check**

1. Start MySQL in Laragon, then run `php artisan serve` and open http://127.0.0.1:8000
   → The browser should **redirect to `/beranda`** and show the storefront.
2. Check the top of the page → golden welcome bar "Selamat datang di Jajanan Tradisional!", logo on the left, search box "Cari produk...", "Akun Saya" and "Keranjang: Rp. 0" on the right.
3. Check the navigation below the header → "Kategori" (Makanan, Minuman) and menu "Beranda / Produk / Lokasi / Hubungi Kami".
4. Watch the banner slider (`#home-slick`) → 3 slides rotate automatically, captions "Jajanan Tradisional" / "Khas Makanan Indonesia", button "Pesan Sekarang". All 3 banner images must load.
5. Scroll down → "Produk Terbaru" section with 4 demo cards (Kue Lapis Tradisional, Onde-Onde Spesial, Klepon Gula Aren, Pastel Basah). **Note:** these are static template placeholders — Week 2 replaces them with real products from the database.
6. Check the footer → store logo, description, social icons, link columns, subscribe form, and copyright with the current year.
7. Open the browser tab → favicon shows the BSI icon (`public/image/icon_univ_bsi.png`).
8. Regression check (backend untouched) → open http://127.0.0.1:8000/backend/login and log in with `kelompok3@gmail.com` / `kelompok123@`; the admin beranda still shows "Selamat Datang, Mahasiswa BSI".

**Files to inspect**

| File | What changed |
| --- | --- |
| `routes/web.php` | `/` redirects to `beranda`; new `/beranda` route |
| `app/Http/Controllers/BerandaController.php` | new `index()` method |
| `resources/views/v_layouts/app.blade.php` | new public layout (new file) |
| `resources/views/v_beranda/index.blade.php` | new beranda view (new file) |
| `public/frontend/*`, `public/image/*` | new assets |

Verify routes were registered: `php artisan route:list --except-vendor` → look for `beranda`.

**Verified**

- `http://127.0.0.1:8000/` → 302 to `/beranda`, renders with status 200.
- All 21 local assets (css, js, images, banners, logo, favicon) return 200 — no broken assets.

### Week 2 — Product Display (list, detail, category filter, all products, pagination) :white_check_mark:

Completed: 2026-10-15

**Goal:** render database products on the storefront: home list, detail page, category filter, all-products page and pagination.

**What was done**

- Seeded 8 products (7 published, 1 blocked: "Kerupuk Melinjo") — photos are free-license images from Wikimedia Commons (kue lapis, onde-onde, klepon, pastel, risoles, es cendol, wedang jahe, emping melinjo) center cover-cropped to 3:4 and saved into `storage/img-produk/` with `thumb_sm_`/`thumb_md_`/`thumb_lg_` variants; 2 extra photos attached to "Kue Lapis Tradisional" via `foto_produk` so the detail gallery has multiple images.
- `resources/views/v_layouts/app.blade.php`:
  - static "Produk Terbaru" block removed, replaced with `@yield('content')` (the section now renders each page's content)
  - `#home` banner wrapped in `@if (request()->segment(1) == '' || request()->segment(1) == 'beranda')` — banner shows only on the home page
  - `$kategori` loaded once at the top of `<body>` (`DB::table('kategori')->orderBy('nama_kategori','asc')->get()`), used by the search select and the category nav
  - `category-nav` gets `show-on-click` on non-home pages; one loop renders the categories with `route('produk.kategori', $k->id)` links
  - menu nav: Beranda → `route('beranda')`, Produk → `route('produk.all')`, Lokasi / Hubungi Kami stay `#`
- `resources/views/v_beranda/index.blade.php` — "Produk Terbaru" grid looping `$produk`: category label, `thumb_md_` image, `Rp.`-formatted price, name and "Detail Produk" both linking to `route('produk.detail', $row->id)`, plus the Pesan form placeholder (wired in Week 6).
- `resources/views/v_produk/produkkategori.blade.php` (Filter Kategori) and `resources/views/v_produk/index.blade.php` (Semua Produk) — aside with the "Filter Kategori" widget + 3-column card grid + bottom `.store-filter` with `{{ $produk->links('vendor.pagination.custom') }}`.
- `resources/views/v_produk/detail.blade.php` — `thumb_lg_` main image, `thumb_sm_` thumbnail strip, additional `foto_produk` photos, category label, name, price, `{!! $row->detail !!}`, weight (gram) and stock, Pesan button.
- `resources/views/vendor/pagination/custom.blade.php` — `<ul class="pagination">` with `&laquo;`/`&raquo;` prev/next, disabled/active states and numbered links.
- `app/Http/Controllers/ProdukController.php` — new `detail($id)` (404 for blocked or unknown products), `produkKategori($id)`, `produkAll()`; all filter `status = 1`, order by `updated_at` desc and pass `$kategori` for the sidebar.
- `routes/web.php` — `GET /produk/all`, `GET /produk/kategori/{id}`, `GET /produk/detail/{id}` registered before the existing `produk/{id}` route so `/produk/all` is not captured by the backend show route.

**Visual fixes (after review)**

- Reported issue: product images looked skewed ("too thick"). Root cause: `ProdukController::store()`/`update()` generated `thumb_md_` (500x519) and `thumb_sm_` (100x110) with a forced height, squashing the 3:4 photos (~22% for `thumb_md_`). Both methods now resize by width only (`500, null` / `100, null`), same as `thumb_lg_` already did.
- All seeded product photos replaced with matching free-license Wikimedia Commons images (kue lapis, onde-onde, klepon, pastel, risoles, es cendol, wedang jahe, emping melinjo), center cover-cropped to 3:4: originals 900x1200, `thumb_lg_` 800x1067, `thumb_md_` 500x667, `thumb_sm_` 100x133. Existing filenames were overwritten, so the database was left untouched. The 2 gallery extras of "Kue Lapis Tradisional" (previously a spaghetti illustration and a Coca-Cola bottle) were also replaced with kue lapis photos.
- Template "E-SHOP" logo replaced with a "Jajanan Tradisional" wordmark (`public/image/logo.png`, 420x140 PNG, orange `#F8694A` + dark `#30323A`) — renders at 210x70 in the header and footer through the existing `max-height: 70px` rule.

**Known issues from the module fixed**

- `detail()` array key is quoted (`'judul' =>`, not `judul =>`).
- `$kategori` is defined once before the home/non-home branches, so non-home pages don't error.
- No stray duplicate `<ul class="category-list">`; a single loop renders the category nav.
- Product-name links (plus a "Detail Produk" button) point to `route('produk.detail', ...)` instead of `#`.
- Pesan forms are placeholders (`action="#"`) until Week 6; blocked products never appear and their detail page returns 404.

**How to check**

1. Start MySQL in Laragon, run `php artisan serve`, open http://127.0.0.1:8000 → redirects to `/beranda`.
2. Home → "Produk Terbaru" shows 6 newest published products (Kue Lapis Tradisional, Onde-Onde Spesial, Klepon Gula Aren, Pastel Basah, Risoles Mayo, Es Cendol Dawet), each with a category badge, price like "Rp. 25.000" and a real snack photo at true 3:4 (no distortion). "Wedang Jahe" (7th) and blocked "Kerupuk Melinjo" are not on this page.
3. Click a product name or "Detail Produk" → detail page with main image, thumbnail strip, category badge, name, price, Stok, Berat in gram, description and a Pesan button. On "Kue Lapis Tradisional" the strip also has 2 extra photos.
4. Click "Produk" in the menu → `/produk/all`: 6 products, "Filter Kategori" sidebar, custom pagination bottom right; click "2" or `»` → only "Wedang Jahe". The banner is gone on this page and the category dropdown is collapsed until clicked (`show-on-click`).
5. Open the "Kategori" dropdown (or the sidebar) → `Makanan` shows only the 5 published food products, `Minuman` only "Es Cendol Dawet" and "Wedang Jahe".
6. Try `/produk/detail/8` (the blocked Kerupuk Melinjo) → 404; it appears nowhere on the storefront.
7. Regression check → http://127.0.0.1:8000/backend/login still loads and admin login works.

**Files to inspect**

| File | What changed |
| --- | --- |
| `routes/web.php` | 3 new public product routes (before `produk/{id}`) |
| `app/Http/Controllers/ProdukController.php` | new `detail`, `produkKategori`, `produkAll`; store/update thumbnails resize by width only (no forced height) |
| `resources/views/v_layouts/app.blade.php` | `@yield('content')`, banner conditional, category nav + menu links, `$kategori` loaded once |
| `resources/views/v_beranda/index.blade.php` | "Produk Terbaru" loop over `$produk` |
| `resources/views/v_produk/index.blade.php`, `produkkategori.blade.php` | list views with Filter Kategori sidebar + pagination |
| `resources/views/v_produk/detail.blade.php` | product detail with gallery |
| `resources/views/vendor/pagination/custom.blade.php` | new custom pagination (new file) |
| Database | 8 seeded products, 2 `foto_produk` rows, images in `storage/img-produk/` |
| `public/storage/img-produk/*.jpg` | product + gallery photos regenerated from Wikimedia Commons, 3:4 (900x1200 / 800x1067 / 500x667 / 100x133) |
| `public/image/logo.png` | "Jajanan Tradisional" wordmark (was the template "E-SHOP" logo) |

**Verified**

- `/` → 302 to `/beranda` (200). Home shows exactly 6 cards, correct ordering, blocked product absent.
- `/produk/all` page 1 = 6 cards; `?page=2` = 1 card ("Wedang Jahe"); custom pagination with prev/next and active state.
- Category pages filter correctly (Makanan 5 items, Minuman 2 items); blocked product absent everywhere; `/produk/detail/8` → 404; `/produk/detail/999999` → 404.
- Detail page: `thumb_lg_`/`thumb_sm_`/additional photos, price "Rp. 25.000", Berat 250 gram, Stok 40, description HTML rendered.
- All 32 image variants return 200; `thumb_md_` fetched over HTTP → 200 `image/jpeg`.
- Regression: `/backend/login` → 200; PHP lint clean on `ProdukController.php` and `routes/web.php`.
- After the visual fixes: all 40 product image files (8 products + 2 extras × original/lg/md/sm) verified at exact 3:4; `/beranda` and `/produk/detail/1` re-screenshotted (headless Edge) and reviewed — no distortion, logo renders at 210x70 in header and footer.
- Full Week 2 HTTP suite re-run after the fixes: 50/51 pass. The single FAIL is a known false positive in the "no wishlist/add-to-cart dead buttons" check (footer's `fa fa-heart-o` icon matches the pattern; there are no actual dead buttons).

### Week 3 — Customer Registration and Login with Google (Socialite) :white_check_mark:

Completed: 2026-10-17

**Goal:** register/log in storefront customers with their Google account (Laravel Socialite) — a first login creates a `user` row (role 2) plus a linked `customer` row.

**What was done**

- `laravel/socialite` installed (`composer require laravel/socialite` → `^5.31`).
- `.env` / `.env.example` — `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` (empty placeholders until the OAuth client exists) and `GOOGLE_REDIRECT_URL=http://localhost:8000/auth/google/callback` (must be `localhost` — Google Cloud rejects IP addresses in OAuth redirect URIs).
- `config/services.php` — `google` client block reading those three env vars.
- `database/migrations/2026_10_17_120000_create_customer_table.php` — `customer` table: `id`, `user_id` (FK → `user.id`, cascade on delete), nullable `google_id`, `google_token`, `alamat`, `pos`, timestamps.
- `app/Models/Customer.php` — extends `Authenticatable`, `$table = 'customer'`, `$fillable` limited to columns that actually exist, `user()` belongsTo, and a `deleting` hook that deletes the related `user` row.
- `app/Http/Controllers/CustomerController.php`:
  - `redirect()` → `Socialite::driver('google')->redirect()`
  - `callback()` → finds the `User` by Google email; if new, creates the `user` (role `'2'`, status 1, random 32-char hashed password) and the `customer` (google_id + google_token), then `Auth::login()`; existing accounts are logged in **only when role is 2**; every failure redirects to `/` with a `msgError` flash
  - `logout(Request)` → `Auth::logout()`, session invalidate + CSRF token regenerate, redirect `/`
- `routes/web.php` — `GET /auth/redirect` (`auth.redirect`), `GET /auth/google/callback` (`auth.callback`), `POST /logout` (`logout`).
- `resources/views/v_layouts/app.blade.php` — Account-area "Login" and "Daftar" links point to `route('auth.redirect')`.

**Known issues from the module fixed**

- `Customer::$fillable` no longer lists `user` columns (`nama, email, status, role, password, hp, foto`) — only the real `customer` columns.
- Default password `'default_password'` replaced with `Hash::make(Str::random(32))`.
- Admin/superadmin emails cannot log in through Google: role ≠ 2 → redirect to `/` with `msgError` "Email ini terdaftar sebagai akun admin, silakan login melalui halaman backend."; admin login stays on `/backend/login`.
- An email that already exists in `user` is reused (no duplicate `user` row), per the module's flow.

**How to check**

1. One-time manual step (user): in the Google Cloud Console create a project, configure the OAuth consent screen (External), create an OAuth client of type "Web application" (the console moved this to **Google Auth Platform → Clients**, no longer under "Credentials") with redirect URI `http://localhost:8000/auth/google/callback` — Google shows *"Invalid URL: cannot contain an IP address"* if `127.0.0.1` is used — then put the Client ID and Secret into `.env` (`GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`). Leave the consent screen's *App domain* and *Authorized domains* fields empty: they never accept `localhost` or IPs (*"Invalid URL: cannot contain a localhost domain"*) and are not required while testing. While the consent screen is in "Testing", add the Gmail used for testing as a test user.
2. Start MySQL in Laragon, run `php artisan serve`, and for the Google flow open **http://localhost:8000** (not `127.0.0.1`): the OAuth `state` is stored in the session cookie, and a cookie set on one host is not sent to the other. Other pages work the same on both hosts.
3. Header "Akun Saya" → "Login" (or "Daftar") → the browser goes to `accounts.google.com`; pick an account and allow.
4. You land back on `/beranda`. The header still shows "Login / Daftar" — expected for now; showing the logged-in name arrives in Week 4.
5. Confirm the database in the browser with **phpMyAdmin** — open http://localhost/phpmyadmin/ (Laragon's pre-configured `/phpmyadmin` alias; opens already logged in as MySQL user `adminer`) → database `db_tokoonline` → table `customer`: one row per registered customer with `google_id` filled; table `user`: the matching row has `role = 2`, `status = 1` (and `customer.google_token` is filled too). In the **SQL** tab, the same check is:
   `SELECT u.nama, u.email, u.role, u.status, c.google_id FROM user u JOIN customer c ON c.user_id = u.id ORDER BY u.id DESC LIMIT 1;`
   Alternative, **Adminer** (also bundled with Laragon): http://localhost/adminer/ → Server `127.0.0.1`, Username `adminer`, Password `adminer`, Database `db_tokoonline`. The dedicated `adminer` MySQL user exists because browser DB tools refuse passwordless logins; the app itself still uses MySQL `root` with an empty password. Recreate the checker user with:
   `CREATE USER IF NOT EXISTS 'adminer'@'localhost' IDENTIFIED BY 'adminer'; CREATE USER IF NOT EXISTS 'adminer'@'127.0.0.1' IDENTIFIED BY 'adminer'; GRANT ALL PRIVILEGES ON db_tokoonline.* TO 'adminer'@'localhost'; GRANT ALL PRIVILEGES ON db_tokoonline.* TO 'adminer'@'127.0.0.1'; FLUSH PRIVILEGES;`
6. Click Login again with the same Gmail → you end up logged in on `/beranda` again; no duplicate `user` row is created.
7. Admin restriction: try Login with the admin email `kelompok3@gmail.com` → you are **not** logged in as admin (still a guest) and no `customer` row is created; the `msgError` flash is set and becomes visible once the layout renders flash messages (Week 4).
8. Without Google credentials yet: `/auth/redirect` still redirects to Google's consent URL (with an empty `client_id`), and `/auth/google/callback` reached without OAuth state redirects gracefully to `/`.

**Files to inspect**

| File | What changed |
| --- | --- |
| `composer.json`, `composer.lock` | added `laravel/socialite` `^5.31` |
| `.env`, `.env.example` | `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URL` |
| `config/services.php` | `google` block |
| `database/migrations/2026_10_17_120000_create_customer_table.php` | new `customer` table (new file) |
| `app/Models/Customer.php` | new model + `deleting` hook (new file) |
| `app/Http/Controllers/CustomerController.php` | `redirect`, `callback`, `logout` (new file) |
| `routes/web.php` | `auth.redirect`, `auth.callback`, `logout` routes |
| `resources/views/v_layouts/app.blade.php` | Account-area Login/Daftar links |
| `docs/blueprint-customer.md` | customer table/relation design doc (new file) |

**Verified**

- Kernel suite with mocked Socialite: **21/21 PASS** — new `user` + `customer` created with role 2 / status 1 / bcrypt hash, session authenticated, admin email rejected without login (flash set), logout tears the session down, test rows cleaned up afterwards.
- HTTP suite against `php artisan serve`: **39/39 PASS** — `/auth/redirect` → 302 to `https://accounts.google.com/o/oauth2/auth?...` with the configured `redirect_uri`; callback without OAuth state → 302 back to `/`; `POST /logout` → 419 without CSRF and 302 to `/` with it; storefront pages unchanged (home 6 cards, all-products 6 + 4, each category 2, blocked product 404); `/backend/login` 200; all checked assets 200.
- Database after the mocked/HTTP suites: 3 users, 0 customers (no test leftovers).
- **Real end-to-end Google login succeeded (2026-10-17)** with the user's own OAuth client: registering with a real Google account created `user` id 6 ("rahmat mubarok", role 2, status 1) and `customer` id 3 (`google_id` and `google_token` filled); the same account logging in again does not create duplicates. The header still shows "Login / Daftar" until Week 4. (`.env` holds the real Google credentials; `.env.example` keeps empty placeholders.)
- Database browser: **phpMyAdmin 5.2.3** at http://localhost/phpmyadmin/ (auto-login as MySQL user `adminer`; installed into Laragon's pre-existing `etc/apps/phpMyAdmin` alias target) — registration rows are visible under `db_tokoonline` → `user` / `customer`. Adminer remains available at http://localhost/adminer/ (`adminer` / `adminer`).
- Environment note: on this machine plain `php artisan serve` initially failed with `Failed to listen on 127.0.0.1:8000 (reason: ?)` — a known Laravel-on-Windows bug (Herd's `php.ini` had `variables_order = "EGPCS"`; Laravel's env whitelist for the child server is case-sensitive, so `Path`/`SystemRoot` were stripped and `php -S` could not bind). Fixed by setting `variables_order = "GPCS"` in `C:\Users\TAMU\.config\herd\bin\php84\php.ini` (backup: `php.ini.egpcs-backup`); plain `php artisan serve` now works.

### Week 5 — Route Groups and Middleware :white_check_mark:

Completed: 2026-10-27

**Goal:** protect the customer pages with a custom middleware and group the customer routes (module Minggu Ke-5). This also delivers the frontend account page listed as Week 4 in the earlier plan.

**What was done**

- `app/Http/Middleware/IsCustomer.php` — new: `auth()->check() && auth()->user()->role == 2` passes, otherwise redirect to `/auth/redirect` with a `msgError` flash (`Anda harus login sebagai customer`).
- `app/Http/Kernel.php` — registered the alias `'is.customer' => \App\Http\Middleware\IsCustomer::class` in `$middlewareAliases`.
- `app/Http/Controllers/CustomerController.php` — `akun($id)` (redirects to the logged-in customer's own id with `msgError` when the id does not match) and `updateAkun(Request $request, $id)` (validates `nama`, `hp`, `foto`, plus conditional `email`/`alamat`/`pos`, deletes the old photo, resizes the new one with `ImageHelper::uploadAndResize(..., 385, 400)` into `storage/img-customer/`).
- `routes/web.php` — `Route::middleware('is.customer')->group(...)` containing `customer.akun` (`GET /customer/akun/{id}`) and `customer.updateakun` (`PUT /customer/updateakun/{id}`).
- `resources/views/v_customer/edit.blade.php` — new account form (photo preview, nama, email, HP, alamat, kode pos, success/error alerts).
- `resources/views/v_layouts/app.blade.php` — "Akun Saya" in the account dropdown now points to `route('customer.akun', ['id' => Auth::user()->id])`.
- `public/image/img-default.jpg` — default avatar for customers without a photo.
- **Login fallback:** because `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` are empty in this environment, an email/password customer login was added — `loginForm()` / `loginProses()` in `CustomerController`, routes `customer.login` (`GET /customer/login`) and `customer.login.proses` (`POST /customer/login`), view `resources/views/v_customer/login.blade.php`. The header dropdown now has **Login** (email/password) and **Login Google** (`auth.redirect`).
- `.env` — `GOOGLE_REDIRECT_URL` aligned with `APP_URL` (`http://127.0.0.1:8000/auth/google/callback`) to avoid `redirect_uri_mismatch`.

**Known issues from the module fixed**

- `'email' => 'unique:customer'` → `unique:user` (the `customer` table has no `email` column).
- Account view guarded on `$edit->foto` (no such column) → `$edit->user->foto`.
- Module's default photo path `storage/img-user/img-default.jpg` lives inside the git-ignored `public/storage` → moved to `asset('image/img-default.jpg')` so it is committed.
- `user.role` is a MySQL `ENUM('0','1','2')`: assigning the integer `2` is read as the enum *index* and silently stores `'1'`, so the middleware rejects the user. Always assign the string `'2'` (e.g. `$user->role = '2';` / `SET role='2'`).
- Route name kept consistent between the form and the route: `customer.updateakun`.

**How to check**

1. Start MySQL, run `php artisan serve`, open http://127.0.0.1:8000/customer/login and sign in with `nailah22@gmail.com` / `customer123`.
   → Redirected to `/beranda`, the header now shows the account name instead of "Login / Daftar".
2. Header "Akun Saya" → `Akun Saya` → opens `/customer/akun/{id}` with the account form and the default avatar.
3. Change nama / HP / alamat / kode pos, optionally upload a photo (max 1024 KB), click **Simpan** → "Data berhasil diperbarui"; the new photo appears in `public/storage/img-customer/`.
4. Edit the URL to someone else's id (`/customer/akun/1`) → redirected back to your own id with "Anda tidak berhak mengakses akun ini."
5. Log out, then open `/customer/akun/4` or `/cart` → redirected to the Google login (`/auth/redirect`), because `is.customer` rejects guests.
6. Log in with an admin account (`kelompok3@gmail.com`) → the same pages reject it (role ≠ 2), admin login stays on `/backend/login`.

**Files to inspect**

| File | What changed |
| --- | --- |
| `app/Http/Middleware/IsCustomer.php` | new middleware (new file) |
| `app/Http/Kernel.php` | `is.customer` alias |
| `app/Http/Controllers/CustomerController.php` | `akun`, `updateAkun`, `loginForm`, `loginProses` |
| `routes/web.php` | `is.customer` route group + local login routes |
| `resources/views/v_customer/edit.blade.php` | account form (new file) |
| `resources/views/v_customer/login.blade.php` | email/password login (new file) |
| `resources/views/v_layouts/app.blade.php` | Akun Saya link, Login + Login Google menu |
| `public/image/img-default.jpg` | default avatar (new file) |

**Verified**

- `php -l` clean on the middleware, controller and routes; `php artisan view:cache` compiles all Blade templates.
- `php artisan route:list --path=cart -v` shows `App\Http\Middleware\IsCustomer` on every route in the group.
- HTTP: `GET /customer/login` → 200; `POST /customer/login` with the test account → 302 to `/beranda`; `GET /cart` with the customer session → 200; without a session → redirect to `/auth/redirect`.
- Account page renders the customer row (`customer` id 1) through `Customer::where('user_id', ...)`, and the photo fallback loads from `image/img-default.jpg`.

### Week 6 — Shopping Cart :white_check_mark:

Completed: 2026-10-27

**Goal:** let a logged-in customer add products to a cart (an `order` row with status `pending`) and see the cart with prices, quantities, weights and totals (module Minggu Ke-6).

**What was done**

- `database/migrations/2026_10_27_000000_create_order_table.php` — `order`: `customer_id` (FK → `customer.id`, cascade), nullable `user_id` (FK → `user.id`, cascade), `status`, `noresi`, `kurir`, `layanan_ongkir`, `biaya_ongkir`, `estimasi_ongkir`, `total_berat`, `total_harga`, `alamat`, `pos`, timestamps.
- `database/migrations/2026_10_27_000001_create_order_item_table.php` — `order_item`: `order_id` (FK → `order.id`, cascade), `produk_id` (FK → `produk.id`, cascade), `quantity`, `harga`, timestamps.
- `app/Models/Order.php` — `$table = 'order'`, `$fillable`, `orderItems()` hasMany, `customer()` belongsTo.
- `app/Models/OrderItem.php` — `$table = 'order_item'`, `$fillable`, `order()` belongsTo, `produk()` belongsTo.
- `app/Http/Controllers/OrderController.php` — `addToCart($id)`: `Customer::where('user_id', Auth::id())`, `firstOrCreate` the pending order, `firstOrCreate` the item, increment quantity when the item already exists, add the price to `total_harga`, then redirect to the cart with a success flash. `viewCart()`: loads the pending/paid order with `orderItems.produk`.
- `routes/web.php` — inside the `is.customer` group: `POST add-to-cart/{id}` (`order.addToCart`), `GET cart` (`order.cart`).
- `resources/views/v_order/cart.blade.php` — new cart table (thumb, nama, berat, stok, harga, quantity, subtotal), empty-state message, hidden `total_price` / `total_weight` for the shipping step in Week 7.
- `resources/views/v_layouts/app.blade.php` — the header "Lihat Keranjang" button now opens `route('order.cart')`.
- Pesan buttons wired to `route('order.addToCart', $row->id)` in `v_beranda/index.blade.php`, `v_produk/index.blade.php`, `v_produk/produkkategori.blade.php` and `v_produk/detail.blade.php`.

**Known issues from the module fixed**

- `Order::where('status', 'pending', 'paid')` (invalid third argument, silently drops the filter) → `whereIn('status', ['pending', 'paid'])`.
- `php artisan make:migration create_order_ item _table` typo → `create_order_item_table`.
- `OrderItem::kategori()` referenced a `kategori_id` that `order_item` does not have → removed.
- Added a guard when a role-2 user has no `customer` row yet → redirect to `/auth/redirect` with a message instead of a fatal error.
- Cart **Update** / **Hapus** forms are intentionally left as `action="#"` (the module introduces them in a later week); totals are computed in the view exactly like the module.

**How to check**

1. Log in (Week 5), then click **Pesan** on any product card on `/beranda` → redirected to `/cart`, one row with the product photo, "Berat", "Stok", price `Rp. 25.000`, quantity `1` and subtotal.
2. Click **Pesan** on the same product again → the same row, quantity `2`, subtotal doubles, `order.total_harga` increases.
3. Add a different product → a second row appears; the hidden `total_price` / `total_weight` inputs carry the cart totals.
4. Log out and open `/cart` → redirected to the Google login (guests cannot see a cart).
5. Database check: `SELECT id, customer_id, status, total_harga FROM db_tokoonline.order;` and `SELECT order_id, produk_id, quantity, harga FROM db_tokoonline.order_item;`.

**Files to inspect**

| File | What changed |
| --- | --- |
| `database/migrations/2026_10_27_000000_create_order_table.php` | new `order` table (new file) |
| `database/migrations/2026_10_27_000001_create_order_item_table.php` | new `order_item` table (new file) |
| `app/Models/Order.php`, `app/Models/OrderItem.php` | new models + relations (new files) |
| `app/Http/Controllers/OrderController.php` | new controller with `addToCart`, `viewCart` (new file) |
| `routes/web.php` | `order.addToCart`, `order.cart` inside the `is.customer` group |
| `resources/views/v_order/cart.blade.php` | cart table (new file) |
| `resources/views/v_beranda/index.blade.php`, `v_produk/*.blade.php` | Pesan forms point to `order.addToCart` |
| `resources/views/v_layouts/app.blade.php` | "Lihat Keranjang" links to `order.cart` |

**Verified**

- `php artisan migrate` created both tables; `php artisan tinker --execute="echo App\Models\Order::count()"` works, so the reserved table name `order` is quoted correctly.
- HTTP suite (server on 127.0.0.1:8000, customer session): `POST /add-to-cart/1` → 302 to `/cart`; `GET /cart` → 200 with the cart table; `GET /cart` without a session → 302 to `/auth/redirect`.
- Database after the test: `order` id 1 (`customer_id` 1, status `pending`, `total_harga` 25000) and `order_item` id 1 (`produk_id` 1, quantity 1, price 25000) — totals match the product price.

### Week 7 — RajaOngkir Shipping Cost API (standalone test page) :white_check_mark:

Completed: 2026-10-29

**Goal:** integrate RajaOngkir so a simple page can load destination areas and calculate shipping cost by origin/destination, weight and courier (module Minggu Ke-7).

**What was done**

- `config/services.php` — new `rajaongkir` block (`api_key`, `base_url`, `timeout`).
- `.env.example` — `RAJAONGKIR_API_KEY`, `RAJAONGKIR_BASE_URL=https://rajaongkir.komerce.id/api`, `RAJAONGKIR_TIMEOUT=15`.
- `app/Http/Controllers/RajaOngkirController.php` — new controller using **RajaOngkir V2**:
  - `getProvinces()` → `GET /v1/destination/province`
  - `getCities()` → `GET /v1/destination/city/{province_id}` with `province_id` validation
  - `getDistricts()` → `GET /v1/destination/district/{city_id}` with `city_id` validation
  - `getCost()` → `POST /v1/calculate/domestic-cost` with `origin`/`destination` **district IDs**, `weight` and `courier` as form data
  - V2 `meta` + `data` responses are normalized to a consistent `rajaongkir.status` / `rajaongkir.results` shape; missing key, connection failures, API errors and malformed responses return readable JSON errors.
- `routes/web.php` — `GET /cek-ongkir` (`ongkir.index`), `GET /provinces` (`ongkir.provinces`), `GET /cities` (`ongkir.cities`), `GET /districts` (`ongkir.districts`), `POST /cost` (`ongkir.cost`).
- `resources/views/ongkir.blade.php` — standalone test page (no storefront layout):
  - **Lokasi Asal (Toko)** and **Lokasi Tujuan**, each with cascading province → city/kabupaten → kecamatan dropdowns
  - weight in grams and courier select (JNE/TIKI/POS)
  - loading/error messages and a result table with courier, service, description, rupiah cost and estimated days
  - V2 returns ETD in English (`8 day`), so the view converts `day/days` to `hari` instead of printing `8 day hari`.
- `tests/Feature/RajaOngkirTest.php` — HTTP tests with `Http::fake()` for the page, location endpoints, cost payload, validation, missing key and API error.

**Known issues from the module fixed**

- **The module's old Starter endpoint is retired.** `https://api.rajaongkir.com/starter` no longer responds (timeout in 2026); current Komerce/RajaOngkir keys use V2 at `https://rajaongkir.komerce.id/api/v1`. The implementation was migrated to V2 so a newly registered key actually works.
- **V2 cost needs district (kecamatan) IDs, not city IDs.** The page therefore adds a district dropdown for both origin and destination; this is the smallest number of API calls with the current V2 hierarchy.
- The API key is no longer written in a route/controller. It is read from `.env` through `config('services.rajaongkir.*')`, so `php artisan config:cache` keeps working.
- Added validation for `province_id`, `city_id`, `origin`, `destination`, `weight` (integer ≥ 1) and `courier` (only `jne`, `tiki`, `pos`); invalid input returns 422 instead of being forwarded to RajaOngkir.
- `Http::post()` sent JSON while the API expects form parameters → `asForm()->post()`.
- Missing API key, connection failure, API error and non-RajaOngkir responses now return a readable message instead of a 500 error.
- The base URL is normalized (no double slash) and the page reports errors in the UI instead of only logging them to the browser console.
- V2 sends the estimated delivery time in English (`8 day`); the result table now shows `8 hari` instead of the duplicated `8 day hari`.

**How to check**

1. Register at https://collaborator.komerce.id/registration, copy the API key, then set `RAJAONGKIR_API_KEY=...` in `.env` (keep `RAJAONGKIR_BASE_URL=https://rajaongkir.komerce.id/api`).
2. Run `php artisan config:clear`, start MySQL (not needed for this page, but keeps the rest of the app working), then `php artisan serve`.
3. Open http://127.0.0.1:8000/cek-ongkir → the page shows "Cek Ongkir" and both origin/destination province dropdowns are filled from RajaOngkir.
4. Choose an origin province → city → kecamatan (the store location), then a destination province → city → kecamatan. Enter e.g. `1000` grams, choose `JNE`, click **Cek Ongkir**.
5. → A result table appears with the courier, service (e.g. `REG`), description, rupiah cost and estimated days. Changing a province resets its city/district because IDs are hierarchical.
6. Remove/empty `RAJAONGKIR_API_KEY` and reload → a yellow warning is shown and the endpoints return `503` with "RAJAONGKIR_API_KEY belum diatur...". With an invalid key, the RajaOngkir error description (e.g. "Invalid Api key, key not found") is shown in the page.
7. Regression check → http://127.0.0.1:8000/beranda and `/cart` behave as before; `php artisan route:list --except-vendor` lists the five `ongkir.*` routes.

**Files to inspect**

| File | What changed |
| --- | --- |
| `config/services.php` | new `rajaongkir` config block |
| `.env.example` | RajaOngkir V2 environment variables |
| `app/Http/Controllers/RajaOngkirController.php` | new controller (provinces, cities, districts, cost, normalization, error handling) |
| `routes/web.php` | `/cek-ongkir`, `/provinces`, `/cities`, `/districts`, `/cost` routes |
| `resources/views/ongkir.blade.php` | standalone shipping-cost test page (new file) |
| `tests/Feature/RajaOngkirTest.php` | feature tests with a fake RajaOngkir V2 API (new file) |

**Verified**

- `php -l` clean on the controller, routes and test file; `php artisan route:list --except-vendor` shows `ongkir.index`, `ongkir.provinces`, `ongkir.cities`, `ongkir.districts`, `ongkir.cost`.
- `php artisan test --filter=RajaOngkirTest`: **7 passed, 39 assertions**; the page test is reported as "deprecated" only because Laravel 10 triggers PHP 8.4 implicit-nullable notices from the framework, not from this feature.
- Full suite after fixing the stale Laravel scaffold test (`/` now asserts the documented redirect to `/beranda`): **9 passed, 42 assertions** (2026-10-29).
- HTTP check on a local server: `GET /cek-ongkir` → 200 and contains "Lokasi Asal (Toko)" + "Lokasi Tujuan"; `GET /provinces` and `/cities` → 503 JSON while `RAJAONGKIR_API_KEY` is empty (expected).
- Browser check (Edge/Playwright) against a local RajaOngkir V2 mock: origin DIWEK → destination GAMBIR, 1000 g, JNE → REG `Rp 18.000` (2-3 hari) and YES `Rp 32.000` (1-1 hari); the success message appears and the browser console has no errors.
- **Live test with the configured Komerce/RajaOngkir key (2026-10-29):** `/provinces` → 34 provinces; `/cities?province_id=1` → 9 cities; `/districts?city_id=1` → 6 districts; `POST /cost` from MATARAM (district 1) to NUSANIWE (district 117), 1000 g, JNE → JTR `Rp 165.000` (30 hari) and REG `Rp 20.000` (8 hari). The full page flow (province → city → district for both ends → Cek Ongkir) was run in Edge/Playwright; results rendered and the browser console had no errors.
- The module's old host `https://api.rajaongkir.com/starter/province` times out, which is why the implementation targets V2.

### Week 10 — Order Management (backend) and Invoices :white_check_mark:

Completed: 2026-10-31

**Goal:** let admins process incoming orders from the backend (paid → shipped → completed), open order details, update status/resi, print invoices, and provide order reports plus customer-facing invoices (module Minggu Ke-10).

**What was done**

- `app/Http/Controllers/OrderController.php` — new Week 10 methods:
  - `statusProses()` — `Paid`, legacy `proses` and `Kirim` orders, newest first, with `customer.user`
  - `statusSelesai()` — `Selesai` orders
  - `statusDetail($id)` — order + customer + items + product + category
  - `statusUpdate()` — validates and saves `status`, `noresi`, `alamat`, `pos`
  - `invoiceBackend($id)` / `invoiceFrontend($id)` — admin and customer invoices; customer invoices are scoped to the logged-in customer
  - `formOrderProses()` / `cetakOrderProses()` and `formOrderSelesai()` / `cetakOrderSelesai()` — date-ranged reports; the completed report also totals revenue
- `routes/web.php` — `backend/pesanan/{proses,selesai,detail/{id},update/{id},invoice/{id}}` (`pesanan.*`, `auth`), four `backend.laporan.*` report routes (`auth`), and customer `GET /invoice/{id}` (`order.invoice`, `is.customer`).
- `resources/views/backend/v_pesanan/` — `proses`, `selesai`, `detail`, `invoice`, `formproses`, `formselesai`, `cetakproses`, `cetakselesai` (all new).
- `resources/views/invoice/print.blade.php` — shared printable invoice layout; `resources/views/v_order/invoice.blade.php` — storefront wrapper. Both show `INV-000xx`, customer/shipping data, the item table and `TOTAL BAYAR`.
- `resources/views/backend/v_layouts/app.blade.php` — sidebar "Pesanan" (Pesanan Proses / Pesanan Selesai) and two "Laporan → Pesanan" entries.
- `resources/views/v_order/history.blade.php` — status badge/label, formatted date, total (products + shipping) and an invoice link per order; the header's "History" menu now opens `order.history`.
- `resources/views/v_order/select_shipping.blade.php` — shipping AJAX calls use the renamed `order.provinces` / `order.cities` / `order.cost` routes.
- `app/Models/Order.php` — `status_label`, `status_badge`, `total_bayar` accessors; `tanggal` added to `$fillable`.
- Week 9 checkout (`checkoutProses`) now sets `status = 'Paid'` and `tanggal = now()`, so new orders enter Week 10's process list instead of staying `pending`.
- `tests/Feature/PesananTest.php` — 14 feature tests (guest protection, lists, detail, update/resi validation, invoices/ownership, reports/date filtering, checkout status).
- `phpunit.xml` — tests run on SQLite in-memory, so `php artisan test` does not touch the MySQL demo database.

**Known issues from the module fixed**

- Duplicate `'judul'` keys in the `statusSelesai()` / `statusDetail()` view arrays → one clean array per method.
- The process report ignored the chosen dates → both reports filter `created_at` between start/end of day, require both dates, and reject an end date before the start date.
- `invoiceFrontend()` rendered the backend view and had no ownership check → separate customer invoice view; another customer's order (or an unknown id) returns **404**.
- Order views used `$order->customer->nama/email/hp`, but `customer` only stores `user_id`, `alamat`, `pos` and Google fields → views now read `$order->customer->user->nama/email/hp`.
- The detail view printed product stock as "Stok (gram)" → separate **Berat** and **Stok** values with correct units.
- Status update accepted any string → `status` is limited to `Paid`, `Kirim`, `Selesai`; `noresi` is required when the status is `Kirim`.
- Week 9's customer shipping endpoints reused `/provinces`, `/cities`, `/cost` and shadowed the Week 7 RajaOngkir page → moved to `/customer/provinces`, `/customer/cities`, `/customer/cost` (`order.*` names), so the `ongkir.*` routes work again.

**How to check**

1. Start MySQL, run `php artisan serve`, and log in at http://127.0.0.1:8000/backend/login (`kelompok3@gmail.com` / `kelompok123@`).
2. Create an order as a customer first (or use existing data): log in at `/customer/login`, add a product, complete shipping/payment → checkout stores it as `Paid`.
3. Backend sidebar → **Pesanan → Pesanan Proses** → the new order appears with customer, date, total and status badge (label "Diproses"). `pending` and `Selesai` orders are not listed.
4. Click **Detail** → customer name/email/HP, shipping info, item table, subtotal, ongkir and **TOTAL BAYAR**. Change the status to **Kirim** without a resi → validation error; fill a resi and click **Simpan** → redirected back with "Data pesanan berhasil diperbarui".
5. Click **Cetak Invoice** (or the invoice action in the list) → printable `INV-000xx` invoice with customer, address, items, ongkir and total.
6. **Pesanan → Pesanan Selesai** → orders with status `Selesai`; set a shipped order to **Selesai** from its detail page to move it here.
7. **Laporan → Pesanan Proses / Pesanan Selesai** → choose a date range covering the orders and print → report table plus total order value / total revenue; an end date before the start date shows a validation message.
8. As the customer, open `/history` → order list with status, date and total; **Invoice** opens their own invoice. Opening another customer's order id → **404**.
9. Log out and open `/backend/pesanan/proses` → redirected to `/backend/login`.

**Files to inspect**

| File | What changed |
| --- | --- |
| `app/Http/Controllers/OrderController.php` | Week 10 list/detail/update/invoice/report methods; checkout sets `Paid` |
| `routes/web.php` | 9 `pesanan.*` / `backend.laporan.*` routes + customer `order.invoice`; shipping endpoints moved under `/customer/*` |
| `app/Models/Order.php` | status label/badge, `total_bayar`, `tanggal` fillable |
| `resources/views/backend/v_pesanan/*` | process/completed lists, detail + status form, invoices, report forms/prints (8 new files) |
| `resources/views/invoice/print.blade.php`, `resources/views/v_order/invoice.blade.php` | shared print layout + customer wrapper (new files) |
| `resources/views/backend/v_layouts/app.blade.php` | Pesanan + Laporan Pesanan sidebar menus |
| `resources/views/v_order/history.blade.php` | status/date/total display, invoice link |
| `resources/views/v_order/select_shipping.blade.php` | shipping AJAX uses the renamed `order.*` endpoint names |
| `resources/views/v_layouts/app.blade.php` | "History" menu links to `order.history` (was `#`) |
| `tests/Feature/PesananTest.php` | 14 Week 10 feature tests (new file) |
| `phpunit.xml` | SQLite in-memory test database |

**Verified**

- `php artisan test`: **24 passed (98 assertions)**, including the 14 new `PesananTest` cases.
- `php artisan route:list --path=pesanan --except-vendor`: 9 routes (5 `pesanan.*`, 4 `backend.laporan.*`); `order.invoice` is registered inside `is.customer`.
- `php artisan view:cache`: all Blade templates compile.
- HTTP run with seeded demo data: admin login, `/backend/pesanan/proses`, `/selesai`, `/detail/1`, `/invoice/1`, both report forms and prints → **200**; customer `/history` and their own `/invoice/{id}` → **200**; `/invoice/999` → **404**; referenced assets → **200**.

## Local end-to-end run — Weeks 1–7 (2026-10-29)

The project was run from a clean local state and all seven weeks were exercised in Edge/Playwright.

**Environment restored**

- MySQL 8.4 (Laragon) started; `php artisan serve` on `127.0.0.1:8000`.
- `php artisan migrate` applied the two pending Week 6 migrations (`order`, `order_item`).
- `php artisan storage:link` + 52 seed photos copied to `storage/app/public/img-produk/` (product photos were 404 before).
- Demo customer recreated because the local `customer` table was empty: `nailah22@gmail.com` / `customer123` (`user` id 8, `customer` id 4). The cart is left with a demo state: 2 × Kue Lapis Tradisional + 1 × Onde-Onde Spesial = Rp 70.000.

**Fixes made during the run**

- `public/frontend/css/slick-theme.css` — slick font URLs pointed to `css/fonts/` but the fonts live in `frontend/fonts/`; changed to `../fonts/`. Removed the missing `ajax-loader.gif` reference (the loader now uses a plain white background). This clears the three local 404s.
- `resources/views/v_layouts/app.blade.php` — added a global flash-message block (`success`, `msgError`, `error`) so login success and middleware errors are visible; removed the now-duplicate local blocks from `v_customer/login.blade.php`, `v_order/cart.blade.php` and `v_customer/edit.blade.php`.
- `resources/views/v_layouts/app.blade.php` — header cart badge/total now reflect the logged-in customer's pending order (`$cartQty`, `$cartTotal`) instead of the hardcoded `0` / `Rp. 0`.

**Verified (Edge/Playwright + HTTP)**

| Week | Check | Result |
| --- | --- | --- |
| 1 | `/` → `/beranda`, title, 3 banners, 5 categories, local images | 200, no broken images; all local assets 200 after the slick fix |
| 2 | home 6 cards; `/produk/all` page 1 = 6, page 2 = 4; category filters; detail; blocked product | all 200; `/produk/detail/8` = 404; 3-image gallery on product 1 |
| 3/5 | email/password login, account page, unauthorized account id | login redirects to `/beranda`, header shows "NAILAH"; account form loads; `/customer/akun/1` redirects to own id with error |
| 6 | add same product twice + another product | 2 rows, quantities 2 + 1, cart total Rp 70.000, hidden total 70000 / weight 700; header badge 3 / Rp 70.000 |
| 7 | live RajaOngkir flow (Mataram → Nusaniwe, 1000 g, JNE) | JTR Rp 165.000 / 30 hari, REG Rp 20.000 / 8 hari; no console errors |
| backend | admin login | `/backend/beranda` shows "Selamat Datang, Mahasiswa BSI" |

- `php artisan test`: **9 passed, 42 assertions** (1 framework PHP 8.4 deprecation notice).
- Google OAuth could not be re-tested end-to-end in this run because `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` are empty in the current `.env`; `/auth/redirect` still returns 302 to Google, and the email/password fallback is the working login path.
- Current catalogue state (user-added products): 10 published products; page 1 = 6, page 2 = 4 (Wedang Jahe, Cireng, Pisang Goreng, Kue Mochi); blocked product 8 = Kerupuk Melinjo.

Next in the module: **Minggu Ke-11 — Grafik Pesanan (dashboard admin)**. Week 11 builds the order chart on the admin dashboard from the `order` / `order_item` data introduced in Weeks 6–10. Week 9 remains partially done: when finishing it, use the V2 district IDs / `calculate/domestic-cost` endpoint above instead of the module's old city-based Starter calls. There is no Week 8.
