<?php 

namespace Scaleflex\Commons;

class Helper {

    public static function config($filename, $keyPath) {
        $rootPath = dirname(__DIR__, 4);
        $filePath = $rootPath . "/config/$filename.php";

        if (!file_exists($filePath)) {
            return "Not found config";
        }
        
        $config = include $filePath;
    
        $keys = explode('.', $keyPath);
        $value = $config;
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null;
            }
        }
        
        return $value;
    }
}
?>