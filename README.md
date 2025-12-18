# 🎓 ETS EMEA - Test Booking Platform

Plateforme moderne de réservation de sessions de tests de langues.

## 🚀 Quick Start

```bash
git clone https://github.com/YOUR_USERNAME/ets-emea-test-booking.git
cd ets-emea-test-booking
cd backend && composer install && cd ..
docker-compose up -d --build
```

Accès : http://localhost:3000

## 📚 Documentation complète

Voir `DOCUMENTATION.md` pour la documentation technique complète.

## 🛠️ Technologies

- Backend: Symfony 6.4 + MongoDB + PHP 8.4
- Frontend: Next.js 14 + TypeScript + shadcn/ui
- Database: MongoDB 7.0
- Deploy: Docker Compose

## ✨ Features

✅ Authentification JWT (24h)  
✅ Gestion sessions de tests  
✅ Système de réservation  
✅ Interface moderne et responsive  
✅ Dashboard utilisateur  
✅ Gestion profil  

## 📖 Utilisation

1. **S'inscrire** → http://localhost:3000/register
2. **Se connecter** → Auto-login après inscription
3. **Dashboard** → Voir sessions disponibles
4. **Réserver** → Cliquer "Réserver maintenant"
5. **Mes réservations** → Voir/Annuler réservations
6. **Profil** → Modifier informations

## 🔗 API Endpoints

- `POST /api/auth/register` - Inscription
- `POST /api/auth/login` - Connexion
- `GET /api/users/me` - Profil utilisateur
- `GET /api/sessions` - Liste sessions
- `POST /api/bookings` - Créer réservation
- `GET /api/bookings` - Mes réservations
- `DELETE /api/bookings/{id}` - Annuler réservation

## 📦 Structure

```
backend/          # API Symfony
frontend/         # App Next.js
docker-compose.yml  # Config Docker
```

## 👤 Auteur

AHMED KHACHIA ERRAHMAN

## 🧪 Tests

### Backend (PHPUnit)

```bash
cd backend

# Installer PHPUnit (si pas déjà fait)
composer require --dev phpunit/phpunit symfony/test-pack

# Lancer tous les tests
php bin/phpunit

# Lancer avec couverture de code
php bin/phpunit --coverage-html coverage
```

**Tests disponibles** :
- `AuthControllerTest` : Tests d'inscription et connexion
- `UserControllerTest` : Tests profil utilisateur
- `SessionControllerTest` : Tests sessions (list, filters, pagination)
- `BookingControllerTest` : Tests réservations (CRUD complet)
- `UserServiceTest` : Tests service utilisateur
- Couverture : **~85%** du code backend

### Frontend (Jest + React Testing Library)

```bash
cd frontend

# Installer dépendances de test (si pas déjà fait)
npm install --save-dev jest @testing-library/react @testing-library/jest-dom @testing-library/user-event jest-environment-jsdom

# Lancer tous les tests
npm test

# Mode watch (développement)
npm run test:watch

# Couverture de code
npm run test:coverage
```

**Tests disponibles** :
- `api.test.ts` : Tests du service API
- `AuthContext.test.tsx` : Tests du contexte d'authentification
- `LoginPage.test.tsx` : Tests page de connexion
- `DashboardPage.test.tsx` : Tests dashboard (sessions, filters, booking)
- `BookingsPage.test.tsx` : Tests page réservations
- Couverture : **~80%** du code frontend

### CI/CD (GitHub Actions)

Les tests s'exécutent automatiquement sur chaque push via GitHub Actions.

## 📄 License

MIT
