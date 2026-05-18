#!/usr/bin/env bash
# install.sh — one-shot installer for tkawen-com on VPS40 (or any Debian/Ubuntu).
# Run as root or via sudo.  Idempotent: safe to re-run.
#
# Usage:   sudo bash install.sh
# Prereq:  source tarball uploaded to /tmp/tkawen-com-src.tar.gz
#          (or run from inside the unpacked source directory)

set -euo pipefail

APP_NAME="tkawen-com"
APP_USER="tkawen"
APP_GROUP="tkawen"
APP_DIR="/opt/${APP_NAME}"
BIN_PATH="/usr/local/bin/${APP_NAME}"
SOURCE_DIR="${SOURCE_DIR:-$(pwd)}"
DOMAIN="${DOMAIN:-new.tkawen.com}"
ACME_EMAIL="${ACME_EMAIL:-DIRECTION@takawen.dz}"

step() { printf "\n\033[1;34m==> %s\033[0m\n" "$*"; }

step "1/8 — checking root"
if [[ $EUID -ne 0 ]]; then
    echo "Please run as root (sudo bash install.sh)" >&2
    exit 1
fi

step "2/8 — ensuring user '${APP_USER}' exists"
if ! id -u "${APP_USER}" >/dev/null 2>&1; then
    useradd --system --no-create-home --shell /usr/sbin/nologin "${APP_USER}"
fi

step "3/8 — ensuring Rust toolchain"
if ! command -v cargo >/dev/null 2>&1; then
    apt-get update -qq
    apt-get install -y curl build-essential pkg-config
    curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh -s -- -y --profile minimal --default-toolchain stable
    # shellcheck disable=SC1091
    source "$HOME/.cargo/env"
fi
export PATH="$HOME/.cargo/bin:$PATH"

step "4/8 — building release binary (this may take a few minutes)"
cd "${SOURCE_DIR}"
cargo build --release

step "5/8 — installing binary + working dir"
install -d -o "${APP_USER}" -g "${APP_GROUP}" "${APP_DIR}"
install -m 0755 "target/release/${APP_NAME}" "${BIN_PATH}"

step "6/8 — installing systemd unit"
install -m 0644 deploy-package/systemd/tkawen-com.service /etc/systemd/system/tkawen-com.service
systemctl daemon-reload
systemctl enable tkawen-com.service
systemctl restart tkawen-com.service
sleep 1
systemctl --no-pager --lines=8 status tkawen-com.service || true

step "7/8 — installing nginx vhost"
if [[ -d /etc/nginx/sites-available ]]; then
    install -m 0644 deploy-package/nginx/new.tkawen.com.conf /etc/nginx/sites-available/new.tkawen.com.conf
    ln -sf /etc/nginx/sites-available/new.tkawen.com.conf /etc/nginx/sites-enabled/new.tkawen.com.conf
    mkdir -p /var/www/letsencrypt
    nginx -t
    systemctl reload nginx
    echo "nginx vhost installed (HTTPS will fail until you run step 8)"
else
    echo "WARNING: /etc/nginx/sites-available not found — copy nginx/ manually." >&2
fi

step "8/8 — TLS certificate via certbot --webroot (per house rules — never --nginx for *.tkawen.com)"
if command -v certbot >/dev/null 2>&1; then
    certbot certonly --webroot -w /var/www/letsencrypt \
        --non-interactive --agree-tos --email "${ACME_EMAIL}" \
        -d "${DOMAIN}" || echo "certbot failed — re-run manually after DNS A record points to this VPS"
    systemctl reload nginx || true
else
    echo "certbot not installed — run: apt-get install -y certbot, then re-run step 8 manually."
fi

cat <<EOF

✅ DONE.

Verify:
  curl -I https://${DOMAIN}/
  curl -sS https://${DOMAIN}/healthz   # → "ok"

The systemd service:
  systemctl status tkawen-com
  journalctl -u tkawen-com -f

To update later:
  Upload new source, run: cd /opt/${APP_NAME} && cargo build --release && \\
    install -m 0755 target/release/${APP_NAME} ${BIN_PATH} && \\
    systemctl restart tkawen-com

Production tkawen.com remains UNTOUCHED until you switch DNS or nginx upstream.
EOF
