#!/usr/bin/env bash
# Builds a throw-away real WordPress (core + SQLite, no MySQL needed) in tools/wp-e2e/.wp and installs the theme zip into it.
# Needs network for Composer (WordPress core) and git (SQLite integration plugin). Usage: tools/wp-e2e/setup.sh
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(cd "$HERE/../.." && pwd)"
WP="$HERE/.wp"
CACHE="$HERE/.cache"
PORT="${WP_E2E_PORT:-8099}"
mkdir -p "$CACHE"

if [ ! -d "$CACHE/core/vendor/johnpbloch/wordpress-core" ]; then
  mkdir -p "$CACHE/core"
  (cd "$CACHE/core" && composer require johnpbloch/wordpress-core --no-interaction --no-progress --no-scripts >/dev/null)
fi
if [ ! -d "$CACHE/sqlite-int" ]; then
  git clone --depth 1 https://github.com/WordPress/sqlite-database-integration.git "$CACHE/sqlite-int" >/dev/null 2>&1
fi

rm -rf "$WP"; mkdir -p "$WP"
cp -r "$CACHE/core/vendor/johnpbloch/wordpress-core/." "$WP/"
mkdir -p "$WP/wp-content/plugins" "$WP/wp-content/themes" "$WP/wp-content/mu-plugins" "$WP/wp-content/database"
cp -rL "$CACHE/sqlite-int/packages/plugin-sqlite-database-integration" "$WP/wp-content/plugins/sqlite-database-integration"
sed -e "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$WP/wp-content/plugins/sqlite-database-integration#" -e "s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" \
  "$WP/wp-content/plugins/sqlite-database-integration/db.copy" > "$WP/wp-content/db.php"
cp "$HERE/mu-plugin/dh-e2e.php" "$WP/wp-content/mu-plugins/dh-e2e.php"

# The theme exactly as it is delivered: the zip.
(cd "$ROOT" && npm run theme:zip >/dev/null 2>&1)
unzip -q "$ROOT/the-dental-hauz-theme.zip" -d "$WP/wp-content/themes/"

cat > "$WP/wp-config.php" <<PHP
<?php
define( 'DB_NAME', 'wp' ); define( 'DB_USER', 'x' ); define( 'DB_PASSWORD', 'x' ); define( 'DB_HOST', 'x' );
define( 'DB_DIR', '$WP/wp-content/database/' ); define( 'DB_FILE', 'wp.sqlite' );
define( 'WP_HOME', 'http://127.0.0.1:$PORT' ); define( 'WP_SITEURL', 'http://127.0.0.1:$PORT' );
define( 'DISABLE_WP_CRON', true ); define( 'WP_DEBUG', true ); define( 'WP_DEBUG_LOG', '$HERE/out/php-errors.log' ); define( 'WP_DEBUG_DISPLAY', false );
define( 'AUTH_KEY', 'a' ); define( 'SECURE_AUTH_KEY', 'a' ); define( 'LOGGED_IN_KEY', 'a' ); define( 'NONCE_KEY', 'a' );
define( 'AUTH_SALT', 'a' ); define( 'SECURE_AUTH_SALT', 'a' ); define( 'LOGGED_IN_SALT', 'a' ); define( 'NONCE_SALT', 'a' );
\$table_prefix = 'wp_';
if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }
require_once ABSPATH . 'wp-settings.php';
PHP
cp "$HERE/router.php" "$WP/router.php"
mkdir -p "$HERE/out"; : > "$HERE/out/php-errors.log"
php "$HERE/provision.php" "$WP"
echo "WordPress ready in $WP (http://127.0.0.1:$PORT)"
