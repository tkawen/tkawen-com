# Migration playbook: tkawen.online/paths → *.tkawen.com subdomains

## Current state (today)

Everything is deployed as **subpaths on `tkawen.online`** because:
- That's where your 3,827 user audience already exists (WordPress)
- That's where SMTP reputation is warmed (120k+ delivered emails)
- That's the only server I have FTP access to from this session

```
✓ https://tkawen.online/intel/     ← dashboard
✓ https://tkawen.online/try/       ← signup portal
✓ https://tkawen.online/tools/     ← 4 free tools
✓ https://tkawen.online/blog/      ← 2 pillar posts
✓ https://tkawen.online/id/        ← magic-link SSO
✓ https://tkawen.online/share/     ← referrals
✓ https://tkawen.online/embed/     ← partner iframes
✗ tkawen.com subdomains            ← 404 (not yet set up)
```

## Target state (your "nuclear reactor" vision)

Each piece on its own subdomain of `tkawen.com`:

```
https://intel.tkawen.com  ─┐
https://try.tkawen.com    ─┤
https://tools.tkawen.com  ─┤  All powered by the same backend
https://blog.tkawen.com   ─┤  on tkawen.online (FTP server)
https://id.tkawen.com     ─┤
https://share.tkawen.com  ─┤
https://embed.tkawen.com  ─┘
```

**The data + SMTP + WordPress stay on tkawen.online forever.** Subdomains are just front-doors.

## The 3 migration paths (pick one)

### Path 1 — Keep things on tkawen.online (zero work, today)
Just use the URLs you already have. Works for the campaign, works for SEO, works for everything.

**Pro:** Done. Zero infra change.
**Con:** "tkawen.online/intel/" doesn't read as "intel.tkawen.com" — less brand-clean.

### Path 2 — DNS CNAME (5 minutes, works for HTTP only)

Add CNAMEs in Cloudflare:
```
intel.tkawen.com.   CNAME   tkawen.online.
try.tkawen.com.     CNAME   tkawen.online.
tools.tkawen.com.   CNAME   tkawen.online.
blog.tkawen.com.    CNAME   tkawen.online.
id.tkawen.com.      CNAME   tkawen.online.
share.tkawen.com.   CNAME   tkawen.online.
embed.tkawen.com.   CNAME   tkawen.online.
```

**Pro:** Cheap, 5 minutes.
**Con:** Visitors will hit tkawen.online's TLS cert (no SAN for tkawen.com subdomains). Browser shows "wrong cert" error. Unusable.

### Path 3 — nginx reverse proxy on VPS40 (recommended, 30 minutes)

This is the right answer. Your tkawen.com Rust server (on VPS40) accepts the subdomain requests and proxies them to tkawen.online behind the scenes. Visitors see HTTPS on tkawen.com perfectly.

**Step 1:** Add DNS A records in Cloudflare (all → VPS40 IP):
```
intel.tkawen.com    A    <VPS40-IP>
try.tkawen.com      A    <VPS40-IP>
tools.tkawen.com    A    <VPS40-IP>
blog.tkawen.com     A    <VPS40-IP>
id.tkawen.com       A    <VPS40-IP>
share.tkawen.com    A    <VPS40-IP>
embed.tkawen.com    A    <VPS40-IP>
```

**Step 2:** SSH to VPS40, add nginx vhost (file: `/etc/nginx/sites-available/tkawen-reactor.conf`):

```nginx
# Reactor subdomain reverse proxy
# Routes intel/try/tools/blog/id/share/embed.tkawen.com to tkawen.online behind the scenes.
# Maps subdomain → path so URLs read clean (no /path in final URL).

upstream tkawen_online {
    server tkawen.online:443;
    keepalive 16;
}

# Map subdomain to upstream path
map $host $reactor_path {
    intel.tkawen.com   /intel;
    try.tkawen.com     /try;
    tools.tkawen.com   /tools;
    blog.tkawen.com    /blog;
    id.tkawen.com      /id;
    share.tkawen.com   /share;
    embed.tkawen.com   /embed;
    default            /;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name intel.tkawen.com try.tkawen.com tools.tkawen.com blog.tkawen.com
                id.tkawen.com share.tkawen.com embed.tkawen.com;

    ssl_certificate     /etc/letsencrypt/live/tkawen.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tkawen.com/privkey.pem;

    # Strong TLS
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;

    # HSTS (only after you're sure it works)
    # add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location / {
        # Rewrite to the right path on tkawen.online
        rewrite ^/(.*)$ $reactor_path/$1 break;

        proxy_pass https://tkawen_online;
        proxy_http_version 1.1;
        proxy_set_header Host tkawen.online;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;

        # Pass through Set-Cookie (so SSO cookie scoped .tkawen.online still works)
        proxy_cookie_domain tkawen.online .tkawen.com;
        proxy_cookie_path / /;

        # Connection reuse
        proxy_set_header Connection "";

        # Timeouts
        proxy_connect_timeout 10s;
        proxy_send_timeout 30s;
        proxy_read_timeout 30s;
    }

    # Block obvious nonsense
    location ~ /\. {
        deny all;
    }
}

# HTTP → HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name intel.tkawen.com try.tkawen.com tools.tkawen.com blog.tkawen.com
                id.tkawen.com share.tkawen.com embed.tkawen.com;
    return 301 https://$host$request_uri;
}
```

**Step 3:** Issue wildcard cert (if not already)
```bash
certbot certonly --webroot -w /var/www/letsencrypt \
    -d tkawen.com -d *.tkawen.com \
    --preferred-challenges dns
```
(Wildcard requires DNS challenge — Cloudflare API key needed for automation.)

**Step 4:** Enable + reload
```bash
ln -s /etc/nginx/sites-available/tkawen-reactor.conf /etc/nginx/sites-enabled/
nginx -t  # syntax check
systemctl reload nginx
```

**Step 5:** Update internal links in tkawen.online code
The PHP files currently use absolute paths like `/intel/capture.php`. Once you migrate, change them to `https://intel.tkawen.com/capture.php`. OR keep them as relative paths — they'll still work because the proxy rewrites `intel.tkawen.com/foo → tkawen.online/intel/foo`.

**Pro:** Production-grade, HTTPS works, clean brand URLs.
**Con:** 30 minutes of nginx work + DNS propagation wait.

## What I recommend

**For the next 30 days:** stay on tkawen.online/paths (Path 1). Focus on conversions, not infra.

**After first revenue:** migrate to Path 3 (nginx reverse proxy). The conversion numbers will be the same either way, but the brand consistency matters for enterprise prospects who'll judge the URL.

## Important — cookie scope changes

Currently cookies are scoped to `.tkawen.online`. After migration they should be scoped to `.tkawen.com`. The nginx `proxy_cookie_domain tkawen.online .tkawen.com;` directive handles this for HTTP responses, but you should also update the PHP `setcookie` calls in:
- `intel/capture.php` (line: `'domain' => '.tkawen.online'`)
- `id/verify.php` (same)
- `id/login.php` (same)
- `id/logout.php` (same)
- `share/index.php` (same)

Change all to `.tkawen.com` after migration.

## SEO impact (zero if done right)

If you migrate, set up 301 redirects FROM the old paths TO the new subdomains. Tools/blog posts already ranking on tkawen.online keep their rank.

```nginx
# Inside the tkawen.online vhost:
location ^~ /intel/ { return 301 https://intel.tkawen.com$request_uri; }
location ^~ /try/   { return 301 https://try.tkawen.com$request_uri; }
location ^~ /tools/ { return 301 https://tools.tkawen.com$request_uri; }
location ^~ /blog/  { return 301 https://blog.tkawen.com$request_uri; }
location ^~ /id/    { return 301 https://id.tkawen.com$request_uri; }
location ^~ /share/ { return 301 https://share.tkawen.com$request_uri; }
location ^~ /embed/ { return 301 https://embed.tkawen.com$request_uri; }
```

301s pass 90-100% of link equity to the new URL. Google figures it out within 1-4 weeks.

## TL;DR

| Question | Answer |
|----------|--------|
| Where is everything deployed today? | `tkawen.online/<path>/` |
| Why not tkawen.com subdomains? | I have FTP-only access; subdomains need SSH on VPS40 |
| Does the campaign work today? | YES — all features fully functional |
| Will SEO work today? | YES — tkawen.online has 5-year-old domain authority |
| Should you migrate? | Eventually yes, but not yet |
| When? | After first 10-50 paying merchants from this campaign |
| Estimated migration time? | 30 minutes of nginx config on VPS40 |
| Risk of staying on tkawen.online? | Zero from a technical standpoint |
