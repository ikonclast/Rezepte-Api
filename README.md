# Rezepte-API

Eine versionierte **REST-API in Laravel** zur Verwaltung von Rezepten, Zutaten und Menüs — mit Token-Authentifizierung, rollenbasierter Autorisierung und OpenAPI-Dokumentation.

> Backend-Projekt mit Fokus auf sauberes API-Design: klare Ressourcen-Struktur, Sanctum-Auth, Policies, Form-Request-Validierung und ein versioniertes `/api/v1`-Layout.

## Features

- **Authentifizierung** über Laravel Sanctum (Token-basiert): Registrierung, Login (rate-limited), Logout
- **Autorisierung** über Policies & Middleware (z. B. Löschen nur für Admins, Self-Service via Policy)
- **Rezepte** als verschachtelte Ressource: ein Rezept kann aus Komponenten (Zutaten/Teilrezepten) zusammengesetzt werden
- **Zutaten** und **Menüs** als vollständige REST-Ressourcen (`apiResource`)
- **Suche** über Rezepte/Zutaten
- **Health-Check-Endpoint** für Monitoring/Deployment
- **OpenAPI/Swagger-Dokumentation** (l5-swagger)

## Tech-Stack

- **PHP 8.x**, **Laravel** (REST-API, kein Blade-Frontend)
- **Laravel Sanctum** — Token-Authentifizierung
- **MySQL** — Datenhaltung, via Eloquent ORM & Migrations
- **l5-swagger** — API-Dokumentation (OpenAPI)
- Form Requests für Validierung, Policies für Autorisierung, Rate-Limiting

## API-Überblick (`/api/v1`)

### Öffentlich
| Methode | Endpoint | Zweck |
|---|---|---|
| `POST` | `/auth/register` | Registrierung |
| `POST` | `/auth/login` | Login (Token), rate-limited |

### Geschützt (Sanctum-Token erforderlich)
| Methode | Endpoint | Zweck |
|---|---|---|
| `POST` | `/auth/logout` | Logout |
| `GET`  | `/me`, `/me/summary` | Eigenes Profil / Zusammenfassung |
| `GET/PUT/DELETE` | `/users/{id}` | Nutzerverwaltung (DELETE nur Admin) |
| `GET/POST/PUT/DELETE` | `/recipes` | Rezepte (CRUD) |
| `POST/DELETE` | `/recipes/{id}/components` | Rezept-Komponenten verwalten |
| `GET/POST/PUT/DELETE` | `/ingredients` | Zutaten (CRUD) |
| `GET/POST/PUT/DELETE` | `/menus` | Menüs (CRUD) |
| `GET` | `/search` | Suche |
| `GET` | `/health` | Health-Check |

## Lokale Einrichtung

```bash
git clone https://github.com/ikonclast/Rezepte-Api.git
cd Rezepte-Api

composer install
cp .env.example .env
php artisan key:generate

# .env anpassen (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
php artisan migrate

php artisan serve
```

Die API läuft anschließend unter `http://localhost:8000/api/v1`.
Die Swagger-Dokumentation ist unter `/api/documentation` erreichbar.

## Beispiel-Request

```bash
# Registrieren
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"secret123","password_confirmation":"secret123"}'

# Login -> Token
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"secret123"}'

# Geschützter Endpoint mit Token
curl http://localhost:8000/api/v1/recipes \
  -H "Authorization: Bearer <TOKEN>"
```

## Kontext

Übungs-/Portfolioprojekt zum Vertiefen von API-Design mit Laravel — bewusst mit Auth, Autorisierung, versionierter Struktur und Dokumentation umgesetzt, wie man es für eine produktive Schnittstelle erwarten würde.
