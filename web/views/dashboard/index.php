<?php
$title = "Astereal Core - Telephony & API Dashboard";
require dirname(__DIR__) . '/layouts/header.php';
?>

<div x-data="dashboardApp()" class="space-y-8">
    <!-- Top Telemetry & Health Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Asterisk Engine Card -->
        <div class="p-6 rounded-2xl bg-[#080d1a]/70 backdrop-blur-md border border-[#00f5a0]/20 shadow-cosmo-card flex items-start justify-between relative overflow-hidden group hover:border-[#00f5a0]/40 transition duration-300">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-[#00f5a0]/5 rounded-full blur-2xl group-hover:bg-[#00f5a0]/10 transition"></div>
            <div class="space-y-1.5 relative z-10">
                <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400">Telephony Engine</span>
                <div class="text-2xl font-extrabold text-white flex items-center gap-2">
                    <span>Asterisk 22</span>
                    <?php if ($asteriskRunning): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-[#00f5a0]/15 text-[#00f5a0] border border-[#00f5a0]/30 shadow-[0_0_10px_rgba(0,245,160,0.2)]">Active</span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/30">Standby</span>
                    <?php endif; ?>
                </div>
                <p class="text-xs font-mono text-slate-400 truncate max-w-[240px]"><?= htmlspecialchars($asteriskVersion) ?></p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#050811] border border-slate-800 flex items-center justify-center text-[#00f5a0] shadow-inner">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
        </div>

        <!-- Database Card -->
        <div class="p-6 rounded-2xl bg-[#080d1a]/70 backdrop-blur-md border border-[#00f5a0]/20 shadow-cosmo-card flex items-start justify-between relative overflow-hidden group hover:border-[#00d9f5]/40 transition duration-300">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-[#00d9f5]/5 rounded-full blur-2xl group-hover:bg-[#00d9f5]/10 transition"></div>
            <div class="space-y-1.5 relative z-10">
                <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400">Database & Registry</span>
                <div class="text-2xl font-extrabold text-white flex items-center gap-2">
                    <span><?= count($callers) ?></span>
                    <span class="text-sm font-normal text-slate-400">Caller Profiles</span>
                </div>
                <p class="text-xs font-mono text-slate-400">SQLite3 / PDO Engine</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#050811] border border-slate-800 flex items-center justify-center text-[#00d9f5] shadow-inner">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
            </div>
        </div>

        <!-- Security Gateway Card -->
        <div class="p-6 rounded-2xl bg-[#080d1a]/70 backdrop-blur-md border border-[#00f5a0]/20 shadow-cosmo-card flex items-start justify-between relative overflow-hidden group hover:border-[#00f5a0]/40 transition duration-300">
            <div class="space-y-1.5 relative z-10">
                <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400">AGI Security Gateway</span>
                <div class="text-2xl font-extrabold text-[#00f5a0] flex items-center gap-2">
                    <span>HMAC-SHA256</span>
                </div>
                <p class="text-xs font-mono text-slate-400">30s Anti-Replay Shield</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#050811] border border-slate-800 flex items-center justify-center text-[#00f5a0] shadow-inner">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
        </div>
    </div>

    <!-- Architecture & Integration Pipeline Card -->
    <div class="p-6 sm:p-8 rounded-3xl bg-[#080d1a]/80 backdrop-blur-md border border-[#00f5a0]/15 shadow-cosmo-card relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#00f5a0] shadow-[0_0_8px_#00f5a0]"></span>
                    <span>AGI &rarr; Secured API &rarr; Database Flow</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">Lifecycle executed when an inbound call enters the Asterisk dialplan.</p>
            </div>
            <span class="text-xs px-3 py-1 rounded-lg bg-[#050811] text-slate-300 font-mono border border-slate-800">
                app/agi/aster_api.php
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-[#050811]/80 border border-slate-800/80 hover:border-[#00f5a0]/30 transition duration-200">
                <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#00f5a0] mb-1">Step 1: Call Enters</div>
                <div class="text-sm font-bold text-white">Asterisk Dialplan</div>
                <p class="text-xs text-slate-400 mt-1">Extracts <code class="text-[#00f5a0]">${CALLERID(num)}</code> & invokes <code class="text-[#00f5a0]">AGI(aster_api.php)</code>.</p>
            </div>
            <div class="p-4 rounded-xl bg-[#050811]/80 border border-slate-800/80 hover:border-[#00f5a0]/30 transition duration-200">
                <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#00d9f5] mb-1">Step 2: Sign Payload</div>
                <div class="text-sm font-bold text-white">HMAC-SHA256 Client</div>
                <p class="text-xs text-slate-400 mt-1">Signs timestamped payload using server secret key before dispatching.</p>
            </div>
            <div class="p-4 rounded-xl bg-[#050811]/80 border border-slate-800/80 hover:border-[#00f5a0]/30 transition duration-200">
                <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#00f5a0] mb-1">Step 3: Verification</div>
                <div class="text-sm font-bold text-white">REST Middleware</div>
                <p class="text-xs text-slate-400 mt-1">Validates signature freshness (&lt;30s) and performs native PDO query.</p>
            </div>
            <div class="p-4 rounded-xl bg-[#050811]/80 border border-slate-800/80 hover:border-[#00f5a0]/30 transition duration-200">
                <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#00d9f5] mb-1">Step 4: Smart Routing</div>
                <div class="text-sm font-bold text-white">Channel Variables</div>
                <p class="text-xs text-slate-400 mt-1">Injects <code class="text-[#00f5a0]">${CALLER_NAME}</code>, <code class="text-[#00f5a0]">${IS_VIP}</code> & <code class="text-[#00f5a0]">${ROUTE_TO}</code>.</p>
            </div>
        </div>
    </div>

    <!-- Caller Directory & Quick Registration -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-bold text-white">Caller & VIP Directory</h3>
                <p class="text-xs text-slate-400">Numbers registered here automatically route according to VIP status and priority target rules.</p>
            </div>
            <button @click="showAddModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#00f5a0] to-[#00d9f5] hover:from-[#10f49c] hover:to-[#22e0fb] text-slate-950 text-sm font-bold shadow-neon-mint transition duration-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Add Caller Profile</span>
            </button>
        </div>

        <!-- Callers Table -->
        <div class="overflow-x-auto rounded-2xl border border-[#00f5a0]/15 bg-[#080d1a]/80 shadow-cosmo-card">
            <table class="w-full min-w-[640px] text-left text-sm text-slate-300">
                <thead class="bg-[#050811]/90 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-800 tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4">ANI / Number</th>
                        <th scope="col" class="px-6 py-4">Contact Name</th>
                        <th scope="col" class="px-6 py-4">Organization</th>
                        <th scope="col" class="px-6 py-4">Status</th>
                        <th scope="col" class="px-6 py-4">Target Route</th>
                        <th scope="col" class="px-6 py-4">Registered</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono text-xs">
                    <?php if (empty($callers)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500 font-sans text-sm">
                                No callers registered yet. Click "Add Caller Profile" or let inbound calls auto-populate!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($callers as $caller): ?>
                            <tr class="hover:bg-[#050811]/60 transition-colors">
                                <td class="px-6 py-4 font-bold text-white">
                                    <?= htmlspecialchars($caller['phone_number'] ?? $caller['ani'] ?? '') ?>
                                </td>
                                <td class="px-6 py-4 font-sans text-sm font-medium text-slate-200">
                                    <?= htmlspecialchars($caller['name'] ?: 'Unknown') ?>
                                </td>
                                <td class="px-6 py-4 font-sans text-sm text-slate-400">
                                    <?= htmlspecialchars($caller['notes'] ?? $caller['company'] ?? '&mdash;') ?>
                                </td>
                                <td class="px-6 py-4 font-sans">
                                    <?php if ($caller['is_vip']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-400/15 text-amber-300 border border-amber-400/30">VIP Priority</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-[#00f5a0] font-bold">
                                    <?= htmlspecialchars($caller['route_to'] ?: '100') ?>
                                </td>
                                <td class="px-6 py-4 text-slate-500 font-mono text-[11px]">
                                    <?= htmlspecialchars($caller['created_at'] ?? '') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Add Caller Profile -->
    <div x-cloak x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md" @keydown.escape.window="showAddModal = false">
        <div class="bg-[#080d1a] border border-[#00f5a0]/25 rounded-3xl max-w-md w-full p-7 shadow-2xl space-y-5 relative" @click.outside="showAddModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <h4 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#00f5a0]"></span>
                    <span>Add Caller Profile</span>
                </h4>
                <button @click="showAddModal = false" class="text-slate-500 hover:text-slate-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="addCallerForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Phone Number (ANI)</label>
                    <input type="text" name="phone" placeholder="e.g. 100 or +123456789" required class="w-full bg-[#050811] border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-[#00f5a0] font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Contact Name</label>
                    <input type="text" name="name" placeholder="e.g. Jerome Soriano" required class="w-full bg-[#050811] border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-[#00f5a0]">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Company / Notes</label>
                    <input type="text" name="notes" placeholder="e.g. Lead Asterisk Developer" class="w-full bg-[#050811] border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-[#00f5a0]">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Target Route</label>
                        <input type="text" name="route_to" placeholder="100" value="100" class="w-full bg-[#050811] border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-[#00f5a0] font-mono">
                    </div>
                    <div class="flex items-center pt-6">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_vip" value="1" class="w-4 h-4 rounded text-[#00f5a0] bg-[#050811] border-slate-800 focus:ring-0">
                            <span class="text-sm font-semibold text-slate-300">VIP Priority</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 text-sm text-slate-400 hover:text-white transition">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-[#00f5a0] to-[#00d9f5] hover:from-[#10f49c] hover:to-[#22e0fb] text-slate-950 text-sm font-bold rounded-xl transition shadow-neon-mint">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function dashboardApp() {
    return {
        showAddModal: false
    }
}

$(document).ready(function() {
    $('#addCallerForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const data = {
            phone: form.find('input[name="phone"]').val().trim(),
            name: form.find('input[name="name"]').val().trim(),
            notes: form.find('input[name="notes"]').val().trim(),
            route_to: form.find('input[name="route_to"]').val().trim() || '100',
            is_vip: form.find('input[name="is_vip"]').is(':checked') ? 1 : 0
        };

        // Submit via AJAX
        $.ajax({
            url: '/api/v1/caller/save',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(resp) {
                alert('Caller saved successfully!');
                window.location.reload();
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    alert('Note: Direct API is protected with HMAC-SHA256 for Asterisk AGI.');
                } else {
                    alert('Error saving caller: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
                }
            }
        });
    });
});
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
