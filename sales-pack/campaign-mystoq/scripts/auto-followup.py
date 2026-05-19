#!/usr/bin/env python3
"""
auto-followup.py — one-command follow-up: pull logs, segment, send.

Chains:
    1. pull-logs.py        — refreshes opens/visits/leads/opt-outs from server
    2. filter.py           — generates non-opener/clicker/signup lists from logs
    3. send.py             — sends the chosen follow-up variant to the right list

Usage:
    # Day +3 — bump non-openers
    python auto-followup.py --base wave-1-customers --stage fu1

    # Day +7 — case study to non-clickers (those who opened but didn't click)
    python auto-followup.py --base wave-1-customers --stage fu2

    # Day +14 — last-day urgency to non-signups (clicked but didn't register)
    python auto-followup.py --base wave-1-customers --stage fu3

Stage -> variant -> target list mapping:
    fu1 -> FU1 -> {base}-non-openers     (Day +3, "did you see it?")
    fu2 -> FU2 -> {base}-non-clickers    (Day +7, case study + stories link)
    fu3 -> FU3 -> {base}-non-signups     (Day +14, last-day urgency)

Required env (same as pull-logs.py + send.py):
    RESEND_API_KEY, TKAWEN_FTP_USER, TKAWEN_FTP_PASS

Add --dry-run to test without sending. Add --skip-pull to reuse already-
downloaded logs (faster for retries).
"""
from __future__ import annotations

import argparse
import subprocess
import sys
from pathlib import Path

try:
    sys.stdout.reconfigure(encoding="utf-8")  # type: ignore[attr-defined]
except Exception:
    pass

SCRIPTS = Path(__file__).resolve().parent
ROOT = SCRIPTS.parent
LISTS = ROOT / "lists"

STAGE_MAP = {
    "fu1": {"variant": "FU1", "target_suffix": "-non-openers",  "label": "Day +3 — bump to non-openers"},
    "fu2": {"variant": "FU2", "target_suffix": "-non-clickers", "label": "Day +7 — case study to non-clickers"},
    "fu3": {"variant": "FU3", "target_suffix": "-non-signups",  "label": "Day +14 — last day to non-signups"},
}


def run(cmd: list[str], step: str) -> int:
    print(f"\n{'─' * 60}\n{step}\n{'─' * 60}")
    print(f"  $ {' '.join(cmd)}")
    return subprocess.call(cmd)


def main() -> int:
    p = argparse.ArgumentParser()
    p.add_argument("--base", required=True, help="base list (e.g. wave-1-customers)")
    p.add_argument("--stage", required=True, choices=list(STAGE_MAP.keys()))
    p.add_argument("--dry-run", action="store_true")
    p.add_argument("--skip-pull", action="store_true", help="reuse already-pulled logs")
    p.add_argument("--delay", type=float, default=2.0)
    args = p.parse_args()

    cfg = STAGE_MAP[args.stage]

    print(f"\n{'=' * 60}")
    print(f"  AUTO FOLLOW-UP — {cfg['label']}")
    print(f"  Base wave: {args.base}")
    print(f"  Variant:   {cfg['variant']}")
    print(f"  Target:    {args.base}{cfg['target_suffix']}.csv")
    print(f"{'=' * 60}")

    # ─── Step 1. Pull logs ─────────────────────────────────────
    if not args.skip_pull:
        rc = run([sys.executable, str(SCRIPTS / "pull-logs.py")], "STEP 1: Pulling fresh logs from server")
        if rc != 0:
            print(f"\nERROR: pull-logs.py failed (rc={rc}). Aborting.")
            return rc

    # ─── Step 2. Filter ────────────────────────────────────────
    rc = run([sys.executable, str(SCRIPTS / "filter.py"), "--base", args.base], "STEP 2: Filtering engagement signals")
    if rc != 0:
        print(f"\nERROR: filter.py failed (rc={rc}). Aborting.")
        return rc

    target_list = f"{args.base}{cfg['target_suffix']}"
    target_csv = LISTS / f"{target_list}.csv"
    if not target_csv.exists():
        print(f"\nWARNING: {target_csv} not found — no recipients for this follow-up.")
        print(f"  Either everyone is already at the next stage, or filter.py produced no matches.")
        return 0

    # Count recipients
    with target_csv.open("r", encoding="utf-8") as f:
        n = sum(1 for _ in f) - 1  # minus header
    if n <= 0:
        print(f"\nList is empty. Nothing to send.")
        return 0

    print(f"\n  Recipients ready: {n}")

    # ─── Step 3. Send ──────────────────────────────────────────
    send_cmd = [
        sys.executable, str(SCRIPTS / "send.py"),
        "--wave", target_list,
        "--variant", cfg["variant"],
        "--delay", str(args.delay),
    ]
    if (LISTS / "opt-outs.log").exists():
        send_cmd += ["--opt-outs", str(LISTS / "opt-outs.log")]
    if args.dry_run:
        send_cmd += ["--dry-run"]

    rc = run(send_cmd, f"STEP 3: Sending {cfg['variant']} to {target_list} ({n} recipients)")
    return rc


if __name__ == "__main__":
    raise SystemExit(main())
