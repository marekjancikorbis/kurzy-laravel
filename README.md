# Kurzy a přihlašování účastníků

Aplikace v Laravelu (SQLite, Blade) pro nabídku kurzů a přihlašování účastníků.

## První spuštění

```
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
```

Databáze je soubor `database/database.sqlite` (vytvoří se při migraci).

## Spuštění

```
php artisan serve
```

Aplikace běží na http://127.0.0.1:8000.

## Automatická kontrola

```
php artisan test
```

Test `test_course_is_created_and_stored` vytvoří kurz a ověří, že je uložen v databázi. Další testy ověřují validační pravidla, přihlašování, kapacitu a filtr. Testy běží nad SQLite v paměti, data aplikace nemění.

## Datový model

- **Course**: name, code (jedinečný), capacity (celé číslo > 0), starts_at
- **Participant**: name, email (platný, jedinečný)
- **Enrollment**: course_id, participant_id, enrolled_at, status (`enrolled`, `cancelled`, `completed`)

## Funkce

- CRUD kurzů (`/courses`) a účastníků (`/participants`).
- Přihlášení účastníka do kurzu na detailu kurzu, tam je i seznam přihlášených a změna stavu přihlášky.
- Obsazenost (aktivní přihlášky / kapacita) v seznamu i na detailu kurzu.
- Filtr kurzů podle termínu (od–do) a volných míst.

## Pravidla

- Aktivní přihláška je jen ve stavu `enrolled`; stavy `cancelled` a `completed` místo v kurzu nezabírají.
- Účastník má v kurzu nejvýše jednu přihlášku (unikátní dvojice v databázi). Zrušenou přihlášku lze obnovit novým přihlášením, pokud je volné místo.
- Počet aktivních přihlášek nepřekročí kapacitu, ani při návratu přihlášky do stavu `enrolled`.
- Kapacitu kurzu nelze snížit pod počet aktivních přihlášek.
- Smazání kurzu nebo účastníka smaže i jejich přihlášky.

## Kde co je

- `database/migrations/2026_11_15_000000_create_courses_tables.php` – tabulky
- `app/Models` – Course, Participant, Enrollment
- `app/Http/Controllers` – CourseController, ParticipantController, EnrollmentController
- `resources/views` – šablony
- `tests/Feature/CoursesTest.php` – automatické testy
