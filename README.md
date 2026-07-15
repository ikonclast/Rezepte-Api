# Rezepte-Kalkulator — REST-API (Backend)

Backend eines **Full-Stack-Projekts** zur Kalkulation von Rezept- und Menükosten: eine versionierte **REST-API in Laravel** mit Token-Authentifizierung, rollenbasierter Autorisierung und OpenAPI-Dokumentation.

> Dieses Repository enthält das **Backend**. Es ist Teil des „Do-It"-Abschlussprojekts meiner Ausbildung zum **Wirtschaftsinformatiker** (2025) und wurde über den kompletten Software-Lifecycle umgesetzt — von Lasten-/Pflichtenheft und Softwarearchitektur bis zum Live-Deployment.

## Das Gesamtprojekt

| Komponente | Technologie | Rolle |
|---|---|---|
| **Android-App** (Tablet) | Java/Kotlin, Android Studio | Frontend: Login, Zutaten-, Rezept- & Menüverwaltung → [App-Repo](https://github.com/ikonclast/rezepte-kalkulator-app) |
| **REST-API** *(dieses Repo)* | Laravel, PHP 8, Sanctum | Geschäftslogik & Datenzugriff |
| **Datenbank** | MySQL | Persistenz |
| **Deployment** | eigener V-Server | Im Projektzeitraum live deployed (`/api`); Server inzwischen abgeschaltet |

**Fachliche Idee:** Zutaten mit Preisen pflegen → Rezepte aus Komponenten (auch Teilrezepten) zusammensetzen → Menüs bilden → Kosten automatisch kalkulieren.

## Features der API

- **Authentifizierung** über Laravel Sanctum (Token): Registrierung, Login (rate-limited), Logout
- **Autorisierung** über Policies & Middleware (Löschen nur für Admins, Self-Service via Policy)
- **Rezepte** als verschachtelte Ressource — zusammengesetzt aus Komponenten (Zutaten/Teilrezepten)
- **Zutaten** und **Menüs** als vollständige REST-Ressourcen (`apiResource`)
- **Suche** sowie **Health-Check-Endpoint** für Monitoring/Deployment
- **OpenAPI/Swagger-Dokumentation** (l5-swagger)
- Versioniertes Layout unter `/api/v1`

## Tech-Stack

**PHP 8** · **Laravel** · **Laravel Sanctum** (Token-Auth) · **MySQL** (Eloquent ORM & Migrations) · **l5-swagger** (OpenAPI) · Form Requests (Validierung) · Policies (Autorisierung) · Rate-Limiting

## API-Überblick (`/api/v1`)

### Öffentlich
| Methode | Endpoint | Zweck |
|---|---|---|
| `POST` | `/auth/register` | Registrierung |
| `POST` | `/auth/login` | Login (Token), rate-limited |

### Geschützt (Sanctum-Token)
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

API danach unter `http://localhost:8000/api/v1`, Swagger-Doku unter `/api/documentation`.

## Beispiel-Requests

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
curl http://localhost:8000/api/v1/recipes -H "Authorization: Bearer <TOKEN>"
```

## Projektkontext

Abschlussprojekt („Do-It") der Ausbildung zum Wirtschaftsinformatiker (2025). Umgesetzt über den vollständigen Entwicklungsprozess inkl. Lastenheft, Pflichtenheft, Softwarearchitektur, Wirtschaftlichkeitsbetrachtung und Projektdokumentation. Bewusst mit Auth, Autorisierung, versionierter Struktur und API-Doku umgesetzt — wie man es für eine produktive Schnittstelle erwartet.
