<?php

namespace Modules\PJSIP;

class Manager
{
    protected string $configPath = '/etc/asterisk/pjsip.conf';

    public function reload(): void
    {
        echo "🔄 Reloading PJSIP module...\n";
        exec('asterisk -rx "module reload res_pjsip.so"', $output, $returnVar);

        if ($returnVar === 0) {
            echo "✅ PJSIP module reloaded successfully.\n";
        } else {
            echo "❌ Failed to reload PJSIP module.\n";
            echo implode("\n", $output) . "\n";
        }
    }

    public function getConfig(): ?string
    {
        if (!file_exists($this->configPath)) {
            echo "⚠️  PJSIP config file not found at {$this->configPath}\n";
            return null;
        }

        return file_get_contents($this->configPath);
    }

    public function saveConfig(string $content): bool
    {
        return file_put_contents($this->configPath, $content) !== false;
    }
}
