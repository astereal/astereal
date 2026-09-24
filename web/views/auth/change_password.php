<!DOCTYPE html>
<html lang="en" class="h-full bg-[#040711] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Permanent Password &bull; <?= htmlspecialchars(getenv('APP_NAME') ?: 'Astereal') ?></title>
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

    <!-- Container -->
    <div class="w-full max-w-md relative z-10">
        <!-- Floating Security Card -->
        <div class="bg-[#080d1a]/85 backdrop-blur-xl border border-[#00f5a0]/25 rounded-3xl p-8 sm:p-10 shadow-cosmo-card relative">
            <!-- Decorative Top Edge Light -->
            <div class="absolute inset-x-12 top-0 h-px bg-gradient-to-r from-transparent via-[#00f5a0] to-transparent opacity-80"></div>

            <!-- Shield Icon & Brand Header -->
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#00f5a0]/20 to-[#00d9f5]/20 border border-[#00f5a0]/40 flex items-center justify-center mb-4 shadow-[0_0_20px_rgba(0,245,160,0.25)]">
                    <svg class="w-7 h-7 text-[#00f5a0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#00f5a0]/10 border border-[#00f5a0]/30 text-[#00f5a0] text-[11px] font-mono font-semibold uppercase tracking-wider mb-2">
                    <span>Initial Password Setup</span>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-white">Create Permanent Password</h1>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                    You signed in with a temporary reset password. Please configure your permanent administrator credentials to continue to the console.
                </p>
            </div>

            <!-- Error Notification Alert -->
            <?php if (!empty($error)): ?>
                <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2.5">
                    <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="/password/change" method="POST" class="space-y-4">
                <!-- User Context -->
                <div class="p-3 rounded-xl bg-[#050811] border border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Account:</span>
                    <span class="font-mono text-[#00f5a0] font-semibold"><?= htmlspecialchars($_SESSION['username'] ?? 'admin') ?></span>
                </div>

                <!-- New Password Field -->
                <div>
                    <label for="new_password" class="block text-xs font-semibold text-slate-300 mb-1.5">New Password</label>
                    <div class="relative">
                        <input type="password" id="new_password" name="new_password" required minlength="8" placeholder="••••••••••••"
                               class="w-full px-4 py-2.5 bg-[#050811] border border-slate-800 rounded-xl text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-[#00f5a0] focus:ring-1 focus:ring-[#00f5a0] transition duration-200 font-mono">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">Minimum 8 characters (letters, numbers, or symbols)</p>
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label for="confirm_password" class="block text-xs font-semibold text-slate-300 mb-1.5">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="••••••••••••"
                               class="w-full px-4 py-2.5 bg-[#050811] border border-slate-800 rounded-xl text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-[#00f5a0] focus:ring-1 focus:ring-[#00f5a0] transition duration-200 font-mono">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3 px-4 bg-gradient-to-r from-[#00f5a0] to-[#00d9f5] hover:opacity-95 text-slate-950 font-bold text-sm rounded-xl shadow-neon-mint transition duration-200 transform hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Update Password & Continue</span>
                    </button>
                </div>
            </form>

            <!-- Sign Out Footer -->
            <div class="mt-6 pt-4 border-t border-slate-800/80 text-center">
                <a href="/logout" class="text-xs text-slate-500 hover:text-rose-400 transition inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Sign Out & Return to Login</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
