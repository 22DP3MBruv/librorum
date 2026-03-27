# Grāmatas un Diskusijas Sistēma

**Kvalifikācijas darba projekts**
Izstrādātājs: *Marks Gerhards Brūveris*
Izglītības iestāde: *Rīgas Valsts tehnikums, Datorikas nodaļa*
Programma: *Programmēšana*
Gads: *2026*

---

## Projekta apraksts

**Grāmatas un Diskusijas Sistēma** ir tīmekļa lietojumprogramma, kas apvieno grāmatu pārvaldību un diskusiju forumu vienotā platformā. Tās mērķis ir nodrošināt lietotājiem ērtu vidi, kur sekot savam lasīšanas progresam, apspriest grāmatas, dalīties viedokļos un sekot citiem lasītājiem.

Platforma paredzēta plašai mērķauditorijai — gan aktīviem lasītājiem, gan lietotājiem, kuri vēlas atklāt jaunas grāmatas vai piedalīties diskusijās latviešu un angļu valodā.

---

## Galvenās funkcijas

* **Lietotāju konti** – reģistrācija, autorizācija ar Laravel Sanctum, profila pārvaldība
* **Privātuma iestatījumi** – privāts/publisks profils, sekošanas pieteikumu sistēma
* **Konta pārvaldība** – lietotājvārda un paroles maiņa, satura un konta dzēšana
* **Grāmatu pārvaldība** – meklēšana pēc nosaukuma, autora vai ISBN, importēšana pēc ISBN vai žanra
* **Lasīšanas progress** – grāmatu katalogs ar statusu un statistikas izsekošanu
* **Diskusijas un komentāri** – diskusiju izveide par grāmatām, komentēšana, patīk atzīmes
* **Sociālās funkcijas** – citu lietotāju sekošana, sekošanas pieteikumu pārvaldība
* **Paziņojumu sistēma** – reāllaika paziņojumi par sociālām aktivitātēm
* **Administrēšana** – lietotāju pārvaldība, satura moderācija, statistika
* **Daudzvalodu atbalsts** – pilnīga saskarnes tulkošana latviešu un angļu valodā (Vue I18n)
* **Ārējais datu avots** – Google Books API integrācija grāmatu datiem (autors, vāks, lappušu skaits, apraksts, izdevējs)

---

## Tehnoloģijas

| Slānis    | Tehnoloģija                                                                      |
| --------- | -------------------------------------------------------------------------------- |
| Frontend  | Vue.js 3, Vite, Tailwind CSS, Pinia (State Management), Vue Router, Vue I18n    |
| Backend   | PHP (Laravel Framework 12), Laravel Sanctum (API Authentication)                |
| Datu bāze | MySQL 8.0+                                                                       |
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
APP_ENV=production
APP_KEY=base64:... # (php artisan key:generate to izveidos)
APP_DEBUG=false
APP_URL=https://jūsu_domēns.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=librorum
DB_USERNAME=jūsu_lietotājvārds
DB_PASSWORD=jūsu_parole

# Google Books API Key
GOOGLE_BOOKS_API_KEY=jūsu_api_atslēga
```

### 5. Datubāzes uzstādīšana ar migrācijām

```bash
# Izveido MySQL datubāzi
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
5. **Google Books API:** ExternalBookApiService apstrādā ISBN un žanra importu

### Datubāzes struktūra

**Galvenās tabulas:**
- `users` - lietotāji ar lomu (admin/user), privātuma iestatījumiem un moderācijas laukiem
- `books` - grāmatas ar pilnīgu metadata (ISBN10/13, cover_image_url, page_count, description, language, publisher, subjects, authors, external_ids)
- `reading_progress` - lietotāju lasīšanas progress un statusi
- `threads` - diskusiju tēmas (saistītas ar grāmatām)
- `comments` - komentāri diskusijās
- `likes` - patīk atzīmes pavedieniem un komentāriem
- `following` - sekotāju saites starp lietotājiem
- `follow_requests` - sekošanas pieteikumi privātiem profiliem
- `notifications` - paziņojumi par sociālām aktivitātēm

### API Endpoints

**Auth:**
- `POST /api/register` - reģistrācija
- `POST /api/login` - pieslēgšanās
- `GET /api/user` - autentificētais lietotājs *(aizsargāts)*
- `POST /api/logout` - iziet *(aizsargāts)*

**Books:**
- `GET /api/books` - visu grāmatu saraksts
- `GET /api/books/search` - grāmatu meklēšana
- `GET /api/books/external-search` - meklēt Google Books API
- `GET /api/books/popular` - populārākās grāmatas
- `GET /api/books/{identifier}` - viena grāmata (pēc ID vai ISBN)
- `POST /api/books` - jauna grāmata *(tikai admins)*
- `POST /api/books/import-isbn` - importēt pēc ISBN *(tikai admins)*
- `POST /api/books/import-by-genre` - masveidā importēt pēc žanra *(tikai admins)*
- `PUT /api/books/{id}` - atjaunot grāmatu *(tikai admins)*
- `POST /api/books/{id}/sync` - sinhronizēt ar Google Books *(tikai admins)*
- `DELETE /api/books/{id}` - dzēst grāmatu *(tikai admins)*

**Reading Progress:** *(aizsargāts)*
- `GET /api/reading-progress` - lietotāja lasīšanas progress
- `POST /api/reading-progress` - pievienot progresu
- `GET /api/reading-progress/{id}` - viens ieraksts
- `PUT /api/reading-progress/{id}` - atjaunot progresu
- `DELETE /api/reading-progress/{id}` - dzēst ierakstu
- `GET /api/reading-progress/book/{bookId}` - progress konkrētai grāmatai

**Threads & Comments:**
- `GET /api/threads` - visu diskusiju saraksts *(publisks)*
- `GET /api/threads/{id}` - viena diskusija *(publisks)*
- `GET /api/books/{bookId}/threads` - grāmatas diskusijas *(publisks)*
- `POST /api/threads` - jauna diskusija *(aizsargāts)*
- `PUT /api/threads/{id}` - atjaunot diskusiju *(aizsargāts)*
- `DELETE /api/threads/{id}` - dzēst diskusiju *(aizsargāts)*
- `GET /api/threads/{threadId}/comments` - komentāri *(publisks)*
- `POST /api/threads/{threadId}/comments` - jauns komentārs *(aizsargāts)*
- `PUT /api/threads/{threadId}/comments/{id}` - atjaunot komentāru *(aizsargāts)*
- `DELETE /api/threads/{threadId}/comments/{id}` - dzēst komentāru *(aizsargāts)*

**Likes:** *(aizsargāts)*
- `POST /api/likes/toggle` - pārslēgt patīk atzīmi
- `GET /api/likes/status` - pārbaudīt patīk statusu

**Sociālās funkcijas:** *(aizsargāts)*
- `GET /api/user/profile/{userId}` - skatīt profilu
- `GET /api/user/followers` - sekotāji
- `GET /api/user/following` - sekojamie
- `POST /api/user/follow/{userId}` - sekot lietotājam
- `DELETE /api/user/unfollow/{userId}` - pārtraukt sekot
- `GET /api/user/follow-requests` - saņemtie sekošanas pieteikumi
- `POST /api/user/follow-requests/{requestId}/accept` - apstiprināt pieteikumu
- `POST /api/user/follow-requests/{requestId}/reject` - noraidīt pieteikumu
- `DELETE /api/user/follow-request/{userId}/cancel` - atcelt nosūtītu pieteikumu

**Privātums un Konta iestatījumi:** *(aizsargāts)*
- `GET /api/user/privacy` - privātuma iestatījumi
- `PUT /api/user/privacy` - atjaunot privātuma iestatījumus
- `PUT /api/user/account/username` - mainīt lietotājvārdu
- `PUT /api/user/account/password` - mainīt paroli
- `DELETE /api/user/account/content` - dzēst savu saturu
- `DELETE /api/user/account` - dzēst kontu

**Paziņojumi:** *(aizsargāts)*
- `GET /api/notifications` - paziņojumu saraksts
- `GET /api/notifications/unread-count` - nelasīto skaits
- `POST /api/notifications/mark-all-read` - atzīmēt visus kā lasītus
- `POST /api/notifications/{id}/mark-read` - atzīmēt kā lasītu
- `POST /api/notifications/{id}/mark-unread` - atzīmēt kā nelasītu
- `DELETE /api/notifications/{id}` - dzēst paziņojumu
- `DELETE /api/notifications/read/all` - dzēst visus lasītos
- `GET /api/notifications/settings` - paziņojumu iestatījumi

**Administrēšana:** *(tikai admins)*
- `GET /api/admin/statistics` - sistēmas statistika
- `GET /api/admin/users` - visu lietotāju saraksts
- `POST /api/admin/users/{userId}/make-admin` - piešķirt admina lomu
- `POST /api/admin/users/{userId}/remove-admin` - noņemt admina lomu
- `DELETE /api/admin/threads/{threadId}` - dzēst diskusiju
- `DELETE /api/admin/comments/{commentId}` - dzēst komentāru
- `POST /api/moderation/flag-user/{userId}` - atzīmēt lietotāju
- `POST /api/moderation/unflag-user/{userId}` - noņemt atzīmi
- `GET /api/moderation/flagged-users` - atzīmēto lietotāju saraksts

### Datubāzes migrācijas prakses

1. **Nosaukumu konvencijas:** `create_table_name_table`, `add_column_to_table_name`
2. **Rollback drošība:** Vienmēr definējiet `down()` metodi
3. **Foreign keys:** Izmantojiet `constrained()` automātiskai foreign key izveidei
4. **Indexes:** Pievienojiet indeksus biežām meklēšanas kolonnām

---

## Sistēmas arhitektūra

Sistēma sastāv no četrām galvenajām apakšsistēmām:

1. **Lietotāju datu apstrāde** – reģistrācija, autentifikācija, privātuma un konta iestatījumi
2. **Grāmatu datu apstrāde** – katalogs, meklēšana, ISBN un žanra API integrācija
3. **Lasīšanas progresa uzskaite** – statusi, statistika, vizualizācijas
4. **Sociālā un moderācijas sistēma** – sekošana, pieteikumi, paziņojumi, lietotāju moderācija

---

## Licence

Šis projekts ir izstrādāts **izglītības nolūkos** Rīgas Valsts tehnikuma kvalifikācijas darba ietvaros.
Autortiesības © 2026 *Marks Gerhards Brūveris*.
