<?php
// 1. Cek session agar tidak bentrok
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 2. Perbaiki jalur pemanggilan database
$pdo = \App\Core\Database::getInstance()->getConnection();



// --- DATA USER ---
$uid = $_SESSION['roll_user_id'] ?? 0;
$role = $_SESSION['role'] ?? 'guest';
$displayName = $_SESSION['nama_lengkap'] ?? 'User';
$displayRole = strtoupper($role);

// Default Avatar
$displayImage = "https://ui-avatars.com/api/?name=" . urlencode($displayName) . "&background=0D8ABC&color=fff&size=128";

// Logic Foto Profil (Menggunakan getenv('APP_URL'))
if ($uid > 0) {
    $stmtU = $pdo->prepare("SELECT photo, nama_lengkap FROM roll_users WHERE id = ?");
    $stmtU->execute([$uid]);
    $userData = $stmtU->fetch();

    if ($userData) {
        if (!isset($_SESSION['nama_lengkap']) || $_SESSION['nama_lengkap'] == 'User') {
            $displayName = $userData['nama_lengkap'] ?? 'User';
            $_SESSION['nama_lengkap'] = $displayName;
        }
        if (!empty($userData['photo'])) {
            $displayImage = rtrim(getenv('APP_URL'), '/') . '/uploads/profiles/' . ltrim(str_replace(['public/img/users/', 'img/users/', 'uploads/profiles/'], '', $userData['photo']), '/');
        }
    }
    
    if ($role == 'user') {
        $stmtC = $pdo->prepare("SELECT c.club_name FROM roll_clubs c JOIN roll_users u ON u.club_id = c.id WHERE u.id = ?");
        $stmtC->execute([$uid]);
        $clubData = $stmtC->fetch();
        if ($clubData) {
            $displayName = $clubData['club_name'];
            $displayRole = "CLUB ADMIN";
        }
    }
}

// Cek Announcement
$globalSettings = $pdo->query("SELECT show_announcement, announcement_text FROM roll_site_settings WHERE id = 1")->fetch();
$showBanner = ($globalSettings && $globalSettings['show_announcement'] == 1 && !empty($globalSettings['announcement_text']));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SET System Dashboard</title>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased overflow-x-hidden selection:bg-orange-500 selection:text-white">

<?php if ($showBanner): ?>
<div class="bg-gradient-to-r from-red-600 to-orange-500 text-white px-4 py-2 text-center text-xs font-black tracking-widest uppercase shadow-md flex items-center justify-center gap-3 animate-pulse">
    <span>⚠️</span>
    <span><?= htmlspecialchars($globalSettings['announcement_text']) ?></span>
    <span>⚠️</span>
</div>
<?php endif; ?>

<!-- Flash Message Global -->
<?php include_once __DIR__ . '/../notifikasi.php'; ?>

<nav class="fixed top-0 left-0 right-0 z-40 bg-white border-b border-gray-200 h-16 shadow-sm sm:ml-64 transition-all">
  <div class="px-3 py-3 lg:px-5 lg:pl-3 h-full">
    <div class="flex items-center justify-between h-full">
      
      <div class="flex items-center justify-start gap-3">
        <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
            <span class="sr-only">Open sidebar</span>
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path></svg>
         </button>
         
         <span class="self-center text-lg font-extrabold whitespace-nowrap text-slate-800 tracking-tight sm:hidden">
            SET System
         </span>
         </div>

      <div class="flex items-center gap-3">
          
          <div class="text-right hidden md:block leading-tight">
              <p class="text-sm font-bold text-slate-800 truncate max-w-[150px]"><?= htmlspecialchars($displayName) ?></p>
              <p class="text-[10px] text-blue-600 font-bold uppercase tracking-widest bg-blue-50 px-2 py-0.5 rounded inline-block mt-0.5"><?= $displayRole ?></p>
          </div>

          <div class="relative">
              <button type="button" class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 border-2 border-slate-100 transition transform hover:scale-105" aria-expanded="false" data-dropdown-toggle="dropdown-user">
                <span class="sr-only">Open user menu</span>
                <img class="w-10 h-10 rounded-full object-cover bg-white" src="<?= $displayImage ?>" alt="user photo">
              </button>
            
              <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-xl shadow-xl w-56 border border-slate-100" id="dropdown-user">
                <div class="px-4 py-3 bg-slate-50 rounded-t-xl">
                  <p class="text-sm text-gray-900 font-bold truncate"><?= htmlspecialchars($displayName) ?></p>
                  <p class="text-xs font-medium text-gray-500 truncate"><?= $_SESSION['email'] ?? '' ?></p>
                </div>
                <ul class="py-2" role="none">
                  <li><a href="<?= getenv('APP_URL') ?>/roll/<?= strtolower($role) ?>/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium flex items-center gap-2"><span>👤</span> Profil & Keamanan</a></li>
                  <div class="border-t border-gray-100 my-1"></div>
                  <li><a href="<?= getenv('APP_URL') ?>/roll/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-bold flex items-center gap-2"><span>🚪</span> Logout</a></li>
                </ul>
              </div>
          </div>

      </div>
    </div>
  </div>
</nav>