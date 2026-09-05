<!DOCTYPE html>
<html lang="en" class="h-full bg-[#040711] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &bull; Astereal Telephony Hub</title>
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
                        'neon-mint': '0 0 25px -5px rgba(0, 245, 160, 0.35)',
                        'neon-cyan': '0 0 25px -5px rgba(0, 217, 245, 0.35)',
                        'cosmo-card': '0 10px 40px -10px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(0, 245, 160, 0.15)',
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, pre, .font-mono { font-family: 'JetBrains Mono', monospace; }
        .cosmic-radial {
            background: radial-gradient(circle at 50% 20%, rgba(0, 245, 160, 0.12) 0%, rgba(0, 217, 245, 0.05) 35%, transparent 70%);
        }
        .neon-glow-logo {
            filter: drop-shadow(0 0 16px rgba(0, 245, 160, 0.45)) drop-shadow(0 0 35px rgba(0, 217, 245, 0.25));
        }
    </style>
</head>
<body class="min-h-full flex items-center justify-center p-4 bg-[#040711] cosmic-radial relative overflow-hidden antialiased">
    <!-- Ambient Cosmic Stars & Nebula Glows -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-emerald-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-[400px] h-[400px] bg-cyan-500/10 rounded-full blur-[100px] pointer-events-none"></div>

    <!-- Login Card -->
    <div class="w-full max-w-md relative z-10">
        <!-- Floating Brand Card -->
        <div class="bg-[#080d1a]/80 backdrop-blur-xl border border-[#00f5a0]/20 rounded-3xl p-8 sm:p-10 shadow-cosmo-card relative">
            <!-- Decorative Top Edge Light -->
            <div class="absolute inset-x-12 top-0 h-px bg-gradient-to-r from-transparent via-[#00f5a0] to-transparent opacity-70"></div>

            <!-- Logo & Brand Header -->
            <div class="flex flex-col items-center text-center mb-8">
                <div class="relative mb-4">
                    <img src="/assets/images/logo.png" alt="Astereal Logo" class="w-24 h-24 object-contain neon-glow-logo transform hover:scale-105 transition duration-300">
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-1.5">
                    <span>ASTEREAL</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-[#00f5a0]/15 text-[#00f5a0] border border-[#00f5a0]/30 font-mono tracking-normal font-semibold">CORE</span>
                </h1>
                <p class="text-xs text-slate-400 mt-1.5 font-medium">Secured Asterisk Telephony Control Hub</p>
            </div>

            <!-- Error Notification Alert -->
            <?php if (!empty($error)): ?>
                <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-3">
                    <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="/login" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required autofocus placeholder="Enter username" class="w-full bg-[#050811]/90 border border-slate-800 rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-[#00f5a0] focus:ring-1 focus:ring-[#00f5a0] transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input type="password" name="password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" class="w-full bg-[#050811]/90 border border-slate-800 rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-[#00f5a0] focus:ring-1 focus:ring-[#00f5a0] transition">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-[#00f5a0] to-[#00d9f5] hover:from-[#10f49c] hover:to-[#22e0fb] text-slate-950 font-bold text-sm tracking-wide shadow-neon-mint hover:shadow-neon-cyan transition duration-200 flex items-center justify-center gap-2">
                        <span>Access Dashboard</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer Notice -->
        <p class="text-center text-xs text-slate-500 mt-6 font-mono">
            Astereal Telephony &bull; Asterisk 22 Engine
        </p>
    </div>
</body>
</html>
