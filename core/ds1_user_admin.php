<?php


namespace ds1\core;


class ds1_user_admin extends ds1_base_model
{
    // ************************************************************************************
    // *********   START SESSION FUNKCE    ************************************************
    // ************************************************************************************

    public function setAdminUserToSession($login) {
        $user_data["login"] = $login;
        $user_data["admin_token"] = $this->generateAdminToken();

        $this->controller->ds1->session->setDataForController("user_admin", "user", $user_data);
    }

    public function getAdminUserFromSession() {
        $user_data = $this->controller->ds1->session->getDataForController("user_admin", "user");
        return $user_data;
    }

    public function removeAdminUserFromSession() {
        $this->controller->ds1->session->setDataForController("user_admin", "user", array());
    }

    public function generateAdminToken($length = 10) {
        $token = bin2hex(random_bytes($length));
        return $token;
    }
    // ************************************************************************************
    // *********   KONEC  SESSION FUNKCE    ***********************************************
    // ************************************************************************************


    public function getUserByLogin($login) {
        // nacist uzivatele podle loginu
        $table_name = TABLE_USERS_ADMIN;
        $where_array = array();
        $where_array[] = array("column" => "login", "value" => $login, "symbol" => "=");
        $where_array[] = array("column" => "smazano", "value" => 0, "symbol" => "=");   // uzivatel nesmi byt smazan, pouzije se v loginu
        //printr($where_array);

        $user = $this->DBSelectOne($table_name, "*", $where_array, "limit 1");

        return $user;
    }

    public function Login($login, $password) {
        // nacist uzivatele dle emailu
        $user = $this->getUserByLogin($login);
        //printr($user);

        if ($user != null) {
            // mam uzivatele

            // test, jestli heslo z formu souhlasi s DB
            // Pozor: nelze delat pres rovnost: if ($form_heslo_bcrypt == $user["password_bcrypt"])
            // funkce bcryptPassword pokazde vraci jiny otisk pro zadane heslo
            if (password_verify($password, $user["password_bcrypt"]))
            {
                // ano, heslo souhlasi - nastavit do session
                $this->setAdminUserToSession($login);
                return true;
            }
            else {
                // ne, otisky nesouhlasi
                return false;
            }
        }
        else {
            // nemam uzivatele, vratit false
            return false;

        }
    }

    public function Logout() {
        $this->removeAdminUserFromSession();
    }


    /**
     * FIXME lepe prepsat test na prihlaseeni do admina
     */
    public function isAdminLogged() {

        $user_data = $this->getAdminUserFromSession();

        // musi existovat user data a v tom navic admin_token
        if ($user_data != null) {
            if (array_key_exists("admin_token", $user_data)) {
                // vypada to dobre, je prihlasen
                return true;
            }
        }

        // defaultni vystup
        return false;

    }

    public function getCurrentUser() {

    }

    public function bcryptPassword($password) {

        // zasifrovat - pouzivam compat lib, takze by to melo chodit vsude
        // https://github.com/ircmaxell/password_compat - je v souboru ds1_password_compat_lib.inc.php
        $password =  password_hash($password, PASSWORD_BCRYPT);

        return $password;
    }
}