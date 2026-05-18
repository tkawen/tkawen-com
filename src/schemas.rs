//! JSON-LD schemas for tkawen.com — SEO/GEO L4+.
//! Organization + 7 Service entries + WebSite + WebPage.

use serde_json::json;

pub fn organization_jsonld() -> String {
    let data = json!({
        "@context": "https://schema.org",
        "@type": "Organization",
        "@id": "https://tkawen.com/#org",
        "name": "TKAWEN",
        "alternateName": ["Takawen", "تكاوين", "TKAWEN Sovereign Cloud"],
        "url": "https://tkawen.com/",
        "logo": "https://tkawen.com/favicon.svg",
        "description": "البنية التحتية الرقمية السيادية للجزائر — 7 طبقات: الهوية، الاتصال، الدفع، التجارة، المعرفة، اللوجستيك، السحابة للمطوّرين.",
        "foundingDate": "2026",
        "founder": {
            "@type": "Person",
            "@id": "https://hartem.tkawen.com/#person",
            "name": "Hartem Yaakoub",
            "alternateName": "حرتام يعقوب",
            "url": "https://hartem.tkawen.com/"
        },
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "DZ",
            "addressRegion": "Annaba"
        },
        "identifier": [
            { "@type": "PropertyValue", "propertyID": "D-U-N-S", "value": "353551313" },
            { "@type": "PropertyValue", "propertyID": "Décret", "value": "20-254" },
            { "@type": "PropertyValue", "propertyID": "Arrêté MESRS", "value": "1275" }
        ],
        "sameAs": [
            "https://github.com/hartemyaakoub",
            "https://liqaa.io",
            "https://mystoq.com",
            "https://algeriacertify.com",
            "https://hartem.tkawen.com",
            "https://catalogue.tkawen.com",
            "https://brand.tkawen.com",
            "https://status.tkawen.com"
        ],
        "areaServed": [
            { "@type": "Country", "name": "Algeria" },
            { "@type": "Place", "name": "MENA" }
        ],
        "knowsLanguage": ["ar", "ar-DZ", "fr", "en"]
    });
    serde_json::to_string(&data).unwrap_or_default()
}

pub fn services_jsonld() -> String {
    let data = json!({
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Service",
                "@id": "https://identity.tkawen.com/#svc",
                "name": "TKAWEN Identity",
                "alternateName": "الهوية",
                "description": "هوية موحَّدة وKYC وTrust Network لكلّ المنصّات الجزائريّة.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "Authentication & Identity Verification",
                "areaServed": { "@type": "Country", "name": "Algeria" }
            },
            {
                "@type": "Service",
                "@id": "https://connect.tkawen.com/#svc",
                "name": "TKAWEN Connect",
                "alternateName": "الاتصال",
                "description": "فيديو، صوت، SMS، WhatsApp — API واحدة بالعربية الجزائريّة.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "Real-time Communication API",
                "areaServed": { "@type": "Country", "name": "Algeria" }
            },
            {
                "@type": "Service",
                "@id": "https://pay.tkawen.com/#svc",
                "name": "TKAWEN Pay",
                "alternateName": "الدفع",
                "description": "تحصيل بالدينار، تسوية بالدينار، التزام بـ DGI ومنظومة CCP.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "Payment Processing",
                "areaServed": { "@type": "Country", "name": "Algeria" }
            },
            {
                "@type": "Service",
                "@id": "https://commerce.tkawen.com/#svc",
                "name": "TKAWEN Commerce",
                "alternateName": "التجارة",
                "description": "بنية متاجر إلكترونيّة بـ 13 عملة و4 ناقلين و4 مزوّدي دفع.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "E-commerce Infrastructure",
                "areaServed": { "@type": "Place", "name": "MENA" }
            },
            {
                "@type": "Service",
                "@id": "https://knowledge.tkawen.com/#svc",
                "name": "TKAWEN Knowledge",
                "alternateName": "المعرفة",
                "description": "منصّات تعلّم وشهادات قابلة للتحقّق بـ QR — Décret 20-254.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "Learning Management & Credentials",
                "areaServed": { "@type": "Country", "name": "Algeria" }
            },
            {
                "@type": "Service",
                "@id": "https://logistics.tkawen.com/#svc",
                "name": "TKAWEN Logistics",
                "alternateName": "اللوجستيك",
                "description": "تتبّع أساطيل + تكامل مع ناقلي DZ (Aramex, CTM, Yalidine…).",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "Fleet & Last-mile Logistics",
                "areaServed": { "@type": "Country", "name": "Algeria" }
            },
            {
                "@type": "Service",
                "@id": "https://developer.tkawen.com/#svc",
                "name": "TKAWEN Developer Cloud",
                "alternateName": "السحابة للمطوّرين",
                "description": "API gateway موحَّدة، SDKs بـ 4 لغات، sandbox مجاني، docs بالعربية.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "Developer Platform",
                "areaServed": { "@type": "Country", "name": "Algeria" }
            },
            {
                "@type": "WebSite",
                "@id": "https://tkawen.com/#website",
                "url": "https://tkawen.com/",
                "name": "TKAWEN — Sovereign Cloud for Algeria",
                "inLanguage": "ar-DZ",
                "publisher": { "@id": "https://tkawen.com/#org" }
            }
        ]
    });
    serde_json::to_string(&data).unwrap_or_default()
}
