<?php

namespace Modules\Dialplan;

class Manager
{
    public function reload(): void
    {
        echo "🔄 Reloading Dialplan...\n";
        exec('asterisk -rx "dialplan reload"', $output, $returnVar);

        if ($returnVar === 0) {
            echo "✅ Dialplan reloaded successfully.\n";
        } else {
            echo "❌ Failed to reload Dialplan.\n";
            echo implode("\n", $output) . "\n";
        }
    }
}
