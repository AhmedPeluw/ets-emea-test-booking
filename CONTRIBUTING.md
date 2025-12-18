# Contributing to ETS EMEA Test Booking

Thank you for your interest in contributing to this project!

## Development Setup

1. Clone the repository
2. Install dependencies:
   ```bash
   cd backend && composer install
   cd ../frontend && npm install
   ```
3. Start development environment:
   ```bash
   docker-compose up -d
   ```

## Coding Standards

### Backend (PHP)
- Follow PSR-12 coding standards
- Write PHPUnit tests for new features
- Maintain minimum 80% code coverage
- Use type hints and return types

### Frontend (TypeScript)
- Use TypeScript strict mode
- Follow React best practices
- Write Jest tests for components
- Use semantic HTML

## Commit Messages

Follow conventional commits:
- `feat:` new feature
- `fix:` bug fix
- `docs:` documentation
- `test:` adding tests
- `refactor:` code refactoring

Example: `feat: add session filtering by date`

## Pull Request Process

1. Create a feature branch from `develop`
2. Write tests for your changes
3. Ensure all tests pass
4. Update documentation if needed
5. Submit PR with clear description

## Running Tests

```bash
# Backend
cd backend && php bin/phpunit

# Frontend
cd frontend && npm test
```

## Questions?

Open an issue for any questions or suggestions.
