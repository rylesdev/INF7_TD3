# Colocation.com

Application web de gestion de colocation : loyers, charges, messagerie et planning ménager en un seul endroit.

---

## Installation

### Prérequis

- [WAMP](https://www.wampserver.com/), [XAMPP](https://www.apachefriends.org/) ou [MAMP](https://www.mamp.info/) (ou équivalent) installé et démarré avec Apache + MySQL actifs
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
| Propriétaire | `proprio@colocation.com` | `Proprio1234!` |
| Locataire | `locataire@colocation.com` | `Locataire1234!` |
| Locataire 2 | `locataire2@colocation.com` | `Locataire1234!` |

---

## Fonctionnalités

### Côté propriétaire
- Gérer ses colocations et les chambres
- Suivre les loyers (payé / en retard)
- Répartir les charges selon la surface de chaque chambre (tantièmes)
- Générer des quittances PDF
- Publier des annonces avec photos et carte interactive
- Suivre les visites des annonces (histogramme)
- Communiquer avec les locataires via la messagerie
- Évaluer les locataires (note + commentaire)

### Côté locataire
- Consulter ses loyers et leur statut
- Télécharger ses quittances en PDF
- Voir sa part des charges mensuelles (tantièmes)
- Gérer le planning ménager avec ses colocataires
- Envoyer des messages au propriétaire
- Consulter ses évaluations
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
