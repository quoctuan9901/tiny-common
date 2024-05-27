<?php

namespace Scaleflex\Commons;

use Illuminate\Database\Capsule\Manager as DB;

class Query {

    public static function fetch($query, $bindings = [])
    {
        $startTime = microtime(true);

        try {
            $result = DB::selectOne($query, $bindings);
        } catch (\Exception $e) {
            Debug::v($query, "🔴 Query error: ");
            throw $e;
        }

        $endTime = microtime(true);

        $executionTime = round($endTime - $startTime, 4);

        Debug::s($query, $executionTime, $result);

        return $result;
    }

    public static function fetchAll($query, $bindings = [])
    {
        $startTime = microtime(true);

        try {
            $result = DB::select($query, $bindings);
        } catch (\Exception $e) {
            Debug::v($query, "🔴 Query error: ");
            throw $e;
        }

        $endTime = microtime(true);

        $executionTime = round($endTime - $startTime, 4);

        Debug::s($query, $executionTime, $result);

        return $result;
    }

    /**
     * @param $str
     * @return string
     */
    public static function qs($str, $connection = null)
    {
        $connection = DB::connection('default');
        
        if (in_array($str, ['null', 'NULL'])) {
            return 'null';
        }

        return $connection->getPdo()->quote($str);
    }
}


?>
