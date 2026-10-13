#!/usr/bin/env bash
# Real end-to-end test: real WordPress + the theme zip + the browser submits the forms + a real SMTP server catches the emails.
# Usage: tools/wp-e2e/run.sh   (needs network the first time: Composer and git, see setup.sh)
set -uo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(cd "$HERE/../.." && pwd)"
PORT="${WP_E2E_PORT:-8099}"
bash "$HERE/setup.sh" || exit 1
pkill -f "smtp_sink.py 2525" 2>/dev/null; pkill -f "php -S 127.0.0.1:$PORT" 2>/dev/null; sleep 0.5
rm -rf "$HERE/out/mail" "$HERE/out/smtp-down.flag"; mkdir -p "$HERE/out/mail"
python3 "$HERE/smtp_sink.py" 2525 "$HERE/out/mail" >"$HERE/out/sink.log" 2>&1 &
SINK=$!
(cd "$HERE/.wp" && PHP_CLI_SERVER_WORKERS=4 php -S 127.0.0.1:$PORT router.php >"$HERE/out/php-server.log" 2>&1) &
SERVER=$!
for i in $(seq 1 30); do curl -sf "http://127.0.0.1:$PORT/" >/dev/null && break; sleep 0.5; done
cd "$ROOT" && npx playwright test -c playwright.wp.config.js
CODE=$?
kill $SINK $SERVER 2>/dev/null; pkill -f "php -S 127.0.0.1:$PORT" 2>/dev/null
echo; echo "PHP errors logged by WordPress during the run: $(grep -c 'PHP ' "$HERE/out/php-errors.log" 2>/dev/null || echo 0)"
echo "Report: $HERE/out/REPORT.md"
exit $CODE
