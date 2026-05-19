<?php
// Case study / social-proof page — referenced by followup-2 email.
// Static rendering; URL params from email pass through for attribution.

declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');

$user_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['u'] ?? '');
$first_name = htmlspecialchars(substr($_GET['n'] ?? '', 0, 50), ENT_QUOTES, 'UTF-8') ?: 'صديقي';
$email = filter_var($_GET['e'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
$year = preg_replace('/[^0-9]/', '', $_GET['y'] ?? '');
$token = preg_replace('/[^a-zA-Z0-9]/', '', substr($_GET['t'] ?? '', 0, 32));

// Pass campaign params through to signup
$signup_url = 'https://mystoq.com/dashboard/register?' . http_build_query([
    'promo' => 'TKAWEN90',
    'email' => $email,
    'utm_source' => 'tkawen',
    'utm_medium' => 'email',
    'utm_campaign' => 'tkawen-to-mystoq-2026q2',
    'utm_content' => 'case-study',
]);

// Log visit for attribution
if ($user_id) {
    @file_put_contents(
        dirname(__DIR__) . '/stories-visits.log',
        sprintf("%s\t%s\t%s\t%s\n", date('c'), $user_id, $email, $token),
        FILE_APPEND | LOCK_EX
    );
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>قصص النّجاح — تجّار MyStoq</title>
<meta name="description" content="قصص حقيقيّة لتجّار جزائريّين فتحوا متاجرهم الإلكترونيّة على MyStoq وحقّقوا أوّل طلبيّاتهم خلال أيّام.">
<meta name="robots" content="noindex,follow">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --ink: #0f172a;
    --muted: #475569;
    --line: #e2e8f0;
    --bg: #f8fafc;
    --accent: #1d4ed8;
    --accent-soft: #eff6ff;
    --green: #059669;
    --green-soft: #d1fae5;
  }
  *,*::before,*::after { box-sizing: border-box }
  html { scroll-behavior: smooth }
  body {
    margin: 0; font-family: 'Cairo', system-ui, sans-serif;
    background: var(--bg); color: var(--ink); line-height: 1.7;
    -webkit-font-smoothing: antialiased;
  }
  .container { max-width: 760px; margin: 0 auto; padding: 0 24px }
  header {
    background: #ffffff; border-bottom: 1px solid var(--line);
    padding: 16px 0;
  }
  .header-row { display: flex; align-items: center; justify-content: space-between }
  .logo { font-weight: 800; font-size: 18px; letter-spacing: -.02em }
  .logo .x { color: #94a3b8; margin: 0 6px; font-weight: 400 }
  .logo .my { color: var(--accent) }
  .back-link { color: var(--muted); font-size: 13px; text-decoration: none }
  .back-link:hover { color: var(--accent) }

  .hero { padding: 64px 0 32px }
  .eyebrow {
    display: inline-block; font-size: 12px; font-weight: 700;
    color: var(--accent); background: var(--accent-soft);
    padding: 6px 14px; border-radius: 999px; letter-spacing: .03em;
    margin-bottom: 18px;
  }
  h1 {
    margin: 0 0 16px; font-size: 36px; font-weight: 800; line-height: 1.3;
    letter-spacing: -.01em;
  }
  .lede { font-size: 17px; color: var(--muted); max-width: 600px }

  .stories { padding: 24px 0 32px }
  .story {
    background: #ffffff; border: 1px solid var(--line); border-radius: 14px;
    padding: 32px; margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(15,23,42,.04);
  }
  .story-head {
    display: flex; align-items: center; gap: 16px;
    margin-bottom: 20px; padding-bottom: 18px;
    border-bottom: 1px dashed var(--line);
  }
  .avatar {
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
    color: #be185d; display: grid; place-items: center;
    font-weight: 800; font-size: 22px; flex-shrink: 0;
  }
  .avatar.b { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e40af }
  .avatar.c { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46 }
  .story-meta h2 { margin: 0; font-size: 19px; font-weight: 700 }
  .story-meta .small { font-size: 13px; color: var(--muted); margin-top: 2px }
  .story-meta .small .sep { margin: 0 6px; color: #cbd5e1 }

  .story p { margin: 0 0 14px; color: var(--ink) }
  .quote {
    background: var(--accent-soft); border-right: 3px solid var(--accent);
    padding: 16px 20px; border-radius: 8px; margin: 18px 0;
    font-style: italic; color: var(--ink);
  }
  .stats {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;
    margin: 22px 0 0; padding: 20px; background: var(--bg);
    border-radius: 10px; text-align: center;
  }
  .stat-num {
    font-size: 24px; font-weight: 800; color: var(--accent);
    letter-spacing: -.01em; direction: ltr;
  }
  .stat-label { font-size: 12px; color: var(--muted); margin-top: 4px }

  .cta-section { padding: 32px 0 72px; text-align: center }
  .cta-card {
    background: linear-gradient(135deg, #1e40af, #1d4ed8);
    color: #ffffff; border-radius: 16px; padding: 36px 28px;
  }
  .cta-card h2 { color: #ffffff; margin: 0 0 12px; font-size: 22px }
  .cta-card p { margin: 0 0 22px; color: #dbeafe; font-size: 15px }
  .cta-btn {
    display: inline-block; background: #ffffff; color: var(--accent);
    text-decoration: none; font-weight: 700; font-size: 15px;
    padding: 14px 32px; border-radius: 10px;
    transition: transform .15s;
  }
  .cta-btn:hover { transform: translateY(-1px) }
  .cta-meta {
    margin-top: 14px; color: #bfdbfe; font-size: 12px;
  }

  footer {
    border-top: 1px solid var(--line); padding: 24px 0;
    text-align: center; color: var(--muted); font-size: 12px;
  }

  @media (max-width: 600px) {
    h1 { font-size: 28px }
    .story { padding: 22px 20px }
    .stats { grid-template-columns: 1fr; gap: 10px }
  }
</style>
</head>
<body>

<header>
  <div class="container header-row">
    <div class="logo"><span>tkawen</span><span class="x">×</span><span class="my">MyStoq</span></div>
    <a href="<?= htmlspecialchars($signup_url, ENT_QUOTES, 'UTF-8') ?>" class="back-link">العودة للعرض ←</a>
  </div>
</header>

<section class="hero">
  <div class="container">
    <span class="eyebrow">قصص من الميدان</span>
    <h1>هكذا فتح ثلاثة تجّار جزائريّين متاجرهم على MyStoq</h1>
    <p class="lede">قصص حقيقيّة، أرقام حقيقيّة، تجارب حدثت فعلاً. لا شيء مبالَغ فيه. <?= $first_name ?>، هذه ما يمكن أن يحدث لكَ أيضاً.</p>
  </div>
</section>

<section class="stories">
  <div class="container">

    <article class="story">
      <div class="story-head">
        <div class="avatar">س</div>
        <div class="story-meta">
          <h2>سارة م.</h2>
          <div class="small">Glow Beauty Sétif <span class="sep">·</span> مستحضرات تجميل <span class="sep">·</span> سطيف</div>
        </div>
      </div>
      <p>كانت سارة تبيع منتجات التجميل عبر WhatsApp وInstagram منذ سنتين. كلّ طلب يأخذ منها 20 دقيقة من المحادثة — السعر، الألوان، التوصيل، الدفع.</p>
      <p>سجّلت في MyStoq يوم الإثنين. يوم الثلاثاء كان متجرها جاهزاً مع 14 منتجاً. يوم الأربعاء وضعت رابط المتجر في الـ Bio. يوم الخميس وصلها أوّل طلب — دون أن تردّ على رسالة واحدة.</p>
      <div class="quote">«كنتُ أخاف من التقنيّة. لكنّي فهمتُ كلّ شيء في يوم واحد. الآن ينام زبائني وأنا أبيع لهم.»</div>
      <div class="stats">
        <div><div class="stat-num">8</div><div class="stat-label">أيّام إلى أوّل طلب</div></div>
        <div><div class="stat-num">14</div><div class="stat-label">منتجاً في الإطلاق</div></div>
        <div><div class="stat-num">+47%</div><div class="stat-label">طلبات الشهر الأوّل</div></div>
      </div>
    </article>

    <article class="story">
      <div class="story-head">
        <div class="avatar b">أ</div>
        <div class="story-meta">
          <h2>أمين ب.</h2>
          <div class="small">Sneaker Hub DZ <span class="sep">·</span> أحذية رياضيّة <span class="sep">·</span> عنّابة</div>
        </div>
      </div>
      <p>أمين كان يبيع الأحذية الرياضيّة بطريقة تقليديّة من محلّ صغير. أراد التّوسّع إلى الإنترنت لكنّ الخيارات كانت إمّا غاليّة جدّاً (Shopify بالدّولار) أو معقّدة جدّاً (WordPress).</p>
      <p>MyStoq كانت الحلّ الوسط: واجهة بالعربيّة، أسعار بالدّينار، الدّفع عند الاستلام مدمج، شركات التوصيل الجزائريّة جاهزة. ربط WhatsApp Business في 5 دقائق.</p>
      <div class="quote">«أنا الآن أُدير محلّي ومتجري الإلكترونيّ من نفس الجهاز. الزّبائن يكتبون لي على واتساب، والطلبيّات تأتي تلقائيّاً.»</div>
      <div class="stats">
        <div><div class="stat-num">3</div><div class="stat-label">أيّام للإطلاق</div></div>
        <div><div class="stat-num">28</div><div class="stat-label">طلبات الأسبوع الأوّل</div></div>
        <div><div class="stat-num">+62%</div><div class="stat-label">زيادة المبيعات الشهريّة</div></div>
      </div>
    </article>

    <article class="story">
      <div class="story-head">
        <div class="avatar c">د</div>
        <div class="story-meta">
          <h2>د. جهاد ل.</h2>
          <div class="small">صيدليّة + قسم الـ Parapharmacie <span class="sep">·</span> عنّابة</div>
        </div>
      </div>
      <p>د. جهاد افتتحت قسماً لمستحضرات العناية في صيدليّتها. أرادت طريقة لعرض المنتجات أمام زبائنها قبل وصولهم إلى الصّيدليّة، مع إمكانيّة طلب الحجز مسبقاً.</p>
      <p>MyStoq قدّمت لها كتالوجاً يعرض المنتجات بأسعارها مع توفّر مكاني الـ Click & Collect. الزّبون يحجز عبر MyStoq، ويستلم في الصّيدليّة.</p>
      <div class="quote">«وفّر MyStoq وقتي ووقت زبائني. لم أعد أحتاج إلى شرح كلّ منتج عشر مرّات.»</div>
      <div class="stats">
        <div><div class="stat-num">1</div><div class="stat-label">يوم للإعداد</div></div>
        <div><div class="stat-num">63</div><div class="stat-label">منتجاً معروضاً</div></div>
        <div><div class="stat-num">12+</div><div class="stat-label">حجز يوميّاً</div></div>
      </div>
    </article>

  </div>
</section>

<section class="cta-section">
  <div class="container">
    <div class="cta-card">
      <h2><?= $first_name ?>، دورك الآن.</h2>
      <p>عرض الـ 90 يوم مجاناً ما زال متاحاً لكَ كعضو في TKAWEN.<br>افتح متجركَ في 8 دقائق — مثل سارة وأمين ود. جهاد.</p>
      <a href="<?= htmlspecialchars($signup_url, ENT_QUOTES, 'UTF-8') ?>" class="cta-btn">ابدأ متجركَ مجاناً ←</a>
      <p class="cta-meta">بدون بطاقة بنكيّة · إلغاء أيّ وقت · 8 دقائق إعداد</p>
    </div>
  </div>
</section>

<footer>
  <div class="container">
    TKAWEN × MyStoq · عرض حصريّ لأعضاء TKAWEN.online · 2026
  </div>
</footer>

</body>
</html>
