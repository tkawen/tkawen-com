#!/usr/bin/env python3
"""
pull-logs.py — downloads campaign tracking logs from tkawen.online via FTP.

Files pulled (TSV/JSONL, all PII-scrubbed before commit by .gitignore):
    /public_html/mystoq-invite/opens.log          -> ../lists/opens.log
    /public_html/mystoq-invite/visits.log         -> ../lists/visits.log
    /public_html/mystoq-invite/leads.jsonl        -> ../lists/leads.jsonl
    /public_html/mystoq-invite/opt-outs.log       -> ../lists/opt-outs.log
    /public_html/mystoq-invite/stories-visits.log -> ../lists/stories-visits.log

Auth: reads FTP credentials from environment variables. NEVER hardcode.
    export TKAWEN_FTP_USER=tkawen08
    export TKAWEN_FTP_PASS=<password>
    export TKAWEN_FTP_HOST=ftp.tkawen.online  # optional, defaults to this

Usage:
    python pull-logs.py
"""
from __future__ import annotations

import os
import sys
from ftplib import FTP, error_perm
from pathlib import Path

try:
    sys.stdout.reconfigure(encoding="utf-8")  # type: ignore[attr-defined]
except Exception:
    pass

ROOT = Path(__file__).resolve().parent.parent
LISTS = ROOT / "lists"
LISTS.mkdir(exist_ok=True)

REMOTE_DIR = "/public_html/mystoq-invite"
FILES = [
    "opens.log",
    "visits.log",
    "leads.jsonl",
    "opt-outs.log",
    "stories-visits.log",
]


def main() -> int:
    user = os.environ.get("TKAWEN_FTP_USER", "")
    password = os.environ.get("TKAWEN_FTP_PASS", "")
    host = os.environ.get("TKAWEN_FTP_HOST", "ftp.tkawen.online")

    if not user or not password:
        print("ERROR: set env vars TKAWEN_FTP_USER and TKAWEN_FTP_PASS")
        print("       (NEVER hardcode credentials — they end up in chat history)")
        return 1

    print(f"Connecting to {host} as {user}...")
    try:
        ftp = FTP(host, timeout=30)
        ftp.login(user, password)
    except Exception as e:
        print(f"ERROR: FTP login failed: {e}")
        return 2

    try:
        ftp.cwd(REMOTE_DIR)
    except error_perm as e:
        print(f"ERROR: cd {REMOTE_DIR}: {e}")
        ftp.quit()
        return 3

    pulled = 0
    missing = 0
    print()
    for fname in FILES:
        local = LISTS / fname
        try:
            with local.open("wb") as f:
                ftp.retrbinary(f"RETR {fname}", f.write)
            size = local.stat().st_size
            line_count = 0
            try:
                with local.open("r", encoding="utf-8", errors="replace") as f:
                    line_count = sum(1 for _ in f)
            except Exception:
                pass
            print(f"  OK    {fname:<24} {size:>8} B  {line_count:>5} lines")
            pulled += 1
        except error_perm as e:
            # File doesn't exist on server yet — not a failure
            print(f"  none  {fname:<24} (not yet on server — {e})")
            if local.exists() and local.stat().st_size == 0:
                local.unlink()
            missing += 1
        except Exception as e:
            print(f"  FAIL  {fname:<24} {e}")
            if local.exists() and local.stat().st_size == 0:
                local.unlink()

    ftp.quit()
    print()
    print(f"Pulled: {pulled} / {len(FILES)}  (missing on server: {missing})")
    print(f"Files in: {LISTS}\n")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
