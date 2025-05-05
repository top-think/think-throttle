<?php

use tests\gc\GCApp;

// [ 应用入口文件 ]

require __DIR__ . '/../../vendor/autoload.php';

$app = new GCApp();
$app->env->set("APP_DEBUG", true);
$app->initialize();
$app->loadApp(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

// 执行HTTP应用并响应
$http = $app->http;

$response = $http->run();

$response->send();

$http->end($response);
