<?php
$title = "Administrator Profile &bull; " . (getenv('APP_NAME') ?: 'Astereal');
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="space-y-8 max-w-6xl mx-auto">
    <!-- Breadcrumb & Page Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-1.5">
                <a href="/" class="hover:text-[#00f5a0] transition">Dashboard</a>
                <span class="text-slate-600">&bull;</span>
                <span class="text-slate-200">User Profile</span>
            </nav>
            <h1 class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2.5">
                <span>Account Profile & Security</span>
            </h1>
        </div>

        <a href="/" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#080d1a] border border-slate-800 text-xs font-semibold text-slate-300 hover:text-white hover:border-slate-700 transition self-start sm:self-auto">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Back to Dashboard</span>
        </a>
    </div>

    <!-- Alert Banners -->
    <?php if (!empty($success)): ?>
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center gap-3 shadow-lg">
            <svg class="w-5 h-5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center gap-3 shadow-lg">
            <svg class="w-5 h-5 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="font-medium"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- User Profile Hero Card -->
    <div class="p-6 sm:p-8 rounded-3xl bg-[#080d1a]/80 backdrop-blur-xl border border-[#00f5a0]/20 shadow-cosmo-card relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-6 -mr-6 w-36 h-36 bg-[#00f5a0]/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 relative z-10">
            <!-- Large Avatar Circle with Gradient Initial -->
            <div class="relative group">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-gradient-to-tr from-[#00f5a0] via-[#10f49c] to-[#00d9f5] flex items-center justify-center font-extrabold text-slate-950 text-3xl sm:text-4xl shadow-neon-mint transform group-hover:scale-105 transition duration-300">
                    <?= strtoupper(substr($user['username'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-[#040711] flex items-center justify-center">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#00f5a0] shadow-[0_0_8px_#00f5a0]"></span>
                </div>
            </div>

            <!-- User Header Details -->
            <div class="space-y-2 text-center sm:text-left flex-1">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                    <h2 class="text-xl sm:text-2xl font-bold text-white"><?= htmlspecialchars($user['name'] ?? 'Admin') ?></h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold bg-[#00f5a0]/15 text-[#00f5a0] border border-[#00f5a0]/30 uppercase tracking-wider">
                        <?= htmlspecialchars($user['role'] ?? 'superadmin') ?>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        Active Account
                    </span>
                </div>

                <p class="text-xs text-slate-400 font-mono flex flex-wrap items-center justify-center sm:justify-start gap-3">
                    <span>@<?= htmlspecialchars($user['username']) ?></span>
                    <span>&bull;</span>
                    <span><?= htmlspecialchars($user['email'] ?? 'No email configured') ?></span>
                    <span>&bull;</span>
                    <span>Member since <?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></span>
                </p>

                <!-- Role description badge -->
                <p class="text-xs text-slate-300 pt-1">
                    Super Administrator account with full operational access to Asterisk telephony, dialplans, CDR, extensions, and web configuration.
                </p>
            </div>
        </div>
    </div>

    <!-- Two-Column Profile & Security Forms -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Card 1: Personal Information -->
        <div class="p-6 sm:p-7 rounded-2xl bg-[#080d1a]/80 backdrop-blur-md border border-[#00f5a0]/15 shadow-cosmo-card flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 pb-5 border-b border-slate-800/80">
                    <div class="w-9 h-9 rounded-xl bg-[#050811] border border-slate-800 flex items-center justify-center text-[#00f5a0]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">General Information</h3>
                        <p class="text-xs text-slate-400">Update your account name and email address</p>
                    </div>
                </div>

                <form id="profileForm" action="/profile" method="POST" class="mt-6 space-y-5">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">Display Name</label>
                        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                               class="w-full px-4 py-2.5 bg-[#050811] border border-slate-800 rounded-xl text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-[#00f5a0] focus:ring-1 focus:ring-[#00f5a0] transition">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                               class="w-full px-4 py-2.5 bg-[#050811] border border-slate-800 rounded-xl text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-[#00f5a0] focus:ring-1 focus:ring-[#00f5a0] transition font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Username</label>
                        <input type="text" disabled value="<?= htmlspecialchars($user['username']) ?>"
                               class="w-full px-4 py-2.5 bg-[#050811]/60 border border-slate-900 rounded-xl text-sm text-slate-500 cursor-not-allowed font-mono">
                        <p class="text-[11px] text-slate-500 mt-1">Username is permanently assigned and cannot be changed.</p>
                    </div>
                </form>
            </div>

            <div class="pt-6 mt-6 border-t border-slate-800/80">
                <button type="submit" form="profileForm"
                        class="px-5 py-2.5 bg-gradient-to-r from-[#00f5a0] to-[#00d9f5] hover:opacity-95 text-slate-950 font-bold text-xs rounded-xl shadow-neon-mint transition duration-200 cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Save Profile Changes</span>
                </button>
            </div>
        </div>

        <!-- Card 2: Security & Password Update -->
        <div class="p-6 sm:p-7 rounded-2xl bg-[#080d1a]/80 backdrop-blur-md border border-[#00f5a0]/15 shadow-cosmo-card flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 pb-5 border-b border-slate-800/80">
                    <div class="w-9 h-9 rounded-xl bg-[#050811] border border-slate-800 flex items-center justify-center text-[#00d9f5]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Security & Password</h3>
                        <p class="text-xs text-slate-400">Change your administrator access credentials</p>
                    </div>
                </div>

                <form id="passwordForm" action="/profile/password" method="POST" class="mt-6 space-y-4">
                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-slate-300 mb-1.5">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required placeholder="••••••••••••"
                               class="w-full px-4 py-2.5 bg-[#050811] border border-slate-800 rounded-xl text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-[#00d9f5] focus:ring-1 focus:ring-[#00d9f5] transition font-mono">
                    </div>

                    <div>
                        <label for="new_password" class="block text-xs font-semibold text-slate-300 mb-1.5">New Password</label>
                        <input type="password" id="new_password" name="new_password" required minlength="8" placeholder="••••••••••••"
                               class="w-full px-4 py-2.5 bg-[#050811] border border-slate-800 rounded-xl text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-[#00d9f5] focus:ring-1 focus:ring-[#00d9f5] transition font-mono">
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-xs font-semibold text-slate-300 mb-1.5">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="••••••••••••"
                               class="w-full px-4 py-2.5 bg-[#050811] border border-slate-800 rounded-xl text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-[#00d9f5] focus:ring-1 focus:ring-[#00d9f5] transition font-mono">
                    </div>
                </form>
            </div>

            <div class="pt-6 mt-6 border-t border-slate-800/80 flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-mono">BCRYPT encrypted</span>
                <button type="submit" form="passwordForm"
                        class="px-5 py-2.5 bg-gradient-to-r from-[#00d9f5] to-emerald-400 hover:opacity-95 text-slate-950 font-bold text-xs rounded-xl shadow-neon-cyan transition duration-200 cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <span>Update Password</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Assigned Roles & RBAC System Privileges -->
    <div class="p-6 sm:p-8 rounded-3xl bg-[#080d1a]/80 backdrop-blur-xl border border-slate-800 shadow-cosmo-card space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="space-y-1">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span>Role-Based Access Control (RBAC) Privileges</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-[#00f5a0]/15 text-[#00f5a0] border border-[#00f5a0]/30 uppercase">
                        <?= count($roles) ?> <?= count($roles) === 1 ? 'Role' : 'Roles' ?>
                    </span>
                </h3>
                <p class="text-xs text-slate-400">Assigned roles and active permissions inherited by this user account</p>
            </div>
        </div>

        <!-- Role Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($roles as $r): ?>
                <div class="p-4 rounded-xl bg-[#050811] border border-[#00f5a0]/20 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-white"><?= htmlspecialchars($r['name']) ?></span>
                        <span class="w-2 h-2 rounded-full bg-[#00f5a0] shadow-[0_0_6px_#00f5a0]"></span>
                    </div>
                    <div class="text-[10px] font-mono text-[#00f5a0]"><?= htmlspecialchars($r['slug']) ?></div>
                    <p class="text-[11px] text-slate-400 leading-tight"><?= htmlspecialchars($r['description'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Permissions Tags -->
        <div class="space-y-3 pt-2">
            <div class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Active Permissions (<?= count($permissions) ?>)</div>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($permissions as $p): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#050811] border border-slate-800 text-[11px] font-mono text-slate-300">
                        <svg class="w-3 h-3 text-[#00f5a0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span><?= htmlspecialchars($p['name']) ?></span>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
