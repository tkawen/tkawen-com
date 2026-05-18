# tkawen-com — Deploy Package

One-shot deploy to VPS40 (or any Debian/Ubuntu) on a fresh subdomain that **does not touch production tkawen.com**.

## What this deploys

- The Rust binary as a systemd service (`tkawen-com.service`) bound to `127.0.0.1:8088`
- nginx vhost at `new.tkawen.com` reverse-proxying to it
- Let's Encrypt TLS via `certbot --webroot` (per house rule: never `--nginx` for `*.tkawen.com`)

Production tkawen.com **stays running, untouched**, until you decide to switch.

## DNS prerequisite (one time)

Add an A record pointing `new.tkawen.com` → `173.212.235.93` (VPS40) in Hostinger DNS.

```
new.tkawen.com.   A   173.212.235.93   TTL 300
```

## Deploy steps

From your Windows workstation:

```powershell
cd D:\F\tkawen-com
powershell -ExecutionPolicy Bypass -File deploy-package\build-source-tarball.ps1
scp deploy-package\tkawen-com-src.tar.gz root@173.212.235.93:/tmp/
ssh root@173.212.235.93
```

On the VPS:

```bash
mkdir -p /tmp/tkawen-src
tar -xzf /tmp/tkawen-com-src.tar.gz -C /tmp/tkawen-src
cd /tmp/tkawen-src
sudo bash deploy-package/install.sh
```

The installer:
1. Creates the `tkawen` system user
2. Installs Rust toolchain (if missing)
3. Compiles `tkawen-com` in release mode
4. Installs the binary to `/usr/local/bin/tkawen-com`
5. Installs and starts the systemd unit
6. Installs the nginx vhost and reloads nginx
7. Issues a Let's Encrypt cert via webroot

## Verify

```bash
curl -I https://new.tkawen.com/
curl -sS https://new.tkawen.com/healthz       # → "ok"
curl -sS -D - https://new.tkawen.com/ | grep -i server-timing
# Expect: server-timing: render;dur=0.XXX
```

## Switch production over (when ready, MANUAL step — not in installer)

Two safe approaches:

**Option A — DNS swap (zero downtime if A record changes propagate):**
```
tkawen.com.       A   173.212.235.93   TTL 60   # already points here
```
Just change the nginx `server_name` in the new vhost from `new.tkawen.com` to `tkawen.com`, reload nginx, and you're live. Old prod content still served from the existing tkawen.com vhost until you remove it.

**Option B — nginx upstream swap (instant):**
Modify the existing `tkawen.com` vhost on the VPS to `proxy_pass http://127.0.0.1:8088;` instead of serving the old PHP/static content.

## Update later

```bash
# On workstation
powershell -ExecutionPolicy Bypass -File deploy-package\build-source-tarball.ps1
scp deploy-package\tkawen-com-src.tar.gz root@173.212.235.93:/tmp/

# On VPS
cd /opt/tkawen-com && tar -xzf /tmp/tkawen-com-src.tar.gz --strip-components=0
cargo build --release
install -m 0755 target/release/tkawen-com /usr/local/bin/tkawen-com
systemctl restart tkawen-com
```

## Rollback

```bash
systemctl stop tkawen-com
systemctl disable tkawen-com
rm /etc/systemd/system/tkawen-com.service
rm /etc/nginx/sites-enabled/new.tkawen.com.conf
systemctl reload nginx
```

Production tkawen.com is never touched by any of this.
