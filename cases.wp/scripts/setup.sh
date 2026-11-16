#!/usr/bin/env bash
# One-shot setup for the local CASES site.
#   docker compose up -d && bash scripts/setup.sh
# Safe to re-run: install and seed steps are idempotent.
set -euo pipefail
export MSYS_NO_PATHCONV=1   # Git Bash on Windows must not rewrite /scripts/... paths
cd "$(dirname "$0")/.."
set -a; . ./.env; set +a

WP="docker compose --profile tools run --rm -T wpcli wp"

if ! $WP core is-installed 2>/dev/null; then
  $WP core install \
    --url="http://localhost:${WP_PORT:-8088}" \
    --title="CASES" \
    --admin_user=admin \
    --admin_password="${ADMIN_PASSWORD:?set ADMIN_PASSWORD in .env}" \
    --admin_email=admin@cases.local \
    --skip-email
fi

$WP theme activate cases
$WP eval-file /scripts/seed.php
$WP eval-file /scripts/seed-certificates.php
$WP eval-file /scripts/seed-news.php
$WP rewrite structure '/%postname%/' --hard >/dev/null

echo "Site:  http://localhost:${WP_PORT:-8088}/"
echo "Admin: http://localhost:${WP_PORT:-8088}/wp-admin/  (user: admin, password in .env)"
