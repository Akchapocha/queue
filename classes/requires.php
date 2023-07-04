<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 02.11.18
 * Time: 13:31
 */

/**
 * Class Requires
 *
 * Класс подключения необходимых файлов
 */
class Requires
{
    public function requiresFiles()
    {
        require_once PDO . 'DB.php';
        require_once CLASSES.'router.php';
        require_once CONTROLLERS.'appController.php';

        require_once LIB.'fpdf/fpdf.php';

    }
}