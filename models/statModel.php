<?php


namespace models;

use DB;
use PDO;
use Exception;

require_once MODELS.'appModel.php';


class StatModel extends AppModel
{

    public static function getSelfStat($date)
    {
        $dateBegin = $date . ' 00:00:00';
        $dateEnd = $date . ' 23:59:59';

        $sql = 'SELECT COUNT(*)
                FROM `queue`
                WHERE `date_time` BETWEEN \'' . $dateBegin . '\' AND \'' . $dateEnd . '\'';

        DB::getInstance();
        $res = self::$pdo->prepare($sql);
        $res->execute();
        $res = $res->fetchAll(PDO::FETCH_ASSOC);

        if ( isset($res[0]['COUNT(*)']) ){

            return $res[0]['COUNT(*)'];

        }

        return [];

    }

    public static function getOutStat($date)
    {
        $url = 'http://queue2.loc/stat';
//        $url = 'http://10.0.5.214/stat';
//        $url = 'http://10.0.7.4/stat';
        $res = [];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'getOutStatByDate=' . $date);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);

        return $res;

    }

}