#!/usr/bin/env bash
# Jalankan test suite di lingkungan php-wasm sandbox.
#
# PHPUnit tidak bisa menjalankan migrasi in-process di php-wasm (stack wasm
# terbatas), jadi database testing disiapkan lebih dulu lewat artisan, lalu
# test berjalan dengan trait DatabaseTransactions (rollback tiap test).
#
# Usage: bin/run-tests.sh [args phpunit...]
set -e
cd "$(dirname "$0")/.."

export DB_DATABASE="database/testing.sqlite"
touch "$DB_DATABASE"
php artisan migrate:fresh --force --quiet

exec node /home/user/.tools/phpunit.mjs "$@"
