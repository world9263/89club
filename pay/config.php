<?php
// Centralized configurations for XSBDWIN and UPAY payment gateways
return [
    'xsbdwin' => [
        'api_url' => 'https://xsbdwin.online/api/pay.php',
        'secret_key' => 'f4445014c07a8b4a9e9d62234c80d128',
        'app_id' => 'GP_SUB_43366914',
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
