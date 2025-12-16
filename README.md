# Grāmatas un Diskusijas Sistēma

**Kvalifikācijas darba projekts**
Izstrādātājs: *Marks Gerhards Brūveris*
Izglītības iestāde: *Rīgas Valsts tehnikums, Datorikas nodaļa*
Programma: *Programmēšana*
Gads: *2025*

---

## Projekta apraksts

**Grāmatas un Diskusijas Sistēma** ir tīmekļa lietojumprogramma, kas apvieno grāmatu pārvaldību un diskusiju forumu vienotā platformā. Tās mērķis ir nodrošināt lietotājiem ērtu vidi, kur sekot savam lasīšanas progresam, apspriest grāmatas, dalīties viedokļos un sekot citiem lasītājiem.

Platforma paredzēta plašai mērķauditorijai — gan aktīviem lasītājiem, gan lietotājiem, kuri vēlas atklāt jaunas grāmatas vai piedalīties diskusijās latviešu un angļu valodā.

---

## Galvenās funkcijas

* **Lietotāju konti** – reģistrācija, autorizācija ar Laravel Sanctum, profila pārvaldība
* **Grāmatu pārvaldība** – meklēšana pēc nosaukuma, autora vai ISBN, importēšana pēc ISBN
* **Lasīšanas progress** – grāmatu katalogs ar kategoriju filtriem
* **Diskusijas un komentāri** – diskusiju izveide par grāmatām, komentēšana
* **Sociālās funkcijas** – citu lietotāju sekošana un aktivitāšu pārraudzība
* **Daudzvalodu atbalsts** – pilnīga saskarnes tulkošana latviešu un angļu valodā (Vue I18n)
* **Ārējais datu avots** – Google Books API integrācija grāmatu datiem (autors, vāks, lappušu skaits, apraksts, izdevējs)

---

## Tehnoloģijas

| Slānis    | Tehnoloģija                                                                      |
| --------- | -------------------------------------------------------------------------------- |
| Frontend  | Vue.js 3, Vite, Tailwind CSS, Pinia (State Management), Vue Router, Vue I18n    |
| Backend   | PHP (Laravel Framework 12.36.1), Laravel Sanctum (API Authentication)           |
| Datu bāze | SQLite (development), MySQL (production)                                         |
| API       | Google Books API                                                                 |
| Papildus  | REST API, Axios (HTTP client), localStorage (token & locale persistence)        |

---

## Arhitektūras uzstādīšana

### Priekšnosacījumi

Pirms sākt, pārliecinieties, ka jūsu sistēmā ir instalēts:

- **PHP 8.1+** ar paplašinājumiem: `mbstring`, `xml`, `ctype`, `json`, `tokenizer`, `openssl`, `pdo`, `mysql`
- **Composer** (PHP pakotņu menedžeris)
- **Node.js 18+** un npm/yarn
- **MySQL 8.0+** vai MariaDB 10.3+
- **Git**

### 1. Projekta klonēšana un bāzes uzstādīšana

```bash
# Klonē repozitoriju
git clone https://github.com/22DP3MBruv/librorum.git
cd librorum

# Instalē Laravel (PHP) atkarības
composer install

# Instalē JavaScript atkarības
npm install
```

### 2. Vue.js frontend uzstādīšana

```bash
# Instalē Vue 3 un saistītās paketes
npm install vue@^3.3.0 @vitejs/plugin-vue
npm install vue-router@^4.2.0 pinia@^2.1.0
npm install vue-i18n@9  # Daudzvalodu atbalsts

# Instalē development tools
npm install --save-dev @vue/compiler-sfc vite
```

### 3. Vite konfigurācija Vue.js darbam

Izveidojiet vai atjauniniet `vite.config.js`:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});
```

### 4. Laravel konfigurācija

```bash
# Izveido .env failu no parauga
cp .env.example .env

# Ģenerē aplikācijas atslēgu
php artisan key:generate

# Konfigurē .env failu ar datubāzes iestatījumiem
```

Rediģējiet `.env` failu:

```env
APP_NAME="Grāmatas un Diskusijas Sistēma"
APP_ENV=local
APP_KEY=base64:... # (php artisan key:generate to izveidos)
APP_DEBUG=true
APP_URL=http://localhost:8000

# Development: SQLite
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# Production: MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=librorum
# DB_USERNAME=jūsu_lietotājvārds
# DB_PASSWORD=jūsu_parole

# Google Books API Key (optional - system works without it)
GOOGLE_BOOKS_API_KEY=
```

### 5. Datubāzes uzstādīšana ar migrācijām

```bash
# SQLite (development) - izveidot datubāzes failu
touch database/database.sqlite

# Vai MySQL (production)
mysql -u root -p
CREATE DATABASE librorum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Palaiž migrācijas
php artisan migrate

# Palaiž seeder (sākotnējie test dati)
php artisan db:seed

# Vai abi uzreiz (fresh start):
php artisan migrate:fresh --seed
```

### 6. Vue.js komponenšu struktūras izveide

Izveidojiet šādas direktorijas un failus:

```
resources/
├── js/
│   ├── app.js              # Galvenais Vue entry point
│   ├── App.vue             # Root komponente ar navigāciju un auth modāliem
│   ├── bootstrap.js        # Axios konfigurācija
│   ├── i18n.js             # Vue I18n konfigurācija (lv/en)
│   ├── components/         # Vue komponentes (future)
│   ├── pages/              # Vue lapas
│   │   ├── Home.vue        # Sākumlapa ar features
│   │   ├── Books.vue       # Grāmatu katalogs ar meklēšanu un import
│   │   ├── Profile.vue     # Lietotāja profils
│   │   ├── BookDiscussions.vue
│   │   └── DiscussionDetail.vue
│   ├── stores/             # Pinia state management
│   │   ├── auth.js         # Autentifikācija (Sanctum token)
│   │   ├── books.js        # Grāmatu CRUD ar Google Books API
│   │   └── discussions.js  # Diskusiju pārvaldība
│   ├── locales/            # I18n tulkojumi
│   │   ├── en.js           # English translations
│   │   └── lv.js           # Latvian translations
│   └── router/             # Vue Router
│       └── index.js        # Route definīcijas
└── views/
    └── app.blade.php       # Laravel layout ar Vue mount point
```

### 7. Diskusiju URL struktūra

Sistēma izmanto strukturētu diskusiju organizāciju:

- **Grāmatu diskusijas**: `/books/<ISBN>/discussions` un `/books/<ISBN>/discussion/<ID>`
- **Vispārīgas diskusijas**: `/discussions` un `/discussions/<ID>`

### 8. Izstrādes serveru palaišana

Atveriet divus termināļus:

**1. terminals - Laravel backend:**
```bash
php artisan serve
# Pieejams uz http://localhost:8000
```

**2. terminals - Vite dev server (hot reload):**
```bash
npm run dev
# Automātiski pārkompilē Vue komponentes
```

### 9. Production build

```bash
# Veido optimizētus failus produkcijai
npm run build

# Laravel optimizācijas
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 10. Datubāzes migrāciju pārvaldība

```bash
# Jauna migrācija
php artisan make:migration create_books_table
php artisan make:migration create_discussions_table
php artisan make:migration create_user_book_progress_table

# Migrāciju palaišana
php artisan migrate

# Migrāciju atsaukšana (pēdējā batch)
php artisan migrate:rollback

# Visa migrāciju atsaukšana
php artisan migrate:reset

# Svaigs starts (reset + migrate + seed)
php artisan migrate:fresh --seed
```

### 11. Noderīgas Laravel komandas

```bash
# Jauna kontroliera izveide
php artisan make:controller BookController --resource

# Jauna modeļa izveide
php artisan make:model Book -m  # ar migrāciju

# Jauna seeder izveide
php artisan make:seeder BooksSeeder

# API resursu kontrolieris
php artisan make:controller Api/BookController --api
```

### 12. Izstrādes workflow un testēšana

**Ikdienas izstrādes process:**

```bash
# 1. Palaidiet Laravel serveri
php artisan serve

# 2. Palaidiet Vite dev server (otrā terminālī)
npm run dev

# 3. Pēc Vue komponenšu izmaiņām - hot reload automātiski
# 4. Pēc backend izmaiņām - pārlādējiet pārlūku
```

**Testēšanas komandas:**

```bash
# Laravel unit un feature testi
php artisan test

# JavaScript/Vue testi (ja konfigurēti)
npm run test

# Koda kvalitātes pārbaude
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse

# JavaScript linting
npm install --save-dev eslint @vue/eslint-config-recommended
npm run lint
```

**Datubāzes testdati:**

```bash
# Izveidot factory (test datu ģenerātoram)
php artisan make:factory BookFactory --model=Book

# Izveidot seeder ar test datiem
php artisan make:seeder TestBooksSeeder

# Palaidīt tikai specifisko seeder
php artisan db:seed --class=TestBooksSeeder
```

---

## Izstrādes padomi

### Vue.js + Laravel integrācija

1. **API komunikācija:** Axios izmanto Bearer token autentifikācijai
2. **Autentifikācija:** Laravel Sanctum SPA autentifikācija ar localStorage token
3. **State management:** Pinia glabā lietotāja stāvokli, grāmatu datus un diskusijas
4. **Valodu pārslēgšana:** Vue I18n ar localStorage persistence (LV/EN)
5. **Google Books API:** ExternalBookApiService apstrādā ISBN importu un grāmatu meklēšanu

### Datubāzes struktūra

**Galvenās tabulas:**
- `users` - lietotāji ar role (admin/user)
- `books` - grāmatas ar pilnīgu metadata (ISBN10/13, cover_image_url, page_count, description, language, publisher, subjects, authors, external_ids)
- `reading_progress` - lietotāju lasīšanas progress
- `threads` - diskusiju tēmas (saistītas ar grāmatām)
- `comments` - komentāri diskusijās
- `likes` - patīk atzīmes
- `following` - sekotāju saites

### API Endpoints

**Auth:**
- `POST /api/register` - reģistrācija
- `POST /api/login` - pieslēgšanās
- `GET /api/user` - autentificētais lietotājs
- `POST /api/logout` - iziet

**Books:**
- `GET /api/books` - visu grāmatu saraksts
- `GET /api/books/{id}` - viena grāmata
- `POST /api/books` - jauna grāmata
- `PUT /api/books/{id}` - atjaunot grāmatu
- `DELETE /api/books/{id}` - dzēst grāmatu
- `POST /api/books/import-isbn` - importēt no Google Books
- `GET /api/books/external-search` - meklēt ārējos avotos

### Datubāzes migrācijas prakses

1. **Nosaukumu konvencijas:** `create_table_name_table`, `add_column_to_table_name`
2. **Rollback drošība:** Vienmēr definējiet `down()` metodi
3. **Foreign keys:** Izmantojiet `constrained()` automātiskai foreign key izveidei
4. **Indexes:** Pievienojiet indeksus biežām meklēšanas kolonnām

---

## Sistēmas arhitektūra

Sistēma sastāv no četrām galvenajām apakšsistēmām:

1. **Lietotāju datu apstrāde** – reģistrācija, autentifikācija, sociālās funkcijas
2. **Grāmatu datu apstrāde** – katalogs, meklēšana, ISBN API integrācija
3. **Lasīšanas progresa uzskaite** – statusi, statistika, vizualizācijas
4. **Diskusiju un komentāru apstrāde** – tēmu un komentāru pārvaldība

---

## Nefunkcionālās prasības

* Saskarne latviešu un angļu valodā
* Responsīvs dizains dažādiem ekrāniem
* Saskarne pielāgota populārākajiem pārlūkiem (Chrome, Firefox, Safari, Edge)
* Droša autentifikācija un datu aizsardzība
* Ātra darbība (< 2s atbildes laiks)
* Atbalsts līdz 10 000 lietotāju un 100 000 grāmatu ierakstu

---

## Licence

Šis projekts ir izstrādāts **izglītības nolūkos** Rīgas Valsts tehnikuma kvalifikācijas darba ietvaros.
Autortiesības © 2025 *Marks Gerhards Brūveris*.
