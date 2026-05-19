#!/usr/bin/env python3
"""
whatsapp-links.py — generates a CSV with click-to-chat WhatsApp URLs
pre-filled with personalised message for each high-priority user.

Reads:  ../lists/wave-1-customers.csv  (or any list)
Writes: ../lists/wa-links-<wave>.csv  with columns:
         user_id, first_name, email, phone, wa_link_pitch, wa_link_followup

Open the resulting CSV in Excel. For each row, copy the WA link and paste it
into a browser — opens WhatsApp with the message pre-typed. You only click Send.

Usage:
    python whatsapp-links.py --wave wave-1-customers --template pitch
    python whatsapp-links.py --wave wave-1-customers-non-signups --template followup
"""
from __future__ import annotations

import argparse
import csv
import hashlib
import sys
from datetime import datetime, timedelta
from pathlib import Path
from urllib.parse import quote, urlencode

try:
    sys.stdout.reconfigure(encoding="utf-8")  # type: ignore[attr-defined]
except Exception:
    pass

ROOT = Path(__file__).resolve().parent.parent
LISTS = ROOT / "lists"

LANDING_BASE = "https://tkawen.online/mystoq-invite/"

EXPIRES = (datetime.now() + timedelta(days=14)).strftime("%d/%m/%Y")

TEMPLATES = {
    "pitch": (
        "السلام عليكم {name}،\n\n"
        "أنا يعقوب، مؤسّس TKAWEN. أنتَ من عملائنا الأوائل، ولهذا أكتب لكَ مباشرةً.\n\n"
        "خصّصتُ لكَ ولأعضاء TKAWEN فقط *90 يوم مجاناً* على MyStoq — متجر إلكترونيّ كامل مع WhatsApp Commerce.\n\n"
        "الرابط المخصّص لكَ (يحفظ بياناتك):\n{url}\n\n"
        "أو ردّ هنا بأيّ سؤال — أنا متاح."
    ),
    "followup": (
        "{name}، تذكير ودّيّ.\n\n"
        "العرض الذي أرسلتُ لكَ عنه (90 يوم MyStoq مجاناً) ينتهي يوم {expires}.\n\n"
        "نفس الرابط المخصّص:\n{url}\n\n"
        "إن لم تكن مهتمّاً، لا تردّ — وسأفهم. شكراً على كلّ حال."
    ),
    "activation": (
        "{name}، شكراً لتسجيلك في MyStoq!\n\n"
        "هل تحتاج 15 دقيقة معي على WhatsApp لتجهيز متجرك في يومه الأوّل؟\n\n"
        "أنا متاح اليوم بين 9 صباحاً و8 مساءً. أيّ وقت يناسبك؟"
    ),
}


def make_token(user_id: str, email: str) -> str:
    seed = f"{user_id}|{email}|tkawen-mystoq-2026q2".encode()
    return hashlib.sha256(seed).hexdigest()[:24]


def clean_phone(p: str) -> str:
    """Normalise DZ phone to international format for wa.me (must be digits only,
    no +, no leading 0). DZ country code is 213."""
    if not p:
        return ""
    digits = "".join(c for c in p if c.isdigit())
    if not digits:
        return ""
    # If starts with 0, replace with 213
    if digits.startswith("0"):
        digits = "213" + digits[1:]
    elif digits.startswith("213"):
        pass
    elif len(digits) == 9:  # bare DZ number without leading 0
        digits = "213" + digits
    return digits


def main():
    p = argparse.ArgumentParser()
    p.add_argument("--wave", required=True)
    p.add_argument("--template", default="pitch", choices=list(TEMPLATES.keys()))
    args = p.parse_args()

    in_file = LISTS / f"{args.wave}.csv"
    if not in_file.exists():
        print(f"ERROR: {in_file} not found")
        return 1

    tpl = TEMPLATES[args.template]

    out_file = LISTS / f"wa-links-{args.wave}-{args.template}.csv"
    n_with_phone = 0
    n_total = 0

    with in_file.open("r", encoding="utf-8") as fin, out_file.open("w", encoding="utf-8", newline="") as fout:
        reader = csv.DictReader(fin)
        writer = csv.DictWriter(fout, fieldnames=[
            "user_id", "first_name", "email", "phone", "wa_link", "landing_url"
        ])
        writer.writeheader()
        for row in reader:
            n_total += 1
            user_id = row.get("user_id", "")
            first_name = row.get("first_name", "") or "صديقي"
            email = row.get("email", "")
            # Accept either pre-cleaned phone_e164 or raw phone column
            phone = row.get("phone_e164", "").strip() or clean_phone(row.get("phone_raw", "") or row.get("phone", ""))

            token = make_token(user_id, email)
            url = LANDING_BASE + "?" + urlencode({
                "u": user_id, "n": first_name, "e": email,
                "y": row.get("registered_year", ""), "t": token, "v": "WA",
            })
            msg = tpl.format(name=first_name, url=url, expires=EXPIRES)
            # wa.me/<phone>?text=<urlencoded>; if no phone, use wa.me link with text only
            if phone:
                wa = f"https://wa.me/{phone}?text={quote(msg)}"
                n_with_phone += 1
            else:
                wa = f"https://wa.me/?text={quote(msg)}"  # opens share-to-WA picker

            writer.writerow({
                "user_id": user_id, "first_name": first_name, "email": email,
                "phone": phone, "wa_link": wa, "landing_url": url,
            })

    print(f"\nWA links generated: {out_file}")
    print(f"  Total rows:      {n_total}")
    print(f"  With phone:      {n_with_phone}  (clickable wa.me/<phone> link)")
    print(f"  Without phone:   {n_total - n_with_phone}  (opens WA share dialog)")
    print(f"  Template:        {args.template}\n")
    print(f"Next: open {out_file.name} in Excel. For each high-priority row,")
    print(f"      copy wa_link into browser → WhatsApp opens with message pre-typed → click Send.\n")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
