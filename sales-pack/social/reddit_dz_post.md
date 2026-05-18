# Reddit Post — r/algeria + tech subreddits

**Subreddits to consider:**
- `r/algeria` (250k members — primary target)
- `r/Annaba` (smaller but engaged local)
- `r/Casablanca`, `r/MENA`, `r/maghreb` (broader)
- `r/selfhosted` (for LIQAA Meet OSS)
- `r/sysadmin` (for Sovereign Cloud angle)

**Reddit rules:** No self-promotion within 10:1 ratio. Comment on other posts first if you're new. Be a Redditor, not a marketer.

---

## Version 1 — r/algeria (Arabic, casual)

**Title:** `بنيت بنية تحتية رقمية سيادية للجزائر — TKAWEN — بعد سنتين هل تستحقّ؟`

```
السلام عليكم,

أنا يعقوب، خرّيج جامعة باجي مختار عنّابة، عمري 29 سنة. آخر
سنتين قعدت ندير شي اسمه TKAWEN — بنية تحتية سحابيّة سياديّة
للجزائر.

العيب لي حاولت نحلّو:

كلّ شركة ناشئة جزائريّة تخدم اليوم بـ Stripe + AWS + Twilio +
Auth0. كلّ شيء بالدولار، كلّ شيء يطلب فيزا أمريكيّة، البيانات
متاع الزبائن تخرج من البلاد.

TKAWEN يقدّم نفس الخدمات بالعربيّة، بالدينار، وعلى خوادم في
الجزائر. سبع طبقات:

1. الهوية (Auth + KYC ضدّ بطاقة التعريف)
2. الاتصال (vidéo + SMS + WhatsApp + TTS بالدارجة)
3. الدفع (EDAHABIA + CCP + فاتورة DGI auto)
4. التجارة (+200 تاجر LIVE)
5. المعرفة (LMS + شهادات QR)
6. اللوجستيك (Yalidine + CTM + Aramex)
7. السحابة للمطوّرين (API + 4 SDKs مفتوحة)

كلّ شيء LIVE اليوم. كلّ شيء قابل للمراجعة على GitHub.
كلّ شيء مفتوح المصدر (LIQAA Meet AGPL، SDKs MIT).

سؤالي للـ community:

1. هل في الـ developers الجزائريّين فعلاً مَن يحتاج هذا، ولا
   راهم مرتاحين مع Stripe/AWS؟
2. التسعير بالدينار metered (من 0.5 DZD لكلّ استدعاء API) —
   هل يبدو واضحاً ولا معقّداً؟
3. شو الطبقة الأكثر إثارة لاهتمامكم؟

الرابط: https://tkawen.com (Sandbox مجاناً، بلا بطاقة)
الكود: https://github.com/hartemyaakoub

شكراً مسبقاً على أيّ feedback صريح.
```

---

## Version 2 — r/selfhosted (English, technical)

**Title:** `LIQAA Meet — Open source Zoom alternative (AGPL-3.0, Rust+Next.js, runs on a single VPS)`

```
Hey r/selfhosted,

I've open-sourced LIQAA Meet — a full Zoom/Google Meet alternative
that runs on a single VPS. Source: github.com/liqaa-cloud/liqaa-meet

Stack:
- Next.js 16 frontend
- LiveKit (Go) for SFU
- Whisper.cpp via WASM for in-browser transcription (no data leaves
  the browser)
- Optional Postgres + Redis for persistent rooms
- AGPL-3.0 (free for self-hosting; commercial licensing available
  if you fork it into a closed product)

Features:
- 4-50 participants per room (depends on VPS specs)
- Recording (server-side, encrypted at rest)
- Live transcription with Arabic, French, English
- E2EE option for 2-party calls
- No accounts required for joining (only for hosting)
- Sub-100ms latency within MENA, sub-200ms globally

Single-binary deploy. systemd unit included. nginx config in repo.
Let's Encrypt via certbot webroot.

Why open-source it? Because the closed Zoom/Meet/Daily SaaS model
fights against self-hosting. We host the commercial version at
meet.liqaa.io but the source is identical to what you'd run.

Hosted at meet.liqaa.io if you want to try without setup. Source
at github.com/liqaa-cloud.

AMA — happy to walk through any architecture decision.
```

---

## Notes

- **r/algeria language**: Arabic + Darija mix is fine, much more authentic than pure Fusha
- **r/algeria timing**: 18:00-22:00 Algiers time (evening, post-dinner browsing)
- **r/selfhosted timing**: 15:00 UTC weekday (US workday afternoon)
- **DON'T**: link to commercial product without a "made by me, ask anything" disclosure
- **DON'T**: post the same content to 5 subreddits at once (cross-posting detection)
- **DO**: engage in 3-5 unrelated posts in the subreddit before/after your post (Reddit karma signaling)
- **DO**: respond to every top-level comment within 60 minutes during the first 4 hours
- **DO**: if mods remove the post for "self-promotion", message them respectfully — sometimes they allow it if you ask
