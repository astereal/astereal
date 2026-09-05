<?php

namespace Astereal\Web\Controllers\Web;

use Astereal\Web\Models\Caller;
use Astereal\Web\Support\Request;
use Astereal\Web\Support\Response;

class DashboardController
{
    public function index(Request $request): void
    {
        $callers = Caller::all();

        // Check local Asterisk daemon status if running on Linux
        $asteriskRunning = false;
        $asteriskVersion = 'Offline';

        if (PHP_OS_FAMILY !== 'Windows') {
            exec('asterisk -rx "core show version" 2>&1', $output, $returnVar);
            if ($returnVar === 0 && !empty($output[0])) {
                $asteriskRunning = true;
                $asteriskVersion = trim($output[0]);
            }
        }

        Response::view('dashboard.index', [
            'callers'         => $callers,
            'asteriskRunning' => $asteriskRunning,
            'asteriskVersion' => $asteriskVersion,
        ]);
    }
}
