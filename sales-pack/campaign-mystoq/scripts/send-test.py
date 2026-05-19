#!/usr/bin/env python3
"""
send-test.py — send ONE test email to verify everything works.

Usage:
    export RESEND_API_KEY=re_xxxxx
    python send-test.py --to your-test@gmail.com --variant A
"""
import argparse
import os
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))
from send import render_template, send_one, make_token, EMAIL_DIR, SENDER, REPLY_TO  # noqa: E402

from datetime import datetime, timedelta
from urllib.parse import urlencode


def main():
    p = argparse.ArgumentParser()
    p.add_argument("--to", required=True, help="your test email address")
    p.add_argument("--variant", default="A", choices=["A", "B", "C"])
    p.add_argument("--name", default="حرتام", help="first name for personalisation")
    args = p.parse_args()

    api_key = os.environ.get("RESEND_API_KEY", "")
    if not api_key:
        print("ERROR: set RESEND_API_KEY env var")
        return 1

    variant_name = {"A": "personal", "B": "benefit", "C": "question"}[args.variant]
    template = EMAIL_DIR / f"template-{args.variant}-{variant_name}.html"

    user_id = "test-000"
    token = make_token(user_id, args.to)
    landing = "https://tkawen.online/mystoq-invite/?" + urlencode({
        "u": user_id, "n": args.name, "e": args.to, "y": "2024", "t": token, "v": args.variant,
    })

    ctx = {
        "FIRST_NAME": args.name,
        "REG_YEAR": "2024",
        "LANDING_URL": landing,
        "UNSUB_URL": "https://tkawen.online/mystoq-invite/unsubscribe.php?u=test-000&t=" + token,
        "TRACK_PIXEL": "https://tkawen.online/mystoq-invite/pixel.php?u=test-000&t=" + token,
        "EXPIRES_DATE": (datetime.now() + timedelta(days=14)).strftime("%d/%m/%Y"),
    }
    html = render_template(template, ctx)

    subjects = {
        "A": f"{args.name}، طريقة لتفتح مشروعك التجاريّ خلال 5 دقائق",
        "B": "90 يوم متجر إلكترونيّ مجاناً — لك فقط كعضو TKAWEN",
        "C": f"{args.name}، هل فكّرتَ يوماً في بيع منتجات أونلاين؟",
    }

    print(f"Sending variant {args.variant} to {args.to}...")
    result = send_one(api_key, SENDER, REPLY_TO, args.to, subjects[args.variant], html)
    print("Result:", result)
    return 0 if "id" in result else 1


if __name__ == "__main__":
    raise SystemExit(main())
