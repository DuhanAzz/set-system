<!-- NAVBAR -->
<nav id="navbar" class="fixed w-full z-50 top-0 start-0 px-6 md:px-10 print-hidden">
    <div class="max-w-screen-2xl flex items-center justify-between mx-auto w-full">
        <a href="<?= getenv('APP_URL') ?>/"><img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-12 md:h-14 w-auto object-contain transition-all duration-300" id="nav-logo" alt="SET System"></a>
        
        <div class="flex items-center gap-12">
            <div class="hidden lg:flex items-center space-x-10">
                <a href="<?= getenv('APP_URL') ?>/swim" class="nav-link">Sistem Renang</a>
                <a href="<?= getenv('APP_URL') ?>/roll" class="nav-link">Sistem Sepatu Roda</a>
                <a href="<?= getenv('APP_URL') ?>/#events" class="nav-link">Jadwal Event</a>
            </div>
            <div class="hidden lg:flex items-center lg:border-l lg:border-white/20 lg:pl-10">
                <a href="<?= getenv('APP_URL') ?>/core/login" class="bg-[#f25822] hover:bg-orange-600 text-white px-8 py-3 rounded font-black text-xs uppercase tracking-widest shadow-xl transition transform hover:-translate-y-1">Login Admin</a>
            </div>
        </div>
    </div>
</nav>
