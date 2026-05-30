<?php
//var_dump(parse_ini_file("config/config.ini"));

require_once __DIR__ . '/helpers/Autoloader.php';
$config = new Configurator();
$router = $config->getRouter();

//$router->dispatch(
//    $_GET['controller'] ?? '',
//        $_GET['method'] ?? ''
//);