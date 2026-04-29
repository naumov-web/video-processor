<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    $dotenv = new Dotenv();
    $projectDir = dirname(__DIR__);

    if (file_exists($projectDir.'/.env')) {
        $dotenv->bootEnv($projectDir.'/.env');
    } elseif (file_exists($projectDir.'/.env.test')) {
        $dotenv->bootEnv($projectDir.'/.env.test');
    }
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
