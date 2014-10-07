<?php

chdir(dirname(__DIR__));

$path = __DIR__ . '/../vendor/zendframework/zendframework/library';
putenv("ZF2_PATH=".$path);

include __DIR__ . '/../init_autoloader.php';