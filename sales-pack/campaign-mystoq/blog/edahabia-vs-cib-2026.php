<?php
declare(strict_types=1);
header_remove('X-Powered-By');
$title = 'Edahabia أم CIB في 2026؟ المقارنة الكاملة من تاجر استعمل الاثنين';
$desc = 'مقارنة بين بطاقتي الدفع الإلكتروني في الجزائر — الرسوم الحقيقية، التغطية، الأمان، التكامل مع المتاجر، والأنسب للتاجر مقابل الزبون.';
$pub_date = '2026-05-19';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<title><?= htmlspecialchars($title) ?> · TKAWEN</title>
<meta name="description" content="<?= htmlspecialchars($desc) ?>">
<meta name="keywords" content="Edahabia, CIB, بطاقة الذهبية, الدفع الإلكتروني الجزائر, ecommerce dz, payment cards Algeria">

<meta property="og:title" content="<?= htmlspecialchars($title) ?>">
<meta property="og:description" content="<?= htmlspecialchars($desc) ?>">
<meta property="og:type" content="article">
<meta property="og:locale" content="ar_DZ">
<meta property="article:published_time" content="<?= $pub_date ?>T00:00:00Z">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "<?= htmlspecialchars($title) ?>",
  "description": "<?= htmlspecialchars($desc) ?>",
  "datePublished": "<?= $pub_date ?>",
  "inLanguage": "ar-DZ",
  "author": {"@type": "Person", "name": "يعقوب حرتام"},
  "publisher": {"@type": "Organization", "name": "TKAWEN", "url": "https://tkawen.com"}
}
</script>

<link rel="canonical" href="https://tkawen.online/blog/edahabia-vs-cib-2026.php">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">

<style>
  :root{--bg:#fff;--bg-2:#f8fafc;--text:#0f172a;--muted:#475569;--dim:#94a3b8;
    --line:#e2e8f0;--accent:#0f172a;--link:#1d4ed8;--green:#059669;--rose:#dc2626;--gold:#d97706}
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:var(--text);font-family:'Cairo',sans-serif;line-height:1.85;direction:rtl;-webkit-font-smoothing:antialiased}
  .lat{font-family:'JetBrains Mono','SF Mono',monospace;direction:ltr;unicode-bidi:embed}
  a{color:var(--link);text-decoration:none}a:hover{text-decoration:underline}

  header.top{background:rgba(255,255,255,.92);border-bottom:1px solid var(--line);padding:14px 24px;position:sticky;top:0;z-index:10;backdrop-filter:blur(8px)}
  .top-row{max-width:760px;margin:0 auto;display:flex;align-items:center;justify-content:space-between}
  .brand{font-weight:800;font-size:16px}.brand .x{color:var(--dim);margin:0 6px}
  .brand .by{color:var(--muted);font-weight:500;font-size:13px}
  .top-back{color:var(--muted);font-size:13px}

  article{max-width:720px;margin:0 auto;padding:48px 20px 60px}
  .post-meta{font-size:13px;color:var(--muted);margin-bottom:14px;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
  .post-meta time{font-family:'JetBrains Mono',monospace}
  h1{margin:0 0 18px;font-size:38px;font-weight:900;letter-spacing:-.02em;line-height:1.25}
  .lede{color:var(--muted);font-size:18px;margin-bottom:30px;line-height:1.7}

  .toc{background:var(--bg-2);border:1px solid var(--line);border-radius:10px;padding:18px 20px;margin-bottom:36px;font-size:14px}
  .toc-h{font-size:11px;color:var(--muted);font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin-bottom:10px}
  .toc ol{margin:0;padding-inline-start:22px}.toc li{margin-bottom:5px}.toc a{color:var(--text)}

  article h2{margin:48px 0 18px;font-size:26px;font-weight:800;letter-spacing:-.01em;line-height:1.35}
  article h3{margin:32px 0 14px;font-size:19px;font-weight:700;line-height:1.4}
  article p{margin:0 0 18px;font-size:16px;line-height:1.85}
  article ul,article ol{margin:0 0 20px;padding-inline-start:26px}
  article li{margin-bottom:8px;font-size:16px}
  article strong{font-weight:700}

  .compare{width:100%;border-collapse:collapse;margin:20px 0 30px;font-size:14px}
  .compare th,.compare td{padding:11px 14px;text-align:right;border-bottom:1px solid var(--line);vertical-align:top}
  .compare th{background:var(--bg-2);font-weight:700;font-size:12px;color:var(--muted);letter-spacing:.04em}
  .compare td:first-child{font-weight:700;width:38%}
  .compare .yes{color:var(--green);font-weight:700}
  .compare .no{color:var(--rose);font-weight:700}

  .card-visual{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:24px 0}
  .pcard{padding:24px;border-radius:14px;color:#fff;font-family:'Cairo',sans-serif}
  .pcard-eda{background:linear-gradient(135deg,#d97706,#92400e)}
  .pcard-cib{background:linear-gradient(135deg,#1e40af,#1e293b)}
  .pcard-h{font-size:11px;letter-spacing:.1em;opacity:.8;font-weight:700;margin-bottom:6px;text-transform:uppercase}
  .pcard-name{font-size:22px;font-weight:900;margin-bottom:18px}
  .pcard-num{font-family:'JetBrains Mono',monospace;font-size:14px;letter-spacing:2px;direction:ltr}
  .pcard-foot{display:flex;justify-content:space-between;margin-top:18px;font-size:11px;opacity:.8;direction:ltr}

  .callout{margin:24px 0;padding:18px 22px;background:linear-gradient(135deg,#fef3c7,#fde68a);
    border:1px solid #fbbf24;border-radius:12px;color:#78350f;font-size:14px}
  .callout.green{background:linear-gradient(135deg,#ecfdf5,#d1fae5);border-color:#6ee7b7;color:#065f46}
  .callout.red{background:linear-gradient(135deg,#fef2f2,#fee2e2);border-color:#fca5a5;color:#7f1d1d}

  .midcta{margin:40px 0;padding:28px;background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border-radius:14px;text-align:center}
  .midcta h3{margin:0 0 6px;font-size:18px;color:#fff}
  .midcta p{color:#94a3b8;font-size:13px;margin:0 0 16px}
  .midcta a{display:inline-block;background:#10b981;color:#fff;padding:11px 24px;border-radius:8px;font-weight:700;font-size:14px}

  .verdict{margin:36px 0;padding:28px;border:2px solid var(--text);border-radius:14px;background:var(--bg-2)}
  .verdict-h{font-size:12px;color:var(--muted);font-weight:700;letter-spacing:.08em;margin-bottom:12px}
  .verdict h3{margin:0 0 12px;font-size:20px}
  .verdict p{font-size:15px;margin-bottom:0}

  .final-cta{margin-top:48px;padding:32px;background:linear-gradient(135deg,#1e40af,#1d4ed8);color:#fff;border-radius:16px;text-align:center}
  .final-cta h2{margin:0 0 10px;font-size:24px;color:#fff}
  .final-cta p{color:#bfdbfe;margin:0 0 18px;font-size:14px}
  .final-cta a{display:inline-block;background:#fff;color:#1d4ed8;padding:14px 32px;border-radius:10px;font-weight:800}

  footer{padding:30px 20px;border-top:1px solid var(--line);text-align:center;color:var(--dim);font-size:12px;max-width:760px;margin:0 auto}

  @media (max-width:600px){
    article{padding:24px 16px 40px}h1{font-size:28px}.lede{font-size:16px}
    article h2{font-size:22px} .card-visual{grid-template-columns:1fr}
  }
</style>
</head>
<body>

<header class="top">
  <div class="top-row">
    <div class="brand"><span class="lat">TKAWEN</span><span class="x">·</span><span class="by">المدونة</span></div>
    <a href="/blog/" class="top-back">← كل المقالات</a>
  </div>
</header>

<article>
  <div class="post-meta">
    <time datetime="<?= $pub_date ?>"><?= $pub_date ?></time>
    <span>·</span><span>7 دقائق قراءة</span><span>·</span><span>يعقوب حرتام</span>
  </div>
  <h1><?= htmlspecialchars($title) ?></h1>
  <p class="lede">إن كنت تبيع أو تشتري أونلاين في الجزائر، فأنت تحتاج بطاقة دفع إلكتروني. لديك خياران رئيسيان: <strong>Edahabia</strong> من بريد الجزائر، و<strong>CIB</strong> من البنوك التجارية. هذه مقارنة من تجربة حقيقية مع الاثنين.</p>

  <nav class="toc">
    <div class="toc-h">محتويات</div>
    <ol>
      <li><a href="#intro">من يصدر كل بطاقة؟</a></li>
      <li><a href="#cost">التكلفة الحقيقية</a></li>
      <li><a href="#coverage">أين تشتغل؟</a></li>
      <li><a href="#online">للتاجر الإلكتروني</a></li>
      <li><a href="#security">الأمان</a></li>
      <li><a href="#verdict">القرار</a></li>
    </ol>
  </nav>

  <div class="card-visual">
    <div class="pcard pcard-eda">
      <div class="pcard-h">Algérie Poste</div>
      <div class="pcard-name">EDAHAB!A</div>
      <div class="pcard-num">8888 1234 5678 9012</div>
      <div class="pcard-foot"><span>VALID 12/28</span><span>YAAKOUB H.</span></div>
    </div>
    <div class="pcard pcard-cib">
      <div class="pcard-h">Bank · Algeria</div>
      <div class="pcard-name">CIB</div>
      <div class="pcard-num">5101 1234 5678 9012</div>
      <div class="pcard-foot"><span>VALID 12/28</span><span>YAAKOUB H.</span></div>
    </div>
  </div>

  <h2 id="intro">1. من يصدر كل بطاقة؟</h2>

  <p><strong>Edahabia</strong> (الذهبية) هي بطاقة سحب ودفع تصدرها <strong>بريد الجزائر (Algérie Poste)</strong> منذ 2016. تأتي مع حساب CCP — أي أن أي شخص لديه حساب بريدي يستطيع طلبها مجانا. هذا جعلها الأكثر انتشارا في الجزائر.</p>

  <p><strong>CIB</strong> (Carte Interbancaire) هي بطاقة تصدرها <strong>البنوك التجارية</strong> (BNA, BEA, CPA, BDL, AGB...) لأصحاب الحسابات البنكية. هي بطاقة فيزا/ماستركارد <strong>محلية</strong> — تشتغل في الجزائر فقط، لكن بمعايير دولية.</p>

  <p>الفرق الأساسي:</p>
  <ul>
    <li>Edahabia: مرتبطة بحساب CCP — يستطيع الحصول عليها أي مواطن جزائري</li>
    <li>CIB: مرتبطة بحساب بنكي تجاري — تحتاج فتح حساب في بنك أولا</li>
  </ul>

  <h2 id="cost">2. التكلفة الحقيقية</h2>

  <table class="compare">
    <thead><tr><th>التكلفة</th><th>Edahabia</th><th>CIB</th></tr></thead>
    <tbody>
      <tr><td>سعر الإصدار</td><td><strong>0 دج</strong> (مجانية)</td><td>500-1,500 دج (حسب البنك)</td></tr>
      <tr><td>اشتراك سنوي</td><td><strong>0 دج</strong></td><td>1,200-3,000 دج/سنة</td></tr>
      <tr><td>سحب من موزّع نفس البنك</td><td>20 دج/عملية</td><td>0 دج عادة</td></tr>
      <tr><td>سحب من موزّع بنك آخر</td><td>20-40 دج/عملية</td><td>40-80 دج/عملية</td></tr>
      <tr><td>دفع إلكتروني (TPE في محل)</td><td><strong>0 دج</strong></td><td><strong>0 دج</strong></td></tr>
      <tr><td>دفع إلكتروني (e-commerce)</td><td>0 دج للزبون</td><td>0 دج للزبون</td></tr>
      <tr><td>رسوم التاجر (السلام للقبول)</td><td>1.5-2.5%</td><td>1.0-1.8% (أقل)</td></tr>
    </tbody>
  </table>

  <p>للزبون العادي: <strong>Edahabia أرخص بكثير</strong>. للتاجر الذي يقبل المدفوعات: <strong>CIB أرخص قليلا</strong> (رسوم أقل لكل معاملة).</p>

  <div class="callout green">
    <strong>ملاحظة:</strong> الفرق في رسوم المعاملة (1.5% Edahabia vs 1.0% CIB) ينتج عنه فارق ملحوظ على المبيعات الكبيرة. إن كان متجرك يبيع 100 طلبية × 5,000 دج شهريا = 500,000 دج، فالفارق 2,500 دج شهريا. ليس كثيرا، لكنه يضاف.
  </div>

  <h2 id="coverage">3. أين تشتغل؟ التغطية</h2>

  <p>هذا ربما أهم سؤال للتاجر — <strong>كم نسبة الزبائن الجزائريين الذين يملكون كل بطاقة؟</strong></p>

  <table class="compare">
    <thead><tr><th>الحاملون (تقديريا 2026)</th><th>Edahabia</th><th>CIB</th></tr></thead>
    <tbody>
      <tr><td>عدد البطاقات الفعلية</td><td><strong>~10 مليون</strong></td><td>~3.5 مليون</td></tr>
      <tr><td>المستخدمون النشطون أونلاين</td><td>~6 مليون</td><td>~2 مليون</td></tr>
      <tr><td>الانتشار الجغرافي</td><td>كل البلد (مع تركز أكبر في الجنوب)</td><td>المدن الكبرى أساسا</td></tr>
      <tr><td>الفئة العمرية الأنشط</td><td>25-45 سنة</td><td>30-55 سنة</td></tr>
      <tr><td>عدم نشاط شائع</td><td>40% من الحاملين</td><td>15% من الحاملين</td></tr>
    </tbody>
  </table>

  <p>الخلاصة: <strong>Edahabia تغطي 3x أكثر من Edahabia</strong> للسوق الجزائري. إن كنت تبيع لزبائن عاديين، Edahabia ضرورية. إن كنت تبيع لشركات وأصحاب أعمال، CIB كافية.</p>

  <h2 id="online">4. للتاجر الإلكتروني</h2>

  <p>كلتا البطاقتين تدعمان الدفع الإلكتروني (e-paiement) منذ 2020-2021. لكن التجربة الفعلية مختلفة:</p>

  <h3>Edahabia عبر SATIM</h3>
  <p>تستعمل بوابة SATIM (الشركة العمومية للنقد الآلي). البوابة عملية لكنها قديمة الواجهة. مشاكل شائعة:</p>
  <ul>
    <li>التحويل من متجرك إلى صفحة SATIM يبدو "خروج من الموقع" — يخفض معدل الإتمام 5-10%</li>
    <li>أوقات الذروة (نهاية الشهر) قد تشهد بطأ في المعالجة</li>
    <li>عند فشل العملية، الزبون يرى رسالة بالفرنسية حتى لو متجرك بالعربية</li>
  </ul>

  <h3>CIB عبر CIB.dz / 3DS</h3>
  <p>بوابة CIB أحدث. تجربة الدفع أكثر سلاسة، الواجهة الموبايل أفضل. مشاكل أقل، لكن:</p>
  <ul>
    <li>أكثر صرامة في التحقق (3D-Secure غالبا مفعل)</li>
    <li>نسبة الزبائن الذين يكملون = 70-80% (مقابل 60-70% مع Edahabia)</li>
  </ul>

  <div class="callout red">
    <strong>تحذير حقيقي:</strong> بعض التجار لا يتقبلون Edahabia عمدا لتجنب المعاملات الصغيرة (متوسط Edahabia = 800 دج، متوسط CIB = 2,500 دج). هذا خطأ استراتيجي — تفقد 60٪ من السوق المحتمل لتوفير 5٪ في الرسوم.
  </div>

  <div class="midcta">
    <h3>تريد قبول Edahabia + CIB في متجرك؟</h3>
    <p>MyStoq يربط الاثنين تلقائيا — التاجر يضغط زرا واحدا، ويبدأ في تحصيل المدفوعات.</p>
    <a href="/try/?p=mystoq&utm_source=blog&utm_campaign=edahabia-vs-cib">جرب MyStoq 90 يوما مجانا ←</a>
  </div>

  <h2 id="security">5. الأمان</h2>

  <p>كلتا البطاقتين تستعمل تشفير EMV (شريحة) + رمز PIN. الفرق في طبقات الأمان الإلكتروني:</p>

  <table class="compare">
    <thead><tr><th>الأمان</th><th>Edahabia</th><th>CIB</th></tr></thead>
    <tbody>
      <tr><td>شريحة EMV</td><td class="yes">✓</td><td class="yes">✓</td></tr>
      <tr><td>OTP بـ SMS عند الدفع</td><td class="yes">✓ (مع SATIM)</td><td class="yes">✓ (3D-Secure)</td></tr>
      <tr><td>الإشعار الفوري بكل عملية</td><td>SMS عادي</td><td class="yes">SMS + تطبيق</td></tr>
      <tr><td>تجميد البطاقة من تطبيق</td><td class="no">يتطلب الذهاب لمكتب البريد</td><td class="yes">✓ من التطبيق</td></tr>
      <tr><td>التعويض في حالة الاحتيال</td><td>إجراءات معقدة، عدة أسابيع</td><td>إجراءات أسرع، أيام</td></tr>
    </tbody>
  </table>

  <p>للمستخدم الذي يقدر سرعة الاستجابة في حالة المشاكل: <strong>CIB أفضل</strong>. للمستخدم الذي يقدر البساطة وقلة التكلفة: <strong>Edahabia كافية</strong>.</p>

  <h2 id="verdict">6. القرار</h2>

  <p>متى تستعمل Edahabia؟</p>
  <ul>
    <li>إن كنت زبونا عاديا تشتري أونلاين من حين لآخر</li>
    <li>إن لم تكن لديك علاقة مع بنك تجاري</li>
    <li>إن كنت تريد بطاقة دفع بدون رسوم سنوية</li>
    <li>إن كنت تاجرا وتريد قبول 80٪ من السوق الجزائري</li>
  </ul>

  <p>متى تستعمل CIB؟</p>
  <ul>
    <li>إن كنت تنجز معاملات كبيرة (>10,000 دج)</li>
    <li>إن كنت تقدر سرعة حلول البنك في المشاكل</li>
    <li>إن كنت تاجرا والمعاملات الكبرى هي حجم متجرك</li>
    <li>إن كنت تحتاج بطاقة لإثبات الملاءة (تأجير، حجوزات...)</li>
  </ul>

  <div class="verdict">
    <div class="verdict-h">القرار النهائي للتاجر الجزائري</div>
    <h3>اقبل الاثنين. ليست هناك مفاضلة حقيقية للتاجر.</h3>
    <p>إن كان متجرك يقبل Edahabia + CIB، فأنت تغطي <strong>95٪+</strong> من السوق الجزائري. تكلفة قبول الاثنين منخفضة (نفس بوابة الدفع، نفس البنية)، والربح من توسيع التغطية أكبر بكثير من رسوم المعاملة. <strong>MyStoq يدعم الاثنين منذ اليوم الأول</strong>، بدون أي إعداد إضافي.</p>
  </div>

  <p>إن كان قرارك بصفة شخصية كزبون: <strong>Edahabia أولا</strong> لأنها مجانية ومنتشرة. اطلب بطاقة CIB لاحقا إن احتجت إجراء معاملات بنكية أكبر.</p>

  <div class="final-cta">
    <h2>افتح متجرك مع قبول Edahabia + CIB في يوم واحد</h2>
    <p>MyStoq يدمج الاثنين تلقائيا. 90 يوم مجاني، بدون بطاقة بنكية.</p>
    <a href="/try/?p=mystoq&utm_source=blog&utm_medium=final-cta&utm_campaign=edahabia-vs-cib">جرب MyStoq ←</a>
  </div>

</article>

<footer>
  <p><a href="/blog/">المزيد من المقالات</a> · <a href="/tools/iban.php">تحقق من IBAN جزائري</a> · <a href="/tools/tva.php">حاسبة TVA</a></p>
  <p>TKAWEN · صنع في عنابة، الجزائر</p>
</footer>

<script async src="/intel/track.js" data-source="blog-edahabia-vs-cib"></script>
</body>
</html>
