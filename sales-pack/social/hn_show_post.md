# Hacker News — Show HN Post

**Title (80 chars max — strict):**
- `Show HN: TKAWEN – Sovereign cloud for Algeria, served in 119μs from Rust`
- (alt) `Show HN: Algerian sovereign-cloud alternative to AWS+Stripe+Twilio in DZD`

**Best posting time:** Tuesday-Thursday, 14:00-17:00 UTC (peak HN traffic, EU + US East morning).
**Account:** Use your existing HN account `hartemyaakoub` (has karma — better placement).

---

## URL Field

`https://tkawen.com`

## Comment (first comment yourself, posted ~30 seconds after submission)

```
Hi HN, Hartem here. I run TKAWEN, a sovereign cloud platform for
Algeria — basically the AWS+Stripe+Twilio+Shopify equivalent for
a country where:

- foreign cloud requires a Visa card most founders don't have
- data residency outside DZ is illegal for several regulated
  sectors (health, finance, education)
- billing in USD adds 7-15% FX cost
- documentation in English/French is a real barrier for the
  Arabic-native developer pool

The platform has 7 pillars (Identity, Connect, Pay, Commerce,
Knowledge, Logistics, Developer Cloud), 4 production SaaS apps
built on top of it (a pharmacy ERP, a multi-tenant e-commerce
backend with 200+ live merchants, a credential issuance system
with 4116 verified practitioners, and an open-source Zoom
alternative under AGPL), and 22 OSS repos at github.com/hartemyaakoub.

Two technical bits HN might find interesting:

1. The marketing site you're looking at (tkawen.com) is
   server-rendered in Rust (Axum + Maud + rust-embed). 3.6 MB
   stripped binary, ~6 MB resident. Sub-millisecond render
   times (verifiable in the `Server-Timing: render;dur=X.XXX`
   response header — try `curl -I tkawen.com`).

2. The real-time layer (LIQAA) is post-WebRTC — we hit
   limitations of WebRTC for low-bandwidth DZ networks and
   wrote a custom UDP protocol with unidirectional control
   streams + datagram media. MVP-0 and MVP-1 (echo + 2-party
   room) are live at rt.liqaa.io. Source coming soon.

I'd love feedback specifically on:

- The API surface design (currently being formalized in OpenAPI)
- The pricing model for emerging-market sovereign cloud
  (we're billing in DZD with metered per-call pricing, but
  unclear if "from 0.5 DZD per call" reads better than per-1000)
- Whether the 7-pillar narrative makes sense or feels overstuffed

Sandbox is free, no credit card. Docs are at developer.tkawen.com.
Happy to answer anything.
```

---

## Notes for posting

- **DO NOT** add UTM params to the URL — HN community will downvote
- **DO NOT** use emoji in title or first comment
- **DO NOT** post and immediately ask friends to upvote (HN detects voting rings; account dies)
- **DO** respond to every single comment within the first 4 hours (algorithmic boost + community goodwill)
- **DO** mention `Show HN:` prefix exactly (case-sensitive in HN flair)
- **DO** time it so you're awake & responsive for 6+ hours after
- **Bonus**: respond in technical depth — HN values "actually answers the question" over "spins"

## If it hits front page (top 30)

- Expect ~5,000-30,000 visitors in 24 hours
- VPS40 should handle this (Rust + 12 cores), but **monitor**:
  - `journalctl -u tkawen-com -f`
  - `htop`
  - status.tkawen.com
- Have your phone ready to respond to comments

## Backup posts if HN doesn't bite

- r/algeria — Arabic version, focus on sovereignty + careers
- r/programming — focus on Rust + sub-ms render flex
- r/selfhosted — focus on LIQAA Meet open source
- DZ tech Slack/Discord communities
