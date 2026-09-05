<?php
use Astereal\Web\Support\Auth;
$currentUser = Auth::user();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#040711] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Astereal Telephony Hub') ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        cosmo: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10f49c',
                            600: '#00f5a0',
                            700: '#042f24',
                            800: '#080d1a',
                            900: '#060813',
                            950: '#040711',
                            cyan: '#00d9f5',
                        }
                    },
                    boxShadow: {
                        'neon-mint': '0 0 20px -3px rgba(0, 245, 160, 0.35)',
                        'neon-cyan': '0 0 20px -3px rgba(0, 217, 245, 0.35)',
                        'cosmo-card': '0 10px 30px -5px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(0, 245, 160, 0.12)',
                    }
                }
            }
        }
    </script>
    <!-- jQuery 3.7.1 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Alpine.js 3.x -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, pre, .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
        .cosmic-radial-bg {
            background: radial-gradient(circle at 50% -10%, rgba(0, 245, 160, 0.08) 0%, rgba(0, 217, 245, 0.04) 25%, transparent 60%);
        }
        .neon-glow-logo-sm {
            filter: drop-shadow(0 0 8px rgba(0, 245, 160, 0.5)) drop-shadow(0 0 16px rgba(0, 217, 245, 0.3));
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-[#040711] cosmic-radial-bg text-slate-100 antialiased relative">
    <!-- Top Navigation Bar with Mobile Responsiveness -->
    <header x-data="{ mobileNavOpen: false }" class="border-b border-[#00f5a0]/15 bg-[#080d1a]/90 backdrop-blur-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand & Hexagon Logo -->
            <div class="flex items-center gap-3">
                <a href="/" class="flex items-center gap-2.5 group">
                    <img src="/assets/images/logo.png" alt="Astereal" class="w-8 h-8 sm:w-9 sm:h-9 object-contain neon-glow-logo-sm transform group-hover:scale-110 transition duration-300">
                    <div class="flex items-center">
                        <span class="font-extrabold text-base sm:text-lg tracking-tight text-white group-hover:text-[#00f5a0] transition">ASTEREAL</span>
                        <span class="hidden sm:inline-block text-[10px] ml-2 px-2 py-0.5 rounded-full bg-[#00f5a0]/10 text-[#00f5a0] border border-[#00f5a0]/30 font-mono font-semibold uppercase tracking-wider">v1.0 Core</span>
                    </div>
                </a>
            </div>

            <!-- Desktop Navigation Links & User Menu (Visible on md and larger) -->
            <nav class="hidden md:flex items-center gap-4 text-sm font-medium text-slate-300">
                <a href="/" class="text-[#00f5a0] font-semibold hover:text-[#34d399] transition-colors flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#00f5a0]/10 border border-[#00f5a0]/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>

                <div class="h-4 w-px bg-slate-800"></div>

                <!-- Asterisk Daemon Status Pill -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#050811] border border-slate-800">
                    <span class="inline-block w-2 h-2 rounded-full <?= ($asteriskRunning ?? false) ? 'bg-[#00f5a0] shadow-[0_0_8px_#00f5a0] animate-pulse' : 'bg-amber-400' ?>"></span>
                    <span class="text-xs font-mono text-slate-400"><?= htmlspecialchars($asteriskVersion ?? 'Asterisk') ?></span>
                </div>

                <div class="h-4 w-px bg-slate-800"></div>

                <!-- Current User Badge & Logout -->
                <?php if ($currentUser): ?>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-[#00f5a0]/5 border border-[#00f5a0]/20">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-[#00f5a0] to-[#00d9f5] flex items-center justify-center font-bold text-slate-950 text-xs shadow-sm">
                                <?= strtoupper(substr($currentUser['username'] ?? 'A', 0, 1)) ?>
                            </div>
                            <span class="text-xs font-semibold text-slate-200"><?= htmlspecialchars($currentUser['username'] ?? 'Admin') ?></span>
                        </div>

                        <a href="/logout" title="Sign Out" class="text-slate-400 hover:text-rose-400 px-2.5 py-1.5 rounded-lg hover:bg-rose-500/10 transition duration-200 flex items-center gap-1.5 text-xs font-medium border border-transparent hover:border-rose-500/20">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Sign Out</span>
                        </a>
                    </div>
                <?php endif; ?>
            </nav>

            <!-- Mobile Menu Toggle Button (Visible on mobile only) -->
            <div class="flex items-center gap-2 md:hidden">
                <!-- Mobile Asterisk Status Dot -->
                <div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-[#050811] border border-slate-800">
                    <span class="inline-block w-2 h-2 rounded-full <?= ($asteriskRunning ?? false) ? 'bg-[#00f5a0] shadow-[0_0_6px_#00f5a0]' : 'bg-amber-400' ?>"></span>
                    <span class="text-[10px] font-mono text-slate-400"><?= ($asteriskRunning ?? false) ? 'Online' : 'Standby' ?></span>
                </div>

                <!-- Hamburger Button -->
                <button @click="mobileNavOpen = !mobileNavOpen" type="button" class="p-2 rounded-lg bg-[#050811] border border-slate-800 text-slate-300 hover:text-[#00f5a0] hover:border-[#00f5a0]/40 transition focus:outline-none" aria-label="Toggle menu">
                    <svg x-show="!mobileNavOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    <svg x-cloak x-show="mobileNavOpen" class="w-5 h-5 text-[#00f5a0]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Collapsible Menu Drawer -->
        <div x-cloak x-show="mobileNavOpen" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0 -translate-y-2" 
             x-transition:enter-end="opacity-100 translate-y-0" 
             x-transition:leave="transition ease-in duration-150" 
             x-transition:leave-start="opacity-100 translate-y-0" 
             x-transition:leave-end="opacity-0 -translate-y-2" 
             class="md:hidden border-t border-[#00f5a0]/15 bg-[#080d1a]/98 backdrop-blur-xl px-4 py-4 space-y-3 shadow-2xl">
            
            <!-- User Status Header on Mobile -->
            <?php if ($currentUser): ?>
                <div class="flex items-center justify-between p-3 rounded-xl bg-[#050811] border border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#00f5a0] to-[#00d9f5] flex items-center justify-center font-bold text-slate-950 text-xs shadow-neon-mint">
                            <?= strtoupper(substr($currentUser['username'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-white"><?= htmlspecialchars($currentUser['username'] ?? 'Admin') ?></div>
                            <div class="text-[10px] text-slate-400 font-mono">Role: <?= htmlspecialchars($currentUser['role'] ?? 'Administrator') ?></div>
                        </div>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-[#00f5a0]/10 text-[#00f5a0] border border-[#00f5a0]/30 font-mono">Logged In</span>
                </div>
            <?php endif; ?>

            <!-- Navigation Links -->
            <div class="space-y-1 pt-1">
                <a href="/" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl bg-[#00f5a0]/15 text-[#00f5a0] font-semibold text-sm border border-[#00f5a0]/30 shadow-[0_0_15px_rgba(0,245,160,0.1)]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>
            </div>

            <!-- Asterisk Telephony Engine Detail on Mobile -->
            <div class="p-3 rounded-xl bg-[#050811] border border-slate-800 space-y-1">
                <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Telephony Engine</div>
                <div class="text-xs font-mono text-slate-200 flex items-center justify-between">
                    <span><?= htmlspecialchars($asteriskVersion ?? 'Asterisk') ?></span>
                    <span class="inline-block w-2 h-2 rounded-full <?= ($asteriskRunning ?? false) ? 'bg-[#00f5a0] shadow-[0_0_6px_#00f5a0]' : 'bg-amber-400' ?>"></span>
                </div>
            </div>

            <!-- Sign Out Button on Mobile -->
            <?php if ($currentUser): ?>
                <div class="pt-2 border-t border-slate-800">
                    <a href="/logout" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 font-semibold text-xs border border-rose-500/30 transition">
                        <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Sign Out of Astereal</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
