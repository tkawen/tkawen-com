//! Content data for tkawen.com — pillars, apps, programs, sovereign promises.

pub struct Pillar {
    pub number: u8,
    pub slug: &'static str,
    pub name_ar: &'static str,
    pub name_en: &'static str,
    pub tagline_ar: &'static str,
    pub replaces: &'static [&'static str],
    pub icon: &'static str, // raw SVG inner content (24x24 viewport)
    pub status: PillarStatus,
}

#[derive(PartialEq)]
pub enum PillarStatus {
    Live,
    Beta,
    Soon,
}

impl PillarStatus {
    pub fn label_ar(&self) -> &'static str {
        match self {
            PillarStatus::Live => "live",
            PillarStatus::Beta => "beta",
            PillarStatus::Soon => "soon",
        }
    }
    pub fn color(&self) -> &'static str {
        match self {
            PillarStatus::Live => "#10b981",
            PillarStatus::Beta => "#f59e0b",
            PillarStatus::Soon => "#64748b",
        }
    }
}

// All icons are raw SVG inner content; rendered via templates::svg_icon().
// Style: 24×24 viewport, stroke="currentColor" width=2, round caps + joins.

pub const PILLARS: &[Pillar] = &[
    Pillar {
        number: 1,
        slug: "identity",
        name_ar: "Identity",
        name_en: "Identity",
        tagline_ar: "OIDC SSO, KYC, trust signals, and unified accounts across every TKAWEN service and your own apps.",
        replaces: &["Auth0", "Okta", "Clerk"],
        icon: r#"<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0 1 16 0v1"/>"#,
        status: PillarStatus::Live,
    },
    Pillar {
        number: 2,
        slug: "connect",
        name_ar: "Connect",
        name_en: "Connect",
        tagline_ar: "Video rooms, voice, SMS, WhatsApp, email, and text-to-speech behind a single API.",
        replaces: &["Twilio", "Zoom", "SendGrid"],
        icon: r#"<path d="M21 11.5a8.38 8.38 0 0 1 -.9 3.8 8.5 8.5 0 0 1 -7.6 4.7 8.38 8.38 0 0 1 -3.8 -.9L3 21l1.9 -5.7a8.38 8.38 0 0 1 -.9 -3.8 8.5 8.5 0 0 1 4.7 -7.6 8.38 8.38 0 0 1 3.8 -.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>"#,
        status: PillarStatus::Live,
    },
    Pillar {
        number: 3,
        slug: "pay",
        name_ar: "Pay",
        name_en: "Pay",
        tagline_ar: "Accept cards, wallets, transfers and recurring billing in 13 currencies with one integration.",
        replaces: &["Stripe", "Paddle", "Recurly"],
        icon: r#"<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/>"#,
        status: PillarStatus::Beta,
    },
    Pillar {
        number: 4,
        slug: "commerce",
        name_ar: "Commerce",
        name_en: "Commerce",
        tagline_ar: "Multi-tenant storefronts, catalog, cart, checkout, and order orchestration as a service.",
        replaces: &["Shopify", "Square", "BigCommerce"],
        icon: r#"<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2 -2V6l-3 -4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1 -8 0"/>"#,
        status: PillarStatus::Live,
    },
    Pillar {
        number: 5,
        slug: "knowledge",
        name_ar: "Knowledge",
        name_en: "Knowledge",
        tagline_ar: "Courses, enrollments, AI tutors, and tamper-proof certificates with public QR verification.",
        replaces: &["Teachable", "Coursera", "Credly"],
        icon: r#"<path d="M22 10v6"/><path d="M2 10l10 -5 10 5 -10 5 -10 -5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6 -1.3 6 -3v-5"/>"#,
        status: PillarStatus::Live,
    },
    Pillar {
        number: 6,
        slug: "logistics",
        name_ar: "Logistics",
        name_en: "Logistics",
        tagline_ar: "Fleet GPS, geofences, multi-carrier shipping quotes and label generation in one call.",
        replaces: &["Onfleet", "Bringg", "ShipBob"],
        icon: r#"<rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>"#,
        status: PillarStatus::Live,
    },
    Pillar {
        number: 7,
        slug: "developer",
        name_ar: "Developer",
        name_en: "Developer",
        tagline_ar: "Unified gateway, 4 SDKs, OpenAPI spec, webhooks, sandboxes, and live status — all free.",
        replaces: &["AWS console", "Vercel", "Cloudflare"],
        icon: r#"<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>"#,
        status: PillarStatus::Beta,
    },
];

pub struct App {
    pub name: &'static str,
    pub domain: &'static str,
    pub tagline_ar: &'static str,
    pub status_ar: &'static str,
    pub built_on: &'static [u8], // pillar numbers
}

pub const APPS: &[App] = &[
    App {
        name: "PharmaPro",
        domain: "pharmapro.tkawen.com",
        tagline_ar: "Pharmacy ERP — POS, inventory, supplier invoices, tax e-invoicing.",
        status_ar: "production",
        built_on: &[1, 2, 3, 7],
    },
    App {
        name: "MyStoq",
        domain: "mystoq.com",
        tagline_ar: "Multi-tenant e-commerce platform powering 200+ live merchants.",
        status_ar: "production",
        built_on: &[1, 3, 4, 6, 7],
    },
    App {
        name: "Algeria Certify",
        domain: "algeriacertify.com",
        tagline_ar: "Credential issuance & public QR verification — 4,116+ practitioners.",
        status_ar: "production",
        built_on: &[1, 5, 7],
    },
    App {
        name: "LIQAA Meet",
        domain: "meet.liqaa.io",
        tagline_ar: "Open-source video meetings — AGPL-3.0, self-hostable.",
        status_ar: "open source",
        built_on: &[1, 2],
    },
];

pub struct SovereignPromise {
    pub icon: &'static str,
    pub title_ar: &'static str,
    pub body_ar: &'static str,
}

// Kept for compile compatibility — not rendered on the homepage.
pub const PROMISES: &[SovereignPromise] = &[];

pub struct Metric {
    pub value: &'static str,
    pub label_ar: &'static str,
}

pub const METRICS: &[Metric] = &[
    Metric { value: "7", label_ar: "API pillars" },
    Metric { value: "4", label_ar: "SDKs (JS · PHP · Python · Go)" },
    Metric { value: "200+", label_ar: "live merchants" },
    Metric { value: "4,116+", label_ar: "verified users" },
    Metric { value: "22", label_ar: "open-source repos" },
    Metric { value: "<1ms", label_ar: "page render time" },
];

/* ───────────────────────────────────────────────────────────────────
   COMPARISON — TKAWEN vs single-pillar incumbents
   ─────────────────────────────────────────────────────────────────── */

pub enum Mark {
    Yes,
    No,
    Partial,
    Text(&'static str),
}

pub struct CompareRow {
    pub feature_ar: &'static str,
    pub tkawen: Mark,
    pub aws: Mark,
    pub twilio: Mark,
    pub stripe: Mark,
}

pub const COMPARE: &[CompareRow] = &[
    CompareRow {
        feature_ar: "One platform for identity, comms, payments, commerce, logistics",
        tkawen: Mark::Yes,
        aws: Mark::Partial,
        twilio: Mark::No,
        stripe: Mark::No,
    },
    CompareRow {
        feature_ar: "Single API key for every service",
        tkawen: Mark::Yes,
        aws: Mark::No,
        twilio: Mark::No,
        stripe: Mark::No,
    },
    CompareRow {
        feature_ar: "Unified billing across services",
        tkawen: Mark::Yes,
        aws: Mark::Yes,
        twilio: Mark::No,
        stripe: Mark::No,
    },
    CompareRow {
        feature_ar: "Multi-currency native (13 currencies)",
        tkawen: Mark::Yes,
        aws: Mark::Partial,
        twilio: Mark::No,
        stripe: Mark::Partial,
    },
    CompareRow {
        feature_ar: "Open-source SDKs (MIT) for every layer",
        tkawen: Mark::Yes,
        aws: Mark::Partial,
        twilio: Mark::Partial,
        stripe: Mark::Partial,
    },
    CompareRow {
        feature_ar: "OpenAPI spec for the whole platform",
        tkawen: Mark::Yes,
        aws: Mark::No,
        twilio: Mark::Partial,
        stripe: Mark::Yes,
    },
    CompareRow {
        feature_ar: "Open-source first-party reference apps",
        tkawen: Mark::Yes,
        aws: Mark::No,
        twilio: Mark::No,
        stripe: Mark::No,
    },
    CompareRow {
        feature_ar: "Free sandbox without credit card",
        tkawen: Mark::Yes,
        aws: Mark::Partial,
        twilio: Mark::Partial,
        stripe: Mark::Yes,
    },
    CompareRow {
        feature_ar: "Time to first API call from signup",
        tkawen: Mark::Text("<60 sec"),
        aws: Mark::Text("5+ min"),
        twilio: Mark::Text("2+ min"),
        stripe: Mark::Text("1+ min"),
    },
];

/* ───────────────────────────────────────────────────────────────────
   PRICING — 3 tiers, USD primary, multi-currency billing
   ─────────────────────────────────────────────────────────────────── */

pub struct PricingTier {
    pub name_ar: &'static str,
    pub price_ar: &'static str,
    pub price_note_ar: &'static str,
    pub tagline_ar: &'static str,
    pub features_ar: &'static [&'static str],
    pub cta_ar: &'static str,
    pub cta_href: &'static str,
    pub highlighted: bool,
}

pub const TIERS: &[PricingTier] = &[
    PricingTier {
        name_ar: "Sandbox",
        price_ar: "Free",
        price_note_ar: "No credit card. No time limit.",
        tagline_ar: "For developers exploring the platform.",
        features_ar: &[
            "1,000 API calls per service per month",
            "All 7 pillars available in isolated sandbox mode",
            "Full documentation, SDKs, and code examples",
            "Community support on GitHub Issues + Discord",
            "Identical API surface to production",
        ],
        cta_ar: "Start free",
        cta_href: "https://id.tkawen.com/signup",
        highlighted: false,
    },
    PricingTier {
        name_ar: "Builder",
        price_ar: "Pay as you go",
        price_note_ar: "From $0.005 / call · billed in 13 currencies",
        tagline_ar: "For teams shipping products to real users.",
        features_ar: &[
            "Unlimited metered API calls across all pillars",
            "Monthly invoicing in USD, EUR, or 11 other currencies",
            "Webhooks, custom domains, custom subdomains",
            "100 GB storage and 1 TB bandwidth included",
            "Email + chat support, 24-hour first response SLA",
            "99.9% uptime SLA with service credits",
        ],
        cta_ar: "Start Builder",
        cta_href: "https://id.tkawen.com/signup?plan=builder",
        highlighted: true,
    },
    PricingTier {
        name_ar: "Enterprise",
        price_ar: "Custom",
        price_note_ar: "Annual contracts · custom invoicing",
        tagline_ar: "For large teams, regulated industries, and high volume.",
        features_ar: &[
            "Negotiated SLA (99.99% available)",
            "Dedicated infrastructure or on-premise deployment",
            "Custom data residency in your preferred region",
            "Security audits, ISO 27001 alignment, custom DPA",
            "Dedicated account manager + technical onboarding",
            "Volume pricing — significant discounts at scale",
        ],
        cta_ar: "Talk to sales",
        cta_href: "mailto:DIRECTION@takawen.dz?subject=Enterprise%20Inquiry",
        highlighted: false,
    },
];

/* ───────────────────────────────────────────────────────────────────
   OPEN SOURCE REPOSITORIES
   ─────────────────────────────────────────────────────────────────── */

pub struct Repo {
    pub name: &'static str,
    pub owner: &'static str,
    pub tagline_ar: &'static str,
    pub language: &'static str,
    pub license: &'static str,
}

pub const REPOS: &[Repo] = &[
    Repo {
        name: "liqaa-meet",
        owner: "liqaa-cloud",
        tagline_ar: "Self-hostable video meetings — Next.js + LiveKit + Whisper WASM.",
        language: "TypeScript",
        license: "AGPL-3.0",
    },
    Repo {
        name: "liqaa-js",
        owner: "liqaa-cloud",
        tagline_ar: "Official JavaScript / TypeScript SDK — npm @liqaa/sdk.",
        language: "TypeScript",
        license: "MIT",
    },
    Repo {
        name: "liqaa-php",
        owner: "liqaa-cloud",
        tagline_ar: "Official PHP / Laravel SDK — composer require liqaa/sdk.",
        language: "PHP",
        license: "MIT",
    },
    Repo {
        name: "liqaa-python",
        owner: "liqaa-cloud",
        tagline_ar: "Official Python SDK with async support — pip install liqaa.",
        language: "Python",
        license: "MIT",
    },
    Repo {
        name: "liqaa-go",
        owner: "liqaa-cloud",
        tagline_ar: "Official Go SDK — context-first, idiomatic, generic-typed.",
        language: "Go",
        license: "MIT",
    },
    Repo {
        name: "tkawen-com",
        owner: "TKAWEN",
        tagline_ar: "This marketing site — Rust + Axum + Maud, sub-millisecond render.",
        language: "Rust",
        license: "AGPL-3.0",
    },
    Repo {
        name: "tkawen-api",
        owner: "TKAWEN",
        tagline_ar: "Unified API gateway — single key, seven pillars, OpenAPI 3.1.",
        language: "Rust",
        license: "AGPL-3.0",
    },
    Repo {
        name: "openapi",
        owner: "liqaa-cloud",
        tagline_ar: "Full OpenAPI 3.1 specification — generate clients for any language.",
        language: "YAML",
        license: "Apache-2.0",
    },
    Repo {
        name: "rfcs",
        owner: "liqaa-cloud",
        tagline_ar: "Public Request for Comments — propose changes, vote, discuss.",
        language: "Markdown",
        license: "CC-BY-4.0",
    },
    Repo {
        name: "examples",
        owner: "liqaa-cloud",
        tagline_ar: "Runnable starter projects in all 4 SDK languages — clone & ship.",
        language: "Polyglot",
        license: "MIT",
    },
    Repo {
        name: "adrs",
        owner: "liqaa-cloud",
        tagline_ar: "Architecture Decision Records — every choice, documented.",
        language: "Markdown",
        license: "CC-BY-4.0",
    },
    Repo {
        name: "compliance",
        owner: "liqaa-cloud",
        tagline_ar: "Public compliance documents — DPAs, security questionnaires.",
        language: "Markdown",
        license: "CC-BY-4.0",
    },
];

/* ───────────────────────────────────────────────────────────────────
   TRUST & LEGAL CREDENTIALS (kept for compile; not rendered on home)
   ─────────────────────────────────────────────────────────────────── */

pub struct Credential {
    pub label_ar: &'static str,
    pub value: &'static str,
    pub authority_ar: &'static str,
}

pub const CREDENTIALS: &[Credential] = &[];

/* ───────────────────────────────────────────────────────────────────
   CUSTOMER / PARTNER LOGOS
   ─────────────────────────────────────────────────────────────────── */

pub struct LogoEntry {
    pub name: &'static str,
    pub subtitle_ar: &'static str,
}

pub const LOGOS: &[LogoEntry] = &[
    LogoEntry { name: "PharmaPro",          subtitle_ar: "Pharmacy ERP" },
    LogoEntry { name: "MyStoq",             subtitle_ar: "200+ merchants" },
    LogoEntry { name: "Algeria Certify",    subtitle_ar: "4,116+ users" },
    LogoEntry { name: "LIQAA Meet",         subtitle_ar: "OSS video" },
    LogoEntry { name: "Authentik",          subtitle_ar: "Identity layer" },
    LogoEntry { name: "LiveKit",            subtitle_ar: "Realtime media" },
    LogoEntry { name: "Chargily",           subtitle_ar: "Payment partner" },
    LogoEntry { name: "Postfix · DKIM",     subtitle_ar: "Mail stack" },
];

/* ───────────────────────────────────────────────────────────────────
   TESTIMONIALS (rolling marquee)
   ─────────────────────────────────────────────────────────────────── */

pub struct Testimonial {
    pub quote_ar: &'static str,
    pub author: &'static str,
    pub role_ar: &'static str,
    pub product: &'static str,
}

pub const TESTIMONIALS: &[Testimonial] = &[
    Testimonial {
        quote_ar: "One platform replaced four vendors. The unified billing alone saved our finance team a day per month.",
        author: "Dr. Djihad",
        role_ar: "Pharmacy owner",
        product: "PharmaPro",
    },
    Testimonial {
        quote_ar: "Spun up a working storefront in two hours. The WhatsApp commerce integration was a day of work, not a quarter.",
        author: "Ahmed K.",
        role_ar: "Cosmetics merchant",
        product: "MyStoq",
    },
    Testimonial {
        quote_ar: "QR-verifiable certificates put us ahead of paper-only providers. Students prefer it, employers ask for it.",
        author: "M. Mohamed",
        role_ar: "Training organisation",
        product: "Algeria Certify",
    },
    Testimonial {
        quote_ar: "Documentation that actually answers the question. The first SDK I've used where I didn't need to read the source.",
        author: "R. Samia",
        role_ar: "Senior engineer",
        product: "Developer Cloud",
    },
    Testimonial {
        quote_ar: "Moved twelve drivers from paper logs to a real-time dashboard in a week. Operations finally has the data.",
        author: "K. Abdelhak",
        role_ar: "Logistics manager",
        product: "Logistics",
    },
    Testimonial {
        quote_ar: "Single sign-on across our entire product suite. One user, one account, eight platforms — finally.",
        author: "Internal team",
        role_ar: "Engineering",
        product: "Identity",
    },
];

/* ───────────────────────────────────────────────────────────────────
   ICONS
   ─────────────────────────────────────────────────────────────────── */

/// Inline SVG: small check mark.
pub const ICON_CHECK: &str = r#"<polyline points="20 6 9 17 4 12"/>"#;

/// Inline SVG: arrow pointing right (LTR forward).
pub const ICON_ARROW_LEFT: &str = r#"<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>"#;
