#!/usr/bin/env python3
"""
build-top-prospects.py — curates the top 50 highest-intent prospects from the
engagement-scored CSV, excluding self/test accounts.

Output:
    ../lists/top-50-prospects.csv   ← phone-outreach priority list
    ../lists/phone-targets.csv      ← all 128 users with phone numbers
"""
from __future__ import annotations

import csv
import re
import sys
from pathlib import Path

try:
    sys.stdout.reconfigure(encoding="utf-8")  # type: ignore[attr-defined]
except Exception:
    pass

ROOT = Path(__file__).resolve().parent.parent
SALES_PACK = ROOT.parent
LISTS = ROOT / "lists"

# Self/test patterns to exclude from real outreach
EXCLUDE_PATTERNS = [
    re.compile(r"yaakoub.*hartem", re.I),
    re.compile(r"hartem.*yaakoub", re.I),
    re.compile(r"yaakoub2?[._-]?facebook", re.I),
    re.compile(r"yaakoub[._-]?formation", re.I),
    re.compile(r"^kradou", re.I),
    re.compile(r"^yy[._-]", re.I),
    re.compile(r"test.*@", re.I),
    re.compile(r"@tkawen\.", re.I),
    re.compile(r"@takawen\.", re.I),
]

# Names that look like self-accounts
EXCLUDE_NAMES = {"TKAWEN", "Yy", "Kradou", "HARTEM", "Mokessem", "Salam"}


def is_self_or_test(row: dict) -> bool:
    email = (row.get("email") or "").lower()
    first = (row.get("first_name") or "").strip()
    for p in EXCLUDE_PATTERNS:
        if p.search(email):
            return True
    if first in EXCLUDE_NAMES:
        return True
    return False


def clean_phone(p: str) -> str:
    if not p:
        return ""
    digits = "".join(c for c in p if c.isdigit())
    if not digits:
        return ""
    if digits.startswith("0"):
        digits = "213" + digits[1:]
    elif digits.startswith("213"):
        pass
    elif len(digits) in (9, 10):
        digits = "213" + digits
    return digits if 11 <= len(digits) <= 13 else ""


def main():
    src = LISTS / "tkawen-users-engaged.csv"
    if not src.exists():
        # Fall back to sales-pack/lists/
        src = SALES_PACK / "lists" / "tkawen-users-engaged.csv"
    if not src.exists():
        print("ERROR: tkawen-users-engaged.csv not found in lists/")
        return 1

    with src.open("r", encoding="utf-8-sig") as f:
        rows = list(csv.DictReader(f))

    print(f"Loaded:           {len(rows)} engagement-ranked rows")

    # Filter self/test
    cleaned = [r for r in rows if not is_self_or_test(r)]
    excluded = len(rows) - len(cleaned)
    print(f"Excluded (self):  {excluded}")
    print(f"Pool:             {len(cleaned)}")

    # Top 50 by engagement
    top_50 = cleaned[:50]
    top50_out = LISTS / "top-50-prospects.csv"
    cols_50 = ["rank", "engagement_score", "user_id", "email", "first_name", "last_name",
               "phone_e164", "phone_raw", "city", "wilaya", "registered", "has_session",
               "outreach_priority", "call_outcome", "next_step", "call_date", "notes"]
    with top50_out.open("w", encoding="utf-8", newline="") as f:
        w = csv.DictWriter(f, fieldnames=cols_50)
        w.writeheader()
        for i, r in enumerate(top_50, 1):
            phone_clean = clean_phone(r.get("phone", ""))
            priority = "P0-CALL" if phone_clean else "P1-EMAIL"
            w.writerow({
                "rank": i,
                "engagement_score": r.get("engagement_score", ""),
                "user_id": r.get("user_id", ""),
                "email": r.get("email", ""),
                "first_name": r.get("first_name", ""),
                "last_name": r.get("last_name", ""),
                "phone_e164": phone_clean,
                "phone_raw": r.get("phone", ""),
                "city": r.get("city", ""),
                "wilaya": r.get("wilaya", ""),
                "registered": r.get("registered", ""),
                "has_session": r.get("has_session", ""),
                "outreach_priority": priority,
                "call_outcome": "",
                "next_step": "",
                "call_date": "",
                "notes": "",
            })

    print(f"\nTOP 50 prospects -> {top50_out}")
    p0 = sum(1 for r in top_50 if clean_phone(r.get("phone", "")))
    print(f"  P0-CALL (with phone):  {p0}")
    print(f"  P1-EMAIL only:         {50 - p0}")

    # All phone-targets
    phone_rows = [r for r in cleaned if clean_phone(r.get("phone", ""))]
    phone_out = LISTS / "phone-targets.csv"
    cols_p = ["user_id", "email", "first_name", "last_name", "phone_e164", "phone_raw",
              "city", "wilaya", "engagement_score", "registered"]
    with phone_out.open("w", encoding="utf-8", newline="") as f:
        w = csv.DictWriter(f, fieldnames=cols_p)
        w.writeheader()
        for r in phone_rows:
            w.writerow({
                "user_id": r["user_id"], "email": r["email"],
                "first_name": r["first_name"], "last_name": r["last_name"],
                "phone_e164": clean_phone(r["phone"]),
                "phone_raw": r["phone"],
                "city": r["city"], "wilaya": r["wilaya"],
                "engagement_score": r["engagement_score"], "registered": r["registered"],
            })

    print(f"\nAll phone targets -> {phone_out}")
    print(f"  Total contactable by phone:  {len(phone_rows)}")

    print(f"\nNEXT STEPS:")
    print(f"  1. Open top-50-prospects.csv in Excel — call the top 20 with phones")
    print(f"  2. python whatsapp-links.py --wave phone-targets --template pitch")
    print(f"  3. Use the generated wa_link column to send personalised WA messages")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
