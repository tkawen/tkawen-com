//! Maud templates for tkawen.com — Sovereign cloud for Algeria.

use crate::content::{
    APPS, COMPARE, CREDENTIALS, ICON_ARROW_LEFT, ICON_CHECK, LOGOS, Mark, METRICS, PILLARS,
    PROMISES, REPOS, TESTIMONIALS, TIERS,
};
use crate::schemas;
use maud::{html, Markup, PreEscaped, DOCTYPE};

const CSS: &str = include_str!("../assets/styles.css");

/// Wrap raw SVG inner content in a stroke-styled 24×24 SVG element.
fn svg_icon(inner: &'static str) -> Markup {
    html! {
        svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {
            (PreEscaped(inner))
        }
    }
}

/// Smaller inline icon (matches text baseline).
fn svg_inline(inner: &'static str) -> Markup {
    html! {
        svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="icon-inline" aria-hidden="true" {
            (PreEscaped(inner))
        }
    }
}

pub fn page(render_us: u128) -> Markup {
    html! {
        (DOCTYPE)
        html lang="ar" dir="rtl" {
            head {
                meta charset="utf-8";
                meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover";
                meta name="color-scheme" content="dark light";
                meta name="theme-color" content="#0a0e1a";
                title { "TKAWEN — Seven APIs. One Platform. Build anything." }
                meta name="description" content="TKAWEN is a unified cloud platform: Identity, Connect, Pay, Commerce, Knowledge, Logistics, Developer — seven APIs that ship together, bill together, and integrate together. 200+ live merchants, 4,116+ verified users, 22 open-source repos.";
                link rel="canonical" href="https://tkawen.com/";
                link rel="icon" type="image/svg+xml" href="/favicon.svg";
                link rel="preconnect" href="https://fonts.googleapis.com";
                link rel="preconnect" href="https://fonts.gstatic.com" crossorigin;
                link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap";
                meta property="og:type" content="website";
                meta property="og:locale" content="en";
                meta property="og:title" content="TKAWEN — Seven APIs. One Platform.";
                meta property="og:description" content="A unified cloud platform: Identity, Connect, Pay, Commerce, Knowledge, Logistics, Developer. Seven APIs that ship together.";
                meta property="og:url" content="https://tkawen.com/";
                meta property="og:image" content="https://tkawen.com/og.svg";
                meta property="og:image:width" content="1200";
                meta property="og:image:height" content="630";
                meta property="og:image:alt" content="TKAWEN — Sovereign Cloud for Algeria";
                meta name="twitter:card" content="summary_large_image";
                meta name="twitter:title" content="TKAWEN — Sovereign Cloud for Algeria";
                meta name="twitter:description" content="7 طبقات بنية تحتية سياديّة جزائريّة، بالدينار، بالعربية.";
                meta name="twitter:image" content="https://tkawen.com/og.svg";
                link rel="manifest" href="/manifest.webmanifest";
                script type="application/ld+json" { (PreEscaped(schemas::organization_jsonld())) }
                script type="application/ld+json" { (PreEscaped(schemas::services_jsonld())) }
                style { (PreEscaped(CSS)) }
            }
            body {
                (nav())
                main {
                    (hero())
                    (logos_strip())
                    (metrics_strip())
                    (stack_section())
                    (comparison_section())
                    (apps_section())
                    (testimonials_section())
                    (code_section())
                    (pricing_section())
                    (oss_section())
                    (cta_section())
                }
                (footer(render_us))
                script {
                    (PreEscaped("if('serviceWorker' in navigator){window.addEventListener('load',()=>navigator.serviceWorker.register('/service-worker.js').catch(()=>{}));}"))
                }
            }
        }
    }
}

pub fn not_found_page() -> Markup {
    html! {
        (DOCTYPE)
        html lang="en" dir="ltr" {
            head {
                meta charset="utf-8";
                meta name="viewport" content="width=device-width, initial-scale=1";
                meta name="theme-color" content="#0a0e1a";
                title { "404 — Page not found · TKAWEN" }
                meta name="robots" content="noindex";
                link rel="icon" type="image/svg+xml" href="/favicon.svg";
                link rel="preconnect" href="https://fonts.googleapis.com";
                link rel="preconnect" href="https://fonts.gstatic.com" crossorigin;
                link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;800;900&display=swap";
                style { (PreEscaped(CSS)) }
                style { ".nf-wrap{min-height:100vh;display:grid;place-items:center;padding:24px}.nf-card{max-width:520px;text-align:center}.nf-code{font-family:var(--font-mono);font-size:clamp(80px,15vw,160px);font-weight:900;line-height:1;background:linear-gradient(135deg,var(--accent),#fbbf24,var(--accent));-webkit-background-clip:text;background-clip:text;color:transparent;letter-spacing:-.05em;margin-bottom:18px}.nf-title{font-size:clamp(20px,3vw,28px);font-weight:800;color:var(--white);margin-bottom:14px}.nf-body{color:var(--white-dim);font-size:16px;line-height:1.7;margin-bottom:32px}.nf-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}" }
            }
            body {
                (nav())
                div.nf-wrap {
                    div.nf-card {
                        div.nf-code { "404" }
                        h1.nf-title { "This page doesn't exist." }
                        p.nf-body {
                            "The link may be old, mistyped, or refer to something that hasn't been written yet. Everything else is on the "
                            a href="/" style="color:var(--accent)" { "homepage" }
                            "."
                        }
                        div.nf-actions {
                            a.btn-primary href="/" { "Back to home" }
                            a.btn-ghost href="https://developer.tkawen.com" { "API docs" }
                        }
                    }
                }
            }
        }
    }
}

fn nav() -> Markup {
    html! {
        header.nav {
            div.nav-inner {
                a.brand href="/" {
                    span.brand-mark { "T" }
                    span.brand-text { "TKAWEN" }
                }
                nav.nav-links {
                    a href="#stack" { "Platform" }
                    a href="#apps" { "Apps" }
                    a href="#pricing" { "Pricing" }
                    a href="https://developer.tkawen.com" { "Docs" }
                    a href="#opensource" { "Open Source" }
                }
                div.nav-cta {
                    a.btn-ghost href="https://id.tkawen.com" { "Sign in" }
                    a.btn-primary href="#cta" { "Get started" }
                }
            }
        }
    }
}

fn hero() -> Markup {
    html! {
        section.hero {
            div.hero-bg {
                div.hero-mesh {}
                div.hero-grid {}
            }
            div.hero-inner {
                div.hero-eyebrow {
                    span.dot {}
                    span { "Seven APIs. One platform. Build anything." }
                }
                h1.hero-title {
                    span.hero-title-line1 { "The unified API for" }
                    " "
                    span.hero-title-accent { "product teams." }
                }
                p.hero-sub {
                    "Identity, Connect, Pay, Commerce, Knowledge, Logistics, Developer — seven cloud APIs that ship together, bill together, and integrate together. "
                    strong { "One SDK, one key, one dashboard. Build globally from day one." }
                }
                div.hero-cta {
                    a.btn-primary.btn-lg href="#cta" {
                        "Get a free API key"
                        span.arrow { (svg_inline(ICON_ARROW_LEFT)) }
                    }
                    a.btn-ghost.btn-lg href="https://developer.tkawen.com" {
                        "Read the docs"
                    }
                }
                div.hero-trust {
                    span { "Powering" }
                    span.trust-item { "200+ live merchants" }
                    span.trust-sep { "·" }
                    span.trust-item { "4,116+ verified users" }
                    span.trust-sep { "·" }
                    span.trust-item { "22 OSS repos" }
                    span.trust-sep { "·" }
                    span.trust-item { "4 SDKs" }
                }
            }
        }
    }
}

fn metrics_strip() -> Markup {
    html! {
        section.metrics {
            div.metrics-inner {
                @for m in METRICS {
                    div.metric {
                        div.metric-value { (m.value) }
                        div.metric-label { (m.label_ar) }
                    }
                }
            }
        }
    }
}

fn stack_section() -> Markup {
    html! {
        section #stack .stack {
            div.section-head {
                span.eyebrow { "Platform" }
                h2 { "Seven services. One platform. One key." }
                p { "Everything a product team needs to ship, served behind a single unified API with one SDK, one dashboard, and one invoice." }
            }
            div.stack-grid {
                @for pillar in PILLARS {
                    article.pillar {
                        div.pillar-head {
                            div.pillar-num { (format!("{:02}", pillar.number)) }
                            div.pillar-icon { (svg_icon(pillar.icon)) }
                            div.pillar-status style=(format!("color: {}", pillar.status.color())) {
                                span.status-dot style=(format!("background: {}", pillar.status.color())) {}
                                (pillar.status.label_ar())
                            }
                        }
                        h3.pillar-name { (pillar.name_ar) }
                        p.pillar-tagline { (pillar.tagline_ar) }
                        div.pillar-replaces {
                            span.replaces-label { "Replaces" }
                            @for (i, r) in pillar.replaces.iter().enumerate() {
                                @if i > 0 { span.replaces-sep { "·" } }
                                span.replaces-item { (r) }
                            }
                        }
                        a.pillar-link href=(format!("https://{}.tkawen.com", pillar.slug)) {
                            (format!("{}.tkawen.com", pillar.slug))
                            span.arrow { (svg_inline(ICON_ARROW_LEFT)) }
                        }
                    }
                }
            }
        }
    }
}

fn apps_section() -> Markup {
    html! {
        section #apps .apps {
            div.section-head {
                span.eyebrow { "Reference apps" }
                h2 { "Four products built on the platform." }
                p { "First-party SaaS products that prove every API works at production scale, with real customers and real workloads." }
            }
            div.apps-grid {
                @for app in APPS {
                    article.app-card {
                        div.app-head {
                            h3.app-name { (app.name) }
                            span.app-status { (app.status_ar) }
                        }
                        p.app-tagline { (app.tagline_ar) }
                        div.app-domain {
                            span.dot.green {}
                            (app.domain)
                        }
                        div.app-built {
                            span.built-label { "Built on" }
                            @for n in app.built_on {
                                span.built-chip { (format!("{:02}", n)) }
                            }
                        }
                    }
                }
            }
        }
    }
}

fn sovereign_section() -> Markup {
    html! {
        section #sovereign .sovereign {
            div.sovereign-bg {}
            div.section-head.light {
                span.eyebrow.gold { "وعد السيادة" }
                h2 { "بنية لا تخدم سياسة دولة أخرى." }
                p { "لأنّ السحابة الأمريكية والأوروبية ليست محايدة — والاقتصاد الجزائري الرقمي يستحقّ ركيزة مستقلّة." }
            }
            div.promises-grid {
                @for p in PROMISES {
                    article.promise {
                        div.promise-icon { (svg_icon(p.icon)) }
                        h3.promise-title { (p.title_ar) }
                        p.promise-body { (p.body_ar) }
                    }
                }
            }
        }
    }
}

fn code_section() -> Markup {
    html! {
        section.code {
            div.code-inner {
                div.code-text {
                    span.eyebrow { "For developers" }
                    h2 { "One line. One key. Every service." }
                    p { "REST API documented with OpenAPI 3.1. Four official SDKs (JavaScript, PHP, Python, Go) under MIT. Free sandbox identical to production. Documentation in three languages." }
                    ul.code-features {
                        li { span.check { (svg_inline(ICON_CHECK)) } " Free sandbox, no credit card required" }
                        li { span.check { (svg_inline(ICON_CHECK)) } " Pay-as-you-go billing in 13 currencies" }
                        li { span.check { (svg_inline(ICON_CHECK)) } " All SDKs open source on GitHub (MIT)" }
                        li { span.check { (svg_inline(ICON_CHECK)) } " Public status page with per-pillar uptime" }
                    }
                    a.btn-ghost href="https://developer.tkawen.com" {
                        "developer.tkawen.com"
                        span.arrow { (svg_inline(ICON_ARROW_LEFT)) }
                    }
                }
                div.code-block dir="ltr" {
                    div.code-header {
                        span.code-dot.red {}
                        span.code-dot.amber {}
                        span.code-dot.green {}
                        span.code-title { "curl" }
                    }
                    pre {
                        code {
                            span.c-comment { "# Create a video room" } "\n"
                            span.c-keyword { "curl" } " -X " span.c-string { "POST" } " https://api.tkawen.com/v1/connect/rooms \\\n"
                            "  -H " span.c-string { "\"Authorization: Bearer $TKAWEN_KEY\"" } " \\\n"
                            "  -H " span.c-string { "\"Content-Type: application/json\"" } " \\\n"
                            "  -d " span.c-string { "'{\"name\":\"team-standup\",\"max_participants\":12}'" } "\n\n"
                            span.c-comment { "# → { \"room_id\":\"rm_8x2k…\", \"join_url\":\"https://meet…\" }" }
                        }
                    }
                }
            }
        }
    }
}

fn logos_strip() -> Markup {
    html! {
        section.logos-strip {
            div.logos-inner {
                p.logos-eyebrow { "Powering" }
                div.logos-row {
                    @for entry in LOGOS {
                        div.logo-item {
                            span.logo-name { (entry.name) }
                            span.logo-sub { (entry.subtitle_ar) }
                        }
                    }
                }
            }
        }
    }
}

fn testimonials_section() -> Markup {
    // Duplicate the testimonials so the marquee loops seamlessly.
    html! {
        section.testimonials {
            div.section-head {
                span.eyebrow { "What customers say" }
                h2 { "Built for teams that ship." }
                p { "Real quotes from teams using TKAWEN in production today across pharmacy, e-commerce, education, logistics, and developer tooling." }
            }
            div.marquee-wrap {
                div.marquee {
                    @for t in TESTIMONIALS {
                        article.testimonial {
                            div.t-quote-mark { "“" }
                            p.t-quote { (t.quote_ar) }
                            div.t-footer {
                                div.t-author { (t.author) }
                                div.t-role { (t.role_ar) " · " span.t-product { (t.product) } }
                            }
                        }
                    }
                    @for t in TESTIMONIALS {
                        article.testimonial aria-hidden="true" {
                            div.t-quote-mark { "“" }
                            p.t-quote { (t.quote_ar) }
                            div.t-footer {
                                div.t-author { (t.author) }
                                div.t-role { (t.role_ar) " · " span.t-product { (t.product) } }
                            }
                        }
                    }
                }
            }
        }
    }
}

fn mark_cell(m: &Mark) -> Markup {
    match m {
        Mark::Yes => html! { span.mark.mark-yes title="نعم" {
            svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" {
                polyline points="20 6 9 17 4 12" {}
            }
        } },
        Mark::No => html! { span.mark.mark-no title="لا" {
            svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" {
                line x1="18" y1="6" x2="6" y2="18" {}
                line x1="6" y1="6" x2="18" y2="18" {}
            }
        } },
        Mark::Partial => html! { span.mark.mark-partial title="جزئيّ" {
            svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" {
                line x1="5" y1="12" x2="19" y2="12" {}
            }
        } },
        Mark::Text(t) => html! { span.mark.mark-text { (t) } },
    }
}

fn comparison_section() -> Markup {
    html! {
        section #compare .compare {
            div.section-head {
                span.eyebrow { "How it compares" }
                h2 { "Built as one platform, not seven point solutions." }
                p { "Most teams glue together five or six SaaS products to build a real application. TKAWEN ships them as one platform with one key, one SDK, and one invoice." }
            }
            div.compare-wrap {
                table.compare-table {
                    thead {
                        tr {
                            th.feature-col scope="col" { "Feature" }
                            th.tkawen-col scope="col" {
                                span.brand-mark { "T" }
                                span { "TKAWEN" }
                            }
                            th scope="col" { "AWS" }
                            th scope="col" { "Twilio" }
                            th scope="col" { "Stripe" }
                        }
                    }
                    tbody {
                        @for row in COMPARE {
                            tr {
                                th.feature-col scope="row" { (row.feature_ar) }
                                td.tkawen-col { (mark_cell(&row.tkawen)) }
                                td { (mark_cell(&row.aws)) }
                                td { (mark_cell(&row.twilio)) }
                                td { (mark_cell(&row.stripe)) }
                            }
                        }
                    }
                }
            }
        }
    }
}

fn pricing_section() -> Markup {
    html! {
        section #pricing .pricing {
            div.section-head {
                span.eyebrow { "Pricing" }
                h2 { "Free to start. Pay as you grow." }
                p { "Sandbox is free forever, no card. Builder is metered per call, billed monthly in your preferred currency. Enterprise is built for scale, regulated industries, and dedicated infrastructure." }
            }
            div.tiers {
                @for tier in TIERS {
                    article.tier .(if tier.highlighted { "tier-highlight" } else { "" }) {
                        @if tier.highlighted {
                            div.tier-badge { "Most popular" }
                        }
                        h3.tier-name { (tier.name_ar) }
                        div.tier-price {
                            span.tier-amount { (tier.price_ar) }
                            span.tier-note { (tier.price_note_ar) }
                        }
                        p.tier-tagline { (tier.tagline_ar) }
                        ul.tier-features {
                            @for f in tier.features_ar {
                                li {
                                    span.check { (svg_inline(ICON_CHECK)) }
                                    (f)
                                }
                            }
                        }
                        a.tier-cta href=(tier.cta_href) {
                            (tier.cta_ar)
                            span.arrow { (svg_inline(ICON_ARROW_LEFT)) }
                        }
                    }
                }
            }
            p.pricing-note {
                "Every tier includes all 7 pillars, all 4 SDKs, OpenAPI spec, and full multi-currency invoicing."
            }
        }
    }
}

fn oss_section() -> Markup {
    html! {
        section #opensource .oss {
            div.section-head {
                span.eyebrow { "Open source" }
                h2 { "Every SDK and tool is on GitHub." }
                p { "Inspect the code. Fork the SDKs. Run the reference apps locally. The platform's source of truth is public, because lock-in is a UX failure, not a moat." }
            }
            div.oss-grid {
                @for r in REPOS {
                    a.repo-card href=(format!("https://github.com/{}/{}", r.owner, r.name)) {
                        div.repo-head {
                            div.repo-icon {
                                svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" {
                                    path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0 -.94 -2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0 -7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0 -1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22" {}
                                }
                            }
                            div.repo-meta {
                                div.repo-owner { (r.owner) "/" }
                                div.repo-name { (r.name) }
                            }
                        }
                        p.repo-tagline { (r.tagline_ar) }
                        div.repo-footer {
                            span.repo-lang { (r.language) }
                            span.repo-license { (r.license) }
                        }
                    }
                }
            }
            div.oss-cta {
                a.btn-ghost href="https://github.com/hartemyaakoub" {
                    "Browse all repositories on GitHub"
                    span.arrow { (svg_inline(ICON_ARROW_LEFT)) }
                }
            }
        }
    }
}

fn trust_section() -> Markup {
    html! {
        section #trust .trust {
            div.trust-inner {
                div.trust-text {
                    span.eyebrow.gold { "الموثوقيّة" }
                    h2 { "موثَّقون قانونياً. منذ اليوم الأوّل." }
                    p {
                        "لسنا startup بلا هويّة قانونيّة. كلّ صفقة، كلّ خدمة، كلّ التزام — مغطّى بأوراق رسميّة جزائريّة ودوليّة. هذا ما يجعل البنوك والوزارات والجامعات قادرة على العمل معنا فوراً، بدون احتكاك قانونيّ."
                    }
                }
                div.creds-grid {
                    @for c in CREDENTIALS {
                        div.cred {
                            div.cred-value { (c.value) }
                            div.cred-label { (c.label_ar) }
                            div.cred-authority { (c.authority_ar) }
                        }
                    }
                }
            }
        }
    }
}

fn cta_section() -> Markup {
    html! {
        section #cta .cta {
            div.cta-inner {
                h2 { "Start building in 60 seconds." }
                p { "Create a free account, get a sandbox API key, and ship your first integration today." }
                div.cta-buttons {
                    a.btn-primary.btn-lg href="https://id.tkawen.com/signup" {
                        "Create a free account"
                        span.arrow { (svg_inline(ICON_ARROW_LEFT)) }
                    }
                    a.btn-ghost.btn-lg href="mailto:DIRECTION@takawen.dz" {
                        "Or book a 30-minute call"
                    }
                }
            }
        }
    }
}

fn footer(render_us: u128) -> Markup {
    html! {
        footer.footer {
            div.footer-inner {
                div.footer-cols {
                    div.footer-col {
                        div.footer-brand {
                            span.brand-mark { "T" }
                            " TKAWEN"
                        }
                        p.footer-tagline {
                            "Seven cloud APIs. One platform. Ship anywhere."
                        }
                    }
                    div.footer-col {
                        h4 { "Platform" }
                        ul {
                            @for p in PILLARS {
                                li { a href=(format!("https://{}.tkawen.com", p.slug)) { (p.name_en) } }
                            }
                        }
                    }
                    div.footer-col {
                        h4 { "Apps" }
                        ul {
                            @for a_app in APPS {
                                li { a href=(format!("https://{}", a_app.domain)) { (a_app.name) } }
                            }
                        }
                    }
                    div.footer-col {
                        h4 { "Developers" }
                        ul {
                            li { a href="https://developer.tkawen.com" { "API docs" } }
                            li { a href="https://status.tkawen.com" { "Status" } }
                            li { a href="https://github.com/hartemyaakoub" { "GitHub" } }
                            li { a href="https://api.tkawen.com/openapi.json" { "OpenAPI spec" } }
                            li { a href="https://discord.gg/tkawen" { "Discord" } }
                        }
                    }
                    div.footer-col {
                        h4 { "Company" }
                        ul {
                            li { a href="#cta" { "Contact" } }
                            li { a href="https://hartem.tkawen.com" { "About" } }
                            li { a href="/legal" { "Terms" } }
                            li { a href="/privacy" { "Privacy" } }
                        }
                    }
                }
                div.footer-bottom {
                    div.footer-perf {
                        "This page rendered in "
                        strong { (format!("{}μs", render_us)) }
                        " · Rust + Axum + Maud"
                    }
                    div.footer-copy {
                        "© 2026 TKAWEN · "
                        a href="https://github.com/hartemyaakoub/tkawen-com" { "Open source AGPL-3.0" }
                    }
                }
            }
        }
    }
}
