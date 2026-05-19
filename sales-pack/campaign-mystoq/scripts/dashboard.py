#!/usr/bin/env python3
"""
dashboard.py — quick campaign stats from logs.

Usage:
    python dashboard.py [--logs ../logs/]

Reads:
    ../logs/send-*.jsonl    (one line per email send attempt)
    Landing page visits.log + leads.jsonl (download from server first)
"""
from __future__ import annotations

import argparse
import json
from collections import Counter, defaultdict
from datetime import datetime
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DEFAULT_LOG_DIR = ROOT / "logs"


def main():
    p = argparse.ArgumentParser()
    p.add_argument("--logs", type=Path, default=DEFAULT_LOG_DIR)
    args = p.parse_args()

    sends_by_wave_variant = defaultdict(lambda: {"sent": 0, "failed": 0})
    all_recipients = set()
    sent_times = []
    failures_sample = []

    for jsonl in sorted(args.logs.glob("send-*.jsonl")):
        with jsonl.open("r", encoding="utf-8") as f:
            for line in f:
                try:
                    e = json.loads(line)
                except json.JSONDecodeError:
                    continue
                key = (e.get("wave", "?"), e.get("variant", "?"))
                if e.get("success"):
                    sends_by_wave_variant[key]["sent"] += 1
                    all_recipients.add(e.get("email", ""))
                    sent_times.append(e.get("ts", ""))
                else:
                    sends_by_wave_variant[key]["failed"] += 1
                    if len(failures_sample) < 5:
                        failures_sample.append((e.get("email"), str(e.get("result") or e.get("error"))[:100]))

    print("\n" + "=" * 60)
    print("  TKAWEN -> MyStoq Campaign Dashboard")
    print("=" * 60 + "\n")

    if not sends_by_wave_variant:
        print("  No send logs yet. Run send.py first.\n")
        return 0

    print(f"{'Wave':<22} {'Variant':<10} {'Sent':>8} {'Failed':>8}")
    print("-" * 50)
    total_sent = 0
    total_failed = 0
    for (wave, variant), stats in sorted(sends_by_wave_variant.items()):
        print(f"{wave:<22} {variant:<10} {stats['sent']:>8} {stats['failed']:>8}")
        total_sent += stats["sent"]
        total_failed += stats["failed"]
    print("-" * 50)
    print(f"{'TOTAL':<22} {'':<10} {total_sent:>8} {total_failed:>8}")

    print(f"\n  Unique recipients reached: {len(all_recipients)}")

    if sent_times:
        first = sorted(sent_times)[0][:19]
        last = sorted(sent_times)[-1][:19]
        print(f"  First send: {first}")
        print(f"  Last send:  {last}")

    if total_failed and failures_sample:
        print(f"\n  Failures (sample):")
        for email, err in failures_sample:
            print(f"    {email}: {err}")

    # ─── Leads from landing page (if downloaded) ────────────────
    leads_file = ROOT / "lists" / "leads.jsonl"
    if leads_file.exists():
        leads = []
        with leads_file.open("r", encoding="utf-8") as f:
            for line in f:
                try:
                    leads.append(json.loads(line))
                except json.JSONDecodeError:
                    pass
        print(f"\n  Leads captured: {len(leads)}")
        if leads:
            by_business = Counter((l.get("business") or "(unspecified)")[:30] for l in leads)
            print(f"\n  Top business interests:")
            for biz, n in by_business.most_common(10):
                print(f"    {n:>3}  {biz}")

    # ─── Visits from pixel (if downloaded) ──────────────────────
    visits_file = ROOT / "lists" / "visits.log"
    if visits_file.exists():
        n_visits = sum(1 for _ in visits_file.open(encoding="utf-8"))
        unique_visitors = set()
        with visits_file.open(encoding="utf-8") as f:
            for line in f:
                parts = line.split("\t")
                if len(parts) >= 3:
                    unique_visitors.add(parts[2])  # email
        print(f"\n  Landing page visits: {n_visits} ({len(unique_visitors)} unique)")
        if total_sent:
            print(f"  -> Click rate: {100*n_visits/total_sent:.1f}% (visits/sent)")
            print(f"  -> Unique CTR: {100*len(unique_visitors)/total_sent:.1f}%")

    print("\n" + "=" * 60 + "\n")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
