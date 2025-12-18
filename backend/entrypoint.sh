#!/bin/bash
set -e

echo "🚀 Starting backend..."

# Attendre MongoDB (max 30s)
echo "⏳ Waiting for MongoDB..."
timeout=30
elapsed=0
while ! php -r "try { (new MongoDB\Driver\Manager('mongodb://mongodb:27017'))->executeCommand('admin', new MongoDB\Driver\Command(['ping' => 1])); exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
  if [ $elapsed -ge $timeout ]; then
    echo "❌ MongoDB timeout"
    exit 1
  fi
  sleep 1
  elapsed=$((elapsed + 1))
done
echo "✅ MongoDB ready"

# Générer clés JWT (seulement si elles n'existent pas)
if [ ! -f "config/jwt/private.pem" ]; then
  echo "🔐 Generating JWT keys..."
  php bin/console lexik:jwt:generate-keypair --skip-if-exists
else
  echo "✅ JWT keys already exist"
fi

# Créer schema MongoDB (ignore les erreurs si déjà créé)
echo "📊 Creating MongoDB schema..."
php bin/console doctrine:mongodb:schema:create --index 2>/dev/null || true

# Nettoyer le cache
echo "🧹 Clearing cache..."
php bin/console cache:clear --no-warmup 2>/dev/null || true

echo "✅ Backend ready!"
echo "🌐 Starting PHP server..."

# Démarrer le serveur
exec php -S 0.0.0.0:8000 -t public
