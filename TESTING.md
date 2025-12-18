# 🧪 Guide de Tests

## Tests Backend (PHPUnit)

### Installation

```bash
cd backend
composer require --dev phpunit/phpunit symfony/test-pack
```

### Lancer les tests

```bash
# Tous les tests
php bin/phpunit

# Un fichier spécifique
php bin/phpunit tests/Controller/AuthControllerTest.php

# Avec couverture HTML
php bin/phpunit --coverage-html coverage
open coverage/index.html
```

### Tests disponibles

**Backend** :
- **AuthControllerTest** : 
  - ✅ Registration (success & validation errors)
  - ✅ Login (success & invalid credentials)
  
- **UserControllerTest** :
  - ✅ Get profile (authorized & unauthorized)
  - ✅ Update profile

- **SessionControllerTest** :
  - ✅ Get all sessions with pagination
  - ✅ Filter by language and level
  - ✅ Get session by ID
  - ✅ Session data structure validation
  - ✅ Invalid parameters handling

- **BookingControllerTest** :
  - ✅ Create booking (success, unauthorized, invalid)
  - ✅ Duplicate booking prevention
  - ✅ Get user bookings with pagination
  - ✅ Cancel booking (success, unauthorized)
  - ✅ Booking data structure validation

- **UserServiceTest** :
  - ✅ Create user with password hashing
  - ✅ Email uniqueness validation
  - ✅ Update user
  - ✅ Find user by email
  - ✅ Default role assignment

**Couverture Backend : ~85%**

```php
<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MyControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/endpoint');
        
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
    }
}
```

## Tests Frontend (Jest + React Testing Library)

### Installation

```bash
cd frontend
npm install --save-dev \
  jest \
  @testing-library/react \
  @testing-library/jest-dom \
  @testing-library/user-event \
  jest-environment-jsdom
```

### Lancer les tests

```bash
# Tous les tests
npm test

# Mode watch (développement)
npm run test:watch

# Avec couverture
npm run test:coverage
open coverage/lcov-report/index.html
```

### Tests disponibles

**Frontend** :
- **api.test.ts** :
  - ✅ Register user
  - ✅ Login & token storage
  - ✅ Get/Update profile
  - ✅ Error handling (401)

- **AuthContext.test.tsx** :
  - ✅ Loading state
  - ✅ Authentication flow
  - ✅ Error handling

- **LoginPage.test.tsx** :
  - ✅ Form rendering and validation
  - ✅ Login submission
  - ✅ Success/Error handling
  - ✅ Loading states
  - ✅ Password visibility toggle

- **DashboardPage.test.tsx** :
  - ✅ Sessions list display
  - ✅ Filters (language, level)
  - ✅ Pagination
  - ✅ Booking creation
  - ✅ Success/Error messages
  - ✅ Authentication redirect

- **BookingsPage.test.tsx** :
  - ✅ Bookings list display
  - ✅ Status badges (confirmed/cancelled)
  - ✅ Cancel booking flow
  - ✅ Confirmation dialog
  - ✅ Pagination
  - ✅ Empty state

**Couverture Frontend : ~80%**

```typescript
import { render, screen } from '@testing-library/react';
import MyComponent from './MyComponent';

describe('MyComponent', () => {
  it('should render correctly', () => {
    render(<MyComponent />);
    expect(screen.getByText('Hello')).toBeInTheDocument();
  });
});
```

## CI/CD (GitHub Actions)

Les tests s'exécutent automatiquement sur chaque push :

- ✅ Backend tests (PHP 8.4 + MongoDB)
- ✅ Frontend tests (Node 18 + Jest)
- ✅ Docker build test
- ✅ Coverage reports

Voir `.github/workflows/ci.yml`

## Couverture de Code

### Objectifs

- Backend : **85%+** de couverture ✅ ATTEINT
- Frontend : **80%+** de couverture ✅ ATTEINT

### Vérifier la couverture

```bash
# Backend
cd backend
php bin/phpunit --coverage-text

# Frontend
cd frontend
npm run test:coverage
```

## Commandes Rapides

```bash
# Backend : Tests + Couverture
cd backend && php bin/phpunit --coverage-html coverage

# Frontend : Tests + Couverture
cd frontend && npm run test:coverage

# Tout tester localement
./run-all-tests.sh
```

## Bonnes Pratiques

1. **Écrire les tests AVANT** de coder (TDD)
2. **Un test = Une fonctionnalité**
3. **Noms descriptifs** : `testUserCanLoginWithValidCredentials`
4. **Arrange, Act, Assert** pattern
5. **Mock les dépendances externes** (API, DB)
6. **Coverage ≠ Qualité** : Tester les cas limites

## Debugging

### Backend

```bash
# Verbose
php bin/phpunit -vvv

# Debug un test
php bin/phpunit --filter testLoginSuccess
```

### Frontend

```bash
# Debug mode
node --inspect-brk node_modules/.bin/jest --runInBand

# Un seul fichier
npm test -- api.test.ts
```

## Ressources

- [PHPUnit Docs](https://phpunit.de/)
- [Jest Docs](https://jestjs.io/)
- [Testing Library](https://testing-library.com/)
- [Symfony Testing](https://symfony.com/doc/current/testing.html)
