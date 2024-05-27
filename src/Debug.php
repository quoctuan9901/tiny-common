<?php

namespace Scaleflex\Commons;

class Debug
{
    public static function v($var, $label = null)
    {
        if (isset($_GET['debug']) && $_GET['debug'] == 8022) {
            $checkUseCurl =  (is_numeric(strpos($_SERVER['HTTP_USER_AGENT'], 'curl'))) ? true : false;

            if ($label) {
                echo "🐞 [Debugging] {$label}: ";
            } else {
                echo "🐞 [Debugging]: ";
            }

            if ($checkUseCurl) {
                if (is_array($var) || is_object($var)) {
                    $output = print_r($var, true);
                } elseif (is_string($var) && json_decode($var) !== null) {
                    $output = json_encode(json_decode($var), JSON_PRETTY_PRINT);
                } else {
                    $output = var_export($var, true);
                }

                echo PHP_EOL . $output . PHP_EOL;
                echo PHP_EOL . "-----------------------------" . PHP_EOL;
            } else {
                dump($var);
            }
        }
    }

    public static function s($sql, $time_execute, $result)
    {
        if (isset($_GET['debug']) && $_GET['debug'] == 8022) {
            $checkUseCurl =  (is_numeric(strpos($_SERVER['HTTP_USER_AGENT'], 'curl'))) ? true : false;

            echo "🐞 [Debugging SQL]: " . PHP_EOL;

            $debugSql = [
                'sql' => $sql,
                'time_execute' => $time_execute . "s",
                'result' => $result
            ];

            if ($checkUseCurl) {
                print_r($debugSql);
                echo PHP_EOL . "-----------------------------" . PHP_EOL;
            } else {
                dump($debugSql);
            }
        }
    }
}
