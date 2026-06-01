# Colocation.com

Application web de gestion de colocation développée avec **Symfony 6.4** : loyers, charges, messagerie, tâches ménagères et planning en un seul endroit.

---

## Sommaire

- [Prérequis & Installation](#installation)
- [Comptes de test](#comptes-de-test)
- [Fonctionnalités](#fonctionnalités)
- [API REST](#api-rest)
- [Tests](#tests)
- [Docker](#docker)

---

## Installation

### Prérequis

- [WAMP](https://www.wampserver.com/), [XAMPP](https://www.apachefriends.org/) ou [MAMP](https://www.mamp.info/) avec Apache + MySQL actifs
- **PHP 8.4** activé (PHP 8.2/8.3 incompatibles avec les dépendances)
- [Composer](https://getcomposer.org/) installé
- [Git for Windows](https://git-scm.com/) installé (fournit `openssl`)

### Emplacement du projet

| Serveur | Dossier cible |
|---|---|
| WAMP | `C:\wamp64\www\INF7_TD3\` |
| XAMPP | `C:\xampp\htdocs\INF7_TD3\` |
| MAMP | `C:\MAMP\htdocs\INF7_TD3\` |

### Vérifier PHP 8.4 dans le PATH

```
php -v   →   doit afficher PHP 8.4.x
```

- **WAMP** : clic gauche sur l'icône WAMP → choisir PHP 8.4
- **XAMPP** : Shell XAMPP ou ajouter `C:\xampp\php\` aux variables d'environnement Windows

### Lancer l'installation

Clic droit sur **`install.bat`** → **"Exécuter en tant qu'administrateur"**.

Le script fait tout automatiquement :
- installe les dépendances Composer
- crée et migre la base de données
- charge les données de démonstration
- génère les clés JWT
- démarre Mailpit (serveur mail de test) en arrière-plan

Puis ouvrir :

| Service | URL |
|---|---|
| Application | **http://localhost/INF7_TD3/public/** |
| Mailpit (emails) | **http://localhost:8025** |

> Si Mailpit n'est pas démarré, double-cliquer sur **`demarrer_mailpit.bat`**. Il se lance aussi automatiquement au démarrage Windows via le Planificateur de tâches.

### Réinitialiser l'application

```
install.bat   (idempotent, relançable à tout moment)
```

---

## Comptes de test

| Rôle | Email | Mot de passe | Données |
|---|---|---|---|
| Propriétaire 1 | `proprio@colocation.com` | `Proprio1234!` | Jean Dupont - 5 colocations (Paris, Lyon, Bordeaux, Marseille, Toulouse) |
| Propriétaire 2 | `proprio2@colocation.com` | `Proprio1234!` | Sophie Bernard - 4 colocations (Nantes, Montpellier, Strasbourg, Rennes) |
| Locataire 1 | `locataire@colocation.com` | `Locataire1234!` | Marie Martin - Chambre A, Les Lilas (Paris) |
| Locataire 2 | `locataire2@colocation.com` | `Locataire1234!` | Pierre Leroy - Chambre B, Les Lilas (Paris) |

---

## Fonctionnalités

### Page d'accueil

- Présentation de la plateforme adaptée au rôle connecté (visiteur / locataire / propriétaire)
- Statistiques de la plateforme, annonces récentes, propriétaires bien notés
- FAQ dynamique selon le rôle (6 questions différentes)
- Navigation claire avec menus par section

### Authentification & Profil

- **Inscription** avec choix du rôle (locataire / propriétaire), validation email et téléphone
- **Connexion** avec "Se souvenir de moi" (7 jours)
- **Mot de passe oublié** : lien de réinitialisation par email (Mailpit, token sécurisé 1h)
- **Changement de mot de passe** depuis le profil (vérification de l'ancien mot de passe)
- **Photo de profil** uploadable
- **Profils publics** propriétaire (annonces actives + évaluations) et locataire

### Côté Propriétaire

#### Colocations & Chambres
- Créer, modifier et supprimer des colocations
- Gérer les chambres inline (nom, surface, loyer mensuel) dans le formulaire de colocation
- Code postal validé côté serveur et navigateur
- Suppression protégée si des locataires sont assignés

#### Annonces
- Créer des annonces avec photos multiples, description, localisation, carte Leaflet
- Filtrer par statut disponible/indisponible
- Suivre les **visites** avec histogramme par jour (30 jours) et liste des visiteurs
- Les annonces passent automatiquement en indisponible quand toutes les chambres sont occupées

#### Candidatures
- Recevoir les candidatures avec pièces jointes (pièce d'identité + justificatif de revenus)
- **Accepter** une candidature : choisir la chambre à attribuer, un loyer du mois en cours est créé automatiquement
- **Refuser** avec message automatique au candidat

#### Loyers & Quittances
- Saisir et gérer les loyers (colocation, chambre, montant, mois, échéance, statut)
- **Valider le paiement** d'un loyer → génère automatiquement la quittance PDF
- Générer manuellement une quittance pour tout loyer payé
- Alertes sur le dashboard pour les loyers en retard

#### Charges & Tantièmes
- Saisir les charges par type : **eau, électricité, internet, taxes**
- **Calculer les tantièmes** : répartition automatique selon la surface de chaque chambre
- Vue d'ensemble des tantièmes par colocation et par charge (avec barre de progression)

#### Messagerie
- Conversations par locataire/colocation
- Messages automatiques pour candidatures, acceptations, refus, résiliations

#### Évaluations
- Évaluer les locataires (note 1-5 + commentaire)
- Voir toutes les évaluations données sur une page dédiée

#### Tâches ménagères
- Créer des tâches (vaisselle, ménage, entretien, autre) et les assigner à n'importe quel membre de la colocation
- **Semainier** : vue calendrier lundi→dimanche de la semaine en cours
- Reconduite automatique des tâches terminées pour la semaine suivante

---

### Côté Locataire

#### Candidatures & Logement
- Parcourir les annonces avec filtres (ville, budget) et pagination
- **Candidater** avec pièce d'identité et justificatif de revenus (PDF ou image)
- Suivi des candidatures (en attente / acceptée / refusée)
- Impossible de candidater si déjà locataire quelque part (résiliation requise)
- **Résilier son bail** en ligne avec notification automatique au propriétaire

#### Loyers & Quittances
- Consulter tous ses loyers et leur statut (payé / impayé / en retard)
- **Payer un loyer** en ligne → quittance générée automatiquement
- **Télécharger ses quittances en PDF** (loyer + charges + total)

#### Tantièmes (charges)
- Visualiser sa part des charges mensuelles selon la surface de sa chambre
- Tableau avec montant total de la charge, pourcentage et montant dû

#### Messagerie
- Contacter le propriétaire directement
- Démarrer une conversation depuis une annonce
- Appuyer sur Entrée pour envoyer (Shift+Entrée = saut de ligne)

#### Tâches ménagères
- Accéder au semainier et à la liste des tâches de sa colocation
- Marquer les tâches terminées, en assigner à d'autres colocataires

#### Évaluations & Avis
- Évaluer son propriétaire (après avoir payé au moins un loyer)
- Laisser un avis sur une annonce (star rating CSS)

#### Notifications
- Notifications temps réel pour loyers, messages, candidatures
- Badge rouge sur la cloche et la messagerie (fetch JS)

---

### Fonctionnalités transverses

| Fonctionnalité | Détail |
|---|---|
| **Internationalisation** | Français 🇫🇷 et Anglais 🇬🇧, sélecteur dans la navbar, locale persistée en cookie (survit au logout) |
| **SEO** | `sitemap.xml` et `robots.txt` dynamiques, `<meta description>`, `<link rel="canonical">`, Open Graph |
| **Green IT** | Bootstrap et Leaflet via CDN, `loading="lazy"` sur toutes les images, scripts en `defer` |
| **Sécurité** | CSP, X-Frame-Options, CSRF sur tous les formulaires, `strip_tags()` sur les setters, JWT pour l'API |
| **Reset mot de passe** | Token sécurisé 32 bytes, expiry 1h, email via Mailpit |

---

## API REST

Documentation interactive Swagger disponible à **`/api/docs`**.

### Authentification

```http
POST /api/login
Content-Type: application/json

{"email": "proprio@colocation.com", "password": "Proprio1234!"}
```

Réponse : `{"token": "eyJ..."}` - à utiliser dans le header `Authorization: Bearer <token>`.

### Ressources disponibles

| Endpoint | Méthodes | Accès |
|---|---|---|
| `/api/annonces` | GET, POST, PUT, DELETE | GET public, écriture → `ROLE_PROPRIETAIRE` |
| `/api/colocations` | GET, POST, PUT, DELETE | GET public, écriture → `ROLE_PROPRIETAIRE` |
| `/api/chambres` | GET, POST, PUT, DELETE | Lecture → `ROLE_USER`, écriture → `ROLE_PROPRIETAIRE` |
| `/api/loyers` | GET, POST, PUT | `ROLE_USER` / `ROLE_PROPRIETAIRE` |
| `/api/charges` | GET, POST, PUT, DELETE | `ROLE_USER` / `ROLE_PROPRIETAIRE` |
| `/api/messages` | GET, POST | `ROLE_USER` |
| `/api/notifications` | GET | `ROLE_USER` |
| `/api/taches` | GET, POST, PUT, DELETE | `ROLE_USER` / `ROLE_PROPRIETAIRE` |
| `/api/users` | GET | `ROLE_USER` |
| `/api/annonces-custom` | GET, POST, PUT | Contrôleur manuel |
| `/api/colocations-custom` | GET, POST | Contrôleur manuel |

---

## Tests

```bash
# Recharger les fixtures avant les tests (obligatoire)
php bin/console doctrine:fixtures:load --no-interaction

# Lancer tous les tests
php vendor/bin/phpunit tests

# Par dossier
php vendor/bin/phpunit tests/Entity
php vendor/bin/phpunit tests/Controller
php vendor/bin/phpunit tests/Api
```

**188 tests, 264 assertions** - tous verts.

| Dossier | Contenu |
|---|---|
| `tests/Entity/` | UserTest, AnnonceTest, ChambreTest, LoyerTest, ChargeTest, EvaluationLocataireTest, VisiteAnnonceTest, ColocationTest |
| `tests/Controller/` | SecurityControllerTest, ProtectedRoutesTest, LocataireControllerTest (16 tests), OwnerAccessTest (17 tests) |
| `tests/Api/` | JwtAuthTest |

> Les tests WebTestCase utilisent directement `colocation_db`. Ne pas modifier la base manuellement entre les tests.

---

## Docker

```bash
# Lancer l'application complète (app + MySQL)
docker compose up -d

# Accès : http://localhost:8080
```

Le `docker-compose.yml` inclut :
- **app** : PHP 8.4 + Apache, port 8080
- **db** : MySQL 8.0, port 3307
- Volumes persistants pour les uploads et les clés JWT

---

## Stack technique

| Composant | Version |
|---|---|
| Symfony | 6.4 LTS |
| PHP | 8.4 |
| Base de données | MySQL 8.0 |
| ORM | Doctrine |
| Templates | Twig 3 |
| CSS | Bootstrap 5.3 |
| Carte | Leaflet + CartoDB |
| Charts | Chart.js 4 |
| PDF | dompdf 3 |
| API | API Platform 3 + JWT |
| Tests | PHPUnit 13 |
| Mail (dev) | Mailpit |
