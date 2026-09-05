<?php

namespace Astereal\Web\Controllers\Api;

use Astereal\Web\Models\Caller;
use Astereal\Web\Support\Request;
use Astereal\Web\Support\Response;

class CallerController
{
    /**
     * Inbound Caller Lookup (Called by Asterisk AGI)
     */
    public function lookup(Request $request): void
    {
        $ani = $request->input('ani') ?: $request->input('phone');

        if (empty($ani)) {
            Response::error('Parameter [ani] is required', 400);
        }

        $caller = Caller::findByPhone($ani);

        if ($caller) {
            Response::json([
                'status'      => 'success',
                'found'       => true,
                'data'        => [
                    'name'     => $caller['name'],
                    'company'  => $caller['notes'] ?? '',
                    'is_vip'   => (string)$caller['is_vip'],
                    'route_to' => $caller['route_to'] ?: '100',
                ],
                'CALLER_NAME' => $caller['name'],
                'IS_VIP'      => (string)$caller['is_vip'],
                'ROUTE_TO'    => $caller['route_to'] ?: '100',
            ]);
        }

        // Return safe defaults for unlisted numbers
        Response::json([
            'status'      => 'success',
            'found'       => false,
            'data'        => [
                'name'     => 'Standard Caller',
                'company'  => '',
                'is_vip'   => '0',
                'route_to' => '100',
            ],
            'CALLER_NAME' => 'Standard Caller',
            'IS_VIP'      => '0',
            'ROUTE_TO'    => '100',
        ]);
    }

    /**
     * Create / Register new caller (Called via Web UI AJAX)
     */
    public function store(Request $request): void
    {
        $phone = $request->input('phone') ?: $request->input('ani');
        $name  = $request->input('name');
        $notes = $request->input('notes') ?: $request->input('company', '');

        if (empty($phone) || empty($name)) {
            Response::error('Phone number and name are required', 422);
        }

        $success = Caller::create([
            'phone_number' => $phone,
            'name'         => $name,
            'is_vip'       => $request->input('is_vip', 0),
            'route_to'     => $request->input('route_to', '100'),
            'notes'        => $notes,
        ]);

        if ($success) {
            Response::json(['status' => 'success', 'message' => 'Caller registered successfully']);
        } else {
            Response::error('Failed to create caller record', 500);
        }
    }
}
