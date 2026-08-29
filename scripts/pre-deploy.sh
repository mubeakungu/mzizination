#!/bin/bash
set -e

echo "📋 Pre-deployment checks..."

# Check PHP version
php_version=$(php -r 'echo PHP_VERSION;')
echo "✓ PHP Version: $php_version"

# Check required extensions
required_extensions=("pdo" "pdo_pgsql" "json" "ctype" "fileinfo")
for ext in "${required_extensions[@]}"; do
  if php -m | grep -q "$ext"; then
    echo "✓ Extension loaded: $ext"
  else
    echo "✗ Missing extension: $ext"
    exit 1
  fi
done

echo "✓ All pre-deployment checks passed"
