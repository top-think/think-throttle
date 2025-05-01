<?php

use tests\gc\GCApp;

// [ 应用入口文件 ]

require __DIR__ . '/../../vendor/autoload.php';

$app = new GCApp();
$app->initialize();

// 加载配置文件
$configPath = __DIR__ . '/../config/';
if (is_dir($configPath)) {
    $files = glob($configPath . '*' . $app->getConfigExt());
    foreach ($files as $file) {
        $app->config->load($file, pathinfo($file, PATHINFO_FILENAME));
    }
}
// 加载中间件
$middlewareFile = __DIR__ . '/../app/middleware.php';
if (is_file($middlewareFile)) {
    $app->middleware->import(include $middlewareFile, 'global');
}

// 执行HTTP应用并响应
$http = $app->http;

$response = $http->run();

$response->send();

$http->end($response);
