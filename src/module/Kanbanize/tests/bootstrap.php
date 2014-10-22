<?php

chdir(dirname(__DIR__));

$path = __DIR__.'/../src/vendor/zendframework/zendframework/library';
putenv("ZF2_PATH=".$path);

include __DIR__ . '/../src/init_autoloader.php';
