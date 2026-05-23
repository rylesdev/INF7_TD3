# Colocation.com — INF7 TD3

Application web de gestion de colocation développée avec **Symfony 6.4**.  
Permet aux propriétaires de gérer leurs colocations et aux locataires de suivre leurs loyers, charges, quittances et tâches ménagères.

---

## Stack technique

| Composant | Technologie |
|---|---|
| Backend | PHP 8.2 + Symfony 6.4 |
| Base de données | MySQL (WAMP) |
| ORM | Doctrine + Migrations |
| Authentification | LexikJWTAuthenticationBundle |
| API auto | API Platform 3 |
| Templates | Twig + Bootstrap 5.3 |
| Carte interactive | Leaflet.js + CartoDB tiles |
| Tests | PHPUnit 13 |

---

## Installation

### Prérequis
- WAMP (Apache + MySQL + PHP 8.2)
- Composer
- Git for Windows (pour `openssl`)

### Lancement

```
install.bat
```

Le script fait automatiquement :
1. `composer install`
2. Vérifie/crée `.env`
3. Supprime et recrée la base `colocation_db`
4. Supprime les anciennes migrations, génère et applique une nouvelle
5. Charge les fixtures (données de test)
6. Génère les clés JWT via `openssl`
7. Crée les dossiers d'upload
8. Vide le cache

> Le script est **idempotent** : on peut le relancer autant de fois que nécessaire sans erreur.

### Accès

URL : `http://localhost/htdocs/INF7_TD3/public/`

| Compte | Email | Mot de passe | Rôle |
|---|---|---|---|
| Propriétaire | `proprio@colocation.com` | `Proprio1234!` | `ROLE_PROPRIETAIRE` |
| Locataire | `locataire@colocation.com` | `Locataire1234!` | `ROLE_USER` |
| Locataire 2 | `locataire2@colocation.com` | `Locataire1234!` | `ROLE_USER` |

---

## Architecture

### Entités

| Entité | Rôle |
|---|---|
| `User` | Propriétaire ou locataire |
| `Colocation` | Logement géré par un propriétaire |
| `Chambre` | Chambre d'une colocation, assignée à un locataire |
| `Annonce` | Annonce de location publiée |
| `PhotoAnnonce` | Photos attachées à une annonce |
| `Loyer` | Paiement mensuel d'une chambre |
| `Charge` | Dépense commune (eau, électricité…) |
| `Quittance` | Reçu PDF généré pour un loyer payé |
| `Tantieme` | Part d'une charge selon la surface de la chambre |
| `Tache` | Tâche ménagère assignée à un locataire |
| `Semainier` | Planning hebdomadaire reconductible |
| `Message` | Message entre locataire et propriétaire |
| `Notification` | Notification système pour un utilisateur |
| `EvaluationLocataire` | Évaluation d'un locataire par le propriétaire |
| `VisiteAnnonce` | Demande de visite sur une annonce |

### Double couche API

- **API Platform** (`/api/*`) : CRUD automatique depuis les attributs `#[ApiResource]` sur les entités
- **Controllers manuels** : `AnnonceApiController`, `ColocationApiController`
- **JWT** : `POST /api/login` → Bearer token à passer dans `Authorization`

### Sécurité

- Headers HTTP injectés sur chaque réponse via `SecurityHeadersSubscriber` : `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`
- CSRF activé sur le formulaire de login
- Validation des uploads (type MIME, taille max)
- Sanitisation des inputs dans les setters des entités (`htmlspecialchars`, `strip_tags`)

---

## Référencement (SEO)

Le projet implémente les bonnes pratiques SEO suivantes :

### Balises meta
- `<meta name="description">` — surchargeable page par page via `{% block meta_description %}`
- `<meta name="robots">` — `index, follow` par défaut, `noindex` pour les pages privées
- `<link rel="canonical">` — URL canonique automatique sur chaque page
- **Open Graph** : `og:title`, `og:description`, `og:url`, `og:type` sur toutes les pages

### Sitemap et robots
- `/sitemap.xml` — généré dynamiquement, liste les annonces disponibles avec `lastmod` et `priority`
- `/robots.txt` — généré dynamiquement, référence le sitemap

### Performance (Green IT)
- Bootstrap et Leaflet chargés depuis CDN (zéro ressource statique lourde en local)
- Attribut `loading="lazy"` sur toutes les images non critiques
- Scripts JS non bloquants (`defer`)

---

## Tests

### Lancer les tests

```bash
php vendor/bin/phpunit tests
php vendor/bin/phpunit tests/Entity/ColocationTest.php
php vendor/bin/phpunit --filter testNomIsSet tests/Entity/ColocationTest.php
```

### État actuel

**14 tests — 10 passent, 4 échouent**

| Test | Statut | Cause |
|---|---|---|
| `testNomIsSet` | ✅ | — |
| `testAdresseIsSet` | ✅ | — |
| `testVilleIsSet` | ✅ | — |
| `testCodePostalIsSet` | ✅ | — |
| `testLoyerIsSet` | ❌ | `loyer` retourne `'1500'` (string) — champ `decimal` Doctrine retourne toujours une string |
| `testProprietaireAssignment` | ✅ | — |
| `testChambresCollectionStartsEmpty` | ✅ | — |
| `testSurfaceTotaleWithNoChambres` | ✅ | — |
| `testSurfaceTotaleWithChambres` | ❌ | `getSurfaceTotale()` retourne `0.0` — `addChambre()` non appelé via `setColocation` |
| `testLatitudeLongitudeNullByDefault` | ✅ | — |
| `testLatitudeLongitudeCanBeSet` | ❌ | `latitude`/`longitude` retournent des strings — champ `decimal` Doctrine |
| `testCreatedAtSetAutomatically` | ❌ | `createdAt` est `null` — `#[PrePersist]` ne s'exécute pas sans Doctrine en test unitaire |
| `testDescriptionCanBeNull` | ✅ | — |
| `testDescriptionIsSet` | ✅ | — |

### Tests manquants (à créer)
- `tests/Entity/UserTest.php`
- `tests/Entity/AnnonceTest.php`
- `tests/Entity/LoyerTest.php`
- `tests/Entity/ChargeTest.php`
- Tests fonctionnels (`WebTestCase`) : login, routes protégées, API JWT

---

## Ce qu'il reste à implémenter

| Fonctionnalité | État |
|---|---|
| Corriger les 4 tests qui échouent | ❌ À faire |
| Ajouter les tests manquants | ❌ À faire |
| Page visite annonce (`VisiteAnnonce`) | ❌ Entité sans controller ni template |
| Page évaluation locataire (`EvaluationLocataire`) | ❌ Entité sans controller ni template |
| Génération PDF quittances | ⚠️ Route présente, dompdf à vérifier |
| Reset password | ⚠️ Nécessite un mailer (`MAILER_DSN` dans `.env`) |
| Messages non lus | ⚠️ `Message` n'a pas de champ `lu`, codé à `0` |
| Dashboard propriétaire | ⚠️ Incohérences template/controller probables |

---

## Commandes utiles

```bash
# Régénérer la base depuis zéro
php bin/console doctrine:database:drop --force --if-exists
php bin/console doctrine:database:create
php bin/console make:migration --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction

# Vider le cache
php bin/console cache:clear

# Lister toutes les routes
php bin/console debug:router

# Vérifier la cohérence du schéma
php bin/console doctrine:schema:validate
```
