<div align="center">

<img src="https://raw.githubusercontent.com/tkawen/tkawen-com/main/assets/og.png" width="640" alt="TKAWEN — Seven APIs. One Platform. Build anything." />

# tkawen-com

**Marketing site for [tkawen.com](https://tkawen.com) — server-rendered in Rust.**

[![ci](https://github.com/tkawen/tkawen-com/actions/workflows/ci.yml/badge.svg)](https://github.com/tkawen/tkawen-com/actions/workflows/ci.yml)
[![License: AGPL-3.0](https://img.shields.io/badge/license-AGPL--3.0-blue.svg)](LICENSE)
[![Status](https://img.shields.io/badge/status-status.tkawen.com-10b981)](https://status.tkawen.com)
[![Discord](https://img.shields.io/badge/community-discord-5865f2)](https://discord.gg/tkawen)

</div>

---

## What this is

The public-facing marketing site at **[tkawen.com](https://tkawen.com)** — a single Rust binary that serves 15 sections of content, full JSON-LD schemas, PWA manifest, sitemap, robots, and `llms.txt` from one process.

The whole thing renders in **sub-millisecond** per request (verifiable via the `Server-Timing: render;dur=X.XXX` response header) and ships as a stripped 3.6 MB static binary.

## Stack

| Layer | Choice | Why |
|-------|--------|-----|
| HTTP server | [Axum](https://github.com/tokio-rs/axum) 0.7 | Async, modular, well-maintained |
| Templating | [Maud](https://github.com/lambda-fairy/maud) 0.26 | Compile-time HTML, zero runtime template overhead |
| Static assets | [rust-embed](https://github.com/pyrossh/rust-embed) | Single-binary deployment |
| Middleware | tower / tower-http | Compression, security headers, tracing |
| Async runtime | Tokio | Standard choice |

The binary itself is one file. No template runtime. No filesystem reads at request time. No DB.

## Quick start

```bash
git clone https://github.com/tkawen/tkawen-com.git
cd tkawen-com
cargo build --release
./target/release/tkawen-com
# → http://127.0.0.1:8088
```

Verify the sub-millisecond claim:

```bash
curl -sSI http://127.0.0.1:8088/ | grep server-timing
# server-timing: render;dur=0.119
```

## Endpoints

| Path | Purpose |
|------|---------|
| `/` | Full marketing homepage (15 sections) |
| `/healthz` | `ok` for load balancers |
| `/og.svg` | 1200×630 social-share image |
| `/favicon.svg` | Site icon |
| `/manifest.webmanifest` | PWA install manifest |
| `/service-worker.js` | Offline-first cache |
| `/robots.txt` | Crawler policy (explicitly allows 18 AI crawlers) |
| `/sitemap.xml` | URL index across all TKAWEN domains |
| `/llms.txt` | Short reference for AI agents |
| `/llms-full.txt` | Comprehensive reference for AI agents |

Plus a branded 404 page for any unknown path.

## Architecture

```
src/
├── main.rs          # Axum router, security headers, compression
├── templates.rs     # Maud HTML for every section + 404
├── content.rs       # PILLARS[7], APPS[4], TIERS[3], COMPARE, LOGOS, …
└── schemas.rs       # JSON-LD Organization + 8 Services

assets/
├── styles.css       # ~22 KB, embedded
├── og.svg           # social-share card
├── manifest.webmanifest
├── service-worker.js
├── robots.txt · sitemap.xml · llms.txt · llms-full.txt
└── indexnow-key.txt
```

## Deploy

The `deploy-package/` directory contains everything for a clean Debian/Ubuntu deploy:

- `nginx/new.tkawen.com.conf` — reverse-proxy vhost
- `systemd/tkawen-com.service` — hardened systemd unit (`NoNewPrivileges`, `ProtectSystem=strict`, …)
- `install.sh` — 8-step idempotent installer (creates user, installs Rust, builds, configures systemd + nginx + certbot)
- `build-source-tarball.ps1` — source bundler (Windows)

See [`deploy-package/README.md`](./deploy-package/README.md) for full instructions.

## Performance

Verified on the production VPS (8 vCPU / 32 GB RAM / 1 Gbps):

| Metric | Value |
|--------|-------|
| Render time per request | 119 – 280 μs |
| Memory (resident) | ~6 MB |
| Binary size (stripped + LTO) | 3.6 MB |
| Compile time (first build) | ~60 s |
| Compile time (incremental) | <1 s |
| HTML payload | ~106 KB |

## Contributing

See [CONTRIBUTING.md](./CONTRIBUTING.md) and our [Code of Conduct](./CODE_OF_CONDUCT.md).

For security disclosures, see [SECURITY.md](./SECURITY.md) — please do not open public issues for vulnerabilities.

## License

[AGPL-3.0-or-later](./LICENSE). If you operate a modified version of this software as a network service, you must offer the source to your users.

## Acknowledgments

Built on the shoulders of [Axum](https://github.com/tokio-rs/axum), [Maud](https://github.com/lambda-fairy/maud), [rust-embed](https://github.com/pyrossh/rust-embed), and the wider Rust async ecosystem. Thank you.
