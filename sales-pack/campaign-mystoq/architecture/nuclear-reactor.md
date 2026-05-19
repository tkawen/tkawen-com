# TKAWEN Nuclear Reactor — full subdomain architecture

> "Each subdomain is a fuel rod. Each fuel rod captures emails. Each email
> enters the intel core. The intel core triggers sequences. The sequences
> produce revenue. Revenue funds more fuel rods. Reactor goes critical."

This document maps the FULL architecture of tkawen.com as a self-sustaining
sales/marketing/distribution engine.

---

## The 7 layers

```
┌─────────────────────────────────────────────────────────────┐
│  LAYER 7 · CONTAINMENT (legal/trust/public)                 │
│  about · team · trust · press · legal · hiring               │
├─────────────────────────────────────────────────────────────┤
│  LAYER 6 · AMPLIFICATION (compound traffic)                 │
│  share · invite · rewards · embed · affiliates              │
├─────────────────────────────────────────────────────────────┤
│  LAYER 5 · AUTOMATION (the reactor core)                    │
│  flows · send · wa · sms · calls · triggers                 │
├─────────────────────────────────────────────────────────────┤
│  LAYER 4 · INTELLIGENCE (the brain)                         │
│  intel · crm · analytics · tracker · funnel                 │
├─────────────────────────────────────────────────────────────┤
│  LAYER 3 · PRODUCTS (what you sell)                         │
│  shop · meet · certify · pay · id · api · dashboard         │
├─────────────────────────────────────────────────────────────┤
│  LAYER 2 · CONVERSION (mid-funnel)                          │
│  try · demo · start · pricing · partners · enterprise       │
├─────────────────────────────────────────────────────────────┤
│  LAYER 1 · ACQUISITION (top-of-funnel — fuel rods)          │
│  blog · tools · academy · stories · community · events      │
│  podcast · newsletter · glossary · calculators              │
└─────────────────────────────────────────────────────────────┘
```

Total: **~50 subdomains**. Built incrementally. Each one autonomous but feeding
the central data layer.

---

## Layer 1 — Acquisition (fuel rods)

These are the SEO/content engines. Each ranks for its own keywords and
funnels organic traffic into the lead capture system.

| Subdomain | Purpose | First win |
|-----------|---------|-----------|
| **blog.tkawen.com** | DZ-Arabic content engine. 50+ posts → 5k+ visitors/mo from search | "كيف افتح متجر الكتروني في الجزائر" #1 in 90 days |
| **tools.tkawen.com** | Free utilities. Each tool ranks separately and captures emails | IBAN validator, Wilaya finder, Yalidine quote |
| **calculators.tkawen.com** | Interactive calculators (VAT, shipping, conversion) | "حاسبة TVA الجزائر" — high intent |
| **academy.tkawen.com** | Free mini-courses. Email-gated content | "اعرف الفرق بين Shopify و WooCommerce" |
| **stories.tkawen.com** | Customer success stories. Each story ranks separately | Already exists in mystoq-invite/stories/ |
| **community.tkawen.com** | Discourse forum for DZ tech community. UGC = compound SEO | DZ developer community migrates from FB groups |
| **events.tkawen.com** | Meetup/webinar landing pages. RSVP = email | "MyStoq Demo Day" monthly |
| **podcast.tkawen.com** | DZ tech founder interviews. Audio = sticky | Episode 1: interview with you on building TKAWEN |
| **newsletter.tkawen.com** | Single-purpose email signup page | "Weekly DZ tech digest" |
| **glossary.tkawen.com** | Technical term glossary in Arabic | "ما هو SaaS؟", "ما معنى DKIM؟" — long-tail SEO |

**Lead capture pattern (universal):**

```html
<!-- Embedded on every Layer-1 page -->
<form action="https://capture.tkawen.com/in" method="post">
  <input type="email" name="email" required>
  <input type="hidden" name="source" value="blog">
  <input type="hidden" name="topic" value="shopify-vs-woo">
  <button>اشترك مجانا</button>
</form>
```

Every email feeds into **intel.tkawen.com** with full attribution.

---

## Layer 2 — Conversion (mid-funnel)

Where curious visitors become trial users.

| Subdomain | Purpose |
|-----------|---------|
| **try.tkawen.com** | **Universal signup portal** — picks product, opens account everywhere |
| **demo.tkawen.com** | Interactive demos. Sandboxed MyStoq, LIQAA, AlgeriaCertify |
| **start.tkawen.com** | Guided onboarding wizard. "What are you trying to build?" → routes |
| **pricing.tkawen.com** | Unified pricing across all products |
| **partners.tkawen.com** | Partner/reseller program landing |
| **enterprise.tkawen.com** | B2B sales — for ministries, big agencies, schools |
| **founders.tkawen.com** | Founder-led sales — high-ticket, "talk to Yaakoub directly" |

**The conversion engine logic:**

```
visitor lands on blog.tkawen.com/post-X
  → reads, clicks CTA
  → lands on try.tkawen.com/?from=blog&topic=X
  → fills email, picks product
  → account auto-provisioned via id.tkawen.com SSO
  → first onboarding email triggered from send.tkawen.com
  → intel.tkawen.com starts tracking engagement score
```

---

## Layer 3 — Products

| Subdomain | What it is |
|-----------|-----------|
| **shop.tkawen.com** | Reverse-proxy or redirect to mystoq.com |
| **meet.tkawen.com** | Reverse-proxy to liqaa.io |
| **certify.tkawen.com** | Reverse-proxy to algeriacertify.com |
| **id.tkawen.com** | **Single Sign-On** for ALL products (already drafted in liqaa/oidc) |
| **pay.tkawen.com** | Unified billing portal — one invoice per customer across products |
| **dashboard.tkawen.com** | Single dashboard showing all your active products + usage |
| **api.tkawen.com** | Unified API gateway (already exists as tkawen-api) |
| **status.tkawen.com** | Uptime monitor (already live on VPS40) |

**The compound effect:**
A customer signs up for MyStoq via shop.tkawen.com → next month they need
video calls → they're already authenticated via id.tkawen.com → one click
to activate LIQAA → cross-sell complete with zero friction.

---

## Layer 4 — Intelligence (the brain)

This is what we just started building.

| Subdomain | Purpose | State |
|-----------|---------|-------|
| **intel.tkawen.com** | Real-time campaign + funnel dashboard | ✅ Phase 1 live at tkawen.online/intel |
| **crm.tkawen.com** | Per-contact profile with full timeline | ⏳ Phase 2 |
| **analytics.tkawen.com** | Your own GA replacement (privacy-respecting) | ⏳ Phase 2 |
| **tracker.tkawen.com** | Drop-in JS pixel for all sites | ⏳ Phase 2 |
| **funnel.tkawen.com** | Visual funnel builder UI | ⏳ Phase 3 |

**Data shape:**

Every event from any subdomain hits **intel.tkawen.com/api/event**:

```json
{
  "ts": "2026-05-19T12:34:56Z",
  "kind": "click|open|view|signup|purchase|reply|...",
  "subdomain": "blog",
  "path": "/posts/shopify-vs-woo",
  "actor": {"email": "...", "user_id": "..."},
  "context": {"utm_source": "...", "referrer": "..."}
}
```

All events → single JSONL stream → indexed → queryable from the dashboard.

---

## Layer 5 — Automation (reactor core)

The piece that makes the system self-sustaining. **No human in the loop.**

| Subdomain | Purpose |
|-----------|---------|
| **flows.tkawen.com** | Visual workflow builder. "If X then Y after Z hours" |
| **send.tkawen.com** | Email sender + sequences (replaces Mailchimp) |
| **wa.tkawen.com** | WhatsApp Business automation |
| **sms.tkawen.com** | SMS via DZ providers (Mobilis API, Djezzy, Ooredoo) |
| **calls.tkawen.com** | Cold-call workflow + voicemail drop |
| **triggers.tkawen.com** | Event-based webhook system |

**Example flow** (what the reactor core does autonomously):

```
TRIGGER: visitor opens blog.tkawen.com/shopify-vs-woo for >60s
  → SEND email after 1h: "ملخص المقال + 3 نصائح إضافية"
  → IF opens email + clicks: lead_score += 30
  → IF score > 50 within 7 days:
       → SEND WhatsApp template: "هل تريد جلسة 15 دقيقة معي؟"
  → IF replies "نعم":
       → CREATE Calendly invite
       → NOTIFY founder via push + Telegram
       → BLOCK further automation (human-takeover flag)
  → IF score > 100 AND no signup after 30 days:
       → SEND final email "آخر فرصة..."
       → AFTER 7 days no response: SUPPRESS for 90 days
```

---

## Layer 6 — Amplification (compound traffic)

Each customer becomes a new acquisition channel.

| Subdomain | Purpose |
|-----------|---------|
| **share.tkawen.com/r/<code>** | Referral links. Tracks who referred whom |
| **invite.tkawen.com** | "Invite a friend, both get 30 days free" flow |
| **rewards.tkawen.com** | Loyalty points + tier system |
| **embed.tkawen.com** | Embeddable widgets (cart, contact form, calendar) for partner sites |
| **affiliate.tkawen.com** | Public affiliate dashboard with commission tracking |

**Viral coefficient target:** k > 1.0
- Each customer invites 2 people on average
- 50% of invited become customers
- k = 2 × 0.5 = 1.0 (steady-state)
- Push to k = 1.3 → exponential compounding

---

## Layer 7 — Containment (trust)

The "boring but critical" subdomains. Without these, no enterprise customer
signs.

| Subdomain | Purpose |
|-----------|---------|
| **about.tkawen.com** | Company narrative, mission, founder story |
| **team.tkawen.com** | Team bios (even if just you for now) |
| **trust.tkawen.com** | Security, compliance, uptime promises (LIVE) |
| **press.tkawen.com** | Media kit, logos, press contact |
| **legal.tkawen.com** | Terms, privacy, GDPR, data processing |
| **hiring.tkawen.com** | Open positions (signal: "we're growing") |
| **investors.tkawen.com** | (Private) pitch deck access — when you raise |

---

## Phase 1 — What to build FIRST (this week)

Don't build all 50 at once. Pick the **5 with highest leverage**:

| # | Subdomain | Why this first | Effort |
|---|-----------|----------------|--------|
| 1 | **intel.tkawen.com** | Already 70% built. Move from tkawen.online/intel → proper subdomain. Brand consolidation + sets up the auth pattern for all others | 1 day |
| 2 | **try.tkawen.com** | Single signup portal. Captures intent from EVERY other domain. Without this, all other subdomains lose conversions | 2 days |
| 3 | **id.tkawen.com** | SSO. The plumbing that enables cross-product cross-sell. Build once, every other product gets cross-sell for free | 3 days |
| 4 | **blog.tkawen.com** | SEO compounds. Start with 5 pillar posts targeting "Shopify Algeria", "WooCommerce vs MyStoq", etc. Each post = 100-500 visitors/mo in 6 months | 4 days (then 1/week ongoing) |
| 5 | **tools.tkawen.com** | One tool = 1k+ visitors/month if it ranks. "Yalidine shipping cost calculator" alone could be massive | 2 days for first 3 tools |

**Total Phase 1 effort: ~2 weeks for the foundation.**

After that, every NEW subdomain is plug-and-play because:
- DNS wildcard `*.tkawen.com` already routes
- Auth via `id.tkawen.com` is universal
- Lead capture pipes into `intel.tkawen.com` automatically
- Email sends go through `send.tkawen.com`

---

## Technical infrastructure (the boring necessary)

### DNS
```
*.tkawen.com.   A   <vps40-ip>
*.tkawen.com.   AAAA  <vps40-ipv6>
```

(Already done — wildcard certificate from Let's Encrypt covers all subdomains.)

### nginx vhost pattern
```nginx
# /etc/nginx/sites-available/tkawen-wildcard.conf
server {
    listen 443 ssl http2;
    server_name ~^(?<sub>[^.]+)\.tkawen\.com$;

    ssl_certificate     /etc/letsencrypt/live/tkawen.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tkawen.com/privkey.pem;

    # Each subdomain → its own folder /var/www/tkawen/<sub>
    root /var/www/tkawen/$sub;

    # Fallback for missing subdomain
    error_page 404 /404.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

Add a new subdomain = create `/var/www/tkawen/<sub>/` and drop files. Zero DNS or nginx work.

### Auth pattern (all subdomains use the same)
- Session cookie scoped to `.tkawen.com` (works across all subdomains)
- Cookie set by `id.tkawen.com` after login
- Each subdomain reads the cookie via a shared PHP include

### Database
Single Postgres on VPS40, schema-per-domain:
- `intel.events` (universal event stream)
- `crm.contacts` (every email ever captured)
- `flows.workflows` (automation definitions)
- `send.campaigns` (email campaign history)

---

## Cost projection

| Item | Monthly cost |
|------|-------------|
| VPS40 (Contabo) | already paid (~10€/mo) |
| Domain tkawen.com | already paid (~1€/mo amortized) |
| Cloudflare (free tier) | 0 |
| Let's Encrypt | 0 |
| Postgres (self-hosted) | 0 |
| Disk for logs/data | ~0 (text-only logs) |
| **Total** | **~11€/mo for 50 subdomains** |

What competitors charge for the same:
- HubSpot Marketing Hub Enterprise: $3,600/mo
- Salesforce Marketing Cloud: $1,250/mo
- Marketo: $1,195/mo

**You save ~$40,000/year** by building it.

---

## Why this beats trillion-dollar companies

1. **You own the data.** No vendor lock-in, no per-contact billing, no
   "we changed our pricing model" surprises.
2. **DZ-native.** Edahabia, Yalidine, Arabic-RTL, wilaya logic baked in
   at every layer. International tools never get this right.
3. **Cross-product superpower.** A customer of MyStoq becomes a lead for
   LIQAA + AlgeriaCertify automatically. SaaS giants can't do this because
   their products aren't owned by the same company.
4. **Founder-led personalisation.** Hot leads get a personal WhatsApp
   from the founder. HubSpot can't fake that.
5. **Speed.** You ship a new tool in 2 hours. HubSpot product roadmap is 18 months.

---

## What I'm scaffolding NEXT (concretely)

After this strategy doc, the immediate next move is to ship a **working
proof of concept** of the architecture pattern. I'll scaffold:

1. **try.tkawen.com** — universal signup portal (single page, picks product,
   creates account everywhere)
2. **capture endpoint** at `intel/capture.php` — receives leads from ANY
   subdomain via a 1-line JS snippet

Both deploy to the same VPS40, same Postgres, same auth — proving the
infrastructure scales horizontally for free.

Say the word and I start building Phase 1 component #2 (try.tkawen.com).
