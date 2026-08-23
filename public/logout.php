<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Services\AuthService;

App::init();
$authService = new AuthService();
$authService->logout();

header('Location: /login.php');
exit;
