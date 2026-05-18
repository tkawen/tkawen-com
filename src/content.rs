//! Content data for tkawen.com — pillars, apps, programs, sovereign promises.

pub struct Pillar {
    pub number: u8,
    pub slug: &'static str,
    pub name_ar: &'static str,
    pub name_en: &'static str,
    pub tagline_ar: &'static str,
    pub replaces: &'static [&'static str],
    pub icon: &'static str, // SVG path d attribute (24x24 viewport)
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
            PillarStatus::Live => "متاح",
            PillarStatus::Beta => "بيتا",
            PillarStatus::Soon => "قريباً",
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
        name_ar: "الهوية",
        name_en: "Identity",
        tagline_ar: "هوية موحَّدة لكلّ الجزائريين — تسجيل دخول واحد لكلّ المنصّات.",
        replaces: &["Auth0", "Okta", "Onfido"],
        icon: r#"<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0 1 16 0v1"/>"#,
        status: PillarStatus::Live,
    },
    Pillar {
        number: 2,
        slug: "connect",
        name_ar: "الاتصال",
        name_en: "Connect",
        tagline_ar: "فيديو، صوت، SMS، WhatsApp — API واحدة بالعربية الجزائرية.",
        replaces: &["Twilio", "Zoom", "SendGrid"],
        icon: r#"<path d="M21 11.5a8.38 8.38 0 0 1 -.9 3.8 8.5 8.5 0 0 1 -7.6 4.7 8.38 8.38 0 0 1 -3.8 -.9L3 21l1.9 -5.7a8.38 8.38 0 0 1 -.9 -3.8 8.5 8.5 0 0 1 4.7 -7.6 8.38 8.38 0 0 1 3.8 -.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>"#,
        status: PillarStatus::Live,
    },
    Pillar {
        number: 3,
        slug: "pay",
        name_ar: "الدفع",
        name_en: "Pay",
        tagline_ar: "تحصيل بالدينار، تسوية بالدينار، التزام بـ DGI ومنظومة CCP.",
        replaces: &["Stripe", "Paddle", "Recurly"],
        icon: r#"<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/>"#,
        status: PillarStatus::Beta,
    },
    Pillar {
        number: 4,
        slug: "commerce",
        name_ar: "التجارة",
        name_en: "Commerce",
        tagline_ar: "بنية متاجر إلكترونية بـ 13 عملة و 4 ناقلين و 4 مزوّدي دفع.",
        replaces: &["Shopify", "Square"],
        icon: r#"<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2 -2V6l-3 -4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1 -8 0"/>"#,
        status: PillarStatus::Live,
    },
    Pillar {
        number: 5,
        slug: "knowledge",
        name_ar: "المعرفة",
        name_en: "Knowledge",
        tagline_ar: "منصّات تعلّم وشهادات قابلة للتحقّق بـ QR — Decree 20-254.",
        replaces: &["Teachable", "Coursera", "Credly"],
        icon: r#"<path d="M22 10v6"/><path d="M2 10l10 -5 10 5 -10 5 -10 -5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6 -1.3 6 -3v-5"/>"#,
        status: PillarStatus::Live,
    },
    Pillar {
        number: 6,
        slug: "logistics",
        name_ar: "اللوجستيك",
        name_en: "Logistics",
        tagline_ar: "تتبّع أساطيل + تكامل مع ناقلي DZ (Aramex, CTM, Yalidine، إلخ).",
        replaces: &["Onfleet", "Bringg"],
        icon: r#"<rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>"#,
        status: PillarStatus::Live,
    },
    Pillar {
        number: 7,
        slug: "developer",
        name_ar: "للمطوّرين",
        name_en: "Developer",
        tagline_ar: "API gateway موحّدة، SDKs بـ 4 لغات، sandbox مجاني، docs بالعربية.",
        replaces: &["AWS", "Vercel", "Cloudflare"],
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
        tagline_ar: "إدارة صيدليّة كاملة — أوّل عميل دفع حقيقي.",
        status_ar: "إنتاج",
        built_on: &[1, 2, 3, 7],
    },
    App {
        name: "MyStoq",
        domain: "mystoq.com",
        tagline_ar: "200+ تاجر LIVE — متاجر إلكترونية جاهزة في دقائق.",
        status_ar: "إنتاج",
        built_on: &[1, 3, 4, 6, 7],
    },
    App {
        name: "Algeria Certify",
        domain: "algeriacertify.com",
        tagline_ar: "4,116 مستخدم — شهادات قابلة للتحقّق بـ QR.",
        status_ar: "إنتاج",
        built_on: &[1, 5, 7],
    },
    App {
        name: "LIQAA Meet",
        domain: "meet.liqaa.io",
        tagline_ar: "بديل Zoom مفتوح المصدر — AGPL-3.0.",
        status_ar: "open source",
        built_on: &[1, 2],
    },
];

pub struct SovereignPromise {
    pub icon: &'static str, // raw SVG inner content (24×24 viewport)
    pub title_ar: &'static str,
    pub body_ar: &'static str,
}

pub const PROMISES: &[SovereignPromise] = &[
    SovereignPromise {
        // server / data-residency icon
        icon: r#"<rect x="3" y="4" width="18" height="6" rx="1"/><rect x="3" y="14" width="18" height="6" rx="1"/><line x1="7" y1="7" x2="7.01" y2="7"/><line x1="7" y1="17" x2="7.01" y2="17"/><path d="M12 10v4"/>"#,
        title_ar: "البيانات تبقى في الجزائر",
        body_ar: "خوادمنا تحت ولايتك القضائية. لا نقل عبر الحدود بدون إذنك الصريح.",
    },
    SovereignPromise {
        // wallet / banknote icon
        icon: r#"<path d="M20 12V8H6a2 2 0 0 1 0-4h12v4"/><path d="M4 6v12a2 2 0 0 0 2 2h14v-8H6a2 2 0 0 1 -2 -2"/><circle cx="16" cy="14" r="1.5" fill="currentColor"/>"#,
        title_ar: "الفوترة بالدينار",
        body_ar: "كلّ الأسعار DZD — لا FX، لا Visa، لا مفاجآت في نهاية الشهر.",
    },
    SovereignPromise {
        // shield with check icon
        icon: r#"<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>"#,
        title_ar: "استقلال قانوني",
        body_ar: "خدماتنا لا تتأثّر بالعقوبات أو سياسات الشركات الأجنبية. R/C + NIF + Decree 20-254.",
    },
    SovereignPromise {
        // open-source / code-brackets icon
        icon: r#"<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/><line x1="14" y1="4" x2="10" y2="20"/>"#,
        title_ar: "مفتوح حيث يهمّ",
        body_ar: "LIQAA Meet و SDKs مفتوحة المصدر تحت AGPL — لا حبس بيانات، لا حبس عملاء.",
    },
];

/// Inline SVG: small check mark, 16×16. Used in feature lists.
pub const ICON_CHECK: &str = r#"<polyline points="20 6 9 17 4 12"/>"#;

/// Inline SVG: arrow pointing left (RTL forward).
pub const ICON_ARROW_LEFT: &str = r#"<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>"#;

pub struct Metric {
    pub value: &'static str,
    pub label_ar: &'static str,
}

pub const METRICS: &[Metric] = &[
    Metric { value: "17", label_ar: "نظاماً في الإنتاج" },
    Metric { value: "4,116+", label_ar: "مستخدم على TKAWEN ID" },
    Metric { value: "200+", label_ar: "متجر مستضاف على البنية" },
    Metric { value: "375+", label_ar: "صفحة فهرستها Google" },
    Metric { value: "22", label_ar: "مشروع مفتوح المصدر" },
    Metric { value: "<1ms", label_ar: "زمن تقديم هذه الصفحة" },
];

/* ───────────────────────────────────────────────────────────────────
   COMPARISON — TKAWEN vs global cloud providers
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
        feature_ar: "البيانات تبقى في الجزائر",
        tkawen: Mark::Yes,
        aws: Mark::No,
        twilio: Mark::No,
        stripe: Mark::No,
    },
    CompareRow {
        feature_ar: "الفوترة بالدينار الجزائري",
        tkawen: Mark::Yes,
        aws: Mark::No,
        twilio: Mark::No,
        stripe: Mark::No,
    },
    CompareRow {
        feature_ar: "العربية كلغة أصليّة (RTL + Darija)",
        tkawen: Mark::Yes,
        aws: Mark::No,
        twilio: Mark::No,
        stripe: Mark::No,
    },
    CompareRow {
        feature_ar: "التزام بـ DGI و CCP الجزائري",
        tkawen: Mark::Yes,
        aws: Mark::No,
        twilio: Mark::No,
        stripe: Mark::No,
    },
    CompareRow {
        feature_ar: "دعم ناقلي الشحن المحليّين (Yalidine, CTM…)",
        tkawen: Mark::Yes,
        aws: Mark::No,
        twilio: Mark::No,
        stripe: Mark::No,
    },
    CompareRow {
        feature_ar: "SDKs مفتوحة المصدر (AGPL/MIT)",
        tkawen: Mark::Yes,
        aws: Mark::Partial,
        twilio: Mark::Partial,
        stripe: Mark::Partial,
    },
    CompareRow {
        feature_ar: "تسجيل قانوني جزائريّ (Décret 20-254 + R/C)",
        tkawen: Mark::Yes,
        aws: Mark::No,
        twilio: Mark::No,
        stripe: Mark::No,
    },
    CompareRow {
        feature_ar: "دعم فنّي بالعربية × 24/7",
        tkawen: Mark::Yes,
        aws: Mark::No,
        twilio: Mark::No,
        stripe: Mark::No,
    },
    CompareRow {
        feature_ar: "بطاقة فيزا أمريكيّة مطلوبة للتفعيل",
        tkawen: Mark::Text("لا — حساب بنكيّ DZ"),
        aws: Mark::Text("نعم"),
        twilio: Mark::Text("نعم"),
        stripe: Mark::Text("نعم + LLC"),
    },
];

/* ───────────────────────────────────────────────────────────────────
   PRICING — 3 tiers, DZD-native
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
        price_ar: "مجاناً",
        price_note_ar: "بلا بطاقة، بلا حدّ زمنيّ",
        tagline_ar: "للمطوّرين الذين يجرّبون البنية.",
        features_ar: &[
            "1,000 استدعاء API/شهر لكلّ خدمة",
            "كلّ الخدمات الـ 7 متاحة",
            "Sandbox عزل كامل (لا بيانات حقيقيّة)",
            "وثائق + SDKs كاملة",
            "دعم عبر GitHub Issues + Discord",
        ],
        cta_ar: "ابدأ مجاناً",
        cta_href: "https://id.tkawen.com/signup",
        highlighted: false,
    },
    PricingTier {
        name_ar: "Builder",
        price_ar: "بحسب الاستهلاك",
        price_note_ar: "من 0.5 DZD لكلّ استدعاء",
        tagline_ar: "للشركات الناشئة التي تنطلق بمنتجها.",
        features_ar: &[
            "استدعاءات API لا محدودة (metered)",
            "فوترة شهريّة بالدينار",
            "Webhooks + Custom domains",
            "Storage حتّى 100 GB مشمول",
            "دعم بالعربية بريد + WhatsApp في 24 ساعة",
            "SLA 99.9% uptime",
        ],
        cta_ar: "اشترك في Builder",
        cta_href: "https://id.tkawen.com/signup?plan=builder",
        highlighted: true,
    },
    PricingTier {
        name_ar: "Enterprise",
        price_ar: "تواصل معنا",
        price_note_ar: "عقود سنويّة، فاتورة TVA",
        tagline_ar: "للبنوك، الجامعات، الإدارات العموميّة.",
        features_ar: &[
            "SLA مفاوَض (99.99%)",
            "تثبيت dedicated على VPS مخصَّص أو on-prem",
            "تكامل KYC مع الـ ID الوطنيّة (PNI)",
            "تدقيق أمني + ISO 27001",
            "محاسب حساب + مدير تسليم مخصَّصان",
            "DPA كامل + بنود قانونيّة جزائريّة",
        ],
        cta_ar: "احجز مكالمة 30 دقيقة",
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
        tagline_ar: "بديل Zoom كامل — Next.js 16 + LiveKit + Whisper",
        language: "TypeScript",
        license: "AGPL-3.0",
    },
    Repo {
        name: "liqaa-js",
        owner: "liqaa-cloud",
        tagline_ar: "SDK رسميّ لجافاسكربت — npm @liqaa/sdk",
        language: "TypeScript",
        license: "MIT",
    },
    Repo {
        name: "liqaa-php",
        owner: "liqaa-cloud",
        tagline_ar: "SDK رسميّ لـ PHP/Laravel — composer install",
        language: "PHP",
        license: "MIT",
    },
    Repo {
        name: "liqaa-python",
        owner: "liqaa-cloud",
        tagline_ar: "SDK رسميّ لـ Python — pip install liqaa",
        language: "Python",
        license: "MIT",
    },
    Repo {
        name: "liqaa-go",
        owner: "liqaa-cloud",
        tagline_ar: "SDK رسميّ لـ Go — go get github.com/liqaa-cloud/liqaa-go",
        language: "Go",
        license: "MIT",
    },
    Repo {
        name: "hartem-tkawen-rs",
        owner: "hartemyaakoub",
        tagline_ar: "موقع شخصيّ يرندر في 87μs — قالب Axum + Maud قابل لإعادة الاستخدام",
        language: "Rust",
        license: "AGPL-3.0",
    },
    Repo {
        name: "tkawen-com",
        owner: "TKAWEN",
        tagline_ar: "هذا الموقع نفسه — Rust + Axum + Maud — مفتوح للمراجعة",
        language: "Rust",
        license: "AGPL-3.0",
    },
    Repo {
        name: "openapi",
        owner: "liqaa-cloud",
        tagline_ar: "مواصفات OpenAPI 3.1 لـ LIQAA Cloud — مرجع رسميّ",
        language: "YAML",
        license: "Apache-2.0",
    },
    Repo {
        name: "rfcs",
        owner: "liqaa-cloud",
        tagline_ar: "اقتراحات تحسين عامّة (Request For Comments) — شارك بالنقاش",
        language: "Markdown",
        license: "CC-BY-4.0",
    },
    Repo {
        name: "examples",
        owner: "liqaa-cloud",
        tagline_ar: "أمثلة تطبيقيّة جاهزة بـ 4 لغات — clone & run",
        language: "Polyglot",
        license: "MIT",
    },
    Repo {
        name: "compliance",
        owner: "liqaa-cloud",
        tagline_ar: "وثائق الامتثال + DPAs + معايير الأمن — للمراجعة العموميّة",
        language: "Markdown",
        license: "CC-BY-4.0",
    },
    Repo {
        name: "adrs",
        owner: "liqaa-cloud",
        tagline_ar: "Architecture Decision Records — تاريخ القرارات التقنيّة",
        language: "Markdown",
        license: "CC-BY-4.0",
    },
];

/* ───────────────────────────────────────────────────────────────────
   TRUST & LEGAL CREDENTIALS
   ─────────────────────────────────────────────────────────────────── */

pub struct Credential {
    pub label_ar: &'static str,
    pub value: &'static str,
    pub authority_ar: &'static str,
}

/* ───────────────────────────────────────────────────────────────────
   CUSTOMER / PARTNER LOGOS (text-driven, no external assets)
   ─────────────────────────────────────────────────────────────────── */

pub struct LogoEntry {
    pub name: &'static str,
    pub subtitle_ar: &'static str,
}

pub const LOGOS: &[LogoEntry] = &[
    LogoEntry { name: "Pharmacie Dr. Djihad", subtitle_ar: "أوّل عميل دفع" },
    LogoEntry { name: "UPROMEDIC", subtitle_ar: "شريك تحاليل طبيّة" },
    LogoEntry { name: "MyStoq", subtitle_ar: "+200 تاجر مستضاف" },
    LogoEntry { name: "Algeria Certify", subtitle_ar: "+4,116 ممارس" },
    LogoEntry { name: "Authentik", subtitle_ar: "طبقة الهوية" },
    LogoEntry { name: "LiveKit", subtitle_ar: "طبقة الفيديو" },
    LogoEntry { name: "Chargily Pay", subtitle_ar: "تكامل المدفوعات" },
    LogoEntry { name: "Décret 20-254", subtitle_ar: "اعتماد أكاديميّ" },
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
        quote_ar: "بدأت يومي الأوّل في الصيدليّة وكلّ شيء جاهز. لا أحتاج محاسباً منفصلاً، DGI integration يعمل من اللحظة الأولى.",
        author: "د. جهاد",
        role_ar: "صيدلانيّة، عنّابة",
        product: "PharmaPro",
    },
    Testimonial {
        quote_ar: "متجري كان يحتاج 3 أشهر على Shopify. على MyStoq فتحته في ساعتين، بالدارجة، مع Yalidine مدمج. لا توجد بطاقة فيزا مطلوبة.",
        author: "أحمد ك.",
        role_ar: "تاجر مستحضرات تجميل",
        product: "MyStoq",
    },
    Testimonial {
        quote_ar: "شهاداتي صارت قابلة للتحقّق بـ QR. الطلاب يحبّون ذلك، الموظّفون يطلبونه. كان مستحيلاً تخيّل هذا قبل سنة.",
        author: "م. محمد",
        role_ar: "مدرّب معتمَد، الجزائر العاصمة",
        product: "Algeria Certify",
    },
    Testimonial {
        quote_ar: "أوّل مرّة أرى وثائق API بالعربية تشتغل فعلاً. كنت أنسخ من Stack Overflow، الآن أقرأ مباشرة بلغتي.",
        author: "ر. سامية",
        role_ar: "مطوّرة جزائريّة",
        product: "Developer Cloud",
    },
    Testimonial {
        quote_ar: "حوّلنا 12 موظّفاً من تتبّع يدويّ على ورق إلى dashboard مركزيّ في أسبوع. الأسطول كلّه على الخريطة لحظياً.",
        author: "ك. عبد الحقّ",
        role_ar: "مدير لوجستيك",
        product: "TKAWEN Track",
    },
    Testimonial {
        quote_ar: "نظام الهويّة الموحَّد بين منصّاتنا كان حلماً. الآن مستخدم واحد، حساب واحد، 8 منصّات.",
        author: "TKAWEN Engineering",
        role_ar: "فريق داخليّ",
        product: "TKAWEN ID",
    },
];

pub const CREDENTIALS: &[Credential] = &[
    Credential {
        label_ar: "تسجيل أكاديميّ",
        value: "Décret 20-254",
        authority_ar: "وزارة التعليم العالي والبحث العلمي",
    },
    Credential {
        label_ar: "اعتماد مهنيّ",
        value: "Arrêté 1275",
        authority_ar: "MESRS",
    },
    Credential {
        label_ar: "وكالة الخدمات الإلكترونيّة",
        value: "ASEP",
        authority_ar: "السلطة الجزائريّة",
    },
    Credential {
        label_ar: "معرّف D-U-N-S دوليّ",
        value: "353551313",
        authority_ar: "Dun & Bradstreet",
    },
    Credential {
        label_ar: "رقم تعريف جبائيّ",
        value: "NIF",
        authority_ar: "DGI الجزائر",
    },
    Credential {
        label_ar: "السجل التجاريّ",
        value: "R/C",
        authority_ar: "CNRC الجزائر",
    },
];
