#!/usr/bin/env python3
"""
launch.py — pre-flight check + safe-launch orchestrator for the MyStoq campaign.

What it does:
    1. Validates EVERY pre-condition (Resend key, segmented lists, landing
       page reachable, all templates present, no recent failures).
    2. Sends ONE verification email to YOU first.
    3. Waits for you to confirm inbox placement.
    4. Sends the warm-up batch (50) with the chosen variant.
    5. Reports back with a summary + tells you what to do tomorrow.

Usage:
    export RESEND_API_KEY=re_xxx
    python launch.py --my-email you@example.com --variant A

Add --skip-verify to bypass the inbox-check (only if you've already verified).
"""
from __future__ import annotations

import argparse
import os
import subprocess
import sys
import time
from pathlib import Path

try:
    sys.stdout.reconfigure(encoding="utf-8")  # type: ignore[attr-defined]
except Exception:
    pass

import urllib.request

ROOT = Path(__file__).resolve().parent.parent
SCRIPTS = ROOT / "scripts"
LISTS = ROOT / "lists"
EMAIL_DIR = ROOT / "email"

GREEN = "\033[32m"; RED = "\033[31m"; YELLOW = "\033[33m"; BOLD = "\033[1m"; RESET = "\033[0m"


def check(label: str, ok: bool, hint: str = "") -> bool:
    icon = f"{GREEN}OK{RESET}  " if ok else f"{RED}FAIL{RESET}"
    print(f"  [{icon}] {label}")
    if not ok and hint:
        print(f"         -> {hint}")
    return ok


def url_alive(url: str, timeout: int = 5) -> bool:
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "TKAWEN-launch/1.0"})
        with urllib.request.urlopen(req, timeout=timeout) as r:
            return 200 <= r.status < 400
    except Exception:
        return False


def main():
    p = argparse.ArgumentParser()
    p.add_argument("--my-email", required=True, help="your inbox for the verification send")
    p.add_argument("--variant", default="A", choices=["A", "B", "C"])
    p.add_argument("--wave", default="wave-1-customers", help="list to send to (warm-up batch)")
    p.add_argument("--warmup-size", type=int, default=50)
    p.add_argument("--delay", type=float, default=3.0)
    p.add_argument("--skip-verify", action="store_true")
    p.add_argument("--actually-send", action="store_true",
                   help="without this flag, performs all checks + verification email ONLY")
    args = p.parse_args()

    print()
    print(BOLD + "=" * 64 + RESET)
    print(BOLD + "  TKAWEN -> MyStoq Campaign — LAUNCH ORCHESTRATOR" + RESET)
    print(BOLD + "=" * 64 + RESET)
    print()

    # ─── 1. Pre-flight checks ──────────────────────────────────
    print(BOLD + "STEP 1 — Pre-flight checks" + RESET)
    print()

    api_key = os.environ.get("RESEND_API_KEY", "")
    passed = True
    passed &= check("RESEND_API_KEY env var set", bool(api_key),
                    "export RESEND_API_KEY=re_xxxxxxx")
    passed &= check(f"Wave list exists: {args.wave}.csv",
                    (LISTS / f"{args.wave}.csv").exists(),
                    "Run: python segment.py")

    template_files = {
        "A": "template-A-personal.html",
        "B": "template-B-benefit.html",
        "C": "template-C-question.html",
    }
    passed &= check(f"Email template {args.variant} exists",
                    (EMAIL_DIR / template_files[args.variant]).exists())

    follow_ups = ["followup-1-day3-bump.html", "followup-2-day7-proof.html", "followup-3-day14-final.html"]
    fu_ok = all((EMAIL_DIR / f).exists() for f in follow_ups)
    check("Follow-up templates present (D+3, +7, +14)", fu_ok)

    landing_ok = url_alive("https://tkawen.online/mystoq-invite/?u=preflight&n=test&e=test@example.com&y=2024")
    passed &= check("Landing page reachable (200 OK)", landing_ok,
                    "Upload landing/ folder via FTP")

    pixel_ok = url_alive("https://tkawen.online/mystoq-invite/pixel.php?u=preflight&t=test&v=A")
    passed &= check("Tracking pixel reachable", pixel_ok)

    unsub_ok = url_alive("https://tkawen.online/mystoq-invite/unsubscribe.php?u=preflight&t=test")
    passed &= check("Unsubscribe page reachable", unsub_ok)

    if not passed:
        print()
        print(RED + BOLD + "PRE-FLIGHT FAILED. Fix the issues above before launching." + RESET)
        return 1

    print()
    print(GREEN + BOLD + "All pre-flight checks passed." + RESET)
    print()

    # ─── 2. Verification send to YOU ───────────────────────────
    if not args.skip_verify:
        print(BOLD + f"STEP 2 — Sending verification email to {args.my_email}" + RESET)
        print()

        cmd = [sys.executable, str(SCRIPTS / "send-test.py"), "--to", args.my_email, "--variant", args.variant]
        print(f"  Running: {' '.join(cmd[1:])}")
        r = subprocess.run(cmd, capture_output=True, text=True, encoding="utf-8")
        print(r.stdout)
        if r.returncode != 0:
            print(RED + "Verification send FAILED:" + RESET)
            print(r.stderr)
            return 2

        print()
        print(YELLOW + BOLD + "ACTION REQUIRED:" + RESET)
        print()
        print(f"  Go check the inbox for {BOLD}{args.my_email}{RESET}")
        print(f"  (also check Spam folder)")
        print()
        print(f"  Did the email arrive correctly?")
        print(f"    - Inbox or Spam?")
        print(f"    - Arabic renders RTL?")
        print(f"    - CTA button works?")
        print(f"    - Unsubscribe link works?")
        print()
        answer = input(BOLD + "  Continue with warm-up send? Type YES to proceed (or anything else to abort): " + RESET).strip()
        if answer != "YES":
            print()
            print(YELLOW + "Aborted by user. No bulk send happened." + RESET)
            return 0

    # ─── 3. Warm-up send ───────────────────────────────────────
    if not args.actually_send:
        print()
        print(YELLOW + BOLD + "DRY-RUN MODE — not actually sending the warm-up batch." + RESET)
        print(f"  To actually send the {args.warmup_size}-email warm-up, re-run with --actually-send")
        return 0

    print()
    print(BOLD + f"STEP 3 — Warm-up send: {args.warmup_size} emails to {args.wave}" + RESET)
    print()

    cmd = [
        sys.executable, str(SCRIPTS / "send.py"),
        "--wave", args.wave,
        "--variant", args.variant,
        "--limit", str(args.warmup_size),
        "--delay", str(args.delay),
    ]
    if (LISTS / "opt-outs.log").exists():
        cmd += ["--opt-outs", str(LISTS / "opt-outs.log")]

    print(f"  Running: {' '.join(cmd[1:])}")
    print()
    r = subprocess.run(cmd)
    if r.returncode != 0:
        print(RED + "Warm-up send had failures. Check the log." + RESET)
        return 3

    # ─── 4. Post-send summary ──────────────────────────────────
    print()
    print(BOLD + "STEP 4 — What to do over the next 72 hours" + RESET)
    print()
    print(f"  {GREEN}Hour +1{RESET}    — Verify in Resend dashboard: bounces <2%, complaints <0.1%")
    print(f"  {GREEN}Hour +4{RESET}    — Check inbox placement at mail-tester.com")
    print(f"  {GREEN}Hour +24{RESET}   — Run: python warroom.py")
    print(f"             — If opens >25%: send next 200 of {args.wave}")
    print(f"             — If opens <15%: pause + investigate deliverability")
    print(f"  {GREEN}Hour +48{RESET}   — Finish the wave (run send.py without --limit)")
    print(f"  {GREEN}Hour +72{RESET}   — Download opens.log + leads.jsonl via FTP, run filter.py")
    print(f"             — Send followup-1 to non-openers")
    print()
    print(f"  {YELLOW}Phone calls{RESET}: open lists/top-50-prospects.csv → call top 20 with phone")
    print(f"  {YELLOW}WhatsApp{RESET}:    open lists/wa-links-phone-targets-pitch.csv → click each wa_link")
    print()
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
