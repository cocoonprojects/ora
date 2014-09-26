<?php

chdir(dirname(__DIR__));

$path = __DIR__ . '/../vendor/zendframework/zendframework/library';
putenv("ZF2_PATH=".$path);

<<<<<<< HEAD
include __DIR__ . '/../src/init_autoloader.php';

=======
include __DIR__ . '/../src/init_autoloader.php';
>>>>>>> refactoring and testing auth feature
