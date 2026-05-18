# Contributing to tkawen-com

Thank you for considering a contribution! This repository powers the marketing site at [tkawen.com](https://tkawen.com) — the public face of TKAWEN.

## Quick start

```bash
git clone https://github.com/tkawen/tkawen-com.git
cd tkawen-com
cargo build --release
./target/release/tkawen-com
# → http://127.0.0.1:8088
```

You need a Rust toolchain (1.80+ recommended) and platform-appropriate linker. On Windows, see the README for LLVM-MinGW setup details.

## How to contribute

### Reporting bugs

Open an issue using the **Bug report** template. Include the commit hash, OS, and a minimal reproduction.

### Suggesting features

Open an issue using the **Feature request** template. Describe the problem before proposing a solution.

### Pull requests

1. Open an issue first for non-trivial changes — saves you work if the direction is wrong
2. Fork the repo and create a feature branch off `main`
3. Make your changes — follow existing code style
4. Run `cargo fmt`, `cargo clippy`, `cargo check`
5. Commit with a clear message (conventional commits welcome)
6. Open a PR using the template

Small fixes (typos, broken links) can go straight to a PR without an issue.

### Code style

- Rust files: 4-space indent, formatted with `cargo fmt`
- CSS: 2-space indent, kebab-case classes
- Maud templates: 4-space indent, lowercase HTML tags
- Markdown: ATX headings (`#`), sentence-case titles

## Project structure

```
src/
├── main.rs          # Axum server + routing
├── templates.rs     # Maud HTML templates
├── content.rs       # Static content data (pillars, apps, pricing, etc.)
└── schemas.rs       # JSON-LD schema generators

assets/
├── styles.css       # Embedded CSS
├── og.svg           # Open Graph image
├── manifest.webmanifest
├── service-worker.js
├── robots.txt
├── sitemap.xml
├── llms.txt / llms-full.txt
└── indexnow-key.txt
```

## Code of Conduct

This project adheres to a [Contributor Covenant Code of Conduct](./CODE_OF_CONDUCT.md). By participating you agree to uphold this code.

## Security

Do **not** open public issues for security vulnerabilities. See [SECURITY.md](./SECURITY.md).

## License

By contributing, you agree that your contributions will be licensed under the AGPL-3.0-or-later license. See [LICENSE](./LICENSE).
