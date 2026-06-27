#!/bin/bash
exec "$(cd "$(dirname "$0")/.." && pwd)/scripts/lint-php.sh"
