<?php
// includes/header.php
if (!isset($page_title)) $page_title = "SakuOrg - Sistem Kas Organisasi";
if (!isset($user_role)) {
    $user_role = isset($_GET['preview_role']) ? $_GET['preview_role'] : 'admin';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?></title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Plus Jakarta Sans', 'sans-serif'],
          },
          colors: {
            brand: {
              50: '#F0F4FA',
              100: '#DBE5F3',
              200: '#B9CCE5',
              500: '#345A9C',
              600: '#234578',
              700: '#172554',
              800: '#111D42',
              900: '#0B1530',
            }
          }
        }
      }
    }
  </script>
</head>
<body class="bg-[#F8FAFC] text-slate-800 font-sans antialiased min-h-screen flex flex-col md:flex-row">