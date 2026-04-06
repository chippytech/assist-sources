<?php
// index.php

// --------------------------------------------------------------------------
// 1. CONFIGURATION & SEO DATA
// --------------------------------------------------------------------------

// Base URL (Change this to your actual domain)
$baseUrl = "https://" . $_SERVER['HTTP_HOST'];

// -- DEFAULT SETTINGS (Used if specific page data is missing) --
$defaultBrand = "ChippyTime";
$defaultTitle = "ChippyTime • Free Web Services & Tools";
$defaultDesc  = "ChippyTime constitutes a software development and engineering company building practical, accessible software with a strong commitment to open-source principles.";
$defaultImg   = "/epixbackground.png"; 

// Define pages with specific custom Title AND Meta Description
// Descriptions updated based on your provided content
$pages = [
  'home' => [
    'title' => 'ChippyTime • Home',
    'desc'  => 'ChippyTime constitutes a software development and engineering company building practical, accessible software with a strong commitment to open-source principles.'
  ],
  'about' => [
    'title' => 'ChippyTime • About Us',
    'desc'  => 'The ChippyTime Group is an independent US tech organization preserving a free, open web. We build powerful AI platforms and support open standards.'
  ],
  'contact' => [
    'title' => 'ChippyTime • Contact',
    'desc'  => 'Get in touch with the ChippyTime team for collaboration or support. Email contact@chippytime.com or join our Discord server.'
  ],
  'projects' => [
    'title' => 'ChippyTime • Projects',
    'desc'  => 'Explore our latest open source projects, coding experiments, and client work.'
  ],
  'gallery' => [
    'title' => 'ChippyTime • Gallery',
    'desc'  => 'View our gallery of web designs, graphics, and community submissions.'
  ],
  'faq' => [
    'title' => 'ChippyTime • FAQ',
    'desc'  => 'Frequently asked questions about our hosting, tools, and services.'
  ],
  'tools' => [
    'title' => 'ChippyTime • Tools',
    'desc'  => 'Free online tools for developers, including formatters, calculators, and generators.'
  ],
  'freehosting' => [
    'title' => 'ChippyTime • Free Web Hosting',
    'desc'  => 'Start your website today with our reliable free web hosting packages. No ads, high uptime.'
  ],
  'webdesign' => [
    'title' => 'ChippyTime • Free Web Design Services',
    'desc'  => 'Need a website? We offer free and discounted web design services for non-profits and starters.'
  ],
  'more' => [
    'title' => 'ChippyTime • More Services',
    'desc'  => 'Discover additional services offered by the ChippyTime network.'
  ],
  'webservices' => [
    'title' => 'ChippyTime • Website Building Service Discounts',
    'desc'  => 'Exclusive discounts on premium website building services and tools.'
  ]
];

// --------------------------------------------------------------------------
// 2. ROUTING LOGIC
// --------------------------------------------------------------------------

// Get requested page securely
$page = $_GET['page'] ?? 'home';
$page = preg_replace('/[^a-zA-Z0-9-_]/', '', $page); // Security sanitization

// Path to the include file
$pageFile = "includes/{$page}.php";
$fileExists = file_exists($pageFile);

// -- LOGIC START --

if (!$fileExists) {
    // SCENARIO 1: File actually doesn't exist -> Show 404
    http_response_code(404);
    $metaTitle = "404 Not Found • " . $defaultBrand;
    $metaDesc  = "The page you are looking for does not exist.";
    $robots    = "noindex, follow"; // Don't index errors
    $canonical = $baseUrl . $_SERVER['REQUEST_URI'];
    $pageFile  = "includes/404.php"; // Force load 404 body
} 
elseif (array_key_exists($page, $pages)) {
    // SCENARIO 2: Page exists AND has custom data in $pages array -> Use Custom
    $metaTitle = $pages[$page]['title'];
    $metaDesc  = $pages[$page]['desc'];
    $robots    = "index, follow";
    // Using ?page= format ensures links work without .htaccess
    $canonical = $baseUrl . "/?page=" . $page; 
} 
else {
    // SCENARIO 3: File exists, but NO custom data -> Use Defaults (Standard Settings)
    // We clean up the page name for the title: 'my_new_page' becomes 'My New Page • ChippyTime'
    $cleanName = ucwords(str_replace(['-', '_'], ' ', $page));
    
    $metaTitle = $cleanName . " • " . $defaultBrand;
    $metaDesc  = $defaultDesc; // Use the global default description
    $robots    = "index, follow";
    $canonical = $baseUrl . "/?page=" . $page;
}

// -- LOGIC END --
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- KEY SEO TAGS -->
  <title><?php echo htmlspecialchars($metaTitle); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($metaDesc); ?>">
  <meta name="robots" content="<?php echo $robots; ?>">
  <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">

  <!-- OPEN GRAPH / FACEBOOK / LINKEDIN -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?php echo htmlspecialchars($canonical); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($metaTitle); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($metaDesc); ?>">
  <meta property="og:image" content="<?php echo $baseUrl . $defaultImg; ?>">

  <!-- TWITTER CARDS -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($metaTitle); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDesc); ?>">
  <meta name="twitter:image" content="<?php echo $baseUrl . $defaultImg; ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/images/Untitled design (2).png?v=<?php echo file_exists('images/Untitled design (2).png') ? filemtime('images/Untitled design (2).png') : time(); ?>" />

  <!-- Fonts & Styles -->
  <link href="https://fonts.googleapis.com/css2?family=Google+Sans+Flex:wght@400;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="/style.css?v=<?php echo file_exists('style.css') ? filemtime('style.css') : time(); ?>">
  <style>
    /* 
       The 'sr-only' class pattern is the standard way to hide elements visually 
       while keeping them accessible to screen readers and keyboard users.
    */
    .skip-link {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
        z-index: 10000;
        background: #000;
        color: white;
    }

    /* 
       When the user Tabs to the link, we undo the hiding properties 
       so it becomes visible immediately.
    */
    .skip-link:focus {
        width: auto;
        height: auto;
        padding: 10px;
        margin: 0;
        clip: auto;
        top: 0;
        left: 0;
        text-decoration: none;
        outline: 3px solid #ffcc00; /* High contrast outline for visibility */
    }
  </style>
  <!-- Structured Data (JSON-LD) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "ChippyTime",
    "url": "<?php echo $baseUrl; ?>",
    "potentialAction": {
      "@type": "SearchAction",
      "target": "<?php echo $baseUrl; ?>/?search={search_term_string}",
      "query-input": "required name=search_term_string"
    }
  }
  </script>
</head>
<body>

  <!-- ACCESSIBILITY: Skip Link -->
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <?php include 'nav.php'; ?>

  <div class="content-wrapper container-fluid p-0">
    <main id="main-content" role="main">
      <?php 
      // The logic block at the top chose the correct $pageFile (either the requested page or the 404 page)
      include $pageFile; 
      ?>
    </main>
  </div>
    

  <!-- BOOTSTRAP JS Bundle (Required for Navbar/Modals) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>