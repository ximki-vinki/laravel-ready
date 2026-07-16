#!/usr/bin/env bash
set -euo pipefail

# Подставляет версию вместо @package_version@ в Application.php (как на CI перед Box).
# Пример: ./scripts/bake-version.sh 9.9.9 && php bin/laravel-ready -V

version="${1:-}"
if [[ -z "$version" ]]; then
  echo "Usage: $0 <version>" >&2
  echo "Example: $0 9.9.9" >&2
  exit 1
fi

root="$(cd "$(dirname "$0")/.." && pwd)"
file="$root/src/Console/Application.php"

if ! grep -q "@package_version@" "$file"; then
  echo "Placeholder @package_version@ not found in $file" >&2
  exit 1
fi

sed -i.bak "s/@package_version@/${version}/g" "$file"
rm -f "${file}.bak"

echo "Baked VERSION=${version} into src/Console/Application.php"
