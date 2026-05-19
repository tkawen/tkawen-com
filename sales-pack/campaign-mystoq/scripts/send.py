#!/usr/bin/env python3
"""
send.py — sends the MyStoq invite campaign via Resend API with throttling.

Setup:
    1. Sign up at https://resend.com (free 100/day, $20/mo for 50k)
    2. Verify domain news.mystoq.com (DKIM/SPF/DMARC via Resend dashboard)
    3. Generate API key, export as env var:
         export RESEND_API_KEY=re_xxxxxxxxxxxxx
    4. pip install requests

Usage:
    # Dry-run (writes to log, does NOT send):
    python send.py --wave wave-1-customers --variant A --dry-run

    # Real send, throttled to safe rate:
    python send.py --wave wave-1-customers --variant A --limit 100 --delay 2

Args:
    --wave        wave-1-customers | wave-2-subscribers | wave-3-cold
    --variant     A | B | C
    --limit N     send only N (warm-up: 50 → 200 → 500)
    --delay S     seconds between sends (default 2)
    --dry-run     log + render, don't actually send
"""
from __future__ import annotations

import argparse
import csv
import hashlib
import json
import os
import re
import sys
import time
from datetime import datetime, timedelta
from pathlib import Path
from urllib.parse import urlencode

try:
    import requests
except ImportError:
    print("ERROR: pip install requests")
    sys.exit(1)

# Force UTF-8 on Windows console (Arabic in subject + email)
try:
    sys.stdout.reconfigure(encoding="utf-8")  # type: ignore[attr-defined]
    sys.stderr.reconfigure(encoding="utf-8")  # type: ignore[attr-defined]
except (AttributeError, Exception):
    pass

# ─── config ──────────────────────────────────────────────────────
ROOT = Path(__file__).resolve().parent.parent
EMAIL_DIR = ROOT / "email"
LIST_DIR = ROOT / "lists"
LOG_DIR = ROOT / "logs"
LOG_DIR.mkdir(exist_ok=True)

# Adjust to your domain & sender
SENDER = "Yaakoub from TKAWEN <yaakoub@news.mystoq.com>"
REPLY_TO = "yaakoub@tkawen.com"

# Landing page URL (where the email CTA points)
LANDING_BASE = "https://tkawen.online/mystoq-invite/"
STORIES_BASE = "https://tkawen.online/mystoq-invite/stories/"
UNSUB_BASE = "https://tkawen.online/mystoq-invite/unsubscribe.php"
TRACK_BASE = "https://tkawen.online/mystoq-invite/pixel.php"  # 1x1 GIF

# Subject per variant (override with --subject)
DEFAULT_SUBJECTS = {
    "A":   "{first_name}، طريقة لتفتح مشروعك التجاريّ خلال 5 دقائق",
    "B":   "90 يوم متجر إلكترونيّ مجاناً — لك فقط كعضو TKAWEN",
    "C":   "{first_name}، هل فكّرتَ يوماً في بيع منتجات أونلاين؟",
    "FU1": "{first_name}، هل وصلتك رسالتي يوم الأحد؟",
    "FU2": "{first_name}، شاهد كيف فتحت سارة متجرها في 8 دقائق",
    "FU3": "{first_name}، آخر يوم — العرض ينتهي اليوم منتصف الليل",
}

# Template filename per variant
TEMPLATE_FILES = {
    "A":   "template-A-personal.html",
    "B":   "template-B-benefit.html",
    "C":   "template-C-question.html",
    "FU1": "followup-1-day3-bump.html",
    "FU2": "followup-2-day7-proof.html",
    "FU3": "followup-3-day14-final.html",
}

RESEND_API = "https://api.resend.com/emails"


# ─── helpers ─────────────────────────────────────────────────────
def make_token(user_id: str, email: str) -> str:
    """One-way token for unsubscribe + tracking — derived, no DB needed."""
    seed = f"{user_id}|{email}|tkawen-mystoq-2026q2".encode()
    return hashlib.sha256(seed).hexdigest()[:24]


def render_template(html_path: Path, ctx: dict) -> str:
    src = html_path.read_text(encoding="utf-8")
    for key, val in ctx.items():
        src = src.replace("{{" + key + "}}", str(val))
    return src


def send_one(api_key: str, sender: str, reply_to: str, to: str,
             subject: str, html: str, dry_run: bool = False) -> dict:
    if dry_run:
        return {"id": "dry-run", "status": "logged"}
    r = requests.post(
        RESEND_API,
        headers={"Authorization": f"Bearer {api_key}", "Content-Type": "application/json"},
        json={
            "from": sender,
            "to": [to],
            "reply_to": reply_to,
            "subject": subject,
            "html": html,
            "headers": {
                "X-Campaign": "tkawen-to-mystoq-2026q2",
                "List-Unsubscribe": f"<mailto:{reply_to}?subject=Unsubscribe>",
                "Precedence": "bulk",
            },
        },
        timeout=15,
    )
    if r.status_code >= 400:
        return {"error": r.text, "status": r.status_code}
    return r.json()


def log(entry: dict, logfile: Path) -> None:
    with logfile.open("a", encoding="utf-8") as f:
        f.write(json.dumps(entry, ensure_ascii=False) + "\n")


# ─── main ────────────────────────────────────────────────────────
def main() -> int:
    p = argparse.ArgumentParser()
    p.add_argument("--wave", required=True, help="any CSV name in lists/ (without .csv)")
    p.add_argument("--variant", required=True, choices=["A", "B", "C", "FU1", "FU2", "FU3"])
    p.add_argument("--subject", help="override default subject")
    p.add_argument("--limit", type=int, default=0, help="0 = no limit")
    p.add_argument("--delay", type=float, default=2.0, help="seconds between sends")
    p.add_argument("--dry-run", action="store_true")
    p.add_argument("--skip-first", type=int, default=0, help="resume after N rows")
    p.add_argument("--opt-outs", type=Path, help="path to opt-outs.log (downloaded from server)")
    args = p.parse_args()

    # Load opt-outs (user_ids that clicked unsubscribe)
    opt_out_ids: set[str] = set()
    if args.opt_outs and args.opt_outs.exists():
        with args.opt_outs.open("r", encoding="utf-8") as f:
            for line in f:
                parts = line.split("\t")
                if len(parts) >= 2:
                    opt_out_ids.add(parts[1].strip())
        print(f"Opt-outs loaded: {len(opt_out_ids)} users will be skipped\n")

    api_key = os.environ.get("RESEND_API_KEY", "")
    if not api_key and not args.dry_run:
        print("ERROR: set RESEND_API_KEY env var, or use --dry-run")
        return 1

    template = EMAIL_DIR / TEMPLATE_FILES[args.variant]
    if not template.exists():
        print(f"ERROR: template not found: {template}")
        return 1

    list_file = LIST_DIR / f"{args.wave}.csv"
    if not list_file.exists():
        print(f"ERROR: list not found: {list_file}. Run segment.py first.")
        return 1

    subject_template = args.subject or DEFAULT_SUBJECTS[args.variant]

    logfile = LOG_DIR / f"send-{args.wave}-{args.variant}-{datetime.now().strftime('%Y%m%d-%H%M%S')}.jsonl"

    print(f"Wave:     {args.wave}")
    print(f"Variant:  {args.variant}")
    print(f"Subject:  {subject_template}")
    print(f"Template: {template.name}")
    print(f"List:     {list_file.name}")
    print(f"Delay:    {args.delay}s between sends")
    print(f"Log:      {logfile}")
    print(f"Dry-run:  {args.dry_run}")
    print()

    if not args.dry_run:
        confirm = input("!! This will send REAL emails. Type 'SEND' to confirm: ")
        if confirm.strip() != "SEND":
            print("Cancelled.")
            return 0

    sent = 0
    failed = 0
    skipped = 0
    expires = (datetime.now() + timedelta(days=14)).strftime("%d/%m/%Y")

    with list_file.open("r", encoding="utf-8") as f:
        rows = list(csv.DictReader(f))

    for idx, row in enumerate(rows):
        if idx < args.skip_first:
            continue
        if args.limit and sent >= args.limit:
            break

        email = (row.get("email") or "").strip().lower()
        first_name = (row.get("first_name") or "").strip() or "صديقي"
        user_id = row.get("user_id") or ""
        reg_year = row.get("registered_year") or ""

        if not email:
            skipped += 1
            continue
        if user_id in opt_out_ids:
            skipped += 1
            continue

        token = make_token(user_id, email)
        user_params = {
            "u": user_id,
            "n": first_name,
            "e": email,
            "y": reg_year,
            "t": token,
            "v": args.variant,
        }
        landing = LANDING_BASE + "?" + urlencode(user_params)
        stories = STORIES_BASE + "?" + urlencode(user_params)
        unsub = UNSUB_BASE + "?" + urlencode({"u": user_id, "t": token})
        pixel = TRACK_BASE + "?" + urlencode({"u": user_id, "t": token, "v": args.variant})

        ctx = {
            "FIRST_NAME": first_name,
            "REG_YEAR": reg_year or str(datetime.now().year),
            "LANDING_URL": landing,
            "STORIES_URL": stories,
            "UNSUB_URL": unsub,
            "TRACK_PIXEL": pixel,
            "EXPIRES_DATE": expires,
        }

        html = render_template(template, ctx)
        subject = subject_template.replace("{first_name}", first_name)

        try:
            result = send_one(api_key, SENDER, REPLY_TO, email, subject, html, args.dry_run)
            success = "id" in result and "error" not in result
            log({
                "ts": datetime.now().isoformat(),
                "user_id": user_id,
                "email": email,
                "subject": subject,
                "variant": args.variant,
                "wave": args.wave,
                "success": success,
                "result": result,
            }, logfile)
            if success:
                sent += 1
                print(f"  [{sent:>4}] {email}  OK  {result.get('id', '')}")
            else:
                failed += 1
                print(f"  [{sent:>4}] {email}  FAIL  {result.get('error', '?')[:80]}")
        except Exception as e:
            failed += 1
            log({"ts": datetime.now().isoformat(), "email": email, "error": str(e)}, logfile)
            print(f"  [{sent:>4}] {email}  FAIL  {e}")

        if not args.dry_run:
            time.sleep(args.delay)

    print(f"\n{'─' * 50}")
    print(f"Sent:    {sent}")
    print(f"Failed:  {failed}")
    print(f"Skipped: {skipped}")
    print(f"Log:     {logfile}")
    return 0 if failed == 0 else 2


if __name__ == "__main__":
    raise SystemExit(main())
