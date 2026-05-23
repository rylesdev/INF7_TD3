# Colocation.com

Application web de gestion de colocation — loyers, charges, messagerie et planning ménager en un seul endroit.

---

## Installation

### Prérequis

- [WAMP](https://www.wampserver.com/) installé et démarré (icône verte)
- [Composer](https://getcomposer.org/) installé
- [Git for Windows](https://git-scm.com/) installé (fournit `openssl`)

### Lancer l'application

Double-cliquez sur **`install.bat`** à la racine du projet.

Le script fait tout automatiquement :
- installe les dépendances
- crée la base de données
- génère les données de test
- configure l'authentification

Une fois terminé, ouvrez votre navigateur à l'adresse :

**http://localhost/htdocs/INF7_TD3/public/**

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
- Répartir les charges selon la surface de chaque chambre
- Générer des quittances PDF
- Publier des annonces avec photos et carte interactive
- Communiquer avec les locataires via la messagerie

### Côté locataire
- Consulter ses loyers et leur statut
- Télécharger ses quittances en PDF
- Voir sa part des charges mensuelles
- Gérer le planning ménager avec ses colocataires
- Envoyer des messages au propriétaire

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
