<?php

define('ENTITY_FOLDER', __DIR__ . '/../include/entities/');
define('LIB_FOLDER', __DIR__ . '/../include/lib/');

require __DIR__ . '/../include/framework/exception.php';
require __DIR__ . '/../include/framework/autoloader.php';
require __DIR__ . '/../include/framework/functions.php';

$paths = [
          __DIR__ . '/../include/framework/',
          __DIR__ . '/../include/entities/',
          __DIR__ . '/../include/controllers/',
          __DIR__ . '/../include/models/',
         ];

$autoloader = new Autoloader($paths);
spl_autoload_register([$autoloader, 'autoloader']);

require_once __DIR__ . '/../vendor/autoload.php';
