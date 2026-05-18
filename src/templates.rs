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
                title { "TKAWEN — البنية الرقمية السيادية للجزائر" }
                meta name="description" content="TKAWEN. البنية التحتية السيادية التي تشغّل الاقتصاد الرقمي الجزائري — الهوية، الاتصال، الدفع، التجارة، المعرفة، اللوجستيك، السحابة للمطوّرين. بياناتك في الجزائر، فوترتك بالدينار.";
                link rel="canonical" href="https://tkawen.com/";
                link rel="icon" type="image/svg+xml" href="/favicon.svg";
                // Cairo (Arabic) + JetBrains Mono (code)
                link rel="preconnect" href="https://fonts.googleapis.com";
                link rel="preconnect" href="https://fonts.gstatic.com" crossorigin;
                link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap";
                meta property="og:type" content="website";
                meta property="og:locale" content="ar_DZ";
                meta property="og:title" content="TKAWEN — Sovereign Cloud for Algeria";
                meta property="og:description" content="البنية التحتية السيادية للاقتصاد الرقمي الجزائري.";
                meta property="og:url" content="https://tkawen.com/";
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
                    (sovereign_section())
                    (code_section())
                    (pricing_section())
                    (oss_section())
                    (trust_section())
                    (cta_section())
                }
                (footer(render_us))
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
                    a href="#stack" { "البنية" }
                    a href="#apps" { "التطبيقات" }
                    a href="#sovereign" { "السيادة" }
                    a href="https://developer.tkawen.com" { "للمطوّرين" }
                    a href="#pricing" { "الأسعار" }
                }
                div.nav-cta {
                    a.btn-ghost href="https://id.tkawen.com" { "دخول" }
                    a.btn-primary href="#cta" { "ابدأ مجاناً" }
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
                    span { "البنية الرقمية السيادية للجزائر" }
                }
                h1.hero-title {
                    span.hero-title-line1 { "البنية الرقمية" }
                    " "
                    span.hero-title-accent { "التي تعتمد عليها الجزائر" }
                }
                p.hero-sub {
                    "سبع طبقات بنية تحتية متكاملة — الهوية، الاتصال، الدفع، التجارة، المعرفة، اللوجستيك، السحابة للمطوّرين. "
                    strong { "بياناتك تبقى هنا. فوترتك بالدينار. واجهتك بالعربية." }
                }
                div.hero-cta {
                    a.btn-primary.btn-lg href="#cta" {
                        "احصل على API key مجاني"
                        span.arrow { (svg_inline(ICON_ARROW_LEFT)) }
                    }
                    a.btn-ghost.btn-lg href="#stack" {
                        "اِكتشف الطبقات السبع"
                    }
                }
                div.hero-trust {
                    span { "موثوقة من" }
                    span.trust-item { "Décret 20-254" }
                    span.trust-sep { "·" }
                    span.trust-item { "ASEP" }
                    span.trust-sep { "·" }
                    span.trust-item { "D-U-N-S 353551313" }
                    span.trust-sep { "·" }
                    span.trust-item { "NIF · R/C" }
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
                span.eyebrow { "البنية" }
                h2 { "سبع طبقات. سيادة كاملة. منصّة واحدة." }
                p { "كلّ ما يحتاجه مؤسّس جزائري لبناء منتج رقمي — في مكان واحد، خلف API واحدة." }
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
                            span.replaces-label { "يحلّ محلّ" }
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
                span.eyebrow { "التطبيقات" }
                h2 { "البنية تثبت نفسها — منتجات حقيقية تعمل اليوم." }
                p { "أربعة منتجات من إنتاجنا، مبنيّة فوق الطبقات السبع، تخدم عملاء يدفعون اليوم." }
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
                            span.built-label { "مبني على:" }
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
                    span.eyebrow { "للمطوّرين" }
                    h2 { "سطر واحد. سيادة كاملة." }
                    p { "تكامل بـ API REST بسيطة، SDKs بـ 4 لغات (PHP · Node · Python · Go)، مفتاح مجاني، وثائق بالعربية والإنجليزية والفرنسية." }
                    ul.code-features {
                        li { span.check { (svg_inline(ICON_CHECK)) } " sandbox مجاني بلا بطاقة" }
                        li { span.check { (svg_inline(ICON_CHECK)) } " فوترة عند الاستخدام، بالدينار" }
                        li { span.check { (svg_inline(ICON_CHECK)) } " SDKs على GitHub — مفتوحة المصدر" }
                        li { span.check { (svg_inline(ICON_CHECK)) } " status.tkawen.com — مراقبة 14 خدمة" }
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
                            span.c-comment { "# Issue a video room (LIQAA Cloud)" } "\n"
                            span.c-keyword { "curl" } " -X " span.c-string { "POST" } " https://api.tkawen.com/v1/connect/rooms \\\n"
                            "  -H " span.c-string { "\"Authorization: Bearer $TKAWEN_KEY\"" } " \\\n"
                            "  -H " span.c-string { "\"Content-Type: application/json\"" } " \\\n"
                            "  -d " span.c-string { "'{\"name\":\"reunion-strategique\",\"max\":12}'" } "\n\n"
                            span.c-comment { "# → { \"room_id\":\"rm_8x2k…\", \"sdk_token\":\"eyJ…\" }" }
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
                p.logos-eyebrow { "موثوقة من" }
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
                span.eyebrow { "ماذا يقولون" }
                h2 { "بنية تخدم الناس فعلاً — لا فقط في صفحات هبوط." }
                p { "اقتباسات حقيقيّة من العملاء والمستخدمين الذين يستعملون TKAWEN كلّ يوم." }
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
                span.eyebrow { "مقارنة" }
                h2 { "ما الذي يميّز TKAWEN عمّا يستخدمه الجميع؟" }
                p { "البنية الأمريكيّة والأوروبيّة ليست سيّئة — لكنّها لم تُصمَّم لمؤسّس جزائريّ. الفرق العمليّ ليس في التقنيّة، بل في الجغرافيا والقانون والعملة." }
            }
            div.compare-wrap {
                table.compare-table {
                    thead {
                        tr {
                            th.feature-col scope="col" { "الميّزة" }
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
                span.eyebrow { "الأسعار" }
                h2 { "ادفع عند الاستهلاك. بالدينار." }
                p { "ابدأ مجاناً، لا توجد بطاقة مطلوبة. ادفع فقط حين تنطلق منصّتك. لا عقود سنويّة مفروضة، لا فوترة دولاريّة، لا مفاجآت." }
            }
            div.tiers {
                @for tier in TIERS {
                    article.tier .(if tier.highlighted { "tier-highlight" } else { "" }) {
                        @if tier.highlighted {
                            div.tier-badge { "الأكثر اختياراً" }
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
                "كلّ الباقات تشمل البنية السياديّة (DZ data residency) + الفوترة بالدينار + الدعم الفنّي بالعربية."
            }
        }
    }
}

fn oss_section() -> Markup {
    html! {
        section #opensource .oss {
            div.section-head {
                span.eyebrow { "مفتوح المصدر" }
                h2 { "نُري الشيفرة. لا أقفال صامتة." }
                p { "كلّ SDK، كلّ أداة، كلّ موقع تسويقيّ — متاح للقراءة على GitHub. لأنّ السيادة الحقيقيّة لا تختبئ خلف صندوق أسود." }
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
                    "اِعرض كلّ المستودعات على GitHub"
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
                h2 { "ابنِ على الجزائر. ابنِ على TKAWEN." }
                p { "افتح حساباً مجاناً، احصل على API key، وابدأ في الاستدعاء خلال خمس دقائق." }
                div.cta-buttons {
                    a.btn-primary.btn-lg href="https://id.tkawen.com/signup" {
                        "أنشئ حسابك المجاني"
                        span.arrow { (svg_inline(ICON_ARROW_LEFT)) }
                    }
                    a.btn-ghost.btn-lg href="mailto:DIRECTION@takawen.dz" {
                        "أو احجز مكالمة 30 دقيقة"
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
                            "البنية الرقمية السيادية للجزائر — منذ 2026."
                        }
                        p.footer-legal {
                            "Décret 20-254 · Arrêté 1275 MESRS · ASEP"
                            br;
                            "D-U-N-S 353551313 · NIF · R/C"
                        }
                    }
                    div.footer-col {
                        h4 { "البنية" }
                        ul {
                            @for p in PILLARS {
                                li { a href=(format!("https://{}.tkawen.com", p.slug)) { (p.name_ar) } }
                            }
                        }
                    }
                    div.footer-col {
                        h4 { "التطبيقات" }
                        ul {
                            @for a_app in APPS {
                                li { a href=(format!("https://{}", a_app.domain)) { (a_app.name) } }
                            }
                        }
                    }
                    div.footer-col {
                        h4 { "للمطوّرين" }
                        ul {
                            li { a href="https://developer.tkawen.com" { "وثائق API" } }
                            li { a href="https://status.tkawen.com" { "حالة الخدمات" } }
                            li { a href="https://github.com/hartemyaakoub" { "GitHub" } }
                            li { a href="https://catalogue.tkawen.com" { "الكاتالوغ التكويني" } }
                            li { a href="https://brand.tkawen.com" { "نظام الهوية البصرية" } }
                        }
                    }
                    div.footer-col {
                        h4 { "الشركة" }
                        ul {
                            li { a href="https://hartem.tkawen.com" { "عن المؤسس" } }
                            li { a href="mailto:DIRECTION@takawen.dz" { "تواصل" } }
                            li { a href="https://tkawen.com/legal" { "الشروط القانونية" } }
                            li { a href="https://tkawen.com/privacy" { "سياسة الخصوصية" } }
                        }
                    }
                }
                div.footer-bottom {
                    div.footer-perf {
                        "هذه الصفحة رُندِرت في "
                        strong { (format!("{}μs", render_us)) }
                        " بـ Rust + Axum + Maud."
                    }
                    div.footer-copy {
                        "© 2026 TKAWEN — HARTEM YAAKOUB · "
                        a href="https://github.com/hartemyaakoub/tkawen-com" { "مفتوح المصدر AGPL-3.0" }
                    }
                }
            }
        }
    }
}
