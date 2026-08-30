<?php
// Centralized configurations for XSBDWIN and UPAY payment gateways
return [
    'xsbdwin' => [
        'base_url' => 'https://xswallet.cyou/api',
        'secret_key' => '32f2e4607777494febcce5ba4fb4157e',
        'app_id' => 'GP_SUB_41588957',
        'name' => 'bdgwin16'
    ],
    'upay' => [
        'api_url' => 'https://api.upay.ink/v1/api/open/order/apply',
        'secret_key' => 'RLu806k8YuZc40sA',
        'app_id' => 'HrmNJS54',
    ],
    'usdt_rate' => 93
];
?>
