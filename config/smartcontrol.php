<?php

return [
    'enabled' => env('SMARTCONTROL_ENABLED', false),

    'url' => env('SMARTCONTROL_URL'),

    'project' => env('SMARTCONTROL_PROJECT', 'smart_pharmacy'),

    'license_key' => env('SMARTCONTROL_LICENSE_KEY'),

    'client_key' => env('SMARTCONTROL_CLIENT_KEY'),

    'client_secret' => env('SMARTCONTROL_CLIENT_SECRET'),

    'instance_id' => env('SMARTCONTROL_INSTANCE_ID', 'smart_pharmacy-main'),

    'grace_minutes' => (int) env('SMARTCONTROL_GRACE_MINUTES', 1440),

    'check_interval_minutes' => (int) env('SMARTCONTROL_CHECK_INTERVAL_MINUTES', 10),

    'fail_open_minutes' => (int) env('SMARTCONTROL_FAIL_OPEN_MINUTES', 1440),
];