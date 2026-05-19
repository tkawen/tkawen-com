#!/usr/bin/env python3
"""
filter.py — generates targeted follow-up lists from campaign logs.

After Wave 1, download these from tkawen.online via FTP:
  /public_html/mystoq-invite/opens.log     -> ../lists/opens.log
  /public_html/mystoq-invite/visits.log    -> ../lists/visits.log
  /public_html/mystoq-invite/leads.jsonl   -> ../lists/leads.jsonl
  /public_html/mystoq-invite/opt-outs.log  -> ../lists/opt-outs.log

Then run:
  python filter.py --base wave-1-customers

Outputs:
  wave-1-customers-non-openers.csv   -> for followup-1 (Day +3 bump)
  wave-1-customers-non-clickers.csv  -> for followup-2 (Day +7 proof)
  wave-1-customers-non-signups.csv   -> for followup-3 (Day +14 final)
  wave-1-customers-signed-up.csv     -> for onboarding sequence
"""
from __future__ import annotations

import argparse
import csv
import json
import sys
from pathlib import Path

try:
    sys.stdout.reconfigure(encoding="utf-8")  # type: ignore[attr-defined]
except Exception:
    pass

ROOT = Path(__file__).resolve().parent.parent
LISTS = ROOT / "lists"


def load_user_ids_from_tsv(path: Path, col_index: int = 1) -> set[str]:
    """Parse opens.log / opt-outs.log / visits.log — TSV with user_id at given col."""
    ids: set[str] = set()
    if not path.exists():
        return ids
    with path.open("r", encoding="utf-8") as f:
        for line in f:
            parts = line.rstrip("\n").split("\t")
            if len(parts) > col_index:
                uid = parts[col_index].strip()
                if uid:
                    ids.add(uid)
    return ids


def load_user_ids_from_jsonl(path: Path, key: str = "user_id") -> set[str]:
    ids: set[str] = set()
    if not path.exists():
        return ids
    with path.open("r", encoding="utf-8") as f:
        for line in f:
            try:
                obj = json.loads(line)
            except json.JSONDecodeError:
                continue
            uid = str(obj.get(key, "")).strip()
            if uid:
                ids.add(uid)
    return ids


def main():
    p = argparse.ArgumentParser()
    p.add_argument("--base", required=True, help="base list name without .csv (e.g. wave-1-customers)")
    args = p.parse_args()

    base_file = LISTS / f"{args.base}.csv"
    if not base_file.exists():
        print(f"ERROR: {base_file} not found")
        return 1

    # Load engagement signals
    opens_path = LISTS / "opens.log"
    visits_path = LISTS / "visits.log"
    leads_path = LISTS / "leads.jsonl"
    opt_outs_path = LISTS / "opt-outs.log"

    opened = load_user_ids_from_tsv(opens_path, col_index=1)       # ts \t user_id \t variant ...
    visited = load_user_ids_from_tsv(visits_path, col_index=1)     # ts \t user_id \t email ...
    signed_up = load_user_ids_from_jsonl(leads_path, key="user_id")
    opted_out = load_user_ids_from_tsv(opt_outs_path, col_index=1)

    print(f"\nEngagement signals loaded:")
    print(f"  Opens:       {len(opened):>5}")
    print(f"  Visits:      {len(visited):>5}")
    print(f"  Signups:     {len(signed_up):>5}")
    print(f"  Opt-outs:    {len(opted_out):>5}")

    # Read base list
    with base_file.open("r", encoding="utf-8") as f:
        all_rows = list(csv.DictReader(f))
        cols = list(all_rows[0].keys()) if all_rows else []

    # Filter (always exclude opt-outs + signed-up — they shouldn't get follow-ups)
    non_openers = [r for r in all_rows if r["user_id"] not in opened and r["user_id"] not in opted_out and r["user_id"] not in signed_up]
    non_clickers = [r for r in all_rows if r["user_id"] in opened and r["user_id"] not in visited and r["user_id"] not in opted_out and r["user_id"] not in signed_up]
    non_signups = [r for r in all_rows if r["user_id"] in visited and r["user_id"] not in signed_up and r["user_id"] not in opted_out]
    signed_up_rows = [r for r in all_rows if r["user_id"] in signed_up]

    outputs = {
        f"{args.base}-non-openers": non_openers,
        f"{args.base}-non-clickers": non_clickers,
        f"{args.base}-non-signups": non_signups,
        f"{args.base}-signed-up": signed_up_rows,
    }

    print(f"\nFiltered lists (from {len(all_rows)} total):\n")
    for name, rows in outputs.items():
        out = LISTS / f"{name}.csv"
        if rows:
            with out.open("w", encoding="utf-8", newline="") as f:
                w = csv.DictWriter(f, fieldnames=cols)
                w.writeheader()
                w.writerows(rows)
        print(f"  {name:45s} {len(rows):>5} rows -> {out.name}")

    print(f"\nNext steps:")
    print(f"  Day +3 bump:        python send.py --wave {args.base}-non-openers --variant FU1 --opt-outs ../lists/opt-outs.log")
    print(f"  Day +7 social proof: python send.py --wave {args.base}-non-clickers --variant FU2 --opt-outs ../lists/opt-outs.log")
    print(f"  Day +14 last call:  python send.py --wave {args.base}-non-signups --variant FU3 --opt-outs ../lists/opt-outs.log")
    print(f"  Onboarding for signed-up: separate flow (handled by MyStoq backend)\n")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
