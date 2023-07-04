<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 01.11.18
 * Time: 12:23
 */

/**
 * Подключаем файл конфигурации
 */
require_once 'config/config.php';
//session_start();

//debug(phpinfo(),1);


$uri = $_SERVER['REQUEST_URI'];

//debug($_SERVER,1);

$route = New Router();
$route->dispatch($uri);

