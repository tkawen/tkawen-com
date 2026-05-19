# TKAWEN → MyStoq invitation campaign

Convert TKAWEN's 3,827 users into MyStoq merchants. Three channels: bulk email,
WhatsApp click-to-chat for 114 phone-equipped users, and direct phone calls to
the top 50 most-engaged prospects.

---

## Status

| Asset | State |
|-------|-------|
| Landing page (https://tkawen.online/mystoq-invite/) | **LIVE** |
| Form handler + tracking pixel + unsubscribe | **LIVE** |
| 3 cold-email templates (A/B/C) | **READY** |
| 3 follow-up templates (D+3, +7, +14) | **READY** |
| 2 onboarding emails (D+0 welcome, +3 check-in) | **READY** |
| User list segmentation (943 customers + 376 hot + 2,506 cold) | **DONE** |
| Top-50 phone-call prospects | **DONE** (48 with phone) |
| 114 WhatsApp click-to-chat links | **READY** |
| Pre-flight launch orchestrator | **GREEN** (6/7 — Resend key pending) |
| First test email sent to founder inbox | **DONE** |

---

## Files

```
campaign-mystoq/
├── README.md                        ← you are here
├── email/
│   ├── template-A-personal.html     ← founder voice (Wave 1)
│   ├── template-B-benefit.html      ← benefit-led hero (Wave 1 B-test)
│   ├── template-C-question.html     ← single question (Wave 1 C-test)
│   ├── followup-1-day3-bump.html    ← "did you see it?" (non-openers)
│   ├── followup-2-day7-proof.html   ← case study (non-clickers)
│   ├── followup-3-day14-final.html  ← last day (non-signups)
│   ├── onboarding-day0-welcome.html ← post-signup
│   ├── onboarding-day3-checkin.html ← "how's it going?"
│   ├── subjects.md                  ← 11 A/B subject variants
│   └── plain-text-fallback.md
├── landing/  (deployed to tkawen.online/mystoq-invite/)
│   ├── index.php          ← form, pre-filled from URL params
│   ├── submit.php         ← captures lead -> mystoq.com/signup
│   ├── pixel.php          ← 1×1 GIF, logs opens
│   ├── unsubscribe.php    ← one-click opt-out
│   ├── thanks.php · .htaccess
├── whatsapp/
│   └── messages.md        ← 5 scripts: cold, pitch, last-day, welcome, activation
├── sales/
│   ├── phone-script.md    ← 15-min call script + objection handling
│   └── top-20-prospects.template.csv
├── scripts/
│   ├── segment.py             ← splits user list into waves
│   ├── send.py                ← bulk Resend sender (throttled, all 6 variants)
│   ├── send-test.py           ← single test send
│   ├── preview.py             ← renders HTML preview without sending
│   ├── filter.py              ← generates non-opener/clicker/signup lists
│   ├── whatsapp-links.py      ← per-user wa.me/<phone>?text= links
│   ├── build-top-prospects.py ← curates top-50 from engagement data
│   ├── launch.py              ← pre-flight + safe-launch orchestrator
│   ├── warroom.py             ← daily funnel dashboard
│   └── dashboard.py           ← basic stats from logs
└── lists/
    ├── tkawen-users-engaged.csv     (3,827 — full data with engagement score)
    ├── wave-1-customers.csv         (943)
    ├── wave-2-subscribers.csv       (376)
    ├── wave-3-cold.csv              (2,506)
    ├── top-50-prospects.csv         (48 with phone — phone-call priority)
    ├── phone-targets.csv            (114 with phone — WhatsApp + phone)
    └── wa-links-phone-targets-pitch.csv  (114 click-to-chat links)
```

---

## ONE-CLICK LAUNCH (after Resend is set up)

```powershell
cd D:\F\tkawen-com\sales-pack\campaign-mystoq\scripts
$env:RESEND_API_KEY = "re_xxxxxxxxxxxxx"

# 1. Pre-flight + verification send to your inbox (no bulk send yet)
python launch.py --my-email you@example.com --variant A

# After you verify the email landed, re-run with --actually-send for warm-up
python launch.py --my-email you@example.com --variant A --actually-send
```

`launch.py` will:
1. Check all 7 pre-conditions (Resend key, lists, templates, landing page reachable, etc.)
2. Send ONE email to your inbox first
3. Wait for you to confirm it arrived correctly
4. Send the warm-up batch (50 emails by default)
5. Print what to do over the next 72 hours

---

## Setup (one-time, ~30 min)

### 1. Resend.com account
1. Sign up: https://resend.com (free: 100/day, 3k/mo — enough for warm-up)
2. Add domain **`news.mystoq.com`**
3. Add the 3 DNS records Resend gives you (SPF + DKIM + DMARC) in Cloudflare
4. Wait ~10 min for verification
5. Create API key with **Sending access** scope

### 2. Local prep
```powershell
pip install requests
$env:RESEND_API_KEY = "re_xxxxxxxxxxxxx"
```

### 3. mystoq.com/signup must accept `?promo=TKAWEN90`
The signup page needs to:
- Pre-fill email from `?email=`
- Auto-apply 90-day free tier if `?promo=TKAWEN90` is present
- Track UTM params for attribution

---

## Daily workflow during the campaign

```powershell
cd D:\F\tkawen-com\sales-pack\campaign-mystoq\scripts
python warroom.py
```

Shows the full funnel + tells you what to do today. Refresh each morning.

---

## Three parallel channels

### Channel 1 — Email (943 paying customers)
- Day 0: warm-up 50 with variant A
- Day 1: scale to 200 (A vs B test)
- Day 2: finish 943 with winning variant
- Day 4: follow-up 1 to non-openers
- Day 8: follow-up 2 with case study to non-clickers
- Day 15: follow-up 3 last-call to non-signups
- Throughout: onboarding emails to anyone who signs up

### Channel 2 — WhatsApp (114 phone-equipped users)
- Open `lists/wa-links-phone-targets-pitch.csv` in Excel
- Click any `wa_link` → WhatsApp opens with personalised message pre-typed
- 3-second pause between sends (DON'T send to >20 in same hour from same number)
- Use templates in `whatsapp/messages.md`

### Channel 3 — Phone calls (top 20 of 48 with phone)
- Open `lists/top-50-prospects.csv` in Excel
- Filter `outreach_priority = P0-CALL`
- Use `sales/phone-script.md` (15-min call + objection handling)
- Expected close rate: 30-50% (vs ~5% from cold email)

---

## Expected outcomes

|                              | Conservative | Realistic | Excellent |
|------------------------------|--------------|-----------|-----------|
| Email opens (Wave 1 + FUs)   | 25%          | 33%       | 42%       |
| Click → landing              | 5%           | 8%        | 12%       |
| Landing → signup             | 18%          | 25%       | 35%       |
| Signup → activated (30d)     | 35%          | 45%       | 55%       |
| Activated → paying after 90d | 50%          | 60%       | 70%       |
| **Email-only MRR (943×)**    | **15k DZD**  | **35k**   | **75k**   |
| **Phone-call close rate**    | 25%          | 40%       | 55%       |
| **Phone MRR (20 calls × 5k)**| **25k DZD**  | **40k**   | **55k**   |
| **WhatsApp close rate**      | 8%           | 15%       | 25%       |
| **WA MRR (114 × 5k)**        | **45k DZD**  | **85k**   | **140k**  |
| **TOTAL MRR**                | **85k DZD**  | **160k**  | **270k**  |
| **Annual revenue**           | **~1M DZD**  | **~1.9M** | **~3.2M** |

---

## Hard rules

- **Never email all 3,827 on day 1** — sender reputation will die instantly
- **Never use mystoq.com as sender** — use subdomain `news.mystoq.com`
- **Never send before DKIM/SPF/DMARC are verified** at Resend
- **Never use Eastern Arabic numerals (٠-٩)** — DZ users read 0-9
- **Never claim "ministry-approved" / "سياديّة"** anywhere
- **Never call before 10am or after 8pm** Algiers time
- **Never reuse the same template on follow-up** — different angle each time
- **Never skip dry-run** — costs nothing, catches template bugs

---

## What to rotate AFTER campaign

- FTP password `ry^TwW6R0$eh` (host: oort-shared.dzsecurity.net)
- cPanel password (same)
- WP DB password (in wp-config.php)
- Both GitHub PATs from earlier sessions
- Resend API key (every 90 days)

---

## Cost breakdown (first 30 days)

| Item              | Cost      |
|-------------------|-----------|
| Resend free tier  | 0 DZD     |
| tkawen.online     | already paid |
| Your time         | ~10 hours total (90 min/day × 7 days warm-up + follow-up) |
| **Total**         | **0 DZD** |

For ~30-50 new paying merchants × 5k DZD/mo. **Payback: instant.**
