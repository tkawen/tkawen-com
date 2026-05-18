# tkawen-com

Sovereign cloud for Algeria — the marketing surface of the TKAWEN platform.

- **Stack:** Rust + Axum + Maud + rust-embed
- **Render time:** sub-millisecond per request (verifiable via `Server-Timing` header)
- **Deploy:** single static binary, ~10MB
- **License:** AGPL-3.0-or-later

## Develop

```
cargo run
# → http://127.0.0.1:8088
```

## Build for production

```
cargo build --release
./target/release/tkawen-com
```

## Architecture

The site is positioned as **TKAWEN Sovereign Cloud** with 7 infrastructure pillars:

1. Identity (OIDC + KYC + Trust)
2. Connect (Video, Audio, SMS, Voice)
3. Pay (DZD-native, Chargily-integrated)
4. Commerce (e-commerce infrastructure)
5. Knowledge (LMS + Credentials)
6. Logistics (GPS + Carriers)
7. Developer (APIs + SDKs + Docs)

Plus 4 first-party applications that prove the stack works (PharmaPro, MyStoq, Algeria Certify, LIQAA Meet) and 1 academic programs surface (Catalogue).
