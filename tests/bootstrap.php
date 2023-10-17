<?php

use Symfony\Component\Dotenv\Dotenv;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Enable DAMA\DoctrineTestBundle extension
StaticDriver::setKeepStaticConnections(false);

// Optionally, you can use other configuration options as needed
// StaticDriver::setCommonYamlFixture('path/to/your/fixtures.yaml');

// Define your cleanup strategy


