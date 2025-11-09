<?php
require_once __DIR__ . '/functions.php';
start_session();
$user = current_user();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HabitRact Supply</title>
    <script>
      window.tailwind = window.tailwind || {};
      window.tailwind.config = {
        theme: {
          extend: {
            colors: {
              brand: {
                50: '#f0fdf4',
                100: '#dcfce7',
                200: '#bbf7d0',
                300: '#86efac',
                400: '#4ade80',
                500: '#22c55e',
                600: '#16a34a',
                700: '#15803d',
                800: '#166534',
                900: '#14532d'
              }
            }
          }
        }
      };
    </script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link href="<?php echo htmlspecialchars(base_url('styles.css')); ?>" rel="stylesheet">
  </head>
  <body class="bg-slate-100 text-slate-900">
    <div class="min-h-screen flex flex-col">
