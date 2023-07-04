<?php
/**
 * Created by PhpStorm.
 * User: it-dept
 * Date: 13.11.18
 * Time: 11:52
 */

namespace models;

use DB;

require_once MODELS.'appModel.php';


class LoginModel extends AppModel
{
    /**
     * Модель для получения всех данных из таблицы $table
     *
     * @param $table - имя таблицы
     * @return array
     */
    public static function getAllFromTable($table)
    {
        $res = self::getTable($table);

        if (isset($res[0])){
            return $res;
        } else {
            return [];
        }
    }
}