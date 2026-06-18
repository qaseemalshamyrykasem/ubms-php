#!/usr/bin/env bash
# UBMS Quick Setup Script
# Usage: ./setup.sh

set -e

echo "🎓 UBMS - University Batch Management System"
echo "=============================================="
echo ""

# Check dependencies
echo "🔍 Checking dependencies..."

check_cmd() {
    if ! command -v $1 &> /dev/null; then
        echo "❌ $1 is not installed. Please install it first."
        echo "   $2"
        exit 1
    fi
}

check_cmd php "https://www.php.net/downloads.php"
check_cmd composer "https://getcomposer.org/download/"
check_cmd mysql "https://dev.mysql.com/downloads/"
check_cmd npm "https://nodejs.org/"

echo "✅ All dependencies found"
echo ""

# Setup Backend
echo "📦 Setting up backend..."
cd backend

if [ ! -f .env ]; then
    cp .env.example .env
    echo "📝 Created .env file. Edit it with your DB credentials."
fi

composer install --no-interaction

# Generate key if not exists
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Ask for DB credentials
read -p "Enter DB name (default: ubms): " DB_NAME
DB_NAME=${DB_NAME:-ubms}
read -p "Enter DB user (default: root): " DB_USER
DB_USER=${DB_USER:-root}
read -s -p "Enter DB password: " DB_PASS
echo ""

# Update .env
sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
rm -f .env.bak

# Create database
mysql -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true

# Run migrations
php artisan migrate --seed --force

# Storage link
php artisan storage:link

# Optimize
php artisan optimize

cd ..

# Setup Frontend
echo ""
echo "📦 Setting up frontend..."
cd frontend
npm install
cd ..

echo ""
echo "✅ Setup complete!"
echo ""
echo "🚀 To start development servers:"
echo "   Terminal 1: cd backend && php artisan serve"
echo "   Terminal 2: cd frontend && npm run dev"
echo ""
echo "🔐 Demo accounts:"
echo "   Admin: admin@ubms.local / password"
echo "   Rep:   rep@ubms.local / password"
echo "   Student: student1@ubms.local / password"
echo ""
echo "📚 Documentation: docs/ folder"
