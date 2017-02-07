<?php

namespace GrumPHPTest;

error_reporting(E_ALL | E_STRICT);
define('PROJECT_BASE_PATH', __DIR__ . '/..');
define('TEST_BASE_PATH', __DIR__);

$autoloadFile = PROJECT_BASE_PATH . '/vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    throw new \RuntimeException('Install dependencies to run test suite.');
}

require_once $autoloadFile;
