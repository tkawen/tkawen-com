#!/usr/bin/env python3
"""
preview.py — render all 3 email variants personalised to a given recipient
and save as HTML files. No API key needed. Open the files in any browser.

Usage:
    python preview.py --to you@example.com --name يعقوب
"""
from __future__ import annotations

import argparse
import sys
import webbrowser
from datetime import datetime, timedelta
from pathlib import Path
from urllib.parse import urlencode

sys.path.insert(0, str(Path(__file__).parent))
from send import EMAIL_DIR, make_token, render_template  # noqa: E402

ROOT = Path(__file__).resolve().parent.parent
PREVIEW_DIR = ROOT / "preview"
PREVIEW_DIR.mkdir(exist_ok=True)

VARIANTS = {
    "A": ("personal", "{name}، طريقة لتفتح مشروعك التجاريّ خلال 5 دقائق"),
    "B": ("benefit", "90 يوم متجر إلكترونيّ مجاناً — لك فقط كعضو TKAWEN"),
    "C": ("question", "{name}، هل فكّرتَ يوماً في بيع منتجات أونلاين؟"),
}


def main():
    p = argparse.ArgumentParser()
    p.add_argument("--to", required=True)
    p.add_argument("--name", default="صديقي")
    p.add_argument("--user-id", default="preview-0")
    p.add_argument("--year", default="2025")
    p.add_argument("--no-open", action="store_true", help="do not auto-open in browser")
    args = p.parse_args()

    try:
        sys.stdout.reconfigure(encoding="utf-8")  # type: ignore[attr-defined]
    except Exception:
        pass

    token = make_token(args.user_id, args.to)
    expires = (datetime.now() + timedelta(days=14)).strftime("%d/%m/%Y")

    base_params = {
        "u": args.user_id, "n": args.name, "e": args.to,
        "y": args.year, "t": token, "v": "X",
    }
    landing = "https://tkawen.online/mystoq-invite/?" + urlencode(base_params)
    stories = "https://tkawen.online/mystoq-invite/stories/?" + urlencode(base_params)

    out_files = []
    for variant, (kind, subject_tpl) in VARIANTS.items():
        tpl = EMAIL_DIR / f"template-{variant}-{kind}.html"
        if not tpl.exists():
            print(f"  MISSING: {tpl}")
            continue
        subject = subject_tpl.replace("{name}", args.name)
        ctx = {
            "FIRST_NAME": args.name,
            "REG_YEAR": args.year,
            "LANDING_URL": landing.replace("v=X", f"v={variant}"),
            "STORIES_URL": stories.replace("v=X", f"v={variant}"),
            "UNSUB_URL": f"https://tkawen.online/mystoq-invite/unsubscribe.php?u={args.user_id}&t={token}",
            "TRACK_PIXEL": f"https://tkawen.online/mystoq-invite/pixel.php?u={args.user_id}&v={variant}&t={token}",
            "EXPIRES_DATE": expires,
        }
        html = render_template(tpl, ctx)
        # Wrap with a subject-line banner so the preview shows what the user will see in inbox
        banner = f"""<!DOCTYPE html>
<html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>Variant {variant} preview</title>
<style>
body{{margin:0;background:#f1f5f9;font-family:'Cairo',system-ui,sans-serif}}
.meta{{background:#0f172a;color:#fff;padding:16px 24px;font-size:13px;line-height:1.6}}
.meta b{{color:#fbbf24}}
.meta code{{background:rgba(255,255,255,.1);padding:2px 8px;border-radius:4px;font-size:12px}}
.email{{max-width:680px;margin:24px auto;background:#fff;box-shadow:0 4px 24px rgba(0,0,0,.08)}}
</style></head><body>
<div class="meta">
  <b>VARIANT {variant} ({kind})</b><br>
  <b>From:</b> Yaakoub from TKAWEN &lt;yaakoub@news.mystoq.com&gt;<br>
  <b>To:</b> {args.to}<br>
  <b>Subject:</b> {subject}<br>
  <b>Landing URL:</b> <code>{landing.replace('v=X', f'v={variant}')[:120]}...</code>
</div>
<div class="email">
{html}
</div>
</body></html>"""
        out = PREVIEW_DIR / f"preview-{variant}-{kind}.html"
        out.write_text(banner, encoding="utf-8")
        out_files.append(out)
        print(f"  Variant {variant} ({kind:8s}) -> {out}")
        print(f"    Subject: {subject}")

    print(f"\n{len(out_files)} preview file(s) written to: {PREVIEW_DIR}")
    if not args.no_open and out_files:
        for f in out_files:
            webbrowser.open(f.as_uri())
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
