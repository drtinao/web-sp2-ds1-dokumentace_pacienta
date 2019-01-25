<?php

    /**
     *
     * Loader
     *
     * Tento objekt pouziju k nacteni konfigurace, routovani apod.
     *
     */

namespace ds1\core;


class ds1_loader {

    /**
     * @params strin type - selected, shared
     * @param string $from - web nebo admin
     * @return string
     */
    public function getPathToConfig($type = "selected", $from = "web") {
        // dle admina nebo webu
        $path_start = DS1_PROJECT_ROOT;

        // TODO test, jestli je definovana konstanta DS1_SELECTED_CONFIGURATION

        // chci vratit __DIR__.'/../simple_eshop3_local/config/routes.inc.php'
        if ($type == "selected") {
            $path = $path_start . DS1_DIR_ROOT_LOCAL . "config/ds1_" . DS1_SELECTED_CONFIGURATION . "_config.inc.php";
        }
        else if ($type == "shared") {
            $path = $path_start . DS1_DIR_ROOT_LOCAL . "config/ds1_" . DS1_SELECTED_CONFIGURATION . "_config.inc.php";
        }
        //echo $path; exit;

        return $path;
    }

    /**
     * @param string $from - web nebo admin
     * @return string
     */
    public function getPathToRoutes() {
        // dle admina nebo webu
        $path_start = DS1_PROJECT_ROOT;

        // chci vratit __DIR__.'/../simple_eshop3_local/config/routes.inc.php'
        $path = $path_start . DS1_DIR_ROOT_LOCAL . "/config/ds1_" . DS1_SELECTED_CONFIGURATION . "_routes_". DS1_CONTEXT .".inc.php";
        //echo $path; exit;

        return $path;
    }

}