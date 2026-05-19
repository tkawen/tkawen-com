# Automated follow-up scheduling

The campaign uses 3 follow-ups over 14 days:
- **Day +3** — "did you see it?" to non-openers
- **Day +7** — case study to non-clickers
- **Day +14** — last-day urgency to non-signups

Running these on the right day is tedious. Set them up to run automatically
the moment they're needed.

---

## Option A — Windows Task Scheduler (recommended for solo founder)

After launching Wave 1, calculate the three target dates and create 3 tasks.

```powershell
# Example: if Wave 1 sent on 2026-05-20, follow-ups are
#   Day +3:  2026-05-23  (FU1)
#   Day +7:  2026-05-27  (FU2)
#   Day +14: 2026-06-03  (FU3)

# Open Task Scheduler GUI, OR create programmatically:
$scriptPath = "D:\F\tkawen-com\sales-pack\campaign-mystoq\scripts\auto-followup.py"
$pythonPath = "C:\Users\$env:USERNAME\AppData\Local\Programs\Python\Python314\python.exe"

# Day +3 (10am Algiers — 09:00 UTC+1)
schtasks /create /tn "TKAWEN-mystoq-FU1" /tr "$pythonPath $scriptPath --base wave-1-customers --stage fu1" /sc once /st 10:00 /sd 2026-05-23

# Day +7
schtasks /create /tn "TKAWEN-mystoq-FU2" /tr "$pythonPath $scriptPath --base wave-1-customers --stage fu2" /sc once /st 10:00 /sd 2026-05-27

# Day +14
schtasks /create /tn "TKAWEN-mystoq-FU3" /tr "$pythonPath $scriptPath --base wave-1-customers --stage fu3" /sc once /st 10:00 /sd 2026-06-03

# Verify
schtasks /query /tn "TKAWEN-mystoq-FU*"
```

**Environment variables** must be set as SYSTEM env vars (not just user-shell)
so Task Scheduler can read them:

```powershell
[System.Environment]::SetEnvironmentVariable("RESEND_API_KEY", "re_xxx", "User")
[System.Environment]::SetEnvironmentVariable("TKAWEN_FTP_USER", "tkawen08", "User")
[System.Environment]::SetEnvironmentVariable("TKAWEN_FTP_PASS", "...", "User")
# Restart your terminal after setting these
```

Tasks will run unattended. They write to `../logs/send-*.jsonl` so you
can review what happened the next morning.

---

## Option B — Manual (5 min per follow-up, no scheduler)

Calendar reminder on the right days. Run:

```powershell
cd D:\F\tkawen-com\sales-pack\campaign-mystoq\scripts
python auto-followup.py --base wave-1-customers --stage fu1   # Day +3
python auto-followup.py --base wave-1-customers --stage fu2   # Day +7
python auto-followup.py --base wave-1-customers --stage fu3   # Day +14
```

Each command:
1. Pulls fresh logs from tkawen.online via FTP
2. Re-segments the wave by engagement
3. Sends the right follow-up variant to the right subset

---

## Option C — VPS cron (production-grade, requires server access)

If you have a VPS with the campaign repo deployed:

```cron
# /etc/cron.d/tkawen-mystoq-campaign
# Format: m  h  dom  mon  dow  user  command

# Wave 1 follow-ups (replace dates with your actual launch + 3/7/14)
0  10  23  5  *  campaign  cd /opt/campaign && python3 scripts/auto-followup.py --base wave-1-customers --stage fu1
0  10  27  5  *  campaign  cd /opt/campaign && python3 scripts/auto-followup.py --base wave-1-customers --stage fu2
0  10   3  6  *  campaign  cd /opt/campaign && python3 scripts/auto-followup.py --base wave-1-customers --stage fu3

# Optional: pull logs every 6 hours for the war-room dashboard
0  */6  *  *  *  campaign  cd /opt/campaign && python3 scripts/pull-logs.py >> logs/pull.log 2>&1
```

Env vars in `/etc/cron.d/` go in the file header:
```
RESEND_API_KEY=re_xxx
TKAWEN_FTP_USER=tkawen08
TKAWEN_FTP_PASS=...
```

---

## What to monitor

Each follow-up writes a log:
```
campaign-mystoq/logs/send-wave-1-customers-non-openers-FU1-<timestamp>.jsonl
```

After each follow-up fires, run `python warroom.py` to see:
- How many were sent / failed
- Updated funnel (cumulative opens/clicks/signups)
- Today's recommended actions

---

## Hard rules

- **Never schedule a follow-up before Day +3.** Some recipients haven't even
  finished reading the original. Same-day or next-day follow-ups feel like
  spam.
- **Never run two follow-ups on the same day** — gives the impression of
  desperation.
- **Day +14 is the absolute end.** No follow-up #4. Either they're a
  customer, a known no, or they're out of the funnel.
- **Suppress opt-outs everywhere.** auto-followup.py automatically passes
  --opt-outs to send.py. If you bypass this script, do it manually.
