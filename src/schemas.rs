//! JSON-LD schemas for tkawen.com — SEO/GEO.
//! Organization + 7 Service entries + WebSite.

use serde_json::json;

pub fn organization_jsonld() -> String {
    let data = json!({
        "@context": "https://schema.org",
        "@type": "Organization",
        "@id": "https://tkawen.com/#org",
        "name": "TKAWEN",
        "url": "https://tkawen.com/",
        "logo": "https://tkawen.com/favicon.svg",
        "description": "Unified cloud platform: seven APIs (Identity, Connect, Pay, Commerce, Knowledge, Logistics, Developer) that ship together, bill together, and integrate together.",
        "foundingDate": "2026",
        "sameAs": [
            "https://github.com/hartemyaakoub",
            "https://github.com/liqaa-cloud",
            "https://liqaa.io",
            "https://mystoq.com",
            "https://hartem.tkawen.com",
            "https://status.tkawen.com"
        ],
        "knowsLanguage": ["en", "ar", "fr"]
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
                "description": "OIDC single sign-on, KYC verification, and cross-product trust signals.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "Authentication & Identity"
            },
            {
                "@type": "Service",
                "@id": "https://connect.tkawen.com/#svc",
                "name": "TKAWEN Connect",
                "description": "Video, voice, SMS, WhatsApp, email, and text-to-speech behind one API.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "Real-time Communication API"
            },
            {
                "@type": "Service",
                "@id": "https://pay.tkawen.com/#svc",
                "name": "TKAWEN Pay",
                "description": "Cards, wallets, transfers, and recurring billing in 13 currencies.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "Payment Processing"
            },
            {
                "@type": "Service",
                "@id": "https://commerce.tkawen.com/#svc",
                "name": "TKAWEN Commerce",
                "description": "Multi-tenant storefronts, catalog, cart, checkout, and order orchestration.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "E-commerce Infrastructure"
            },
            {
                "@type": "Service",
                "@id": "https://knowledge.tkawen.com/#svc",
                "name": "TKAWEN Knowledge",
                "description": "Courses, AI tutors, and tamper-proof credentials with public QR verification.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "Learning & Credentials"
            },
            {
                "@type": "Service",
                "@id": "https://logistics.tkawen.com/#svc",
                "name": "TKAWEN Logistics",
                "description": "Fleet GPS, geofences, multi-carrier shipping quotes and labels.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "Logistics & Fleet"
            },
            {
                "@type": "Service",
                "@id": "https://developer.tkawen.com/#svc",
                "name": "TKAWEN Developer Cloud",
                "description": "Unified API gateway, four SDKs, OpenAPI spec, free sandbox, public status page.",
                "provider": { "@id": "https://tkawen.com/#org" },
                "serviceType": "Developer Platform"
            },
            {
                "@type": "WebSite",
                "@id": "https://tkawen.com/#website",
                "url": "https://tkawen.com/",
                "name": "TKAWEN",
                "inLanguage": "en",
                "publisher": { "@id": "https://tkawen.com/#org" }
            }
        ]
    });
    serde_json::to_string(&data).unwrap_or_default()
}
