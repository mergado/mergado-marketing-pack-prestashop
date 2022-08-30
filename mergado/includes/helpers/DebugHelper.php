<?php

namespace Mergado\includes\helpers;

class DebugHelper
{
    public static function dd($data){
        if(MERGADO_DEBUG) {
            highlight_string("<?php\n " . var_export($data, true) . "?>");
            echo '<script>document.getElementsByTagName("code")[0].getElementsByTagName("span")[1].remove() ;document.getElementsByTagName("code")[0].getElementsByTagName("span")[document.getElementsByTagName("code")[0].getElementsByTagName("span").length - 1].remove() ; </script>';
            die();
        }
    }

    public static function dump($data){
        if(MERGADO_DEBUG) {
            highlight_string("<?php\n " . var_export($data, true) . "?>");
            echo '<script>document.getElementsByTagName("code")[0].getElementsByTagName("span")[1].remove() ;document.getElementsByTagName("code")[0].getElementsByTagName("span")[document.getElementsByTagName("code")[0].getElementsByTagName("span").length - 1].remove() ; </script>';
        }
    }
}
