<?php

namespace Bootstrap\Commands;

use Modules\PJSIP\Manager;

class PjsipCommand
{
    public string $name = 'pjsip';
    public string $description = 'Manage the PJSIP module (reload, show, etc.)';

    public function handle(array $args): void
    {
        $action = $args[0] ?? 'help';
        $manager = new Manager();

        switch ($action) {
            case 'reload':
                $manager->reload();
                break;

            case 'show':
                $config = $manager->getConfig();
                if ($config) {
                    echo "📄 Current PJSIP Configuration:\n\n";
                    echo $config . "\n";
                }
                break;

            default:
                echo "Usage:\n";
                echo "  php aster pjsip:reload   Reload the PJSIP module\n";
        }
    }
}
