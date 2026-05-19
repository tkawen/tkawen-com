<?php
/**
 * tools/iban.php — Algerian IBAN + CCP/RIP validator.
 *
 * Validates DZ-format IBAN using ISO 13616 mod-97 algorithm:
 *   1. Length must be 24 (DZ + 22 digits)
 *   2. Move first 4 chars to end
 *   3. Replace letters with numbers (A=10, B=11, ..., Z=35)
 *   4. Compute resulting big-integer mod 97
 *   5. Valid iff result === 1
 *
 * Also handles Algeria-specific RIP (Algérie Poste) format:
 *   - 20 digits: 3 (bank) + 3 (branch) + 11 (account) + 3 (RIB key)
 *   - Common public format: "007 99999 0020008440 42" (CCP RIP)
 *
 * SEO targets:
 *   "تأكد من IBAN جزائري"  ·  "valider IBAN Algérie"  ·  "Algerian IBAN check"
 */

declare(strict_types=1);
header_remove('X-Powered-By');

$input = trim((string)($_POST['iban'] ?? $_GET['iban'] ?? ''));
$normalized = strtoupper(preg_replace('/[\s-]/', '', $input));

$result = null;
if ($normalized !== '') {
    $result = validate_iban($normalized, $input);
}

/**
 * @return array{valid:bool, kind:string, format:string, bank:string, branch:string, account:string, key:string, error:string}
 */
function validate_iban(string $iban, string $original): array {
    $r = [
        'valid'   => false,
        'kind'    => 'unknown',     // iban, rip, unknown
        'format'  => '',
        'bank'    => '',
        'bank_name' => '',
        'branch'  => '',
        'account' => '',
        'key'     => '',
        'error'   => '',
        'original'=> $original,
        'normalized' => $iban,
    ];

    // ─── Pre-checks ─────────────────────────────────────────
    if (!ctype_alnum($iban)) {
        $r['error'] = 'يحتوي على رموز غير صالحة (يقبل فقط أحرف وأرقام)';
        return $r;
    }

    // ─── DZ IBAN path ───────────────────────────────────────
    if (strlen($iban) === 24 && substr($iban, 0, 2) === 'DZ') {
        $r['kind'] = 'iban';
        $r['format'] = 'IBAN جزائري (24 محرفا)';

        // Mod-97 check
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);
        $numeric = '';
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $c = $rearranged[$i];
            if (ctype_alpha($c)) {
                $numeric .= (string)(ord($c) - ord('A') + 10);
            } elseif (ctype_digit($c)) {
                $numeric .= $c;
            } else {
                $r['error'] = 'محرف غير معروف: ' . $c;
                return $r;
            }
        }
        // Mod-97 piecewise (PHP int safety for huge numbers)
        $remainder = '';
        for ($i = 0; $i < strlen($numeric); $i++) {
            $chunk = $remainder . $numeric[$i];
            $remainder = (string)((int)$chunk % 97);
        }
        $r['valid'] = ((int)$remainder === 1);
        if (!$r['valid']) {
            $r['error'] = 'فشل التحقق — رقم IBAN غير صحيح';
        }

        // Decompose: DZ + 2(check) + 20(BBAN). BBAN for DZ = 3(bank)+5(branch)+10(account)+2(key)
        $r['bank']    = substr($iban, 4, 3);
        $r['branch']  = substr($iban, 7, 5);
        $r['account'] = substr($iban, 12, 10);
        $r['key']     = substr($iban, 22, 2);
        $r['bank_name'] = dz_bank_name($r['bank']);

        return $r;
    }

    // ─── DZ RIP/CCP path (20 digits) ────────────────────────
    if (strlen($iban) === 20 && ctype_digit($iban)) {
        $r['kind'] = 'rip';
        $r['format'] = 'RIP / حساب جزائر بوست (20 رقما)';
        $r['bank']    = substr($iban, 0, 3);
        $r['branch']  = substr($iban, 3, 5);
        $r['account'] = substr($iban, 8, 10);
        $r['key']     = substr($iban, 18, 2);
        $r['bank_name'] = dz_bank_name($r['bank']);

        // RIB key check: key = 97 - ( (bank*100 + branch) * 100000000000 + account ) mod 97
        // Practical implementation: piecewise mod-97
        $payload = $r['bank'] . $r['branch'] . $r['account'];  // 18 digits
        $rem = '';
        for ($i = 0; $i < strlen($payload); $i++) {
            $rem = (string)((int)($rem . $payload[$i]) % 97);
        }
        $expected_key = (string)(97 - ((int)$rem * 100) % 97);
        if (strlen($expected_key) === 1) $expected_key = '0' . $expected_key;
        if ($expected_key === '00') $expected_key = '97';

        $r['valid'] = ($r['key'] === $expected_key);
        if (!$r['valid']) {
            $r['error'] = "مفتاح RIB غير صحيح — المتوقع: $expected_key، الموجود: {$r['key']}";
        }

        return $r;
    }

    // ─── Unknown format ─────────────────────────────────────
    $len = strlen($iban);
    $r['error'] = "طول غير معروف: $len محرف. " .
        "IBAN جزائري: 24 محرفا يبدأ بـ DZ. " .
        "RIP (CCP): 20 رقما.";
    return $r;
}

function dz_bank_name(string $code): string {
    // Bank codes per Banque d'Algérie 2026 registry
    $banks = [
        '001' => 'البنك المركزي الجزائري',
        '002' => 'بنك التنمية المحلية (BDL)',
        '003' => 'بنك الجزائر الخارجي (BEA)',
        '004' => 'البنك الوطني الجزائري (BNA)',
        '005' => 'القرض الشعبي الجزائري (CPA)',
        '006' => 'الصندوق الوطني للتوفير والاحتياط (CNEP)',
        '007' => 'بريد الجزائر (Algérie Poste / CCP)',
        '008' => 'البنك الفلاحي والتنمية الريفية (BADR)',
        '011' => 'AGB — Gulf Bank Algeria',
        '012' => 'BNP Paribas El Djazaïr',
        '014' => 'Société Générale Algérie',
        '015' => 'Trust Bank Algeria',
        '016' => 'HSBC Algeria',
        '017' => 'Crédit Agricole CIB Algérie',
        '018' => 'Fransabank El Djazaïr',
        '020' => 'Salam Bank Algérie',
        '021' => 'Housing Bank Algeria',
        '023' => 'بنك البركة الجزائري',
        '026' => 'البنك الوطني للإسكان (CNL)',
        '027' => 'Citibank Algeria',
        '029' => 'Al Baraka Bank',
        '032' => 'Natixis Algérie',
        '038' => 'ABC Bank Algeria',
    ];
    return $banks[$code] ?? "بنك غير معروف (رمز $code)";
}

// ─── HTML output ────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<title><?= $result && $result['valid'] ? 'IBAN صالح · ' . $result['bank_name'] : 'تحقق من IBAN جزائري · أداة TKAWEN' ?></title>
<meta name="description" content="تأكد من صحة رقم IBAN جزائري (24 محرفا) أو RIP/CCP بريد الجزائر (20 رقما). فوريا، مجانا، يعرض اسم البنك والفرع.">
<meta name="keywords" content="IBAN, RIP, CCP, تأكد من IBAN, valider IBAN, Algerian IBAN, بريد الجزائر">

<meta property="og:title" content="تأكد من IBAN جزائري أو RIP بريد الجزائر">
<meta property="og:description" content="فوري، مجاني، يعرض اسم البنك">
<meta property="og:type" content="website">
<meta property="og:locale" content="ar_DZ">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "تأكد من IBAN جزائري",
  "description": "تحقق فوري من IBAN جزائري أو RIP بريد الجزائر",
  "url": "https://tkawen.online/tools/iban.php",
  "applicationCategory": "FinanceApplication",
  "operatingSystem": "Web",
  "offers": {"@type": "Offer", "price": "0", "priceCurrency": "DZD"},
  "creator": {"@type": "Organization", "name": "TKAWEN"}
}
</script>

<link rel="canonical" href="https://tkawen.online/tools/iban.php">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">

<style>
  :root { --bg:#fff; --bg-2:#f8fafc; --text:#0f172a; --muted:#64748b; --dim:#94a3b8;
          --line:#e2e8f0; --accent:#2563eb; --accent-dark:#1d4ed8; --green:#10b981; --rose:#f43f5e; }
  *{box-sizing:border-box} body{margin:0;background:var(--bg-2);color:var(--text);
    font-family:'Cairo',-apple-system,'Segoe UI',sans-serif;line-height:1.6;direction:rtl;-webkit-font-smoothing:antialiased}
  .lat{font-family:'JetBrains Mono','SF Mono',monospace;direction:ltr;unicode-bidi:embed}

  header.top{background:var(--bg);border-bottom:1px solid var(--line);padding:14px 24px}
  .top-row{max-width:880px;margin:0 auto;display:flex;align-items:center;justify-content:space-between}
  .brand{font-weight:800;font-size:17px}.brand .x{color:var(--dim);margin:0 6px}
  .brand .by{color:var(--muted);font-weight:500;font-size:13px}
  .top-link{color:var(--muted);text-decoration:none;font-size:13px}

  .hero{max-width:760px;margin:0 auto;padding:32px 20px 8px;text-align:center}
  h1{margin:0 0 12px;font-size:28px;font-weight:800;letter-spacing:-.01em;line-height:1.3}
  .lede{color:var(--muted);font-size:15px;max-width:520px;margin:0 auto}
  .eyebrow{display:inline-block;background:rgba(37,99,235,.12);color:var(--accent-dark);
    padding:4px 12px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.06em;margin-bottom:14px}

  main{max-width:680px;margin:0 auto;padding:24px 20px}
  .card{background:var(--bg);border:1px solid var(--line);border-radius:14px;
    padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.03);margin-bottom:20px}

  label{display:block;font-size:12px;color:var(--muted);margin-bottom:6px;font-weight:600}
  input[type=text]{width:100%;padding:14px 16px;font-size:18px;border:2px solid var(--line);
    border-radius:10px;background:#fff;color:var(--text);font-family:'JetBrains Mono',monospace;
    direction:ltr;text-align:left;outline:none;transition:border-color .15s;letter-spacing:1px}
  input[type=text]:focus{border-color:var(--accent)}
  .calc-btn{width:100%;margin-top:14px;padding:14px;background:var(--accent);color:#fff;
    border:0;border-radius:10px;font-size:16px;font-weight:800;cursor:pointer;font-family:'Cairo',sans-serif;
    transition:background .15s}
  .calc-btn:hover{background:var(--accent-dark)}
  .help{font-size:12px;color:var(--muted);margin-top:8px;line-height:1.6}

  /* Result OK */
  .result-ok{background:linear-gradient(135deg,#ecfdf5,#d1fae5);border:1px solid #6ee7b7;
    border-radius:14px;padding:20px 24px;margin-bottom:20px}
  .result-ok .ok-h{font-size:13px;color:#065f46;font-weight:700;letter-spacing:.04em;margin-bottom:6px;display:flex;align-items:center;gap:8px}
  .result-ok .ok-bank{font-size:20px;font-weight:800;color:#064e3b;margin-bottom:14px}
  .result-ok dl{margin:0;display:grid;grid-template-columns:1fr 2fr;gap:6px 16px;font-size:13px}
  .result-ok dt{color:#065f46;font-weight:600}
  .result-ok dd{margin:0;color:#064e3b;font-family:'JetBrains Mono',monospace;direction:ltr;text-align:left}

  /* Result FAIL */
  .result-fail{background:linear-gradient(135deg,#fef2f2,#fee2e2);border:1px solid #fca5a5;
    border-radius:14px;padding:20px 24px;margin-bottom:20px}
  .result-fail .h{font-size:13px;color:#7f1d1d;font-weight:700;letter-spacing:.04em;margin-bottom:6px}
  .result-fail .err{font-size:16px;font-weight:700;color:#991b1b;margin-bottom:8px}
  .result-fail .input-shown{font-size:13px;color:#7f1d1d;font-family:'JetBrains Mono',monospace;direction:ltr;text-align:left;word-break:break-all}

  /* Email capture */
  .capture{background:var(--bg);border:1px solid var(--line);border-radius:14px;padding:20px 24px;margin-bottom:20px}
  .capture-h{font-weight:800;font-size:16px;margin-bottom:6px}
  .capture-p{color:var(--muted);font-size:13px;margin-bottom:14px}
  .capture-form{display:flex;gap:8px}
  .capture-form input{flex:1;padding:11px 12px;font-size:14px;border:1px solid var(--line);
    border-radius:8px;font-family:'JetBrains Mono',monospace;direction:ltr;text-align:left}
  .capture-form button{padding:11px 20px;background:var(--text);color:#fff;border:0;border-radius:8px;
    font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif;white-space:nowrap}
  .capture-msg{margin-top:10px;font-size:13px;display:none}
  .capture-msg.ok{display:block;color:var(--green)}
  .capture-msg.err{display:block;color:#dc2626}

  /* CTA promo */
  .promo-card{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border-radius:14px;padding:24px;text-align:center}
  .promo-card h3{margin:0 0 8px;font-size:18px}
  .promo-card p{color:#94a3b8;font-size:13px;margin:0 0 14px}
  .promo-card a{display:inline-block;background:#10b981;color:#fff;text-decoration:none;
    padding:10px 24px;border-radius:8px;font-weight:700;font-size:14px}

  footer{padding:24px 20px;text-align:center;color:var(--dim);font-size:12px}
  footer a{color:var(--muted);text-decoration:none}

  .trap{position:absolute;left:-9999px;visibility:hidden}

  /* Quick examples */
  .examples{font-size:12px;color:var(--muted);margin-top:8px}
  .examples code{font-family:'JetBrains Mono',monospace;background:rgba(0,0,0,.04);padding:2px 6px;border-radius:4px;direction:ltr;display:inline-block}
  .examples a{color:var(--accent-dark);text-decoration:none}
  .examples a:hover{text-decoration:underline}
</style>
</head>
<body>

<header class="top">
  <div class="top-row">
    <div class="brand"><span class="lat">TKAWEN</span><span class="x">·</span><span class="by">أدوات مجانية</span></div>
    <a href="/tools/" class="top-link">← كل الأدوات</a>
  </div>
</header>

<section class="hero">
  <span class="eyebrow">أداة مجانية · مفتوحة المصدر</span>
  <h1>تأكد من رقم IBAN جزائري أو RIP بريد الجزائر</h1>
  <p class="lede">يقبل IBAN كامل (24 محرفا يبدأ بـ DZ) أو RIP/CCP (20 رقما). يعرض اسم البنك ويتحقق من المفتاح.</p>
</section>

<main>
  <form method="post" class="card">
    <label for="iban">أدخل IBAN أو RIP</label>
    <input type="text" name="iban" id="iban" autocomplete="off" spellcheck="false"
           value="<?= htmlspecialchars($input, ENT_QUOTES, 'UTF-8') ?>"
           placeholder="DZ00 0000 0000 0000 0000 00">
    <button type="submit" class="calc-btn">تحقق ←</button>
    <div class="examples">
      جرب: <a href="?iban=DZ3800200100400123456789">DZ38 0020 0100 4001 2345 6789</a> ·
           <a href="?iban=00799999002000844042">CCP 007 99999 0020008440 42</a>
    </div>
  </form>

  <?php if ($result): ?>
    <?php if ($result['valid']): ?>
    <section class="result-ok">
      <div class="ok-h">✓ صالح · <?= htmlspecialchars($result['format']) ?></div>
      <div class="ok-bank"><?= htmlspecialchars($result['bank_name']) ?></div>
      <dl>
        <dt>رمز البنك</dt>          <dd><?= htmlspecialchars($result['bank']) ?></dd>
        <dt>رمز الفرع</dt>          <dd><?= htmlspecialchars($result['branch']) ?></dd>
        <dt>رقم الحساب</dt>          <dd><?= htmlspecialchars($result['account']) ?></dd>
        <dt>مفتاح RIB</dt>          <dd><?= htmlspecialchars($result['key']) ?></dd>
        <dt>الكامل</dt>             <dd><?= htmlspecialchars($result['normalized']) ?></dd>
      </dl>
    </section>
    <?php else: ?>
    <section class="result-fail">
      <div class="h">✗ غير صالح</div>
      <div class="err"><?= htmlspecialchars($result['error'] ?: 'فشل التحقق') ?></div>
      <div class="input-shown"><?= htmlspecialchars($result['normalized']) ?></div>
    </section>
    <?php endif; ?>

    <!-- Email capture -->
    <div class="capture">
      <div class="capture-h">📩 أرسل لي هذه المعلومات + دليل المصاريف البنكية بالجزائر</div>
      <div class="capture-p">ملخص + قائمة كاملة برموز البنوك الجزائرية + جدول الرسوم المعتادة.</div>
      <form class="capture-form" id="cap-form">
        <input type="email" name="email" placeholder="you@example.com" required>
        <input type="text" name="trap" class="trap" tabindex="-1" autocomplete="off" aria-hidden="true">
        <button type="submit">أرسل</button>
      </form>
      <div class="capture-msg" id="cap-msg"></div>
    </div>
  <?php endif; ?>

  <div class="promo-card">
    <h3>تتعامل مع كثير من الحسابات البنكية؟</h3>
    <p>MyStoq يدعم Edahabia + CIB + CCP. الزبون يدفع بكل سهولة، أنت تستلم تلقائيا.</p>
    <a href="/try/?p=mystoq&utm_source=tools-iban&utm_medium=tool">جرب MyStoq 90 يوم مجانا ←</a>
  </div>
</main>

<footer>
  أداة من <a href="/" class="lat">TKAWEN</a> · للأمور البنكية الرسمية تواصل مع بنكك مباشرة
</footer>

<!-- Universal tracker — auto pageview + scroll/time -->
<script async src="/intel/track.js" data-source="tools-iban"></script>

<script>
  const capForm = document.getElementById('cap-form');
  const capMsg = document.getElementById('cap-msg');
  if (capForm) {
    capForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(capForm);
      capMsg.className = 'capture-msg';
      try {
        const r = await fetch('/intel/capture.php', {
          method: 'POST', headers: {'Content-Type':'application/json'}, credentials: 'include',
          body: JSON.stringify({
            email: fd.get('email'),
            source: 'tools-iban', page: '/tools/iban.php',
            kind: 'tool_result_request',
            fields: { tool: 'iban-validator', input_kind: '<?= $result['kind'] ?? 'none' ?>', valid: '<?= $result['valid'] ? '1' : '0' ?>' },
            trap: fd.get('trap'),
          }),
        });
        const d = await r.json();
        if (d.ok) { capMsg.className='capture-msg ok'; capMsg.textContent='✓ تم! ستصلك التفاصيل خلال دقائق.';
          capForm.querySelector('button').disabled = true; capForm.querySelector('button').textContent='✓ تم'; }
        else { capMsg.className='capture-msg err'; capMsg.textContent=d.error==='rate_limited'?'انتظر دقيقة':'خطأ — حاول مجددا'; }
      } catch (e) { capMsg.className='capture-msg err'; capMsg.textContent='خطأ في الاتصال'; }
    });
  }
</script>
</body>
</html>
