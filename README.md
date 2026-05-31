# Colocation.com

Application web de gestion de colocation : loyers, charges, messagerie et planning ménager en un seul endroit.

---

## Installation

### Prérequis

- [WAMP](https://www.wampserver.com/), [XAMPP](https://www.apachefriends.org/) ou [MAMP](https://www.mamp.info/) (ou équivalent) installé et démarré avec Apache + MySQL actifs
- **PHP 8.4** activé dans votre serveur local (PHP 8.2 et 8.3 sont incompatibles avec les dépendances du projet)
- [Composer](https://getcomposer.org/) installé
- [Git for Windows](https://git-scm.com/) installé (fournit `openssl`)

### Lancer l'application

Faites un clic droit sur **`install.bat`** → **"Exécuter en tant qu'administrateur"**.

Le script fait tout automatiquement :
- installe les dépendances
- crée la base de données
- génère les données de test
- configure l'authentification
- télécharge et démarre Mailpit (serveur mail de test) en arrière-plan

Une fois terminé, ouvrez votre navigateur à l'adresse :

**http://localhost/htdocs/INF7_TD3/public/**

Pour consulter les emails envoyés par l'application (ex : réinitialisation de mot de passe) :

**http://localhost:8025** (interface Mailpit)

> Si Mailpit n'est pas démarré, double-cliquez sur **`demarrer_mailpit.bat`** à la racine du projet. Il démarre aussi automatiquement à chaque ouverture de session Windows (via le Planificateur de tâches, configuré par `install.bat`).

---

## Comptes de test

| Rôle | Email | Mot de passe |
|---|---|---|
| Propriétaire 1 (Jean Dupont) | `proprio@colocation.com` | `Proprio1234!` |
| Propriétaire 2 (Sophie Bernard) | `proprio2@colocation.com` | `Proprio1234!` |
| Locataire | `locataire@colocation.com` | `Locataire1234!` |
| Locataire 2 | `locataire2@colocation.com` | `Locataire1234!` |

---

## Fonctionnalités

### Côté propriétaire
- Gérer ses colocations et les chambres
- Recevoir les candidatures des locataires (avec pièces jointes) et les accepter ou refuser
- Suivre les loyers (payé / en retard) et générer des quittances PDF
- Répartir les charges selon la surface de chaque chambre (tantièmes)
- Publier des annonces avec photos et carte interactive
- Suivre les visites des annonces (histogramme par jour)
- Communiquer avec les locataires via la messagerie
- Évaluer les locataires (note + commentaire)

### Côté locataire
- Parcourir les annonces avec filtres (ville, prix) et candidater avec pièces jointes
- Consulter ses loyers, les payer en ligne et télécharger ses quittances en PDF
- Voir sa part des charges mensuelles (tantièmes)
- Gérer le planning ménager avec ses colocataires
- Évaluer son propriétaire et laisser un avis sur une annonce
- Résilier son bail en ligne
- Communiquer avec le propriétaire via la messagerie
- Réinitialiser son mot de passe par email

---

## Réinitialiser l'application

Si vous souhaitez repartir de zéro (base de données vide + données fraîches), relancez simplement `install.bat`.

---

## API

Une API REST est disponible à `/api` avec authentification par token JWT.

```
POST /api/login   →  { "username": "email", "password": "motdepasse" }
```

Le token retourné s'utilise dans le header `Authorization: Bearer <token>`.
