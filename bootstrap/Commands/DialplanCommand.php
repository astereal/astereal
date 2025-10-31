<?php

namespace Bootstrap\Commands;

use Modules\Dialplan\Manager;

class DialplanCommand
{
    public string $name = 'dialplan';
    public string $description = 'Manage your dialplan (reload)';

    public function handle(array $args): void
    {
        $action = $args[0] ?? 'help';
        $manager = new Manager();

        switch ($action) {
            case 'reload':
                $manager->reload();
                break;

            default:
                echo "Usage:\n";
                echo "  php aster dialplan:reload   Reload the asterisk dialplan\n";
        }
    }
}
