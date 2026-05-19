#!/usr/bin/env python3
"""
segment.py — split the tkawen.online user export into priority waves.

Usage:
    python segment.py [--input tkawen-users.csv] [--out-dir ../lists/]

Output files:
    wave-1-customers.csv     (highest intent: bought on WooCommerce)
    wave-2-subscribers.csv   (registered but not yet customers)
    wave-3-cold.csv          (oldest, lowest engagement — send LAST or skip)
    excluded.csv             (invalid emails, bounces, opt-outs)

Each output file has the columns the sender needs:
    user_id, email, first_name, registered_year
"""
from __future__ import annotations

import argparse
import csv
import json
import re
from datetime import datetime
from pathlib import Path

# ─── config ──────────────────────────────────────────────────────
ROOT = Path(__file__).resolve().parent.parent
SALES_PACK_ROOT = ROOT.parent  # sales-pack/
# Input CSV lives in sales-pack/lists/ (shared across campaigns)
DEFAULT_INPUT = SALES_PACK_ROOT / "lists" / "tkawen-users.csv"
# Segmented outputs live in campaign-mystoq/lists/
DEFAULT_OUT = ROOT / "lists"

# Hard blocklist — never email these (test accounts, role addresses, etc.)
BLOCK_PATTERNS = [
    re.compile(r"^(test|admin|noreply|no-reply|postmaster|webmaster|root)@", re.I),
    re.compile(r"@example\.(com|org|net)$", re.I),
    re.compile(r"@(tkawen\.com|takawen\.dz)$", re.I),  # internal domains
]


def is_valid_email(e: str) -> bool:
    if not e or "@" not in e:
        return False
    e = e.strip().lower()
    if any(p.search(e) for p in BLOCK_PATTERNS):
        return False
    # Basic shape check
    if not re.match(r"^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$", e):
        return False
    return True


def parse_roles(meta_value: str) -> set[str]:
    """WordPress capabilities are serialized PHP. Extract role names."""
    if not meta_value:
        return set()
    # Patterns like: a:1:{s:8:"customer";b:1;}
    return set(re.findall(r'"([a-z_]+)";b:1', meta_value))


def parse_year(registered: str) -> str:
    if not registered:
        return ""
    try:
        return datetime.fromisoformat(registered.split()[0]).strftime("%Y")
    except (ValueError, IndexError):
        return ""


def main() -> int:
    p = argparse.ArgumentParser()
    p.add_argument("--input", type=Path, default=DEFAULT_INPUT)
    p.add_argument("--out-dir", type=Path, default=DEFAULT_OUT)
    args = p.parse_args()

    if not args.input.exists():
        print(f"ERROR: input file not found: {args.input}")
        return 1
    args.out_dir.mkdir(parents=True, exist_ok=True)

    waves: dict[str, list[dict]] = {
        "wave-1-customers": [],
        "wave-2-subscribers": [],
        "wave-3-cold": [],
        "excluded": [],
    }

    seen_emails: set[str] = set()

    with args.input.open("r", encoding="utf-8-sig", newline="") as f:
        reader = csv.DictReader(f)
        for row in reader:
            email = (row.get("email") or "").strip().lower()
            user_id = row.get("user_id") or ""
            display = (row.get("display_name") or "").strip()
            first = (row.get("first_name") or "").strip() or display
            last = (row.get("last_name") or "").strip()
            registered = row.get("registered") or ""
            roles = parse_roles(row.get("roles") or "")
            has_session = (row.get("has_active_session") or "0") == "1"

            # Build output row
            out_row = {
                "user_id": user_id,
                "email": email,
                "first_name": first,
                "last_name": last,
                "registered_year": parse_year(registered),
                "has_active_session": "1" if has_session else "0",
            }

            # Exclude invalid
            if not is_valid_email(email):
                out_row["reason"] = "invalid_email"
                waves["excluded"].append(out_row)
                continue
            # Dedupe by email (case-insensitive)
            if email in seen_emails:
                out_row["reason"] = "duplicate"
                waves["excluded"].append(out_row)
                continue
            seen_emails.add(email)

            # Segment by role
            if "customer" in roles:
                waves["wave-1-customers"].append(out_row)
            elif "subscriber" in roles or "subscribers" in roles:
                # Recently registered → wave 2; older → wave 3
                year = out_row["registered_year"]
                if year and int(year) >= datetime.now().year - 1:
                    waves["wave-2-subscribers"].append(out_row)
                else:
                    waves["wave-3-cold"].append(out_row)
            else:
                waves["wave-3-cold"].append(out_row)

    # Write outputs
    print(f"\nSegmentation summary (input: {args.input}):\n")
    total = 0
    for name, rows in waves.items():
        outfile = args.out_dir / f"{name}.csv"
        if rows:
            cols = list(rows[0].keys())
            with outfile.open("w", encoding="utf-8", newline="") as f:
                w = csv.DictWriter(f, fieldnames=cols)
                w.writeheader()
                w.writerows(rows)
        print(f"  {name:25s} {len(rows):>6} rows -> {outfile.name}")
        if name != "excluded":
            total += len(rows)

    print(f"\n  {'TOTAL sendable':25s} {total:>6} users")
    print(f"  {'excluded':25s} {len(waves['excluded']):>6}")

    # Also write a JSON summary for the dashboard
    summary = {
        "generated_at": datetime.now().isoformat(),
        "input": str(args.input),
        "counts": {k: len(v) for k, v in waves.items()},
        "total_sendable": total,
    }
    summary_path = args.out_dir / "_segmentation-summary.json"
    summary_path.write_text(json.dumps(summary, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"\nSummary written to: {summary_path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
