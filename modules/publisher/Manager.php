<?php

namespace Modules\Publisher;

class Manager
{
    public function reload(): void
    {
        echo "🔄 Reloading PJSIP module...\n";
        exec('asterisk -rx "pjsip reload"', $output, $returnVar);

        if ($returnVar === 0) {
            echo "✅ PJSIP module reloaded successfully.\n";
        } else {
            echo "❌ Failed to reload PJSIP module.\n";
            echo implode("\n", $output) . "\n";
        }
    }
}
