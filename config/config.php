<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 01.11.18
 * Time: 15:24
 */

ini_set('display_errors','On');
error_reporting(E_ALL);

/**
 * Функция распечатки массивов в нормальном виде.
 *
 * @param $arr - переменная
 * @param int $stop = 1 - остановка скрипта после рапечатки.
 */
function debug($arr, $stop = 0)
{
    echo '<pre>';
    print_r($arr);
    if ($stop == 1)
    {
        exit('</pre>');
    } else
    {
        echo '</pre>';
    }
}

/**
 * Выбор очереди
 */
define('QUEUE_NUMBER', 1);
//define('QUEUE_NUMBER', 2);

//define('QUEUE', array(1 => 'http://10.0.25.4', 2 => 'http://10.0.25.5'));
//define('QUEUE', array(1 => 'http://10.0.5.214', 2 => 'http://10.0.5.215'));

define('QUEUE', array(1 => 'http://queue1.loc', 2 => 'http://queue2.loc'));

/**
 * Общие адресные переменные
 */
define('PATH_PREFIX',''); /**Путь до очереди */

define('ROOT',dirname(__DIR__));
define('CONFIG',ROOT.'/config/');
define('LIB',ROOT.'/lib/');
define('TICKETS',ROOT.'/tickets/');
define('TEST',ROOT.'/test_classes/');

define('CLASSES',ROOT.'/classes/');
define('CONTROLLERS',ROOT.'/controllers/');
define('MODELS',ROOT.'/models/');
define('VIEWS',ROOT.'/views/');
define('LAYOUTS',VIEWS.'layouts/');

define('PDO',ROOT.'/PDO/');
define('ERRORS',ROOT.'/errors/');

//debug(PDO);


/**
 * Подключение необходимых файлов
 */
require_once CLASSES . 'requires.php';
$reqObj = New Requires();
$reqObj->requiresFiles();