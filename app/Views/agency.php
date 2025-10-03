<?php
require_once __DIR__ . '/../Core/config.php';
require_once __DIR__ . '/../Core/helpers.php';

$brand = env('AGENCY_NAME', 'Sua Agência Digital');
$slogan = env('AGENCY_SLOGAN', 'Tecnologia, design e performance para vender todos os dias.');
$projects = env('AGENCY_PROJECTS_COUNT', '100+');
$years = env('AGENCY_YEARS', '5+');
$clients = env('AGENCY_CLIENTS_COUNT', '80+');
$waRaw = env('WHATSAPP_NUMBER', '55XXXXXXXXXX');
$waNumber = preg_replace('/\D+/', '', $waRaw);
$waMsg = rawurlencode('Olá! Gostaria de um orçamento.');
$waLink = "https://wa.me/{$waNumber}?text={$waMsg}";
$siteTitle = "Sites, Landing Pages e Sistemas | {$brand}";
$metaDesc = 'Velocidade, design moderno e tecnologia de ponta. Desenvolvemos sites, landing pages e sistemas personalizados focados em conversão e suporte premium.';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($siteTitle) ?></title>
  <meta name="description" content="<?= e($metaDesc) ?>" />
  <meta name="theme-color" content="#2563eb" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="<?= e($siteTitle) ?>" />
  <meta property="og:description" content="<?= e($metaDesc) ?>" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">

  <!-- Tailwind CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">

  <style>
    :root {
      --blue: #2563eb; /* blue-600 */
      --blue-hover: #1e40af; /* blue-800 */
      --ice: #f8fafc; /* slate-50 */
      --card: #ffffff;
      --text: #0f172a; /* slate-900 */
    }
    html { scroll-behavior: smooth; }
    body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, "Helvetica Neue", Arial, "Noto Sans", sans-serif; color: var(--text); }
    .font-heading { font-family: Sora, Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, "Helvetica Neue", Arial, "Noto Sans", sans-serif; }
    .btn-primary { background: var(--blue); color: #fff; }
    .btn-primary:hover { background: var(--blue-hover); }
    .btn-outline { border: 1px solid var(--blue); color: var(--blue); }
    .btn-outline:hover { background: #eff6ff; }
    .elevate { transition: transform .2s ease, box-shadow .2s ease; }
    .elevate:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -10px rgba(2, 6, 23, .25); }
    .sticky-header { backdrop-filter: saturate(180%) blur(8px); }
  </style>
  
  <!-- JSON-LD: Organization -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": <?= json_encode($brand, JSON_UNESCAPED_UNICODE) ?>,
    "url": "",
    "sameAs": [],
    "contactPoint": [{
      "@type": "ContactPoint",
      "contactType": "customer support",
      "telephone": "<?= e($waNumber) ?>"
    }]
  }
  </script>

  <!-- JSON-LD: Services -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "ProfessionalService",
    "name": <?= json_encode($brand, JSON_UNESCAPED_UNICODE) ?>,
    "serviceType": ["Website Development", "Landing Page Design", "Custom Software Development"],
    "areaServed": "BR",
    "availableChannel": {
      "@type": "ServiceChannel",
      "serviceUrl": "",
      "servicePhone": "<?= e($waNumber) ?>"
    }
  }
  </script>

  <!-- Basic styles to ensure smooth anchor spacing under sticky header -->
  <style>
    section { scroll-margin-top: 90px; }
  </style>
</head>
<body class="bg-[var(--ice)]">
  <!-- Header -->
  <header id="header" class="sticky top-0 z-40 sticky-header bg-white/80 border-b border-slate-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <a href="#inicio" class="flex items-center gap-2 group">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white font-bold">A</span>
        <div class="leading-tight">
          <div class="font-heading font-semibold"><?= e($brand) ?></div>
          <div class="text-xs text-slate-500"><?= e($slogan) ?></div>
        </div>
      </a>
      <nav class="hidden md:flex items-center gap-6 text-sm">
        <a href="#inicio" class="text-slate-700 hover:text-slate-900">Início</a>
        <a href="#servicos" class="text-slate-700 hover:text-slate-900">Serviços</a>
        <a href="#portfolio" class="text-slate-700 hover:text-slate-900">Portfólio</a>
        <a href="#depoimentos" class="text-slate-700 hover:text-slate-900">Depoimentos</a>
        <a href="#planos" class="text-slate-700 hover:text-slate-900">Planos</a>
        <a href="#faq" class="text-slate-700 hover:text-slate-900">FAQ</a>
        <a href="#contato" class="text-slate-700 hover:text-slate-900">Contato</a>
      </nav>
      <div class="hidden md:block">
        <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="btn-primary elevate rounded-xl px-4 py-2 text-sm font-medium" data-ev="Contact" data-ev-label="Header WhatsApp">Falar no WhatsApp</a>
      </div>
      <button id="menuBtn" class="md:hidden inline-flex items-center justify-center h-10 w-10 rounded-lg hover:bg-slate-100" aria-label="Abrir menu">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
      </button>
    </div>
    <div id="mobileNav" class="md:hidden hidden border-t border-slate-200 bg-white">
      <div class="px-4 py-3 grid gap-3 text-sm">
        <a href="#inicio" class="block">Início</a>
        <a href="#servicos" class="block">Serviços</a>
        <a href="#portfolio" class="block">Portfólio</a>
        <a href="#depoimentos" class="block">Depoimentos</a>
        <a href="#planos" class="block">Planos</a>
        <a href="#faq" class="block">FAQ</a>
        <a href="#contato" class="block">Contato</a>
        <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="btn-primary rounded-xl px-4 py-2 text-center" data-ev="Contact" data-ev-label="Mobile WhatsApp">Falar no WhatsApp</a>
      </div>
    </div>
  </header>

  <!-- Hero -->
  <section id="inicio" class="relative">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
      <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
          <h1 class="font-heading text-3xl sm:text-5xl font-bold tracking-tight text-slate-900">Sites, Landing Pages e Sistemas que vendem todos os dias.</h1>
          <p class="mt-5 text-lg text-slate-700">Velocidade, design moderno e tecnologia de ponta para seu negócio crescer.</p>
          <div class="mt-7 flex flex-wrap gap-3">
            <a href="#contato" class="btn-primary elevate rounded-xl px-5 py-3 font-medium" data-ev="ViewContent" data-ev-label="Hero Orçamento">Quero um orçamento</a>
            <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="btn-outline elevate rounded-xl px-5 py-3 font-medium" data-ev="Contact" data-ev-label="Hero WhatsApp">Falar no WhatsApp</a>
          </div>
          <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-slate-600">
            <span class="inline-flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75"/></svg><?= e($projects) ?> projetos entregues</span>
            <span class="inline-flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg><?= e($years) ?> anos de mercado</span>
            <span class="inline-flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>Suporte premium</span>
          </div>
          <div class="mt-4 flex gap-6 text-xs text-slate-500">
            <span class="inline-flex items-center gap-2" title="SSL"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75A4.5 4.5 0 0 0 12 2.25v0a4.5 4.5 0 0 0-4.5 4.5V10.5m-2.25 0h13.5a2.25 2.25 0 0 1 2.25 2.25v6.75a2.25 2.25 0 0 1-2.25 2.25H3.75A2.25 2.25 0 0 1 1.5 19.5v-6.75A2.25 2.25 0 0 1 3.75 10.5Z"/></svg>SSL</span>
            <span class="inline-flex items-center gap-2" title="Garantia"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4m3.75 5.25V6.108c0-.51-.324-.963-.802-1.124l-6-2a1.5 1.5 0 0 0-.896 0l-6 2a1.125 1.125 0 0 0-.802 1.124V17.25A2.25 2.25 0 0 0 4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25Z"/></svg>Garantia</span>
            <span class="inline-flex items-center gap-2" title="Suporte"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 1 1-12.728 12.728M15 11.25A3.75 3.75 0 1 1 8.25 7.5"/></svg>Suporte</span>
          </div>
        </div>
        <div class="relative">
          <div class="rounded-2xl bg-white shadow p-6 border border-slate-100">
            <img src="https://placehold.co/720x420?text=Seu+Projeto+em+Destaque" alt="Mockup projeto" class="rounded-xl w-full h-auto" loading="lazy">
            <div class="mt-3 text-xs text-slate-500">* Imagem ilustrativa</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Diferenciais Rápidos -->
  <section class="py-16" aria-labelledby="diferenciais">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <h2 id="diferenciais" class="font-heading text-2xl sm:text-3xl font-semibold">Por que escolher a <?= e($brand) ?>?</h2>
      <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <?php $diffs = [
          ['Velocidade e performance', 'M12 3v3m0 12v3M3 12h3m12 0h3M5.636 5.636l2.121 2.121m8.486 8.486 2.121 2.121M5.636 18.364l2.121-2.121m8.486-8.486 2.121-2.121'],
          ['Design responsivo', 'M3 6.75h17.25M3 12h17.25M3 17.25h10.5'],
          ['SEO técnico', 'M3 12l2.25 2.25L9 9.75m3 6.75h9M12 3v18'],
          ['Integrações (Pixel, CAPI, CRM)', 'M4.5 12a7.5 7.5 0 1 0 15 0 7.5 7.5 0 0 0-15 0z'],
          ['Suporte dedicado', 'M2.25 12 21.75 12M4.5 7.5 19.5 7.5M6.75 3 17.25 3']
        ]; foreach ($diffs as [$title, $path]): ?>
        <div class="rounded-2xl bg-white border border-slate-100 p-5 elevate">
          <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="<?= e($path) ?>"/></svg>
          </div>
          <div class="mt-3 font-medium text-slate-800"><?= e($title) ?></div>
          <div class="text-sm text-slate-600">Excelência técnica com foco em resultado.</div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Serviços -->
  <section id="servicos" class="py-16 border-t border-slate-200 bg-white" aria-labelledby="servicos-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <h2 id="servicos-title" class="font-heading text-2xl sm:text-3xl font-semibold">Serviços</h2>
      <div class="mt-8 grid lg:grid-cols-3 gap-6">
        <!-- Sites Institucionais -->
        <div class="rounded-2xl border border-slate-200 p-6 bg-white elevate">
          <div class="flex items-center gap-3">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18M3 9.75h18M3 15h18M3 19.5h18"/></svg>
            </div>
            <h3 class="font-semibold text-lg">Sites Institucionais</h3>
          </div>
          <p class="mt-3 text-slate-700">Presença digital completa com blog, SEO e páginas essenciais.</p>
          <ul class="mt-4 space-y-2 text-sm text-slate-700 list-disc pl-5">
            <li>Arquitetura de informação e conteúdo</li>
            <li>SEO on-page e performance</li>
            <li>Integrações (Analytics, Pixel, CRM)</li>
            <li>Prazo médio: 2–4 semanas</li>
          </ul>
          <a href="#contato" class="mt-6 inline-block btn-primary rounded-xl px-4 py-2" data-ev="Lead" data-ev-label="Card Sites">Pedir orçamento</a>
        </div>
        <!-- Landing Pages -->
        <div class="rounded-2xl border border-slate-200 p-6 bg-white elevate">
          <div class="flex items-center gap-3">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M6 10.5h12M6 14.25h7.5M6 18h4.5"/></svg>
            </div>
            <h3 class="font-semibold text-lg">Landing Pages de Alta Conversão</h3>
          </div>
          <p class="mt-3 text-slate-700">Campanhas, captação de leads e VSL com copy e UX focadas em conversão.</p>
          <ul class="mt-4 space-y-2 text-sm text-slate-700 list-disc pl-5">
            <li>Estratégia de oferta e prova social</li>
            <li>Eventos e pixels (ViewContent, Lead)</li>
            <li>Formulários/WhatsApp e timers opcionais</li>
            <li>Prazo médio: 1–2 semanas</li>
          </ul>
          <a href="#contato" class="mt-6 inline-block btn-primary rounded-xl px-4 py-2" data-ev="Lead" data-ev-label="Card Landing">Pedir orçamento</a>
        </div>
        <!-- Sistemas Personalizados -->
        <div class="rounded-2xl border border-slate-200 p-6 bg-white elevate">
          <div class="flex items-center gap-3">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h10.5v10.5H6.75z"/></svg>
            </div>
            <h3 class="font-semibold text-lg">Sistemas Personalizados</h3>
          </div>
          <p class="mt-3 text-slate-700">CRMs, financeiros, automações e integrações sob medida.</p>
          <ul class="mt-4 space-y-2 text-sm text-slate-700 list-disc pl-5">
            <li>Mapeamento de processos e UX</li>
            <li>Integrações via APIs</li>
            <li>Escalabilidade e segurança</li>
            <li>Prazo: sob estimativa</li>
          </ul>
          <a href="#contato" class="mt-6 inline-block btn-primary rounded-xl px-4 py-2" data-ev="Lead" data-ev-label="Card Sistema">Pedir orçamento</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Portfólio / Cases -->
  <section id="portfolio" class="py-16" aria-labelledby="portfolio-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex items-end justify-between gap-4">
        <div>
          <h2 id="portfolio-title" class="font-heading text-2xl sm:text-3xl font-semibold">Portfólio / Cases</h2>
          <p class="text-slate-700 mt-1">Resultados reais em velocidade, SEO e conversão.</p>
        </div>
        <a href="#contato" class="hidden sm:inline-block btn-outline rounded-xl px-4 py-2">Ver mais cases</a>
      </div>
      <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php for ($i=1; $i<=6; $i++): ?>
        <article class="rounded-2xl overflow-hidden bg-white border border-slate-200 elevate">
          <img src="https://placehold.co/640x360?text=Projeto+<?= $i ?>" alt="Projeto <?= $i ?>" class="w-full h-auto" loading="lazy">
          <div class="p-4">
            <h3 class="font-medium">Projeto <?= $i ?></h3>
            <p class="text-sm text-slate-600">Nicho <?= $i ?></p>
          </div>
        </article>
        <?php endfor; ?>
      </div>
      <div class="sm:hidden mt-6">
        <a href="#contato" class="btn-outline rounded-xl px-4 py-2 w-full text-center block">Ver mais cases</a>
      </div>
    </div>
  </section>

  <!-- Depoimentos -->
  <section id="depoimentos" class="py-16 border-t border-slate-200 bg-white" aria-labelledby="depoimentos-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <h2 id="depoimentos-title" class="font-heading text-2xl sm:text-3xl font-semibold">Depoimentos</h2>
      <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php $depos = [
          ['Ana Souza', 'CEO - Loja X', 'A equipe entregou rápido e com qualidade. Nossa taxa de conversão dobrou.'],
          ['Rafael Lima', 'CMO - Startup Y', 'Excelente suporte e integrações impecáveis com nosso CRM.'],
          ['Mariana Costa', 'Diretora - Escola Z', 'Site leve, moderno e fácil de atualizar. Recomendo!'],
        ]; foreach ($depos as [$name,$role,$text]): ?>
        <figure class="rounded-2xl border border-slate-200 bg-white p-6 elevate">
          <div class="flex items-center gap-2 text-amber-500" aria-label="5 estrelas">
            <?php for ($s=0; $s<5; $s++): ?>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M12 2.25 14.93 8l6.57.96-4.75 4.63 1.12 6.54L12 16.98 6.13 20.13l1.12-6.54L2.5 8.96 9.07 8 12 2.25z"/></svg>
            <?php endfor; ?>
          </div>
          <blockquote class="mt-3 text-slate-700">“<?= e($text) ?>”</blockquote>
          <figcaption class="mt-4 text-sm text-slate-600">— <?= e($name) ?>, <?= e($role) ?></figcaption>
        </figure>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Planos & Preços -->
  <section id="planos" class="py-16" aria-labelledby="planos-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <h2 id="planos-title" class="font-heading text-2xl sm:text-3xl font-semibold">Planos & Preços</h2>
      <div class="mt-8 grid lg:grid-cols-3 gap-6">
        <?php $plans = [
          ['Site', ['Até X páginas', 'Blog e SEO básico', 'Integrações e formulários', 'Suporte'], 'site'],
          ['Landing Page', ['Oferta + prova social', 'Formulário/WhatsApp', 'Pixel/Analytics', 'Timer opcional'], 'landing'],
          ['Sistema Sob Medida', ['Mapeamento de processos', 'Integrações via APIs', 'Escopo por demanda', 'Entrega incremental'], 'sistema'],
        ]; foreach ($plans as [$title,$features,$value]): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 elevate">
          <h3 class="font-semibold text-lg"><?= e($title) ?></h3>
          <ul class="mt-4 text-sm text-slate-700 space-y-2 list-disc pl-5">
            <?php foreach ($features as $f): ?><li><?= e($f) ?></li><?php endforeach; ?>
          </ul>
          <a href="#contato" data-plan="<?= e($value) ?>" class="plan-cta mt-6 inline-block btn-primary rounded-xl px-4 py-2">Quero este plano</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Processo de Entrega -->
  <section class="py-16 border-t border-slate-200 bg-white" aria-labelledby="processo-title">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <h2 id="processo-title" class="font-heading text-2xl sm:text-3xl font-semibold">Processo de Entrega</h2>
      <ol class="mt-8 grid sm:grid-cols-3 lg:grid-cols-6 gap-6 text-sm">
        <?php $steps = ['Briefing', 'UI/UX', 'Desenvolvimento', 'Aprovação', 'Publicação', 'Suporte/otimização']; foreach ($steps as $i => $s): ?>
        <li class="rounded-2xl border border-slate-200 bg-white p-4 text-center">
          <div class="text-blue-700 font-semibold">0<?= $i+1 ?></div>
          <div class="mt-1 font-medium text-slate-800"><?= e($s) ?></div>
        </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq" class="py-16" aria-labelledby="faq-title">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
      <h2 id="faq-title" class="font-heading text-2xl sm:text-3xl font-semibold">FAQ</h2>
      <div class="mt-6 space-y-3">
        <?php $faqs = [
          ['Qual o prazo de entrega?', 'Depende do escopo. Landing pages: 1–2 semanas. Sites: 2–4 semanas. Sistemas: sob estimativa.'],
          ['Quantas revisões estão incluídas?', 'Trabalhamos com ciclos de validação a cada etapa e revisões dentro do escopo acordado.'],
          ['Eu serei dono do código?', 'Para projetos sob medida, a titularidade pode ser definida em contrato. Para sites e LPs, entregamos os arquivos finais.'],
          ['Vocês oferecem hospedagem?', 'Podemos indicar provedores confiáveis e configurar o ambiente. Também podemos publicar no seu provedor.'],
          ['Como funciona o pagamento?', 'Entrada na aprovação e saldo na entrega (ou parcelado conforme contrato). Emitimos nota fiscal.'],
          ['Vocês fazem SEO?', 'Aplicamos boas práticas de SEO técnico e estrutura semântica. Estratégias de conteúdo são opcionais.'],
          ['Integram com WhatsApp, Pixel e CRM?', 'Sim. Implementamos eventos (ViewContent, Lead, Contact), Pixel/Analytics e integrações com CRMs.'],
        ]; foreach ($faqs as [$q,$a]): ?>
        <details class="rounded-xl border border-slate-200 bg-white p-4">
          <summary class="font-medium cursor-pointer select-none"><?= e($q) ?></summary>
          <div class="mt-2 text-slate-700 text-sm"><?= e($a) ?></div>
        </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Call Final + Contato -->
  <section id="contato" class="py-16 border-t border-slate-200 bg-white" aria-labelledby="contato-title">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
      <div class="grid lg:grid-cols-2 gap-10 items-start">
        <div>
          <h2 id="contato-title" class="font-heading text-2xl sm:text-3xl font-semibold">Pronto para acelerar seu projeto?</h2>
          <p class="mt-2 text-slate-700">Resposta em até 24h úteis. Fale com nosso time e receba um orçamento sob medida.</p>
          <div class="mt-5 flex gap-3">
            <a href="#orcamento" class="btn-primary rounded-xl px-5 py-3">Solicitar Orçamento</a>
            <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="btn-outline rounded-xl px-5 py-3" data-ev="Contact" data-ev-label="Contato WhatsApp">Falar no WhatsApp</a>
          </div>
          <div class="mt-6 text-sm text-slate-600">
            <p>Atendemos diversos nichos: e-commerce, educação, saúde, serviços B2B e mais.</p>
          </div>
        </div>
        <div id="orcamento" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <form id="quoteForm" class="grid gap-4" novalidate>
            <div>
              <label for="nome" class="block text-sm font-medium">Nome</label>
              <input id="nome" name="nome" type="text" required class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600" placeholder="Seu nome completo" />
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label for="email" class="block text-sm font-medium">E-mail</label>
                <input id="email" name="email" type="email" required class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600" placeholder="voce@empresa.com" />
              </div>
              <div>
                <label for="whatsapp" class="block text-sm font-medium">WhatsApp</label>
                <input id="whatsapp" name="whatsapp" type="tel" required class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600" placeholder="(DDD) 90000-0000" />
              </div>
            </div>
            <div>
              <label for="tipo" class="block text-sm font-medium">Tipo de projeto</label>
              <select id="tipo" name="tipo" class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600">
                <option value="Site">Site institucional</option>
                <option value="Landing Page">Landing Page</option>
                <option value="Sistema">Sistema sob medida</option>
              </select>
            </div>
            <div>
              <label for="mensagem" class="block text-sm font-medium">Mensagem</label>
              <textarea id="mensagem" name="mensagem" rows="4" class="mt-1 w-full rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600" placeholder="Conte um pouco sobre seu projeto"></textarea>
            </div>
            <div class="flex items-start gap-2 text-sm">
              <input id="consent" name="consent" type="checkbox" required class="mt-1" />
              <label for="consent">Autorizo o contato e concordo com a <a href="#" class="text-blue-700 underline hover:no-underline">Política de Privacidade</a>.</label>
            </div>
            <div class="flex gap-3">
              <button type="submit" class="btn-primary rounded-xl px-5 py-3" data-ev="Submit" data-ev-label="Form Orçamento">Enviar</button>
              <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="btn-outline rounded-xl px-5 py-3" data-ev="Contact" data-ev-label="Form WhatsApp">Falar no WhatsApp</a>
            </div>
            <p id="formFeedback" class="hidden text-sm"></p>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Rodapé -->
  <footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 grid md:grid-cols-3 gap-6 text-sm">
      <div>
        <div class="font-heading font-semibold text-lg"><?= e($brand) ?></div>
        <div class="text-slate-600">&copy; <?= date('Y') ?>. Todos os direitos reservados.</div>
      </div>
      <div>
        <div class="font-medium mb-2">Links</div>
        <nav class="grid gap-1">
          <a href="#inicio" class="text-slate-600 hover:text-slate-900">Início</a>
          <a href="#servicos" class="text-slate-600 hover:text-slate-900">Serviços</a>
          <a href="#portfolio" class="text-slate-600 hover:text-slate-900">Portfólio</a>
          <a href="#depoimentos" class="text-slate-600 hover:text-slate-900">Depoimentos</a>
          <a href="#planos" class="text-slate-600 hover:text-slate-900">Planos</a>
          <a href="#faq" class="text-slate-600 hover:text-slate-900">FAQ</a>
          <a href="#contato" class="text-slate-600 hover:text-slate-900">Contato</a>
        </nav>
      </div>
      <div>
        <div class="font-medium mb-2">Políticas & Contato</div>
        <div class="grid gap-1">
          <a href="#" class="text-slate-600 hover:text-slate-900">Política de Privacidade</a>
          <a href="#" class="text-slate-600 hover:text-slate-900">Termos de Uso</a>
          <a href="mailto:contato@example.com" class="text-slate-600 hover:text-slate-900">contato@example.com</a>
          <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" class="text-slate-600 hover:text-slate-900">WhatsApp</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Botão flutuante WhatsApp -->
  <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" aria-label="Abrir WhatsApp" class="fixed bottom-5 right-5 inline-flex items-center justify-center h-12 w-12 rounded-full shadow-lg bg-green-500 text-white elevate" data-ev="Contact" data-ev-label="Float WhatsApp">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M20.52 3.48A11.94 11.94 0 0 0 12.01 0C5.39 0 .02 5.37.02 11.98c0 2.11.55 4.18 1.6 6.01L0 24l6.17-1.61a12 12 0 0 0 5.84 1.49h.01c6.62 0 12-5.37 12-11.99a11.94 11.94 0 0 0-3.5-8.41ZM12.01 22.05h-.01a9.99 9.99 0 0 1-5.09-1.39l-.36-.21-3.66.96.98-3.56-.24-.37a10 10 0 1 1 8.38 4.57Zm5.49-7.5c-.3-.15-1.77-.87-2.05-.97-.28-.1-.48-.15-.68.15-.2.3-.78.97-.96 1.17-.18.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.8-1.49-1.78-1.67-2.08-.18-.3-.02-.46.13-.61.14-.14.3-.35.45-.52.15-.17.2-.29.3-.49.1-.2.05-.37-.02-.52-.07-.15-.68-1.64-.93-2.25-.24-.57-.49-.5-.68-.5-.18 0-.37 0-.57 0-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.22 3.07.15.2 2.1 3.2 5.09 4.49.71.31 1.27.49 1.7.63.71.23 1.35.2 1.86.12.57-.08 1.77-.72 2.02-1.41.25-.69.25-1.28.17-1.41-.07-.13-.27-.2-.57-.35Z"/></svg>
  </a>

  <!-- Analytics stubs + interactions -->
  <script>
    window.dataLayer = window.dataLayer || [];
    function trackEvent(name, params = {}) {
      try { window.dataLayer.push({ event: name, ...params }); } catch (e) {}
      if (window.fbq) { try { window.fbq('track', name, params); } catch (e) {} }
      if (window.gtag) { try { window.gtag('event', name, params); } catch (e) {} }
    }
    document.addEventListener('DOMContentLoaded', function () {
      // header shadow on scroll
      var header = document.getElementById('header');
      function onScroll() {
        if (window.scrollY > 4) { header.classList.add('shadow'); } else { header.classList.remove('shadow'); }
      }
      onScroll();
      window.addEventListener('scroll', onScroll, { passive: true });

      // mobile nav toggle
      var menuBtn = document.getElementById('menuBtn');
      var mobileNav = document.getElementById('mobileNav');
      if (menuBtn) menuBtn.addEventListener('click', function(){ mobileNav.classList.toggle('hidden'); });

      // plan CTA preselect
      document.querySelectorAll('.plan-cta').forEach(function(btn){
        btn.addEventListener('click', function(){
          var val = btn.getAttribute('data-plan') || '';
          var sel = document.getElementById('tipo');
          if (sel) {
            var map = { site: 'Site', landing: 'Landing Page', sistema: 'Sistema' };
            sel.value = map[val] || sel.value;
          }
        });
      });

      // event hooks
      document.querySelectorAll('[data-ev]').forEach(function(el){
        el.addEventListener('click', function(){ trackEvent(el.getAttribute('data-ev'), { label: el.getAttribute('data-ev-label') }); });
      });

      // page view
      trackEvent('ViewContent', { page: 'agency_onepage' });

      // form submit -> WhatsApp redirect
      var form = document.getElementById('quoteForm');
      var feedback = document.getElementById('formFeedback');
      form && form.addEventListener('submit', function(e){
        e.preventDefault();
        var data = new FormData(form);
        if (!form.reportValidity()) return;
        if (!document.getElementById('consent').checked) { alert('Por favor, concorde com a Política de Privacidade.'); return; }
        var nome = data.get('nome');
        var email = data.get('email');
        var whatsapp = (data.get('whatsapp')||'').toString().replace(/\D+/g, '');
        var tipo = data.get('tipo');
        var msg = (data.get('mensagem')||'').toString();
        var composed = 'Olá! Gostaria de um orçamento.%0A' +
          '*Nome:* ' + encodeURIComponent(nome) + '%0A' +
          '*E-mail:* ' + encodeURIComponent(email) + '%0A' +
          '*WhatsApp:* +' + encodeURIComponent(whatsapp) + '%0A' +
          '*Projeto:* ' + encodeURIComponent(tipo) + '%0A' +
          '*Mensagem:* ' + encodeURIComponent(msg);
        var url = 'https://wa.me/<?= e($waNumber) ?>?text=' + composed;
        trackEvent('Lead', { form: 'budget' });
        window.open(url, '_blank');
        if (feedback) {
          feedback.classList.remove('hidden');
          feedback.textContent = 'Pronto! Abrimos o WhatsApp com sua mensagem. Se preferir, aguardamos seu contato por e-mail.';
          feedback.classList.add('text-green-700');
        }
        form.reset();
      });
    });
  </script>

  <!-- JSON-LD: FAQPage -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
      {"@type": "Question", "name": "Qual o prazo de entrega?", "acceptedAnswer": {"@type": "Answer", "text": "Landing pages: 1–2 semanas. Sites: 2–4 semanas. Sistemas: sob estimativa."}},
      {"@type": "Question", "name": "Quantas revisões estão incluídas?", "acceptedAnswer": {"@type": "Answer", "text": "Revisões por etapa dentro do escopo aprovado."}},
      {"@type": "Question", "name": "Eu serei dono do código?", "acceptedAnswer": {"@type": "Answer", "text": "Para projetos sob medida, titularidade definida em contrato; sites/LPs com arquivos finais entregues."}},
      {"@type": "Question", "name": "Vocês oferecem hospedagem?", "acceptedAnswer": {"@type": "Answer", "text": "Indicamos provedores e configuramos publicação no ambiente do cliente."}},
      {"@type": "Question", "name": "Como funciona o pagamento?", "acceptedAnswer": {"@type": "Answer", "text": "Entrada na aprovação e saldo na entrega, com possibilidade de parcelamento contratual."}}
    ]
  }
  </script>

</body>
</html>
