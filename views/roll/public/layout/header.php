<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Pertandingan Sepatu Roda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🛼</span>
                    <a href="<?= getenv('APP_URL') ?>/roll" class="font-black text-xl tracking-tight text-slate-800 uppercase">
                        Skate<span class="text-blue-600">Sync</span>
                    </a>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="<?= getenv('APP_URL') ?>/roll" class="text-slate-600 hover:text-blue-600 font-medium transition-colors">Beranda</a>
                    <a href="<?= getenv('APP_URL') ?>/roll/live" class="text-slate-600 hover:text-blue-600 font-medium transition-colors">Live Result</a>
                </div>
                <div>
                    <a href="<?= getenv('APP_URL') ?>/roll/login" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-md hover:shadow-lg transition-all text-sm">
                        Login Klub
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content wrapper -->
    <main class="flex-grow">
