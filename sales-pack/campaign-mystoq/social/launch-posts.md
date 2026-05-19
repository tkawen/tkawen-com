# Launch posts — Reddit / HN / ProductHunt / IndieHackers

Major-platform launch posts. Each one has lottery potential — most go
nowhere, but ONE landing on Hacker News front page or ProductHunt top-5
can drive 5,000-20,000 visitors in 24 hours.

**Strategy:** post one per week, spaced out. Don't blast all four
simultaneously — if HN flags you as a serial promoter, your account
gets shadow-banned.

**Hard rule:** no AI-generated tone. These read clearly as a founder
who built something. Numbers must be real. Pain points must be visceral.

---

## 1. Hacker News — "Show HN"

**Title (max 80 chars):**
> Show HN: MyStoq – Shopify for Algeria with native payments and carriers

**First-comment context (HN expects you to drop a comment with the story):**
> Hi HN — I'm Yaakoub from Algeria. I built MyStoq because I watched too many
> Algerian merchants try Shopify, hit the wall, and quit.
>
> The wall has 4 bricks:
> 1. **Pricing in USD** — 39$/mo = ~5,300 DZD = a meaningful slice of monthly
>    revenue for a small DZ store.
> 2. **No native payment** — Algeria's Edahabia/CIB cards don't work with
>    Stripe. You can plug a Chargily/PayCity gateway, but it's bolted on,
>    not integrated.
> 3. **Carrier integration is manual** — Aramex, Yalidine, CTM, ZR Express
>    each have their own portal. Merchants copy-paste tracking IDs from
>    Excel into Shopify orders.
> 4. **Arabic RTL is half-broken** — Shopify's RTL works for the product
>    page but breaks in the cart, checkout, and admin in subtle ways
>    (form alignment, currency placement, etc.).
>
> MyStoq is a Laravel + Vue + LiveKit stack. Multi-tenant from day 1 (each
> merchant gets `merchant.mystoq.com` or their custom domain). Stack
> highlights:
>
> - **Tenancy:** stancl/tenancy with per-tenant DB connection on the same
>   physical Postgres
> - **Payments:** native Edahabia/CIB via Chargily, plus Tabby/Tamara BNPL
>   for the MENA layer
> - **Carriers:** 4 DZ-domestic + 2 international, each with quote/label/
>   track APIs reduced to one shipping abstraction
> - **WhatsApp Commerce:** Cloud API integration per tenant — products
>   sync to WA catalogue, orders flow back via webhook
> - **i18n:** AR/FR/EN, 13 currencies, FX from Frankfurter + fawazahmed0
>   CDN (fallback chain for MENA pairs)
>
> 90-day free trial active for the launch — no card required.
>
> The repo isn't public yet (will be AGPL-3.0 once we're past the cleanup
> pass), but the developer docs are at developer.tkawen.com if you're
> curious how the multi-tenant currency engine works under the hood.
>
> Happy to answer any questions about building SaaS for emerging markets,
> currency hedging on the merchant side, WhatsApp Commerce gotchas, or
> the joy of debugging RTL CSS at 2am.

**URL:** `https://mystoq.com/?utm_source=hn&utm_medium=social&utm_campaign=show-hn`

**Timing:** Tuesday 9am ET (= 3pm DZ). Avoid Mondays (volume) and Fridays
(quiet). Have 2-3 friends ready to upvote in the first 30 min (NOT
brigaded — just primed).

**If you hit top 30:** stay AT THE COMPUTER for 6 hours and reply to every
comment. HN punishes posters who disappear.

---

## 2. Reddit r/algeria — DZ-native angle

**Title:**
> أنا جزائري بنيتُ Shopify خاصّ بنا (يدعم Edahabia، Yalidine، WhatsApp). 90 يوم مجاناً لأوّل 100

**Body:**
> سلام جماعة،
>
> منذ سنتين وأنا أرى مئات من الجزائريّين يحاولون فتح متاجر إلكترونيّة على
> Shopify ويفشلون. ليس لأنّ منتجاتهم سيّئة — لأنّ الأدوات الأجنبيّة لا تفهم
> سوقنا:
>
> · 39$ شهريّاً = أزيد من 5,000 دج
> · لا يدعم بطاقات Edahabia/CIB
> · لا يتكامل مع Yalidine, ZR Express, CTM
> · الواجهة العربيّة RTL مكسورة في عدّة أماكن
>
> فبنيتُ MyStoq. منصّة بالكامل بالعربيّة، الأسعار بالدّينار، دفع بـ Edahabia
> مدمج، و4 شركات توصيل جزائريّة في API واحد.
>
> أعطي 90 يوم استخدام كامل مجانيّ لأوّل 100 شخص يجرّبه — بدون بطاقة بنكيّة.
>
> [الرّابط]
>
> صراحة، لستُ هنا لأبيع. لو كان عندي 100 مستخدم نشط أكون مرتاحاً. أحتاج
> ملاحظاتكم — ما هو الشّيء الّذي تبدو لكم ناقصاً؟

**URL:** `https://mystoq.com/?utm_source=reddit&utm_medium=social&utm_campaign=algeria-launch`

**Timing:** Sunday or Monday evening (= when DZ users are scrolling).

**Subreddits to target (in this order):**
1. r/algeria (300k+ members)
2. r/MENAStartups
3. r/Entrepreneur (only if the post explicitly mentions emerging markets)
4. r/SaaS (only if you frame it as "lessons from building for emerging markets")

**DON'T** post to r/Shopify or r/ecommerce — they'll flag it as competitor
promotion. Their rules are strict.

---

## 3. ProductHunt

**Tagline (max 60 chars):**
> Shopify for Algeria — native payments, carriers, WhatsApp

**Description:**
> Algerian merchants tried Shopify and gave up. Pricing in USD, no Edahabia
> support, no native carriers (Aramex/Yalidine/CTM), half-broken RTL.
>
> MyStoq is an e-commerce platform built ground-up for Algeria + MENA:
>
> ⚡ 8-minute setup, Arabic RTL throughout
> 💳 4 DZ payment methods (Edahabia, CIB, Tabby, Tamara)
> 📦 4 native carriers with one shipping API
> 💬 WhatsApp Business integration per tenant
> 🌍 13 currencies, 3 languages, automatic FX
> 💰 5,000 DZD/month after a 90-day free trial (no card required)
>
> Built by a solo founder over 2 years. Stack: Laravel 12, Vue 3, multi-
> tenant Postgres.
>
> 90 days free for ProductHunt launch — use code PHLAUNCH90.

**Maker comment (post first thing after launch goes live):**
> Hey Hunters! I'm Yaakoub from Algeria. I built MyStoq because I'm tired
> of seeing DZ entrepreneurs lose to bad tooling.
>
> Happy to answer anything about:
> - Building SaaS for emerging markets
> - Multi-tenancy with stancl/tenancy in Laravel
> - WhatsApp Commerce gotchas
> - Why the official MENA payment integrations are so painful
> - How we get FX rates that ACTUALLY work for DZ/EG/TN/MA pairs
>
> Will be at the computer all day for AMA-style questions.

**Pre-launch checklist:**
- Create ProductHunt account >30 days before launch (new accounts get
  flagged as promo)
- Submit product as "scheduled launch" — gives PH 7 days to vet you
- Have a 3-screenshot carousel ready (homepage, dashboard, mobile mockup)
- Tagline-of-the-day rotation — PH shows 4 launches per slot, you compete
  with 3 others for the day

**Timing:** Wednesday or Thursday. PH algorithm boosts mid-week launches.
DON'T launch on weekends.

**URL:** `https://mystoq.com/?utm_source=producthunt&utm_medium=social&utm_campaign=ph-launch`

**Realistic outcome:** top-5 in your category = ~500-1,500 visitors. #1 of
the day = 5,000-10,000.

---

## 4. IndieHackers

**Title:**
> Built an e-commerce platform for my country (Algeria) — 2 years, solo, finally launching

**Body:**
> Hi IH,
>
> 2 years of nights-and-weekends later, I'm finally launching MyStoq —
> a Shopify-equivalent built specifically for the Algerian market.
>
> **The why:**
> Algeria has ~45M people, growing middle class, and a real appetite for
> online shopping. But the e-commerce tooling situation is brutal:
> - Shopify is too expensive (USD pricing + no DZ payment support)
> - WooCommerce requires technical skill most merchants don't have
> - Local players exist but are basic + non-standard APIs
>
> **What's different:**
> - Native Edahabia/CIB/Tabby/Tamara payments
> - 4 DZ carriers with unified shipping API
> - WhatsApp Commerce (DZ does 70%+ of mobile commerce via WhatsApp)
> - 13 currencies for the MENA + EU expansion path
> - Arabic-first UI (proper RTL throughout, not bolted on)
>
> **The numbers I care about (closed beta):**
> - 30 merchants on the platform
> - 8-minute median setup time
> - 3.2% storefront conversion rate (vs ~1.4% Shopify avg in DZ)
> - First customer revenue typically within Day 6
>
> **What I'm asking IH for:**
> 1. Honest feedback on the landing page
> 2. If you've built SaaS for emerging markets — what's the #1 thing
>    you'd warn me about?
> 3. If you're a Laravel/Vue dev who'd want to peek at the multi-tenant
>    architecture — DM me, I'll give you a guided tour
>
> https://mystoq.com/?utm_source=indiehackers
>
> Cheers,
> Yaakoub

**Timing:** Any weekday morning. IH is slower-burn — posts get traction
over 48-72 hours instead of 6.

**Bonus:** post a follow-up "1 week later" thread with real numbers.
IH loves the transparency arc.

---

## Coordinated launch week (week 5-6 of campaign)

| Day | Platform | Headline focus |
|-----|----------|---------------|
| Tue | HN Show HN | Technical stack + emerging-market angle |
| Wed | ProductHunt | Solo founder + DZ focus |
| Thu | IndieHackers | 2-year build + real metrics |
| Sun | Reddit r/algeria | DZ-native, Arabic copy |

Combined optimistic reach: **8,000-25,000 visitors**, ~3-8% click-through
to signup = **240-2,000 free trials**. Conservative target: 50-100 trials.

---

## What absolutely NOT to do

- **No fake screenshots.** HN/Reddit will eat you alive if a screenshot
  shows fake data or numbers don't match elsewhere.
- **No paid upvote rings.** Both HN and PH detect them and ban accounts.
- **No "we're disrupting Shopify".** Shopify isn't your enemy — your
  enemy is "merchant chose Shopify, couldn't make it work, gave up".
- **No comment edits >30 min after posting.** HN shows "edited" badge
  prominently and people get suspicious.
- **No politics, no sovereignty claims.** Tech post stays tech.
