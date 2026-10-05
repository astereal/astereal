<?php

declare(strict_types=1);

namespace Bootstrap\Commands;

class MakeCommand extends CreateCommand
{
    public string $name = 'make';
    public string $description = 'Alias for create command to scaffold components';
}
