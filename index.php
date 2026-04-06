<?php
// 1. Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Determine the requested page name ($page)
if (isset($_GET['page']) && $_GET['page'] !== "index") {
    $page = $_GET['page'];
} else {
    // If no page is set, or if the page is literally "index", set the default
    $page = (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) ? 'chat' : 'homepage';
}

// 3. Security sanitization
$page = preg_replace('/[^a-zA-Z0-9-_]/', '', $page);

// 4. Setup Pathing
$defaultBrand = "Assist by ChippyTime";
$pageFile = "{$page}.php"; 

// 5. THE SELF-INCLUSION GUARD 
// If after sanitization the file is still index.php, force it to a safe default
if ($pageFile === "index.php") {
    $page = (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) ? 'chat' : 'homepage';
    $pageFile = "{$page}.php";
}

// 6. Define pages that DON'T require a login
$publicPages = ['auth', 'homepage', 'signup', 'login', 'tos', 'privacy'];


// 5. THE SECURITY CHECK
// If user is NOT logged in...
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // ...and they are trying to access a page that isn't public
    if (!in_array($page, $publicPages)) {
        // Redirect to your login/auth page
        header("Location: /auth"); 
        exit;
    }
}

// 6. VALIDATE FILE EXISTENCE
if (!file_exists($pageFile)) {
    http_response_code(404);
    // You might want to create a verified 404.php file or default to homepage
    $pageFile = (file_exists("404.php")) ? "404.php" : "homepage.php"; 
    $metaTitle = "404 - Not Found • " . $defaultBrand;
} else {
    $cleanName = ucwords(str_replace(['-', '_'], ' ', $page));
    $metaTitle = $cleanName . " • " . $defaultBrand;
}
?>
<div class="alert alert-success alert-dismissible fade show text-center mb-0" role="alert" style="position: sticky; top: 0; z-index: 1000;">
Assist will be discontinued in the coming days due to low usage. Please download your data from the settings page.
</div>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title><?php echo $metaTitle ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- CSS Dependencies -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://kit.fontawesome.com/665f85a9a8.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <link rel="stylesheet" href="/style.css?v=<?php echo file_exists('style.css') ? filemtime('style.css') : time(); ?>">

    <link rel="icon" href="/assist_logo.png" type="image/x-icon">
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0066ff">
  <!-- JS Dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- Default Statcounter code for Assist
http://assist.chippytime.com -->
<script type="text/javascript">
var sc_project=13208180; 
var sc_invisible=1; 
var sc_security="3f7972c2"; 
</script>
<script type="text/javascript"
src="https://www.statcounter.com/counter/counter.js"
async></script>
<noscript><div class="statcounter"><a title="Web Analytics"
href="https://statcounter.com/" target="_blank"><img
class="statcounter"
src="https://c.statcounter.com/13208180/0/3f7972c2/1/"
alt="Web Analytics"
referrerPolicy="no-referrer-when-downgrade"></a></div></noscript>
<!-- End of Statcounter Code -->

</head>
<body>
<body class="light"> <main>
    <?php 
    // This is the ONLY place where we include the page content
    if (file_exists($pageFile)) {
        include $pageFile; 
    } else {
        echo "<div class='container mt-5'><h1>Error: Page file missing.</h1></div>";
    }
    ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>