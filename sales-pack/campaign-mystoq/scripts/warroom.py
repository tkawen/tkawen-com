#!/usr/bin/env python3
"""
warroom.py — daily war-room dashboard, shows the whole funnel at a glance.

Run every morning during campaign:
    python warroom.py

Reads everything in ../logs/ + ../lists/ — no setup needed.
"""
from __future__ import annotations

import json
import sys
from collections import Counter, defaultdict
from datetime import datetime
from pathlib import Path

try:
    sys.stdout.reconfigure(encoding="utf-8")  # type: ignore[attr-defined]
except Exception:
    pass

ROOT = Path(__file__).resolve().parent.parent
LISTS = ROOT / "lists"
LOGS = ROOT / "logs"

# ANSI for terminal colour (works in Windows Terminal + PowerShell 7+)
CYAN = "\033[36m"; GREEN = "\033[32m"; YELLOW = "\033[33m"; RED = "\033[31m"
GRAY = "\033[90m"; BOLD = "\033[1m"; RESET = "\033[0m"


def bar(value: int, total: int, width: int = 30) -> str:
    if total == 0:
        return GRAY + "·" * width + RESET
    filled = round(width * value / total)
    return GREEN + "█" * filled + GRAY + "·" * (width - filled) + RESET


def count_jsonl_field(path: Path, key: str) -> set:
    s = set()
    if not path.exists():
        return s
    with path.open("r", encoding="utf-8") as f:
        for line in f:
            try:
                obj = json.loads(line)
                v = obj.get(key)
                if v:
                    s.add(str(v))
            except json.JSONDecodeError:
                pass
    return s


def count_tsv_field(path: Path, col: int) -> set:
    s = set()
    if not path.exists():
        return s
    with path.open("r", encoding="utf-8") as f:
        for line in f:
            parts = line.rstrip("\n").split("\t")
            if len(parts) > col and parts[col].strip():
                s.add(parts[col].strip())
    return s


def main():
    print()
    print(BOLD + CYAN + "=" * 64 + RESET)
    print(BOLD + "  TKAWEN -> MyStoq Campaign — WAR ROOM" + RESET)
    print(BOLD + CYAN + "  " + datetime.now().strftime("%A, %d %B %Y · %H:%M") + RESET)
    print(BOLD + CYAN + "=" * 64 + RESET)
    print()

    # ─── 1. SENT (from send-*.jsonl) ───────────────────────────
    sent_emails: set[str] = set()
    sent_uids: set[str] = set()
    sent_by_wave_variant: defaultdict[tuple[str, str], dict] = defaultdict(lambda: {"sent": 0, "failed": 0})
    for f in sorted(LOGS.glob("send-*.jsonl")):
        with f.open("r", encoding="utf-8") as fh:
            for line in fh:
                try:
                    e = json.loads(line)
                except json.JSONDecodeError:
                    continue
                k = (e.get("wave", "?"), e.get("variant", "?"))
                if e.get("success"):
                    sent_by_wave_variant[k]["sent"] += 1
                    if e.get("email"):
                        sent_emails.add(e["email"])
                    if e.get("user_id"):
                        sent_uids.add(str(e["user_id"]))
                else:
                    sent_by_wave_variant[k]["failed"] += 1

    # ─── 2. OPENED (from opens.log via pixel.php) ──────────────
    opened = count_tsv_field(LISTS / "opens.log", col=1)

    # ─── 3. VISITED landing page (from visits.log) ─────────────
    visited = count_tsv_field(LISTS / "visits.log", col=1)

    # ─── 4. SIGNED UP (leads.jsonl from submit.php) ────────────
    signed_up = count_jsonl_field(LISTS / "leads.jsonl", "user_id")

    # ─── 5. OPT-OUTS ───────────────────────────────────────────
    opted_out = count_tsv_field(LISTS / "opt-outs.log", col=1)

    # ─── FUNNEL ────────────────────────────────────────────────
    total_sent = len(sent_uids)
    n_open = len(opened & sent_uids) if sent_uids else len(opened)
    n_visit = len(visited & sent_uids) if sent_uids else len(visited)
    n_signup = len(signed_up & sent_uids) if sent_uids else len(signed_up)
    n_unsub = len(opted_out)

    print(BOLD + "  FUNNEL" + RESET)
    print()

    def row(label, value, denom, fmt_rate=True):
        rate = (100.0 * value / denom) if denom else 0.0
        rate_str = f"{rate:>5.1f}%" if fmt_rate else ""
        color = GREEN if rate >= 20 else YELLOW if rate >= 5 else RED if denom else GRAY
        print(f"  {label:<14} {bar(value, denom)} {color}{value:>5}{RESET} {GRAY}/ {denom or 0:<5}{RESET}  {color}{rate_str}{RESET}")

    if total_sent == 0:
        print(f"  {GRAY}No sends logged yet. Run send.py.{RESET}\n")
    else:
        row("Sent       ", total_sent, total_sent, fmt_rate=False)
        row("Opened     ", n_open, total_sent)
        row("Visited    ", n_visit, total_sent)
        row("Signed up  ", n_signup, total_sent)
        row("Unsubscribed", n_unsub, total_sent)

    # ─── BY WAVE / VARIANT ─────────────────────────────────────
    print()
    print(BOLD + "  BY WAVE × VARIANT" + RESET)
    print()
    if sent_by_wave_variant:
        print(f"  {'Wave':<32} {'Var':<5} {'Sent':>6} {'Fail':>6}")
        print(f"  {GRAY}{'-' * 60}{RESET}")
        for (wave, variant), stats in sorted(sent_by_wave_variant.items()):
            print(f"  {wave:<32} {variant:<5} {stats['sent']:>6} {stats['failed']:>6}")
    else:
        print(f"  {GRAY}(no data){RESET}")

    # ─── REVENUE ESTIMATE ──────────────────────────────────────
    print()
    print(BOLD + "  REVENUE PROJECTION (conservative)" + RESET)
    print()
    # Assume 45% of signups activate within 30d, 60% convert to paid after trial
    activated_est = int(n_signup * 0.45)
    paid_est = int(activated_est * 0.60)
    mrr_est = paid_est * 5000  # DZD
    print(f"  Signups:                {n_signup}")
    print(f"  -> Activated (45%):     ~{activated_est}")
    print(f"  -> Will pay (60% of ↑): ~{paid_est}  ({GREEN}~{mrr_est:,} DZD MRR{RESET})")
    print(f"  -> Annual (12×):                ~{mrr_est * 12:,} DZD")

    # ─── TODAY'S ACTIONS ───────────────────────────────────────
    print()
    print(BOLD + YELLOW + "  TODAY'S ACTIONS" + RESET)
    print()
    actions = []
    if total_sent == 0:
        actions.append("Set up Resend + verify sender domain")
        actions.append("Run: python send.py --wave wave-1-customers --variant A --limit 50")
    elif total_sent < 200:
        actions.append(f"Day-2 send: scale to 200 (currently at {total_sent})")
        actions.append("Compare A/B opens in Resend dashboard")
    elif n_open and n_signup == 0:
        actions.append("Opens but no signups: check submit.php on tkawen.online")
        actions.append("Send 5 WhatsApp messages to recent openers (use whatsapp-links.py)")
    elif n_signup and n_signup < 5:
        actions.append(f"You have {n_signup} signups: phone-call each one personally TODAY")
        actions.append("Send onboarding-day0-welcome to each")
    elif n_signup >= 5:
        actions.append(f"{n_signup} signups: time for top-20 phone outreach (sales/phone-script.md)")
        actions.append("Download opt-outs.log + opens.log, run filter.py")
        actions.append("Send followup-1-day3-bump to non-openers")

    for i, a in enumerate(actions, 1):
        print(f"  {i}. {a}")

    print()
    print(BOLD + CYAN + "=" * 64 + RESET)
    print()
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
