<?php
/*
 * objekt pro praci se SESSION
*/


namespace ds1\core;


class ds1_session
{
    /**
     * Start session
     */
    public function start() {
        session_start();

        // provest inicializaci
        if (!array_key_exists(DS1_SESSION_MAIN_KEY, $_SESSION)) {

            // zalozit
            $_SESSION[DS1_SESSION_MAIN_KEY] = array();

            // data controlleru
            $_SESSION[DS1_SESSION_MAIN_KEY]["controllers"] = array();
        }
    }


    /**
     * Vratit data ze SESSION pro controller pro tuto aplikaci.
     * @param $controller_name
     * @param $key
     * @param $default - defaultni navrat
     */
    public function getDataForController($controller_name, $key, $default = null) {
        // "goods_section_controller", "search_params"

        // kompletni data pro kontrolery
        $all_data_controllers = $_SESSION[DS1_SESSION_MAIN_KEY]["controllers"];

        // existuje tento controller?
        if (array_key_exists($controller_name, $all_data_controllers)) {
            // mam data pro tento controllers
            $controller_data = $all_data_controllers[$controller_name];

            // mam tento klic?
            if (array_key_exists($key, $controller_data)) {
                return $controller_data[$key];
            }
        }
        else {
            // nemam data pro kontroller
            return $default;
        }
    }

    public function setDataForController($controller_name, $key, $data) {

        if (!array_key_exists($controller_name, $_SESSION[DS1_SESSION_MAIN_KEY]["controllers"])) {
            // zalozit slozku pro controller
            $_SESSION[DS1_SESSION_MAIN_KEY]["controllers"][$controller_name] = array();
        }

        // ted to tam muzu narvat
        $_SESSION[DS1_SESSION_MAIN_KEY]["controllers"][$controller_name][$key] = $data;
    }
}