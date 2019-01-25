<?php


namespace ds1\core;


class ds1_user_manager extends ds1_base_model
{

    // ************************************************************************************
    // *********   START SESSION FUNKCE    ************************************************
    // ************************************************************************************

    public function setUserToSession($user) {
        $user_data = $user;
        $this->controller->ds1->session->setDataForController("user_manager", "user", $user_data);
    }

    public function getUserFromSession() {
        $user_data = $this->controller->ds1->session->getDataForController("user_manager", "user");
        return $user_data;
    }

    public function removeUserFromSession() {
        $this->controller->ds1->session->setDataForController("user_manager", "user", array());
    }

    // ************************************************************************************
    // *********   KONEC  SESSION FUNKCE    ***********************************************
    // ************************************************************************************

    public function getUserById($id) {
        // nacist uzivatele dle id
        $id += 0;

        $table_name = TABLE_USERS_ADMIN;
        $where_array = array();
        $where_array[] = array("column" => "id", "value" => $id, "symbol" => "=");
        //printr($where_array);

        $user = $this->DBSelectOne($table_name, "*", $where_array, "limit 1");
        //printr($user);

        return $user;
    }

    public function getUserByEmail($email) {
        // nacist uzivatele podle emailu
        $table_name = TABLE_USERS_ADMIN;
        $where_array = array();
        $where_array[] = array("column" => "email", "value" => $email, "symbol" => "=");
        //printr($where_array);

        $user = $this->DBSelectOne($table_name, "*", $where_array, "limit 1");
        //printr($user);

        return $user;
    }



    public function CheckPasswordForUser($user_id, $heslo) {
        $user_id += 0;
        $user = $this->getUserById($user_id);

        if ($user != null) {
            // mam uzivatele

            // test, jestli heslo z formu souhlasi s DB
            // Pozor: nelze delat pres rovnost: if ($form_heslo_bcrypt == $user["password_bcrypt"])
            // funkce bcryptPassword pokazde vraci jiny otisk pro zadane heslo
            if (password_verify($heslo, $user[TABLE_USER_COLUMN_PASSWORD_BCRYPT])) //"password_bcrypt"
            {
                // ano, heslo souhlasi
                return true;
            }
            else {
                // ne, otisky nesouhlasi
                return false;
            }
        } // od mam usera

        return false;
    }

    public function Login($email, $heslo) {
        // nacist uzivatele dle emailu
        $user = $this->getUserByEmail($email);
        //printr($user);

        if ($user != null) {
            // mam uzivatele

            if ($this->CheckPasswordForUser($user["id"], $heslo)) {
                // ano, heslo souhlasi - nastavit do session
                $this->setUserToSession($user);
                return true;
            }
            else {
                // heslo nesouhlasi
                // ne, otisky nesouhlasi
                return false;
            }
        }

        // asi chyba vratit false
        return false;
    }

    public function Logout() {
        $this->removeUserFromSession();
    }


    public function existsUserByEmail($email) {
        $email = trim($email);
        $user = $this->getUserByEmail($email);

        if ($user != null) {
            return true; // existuje
        }
        else {
            return false; // neexistuje
        }
    }

    /**
     * Registrace uzivatele.
     *
     * @param $user - pole user ma stejnou strukturu jako DB - sablona je vytvorena dle DB
     * @param $password - nesifrovane heslo
     * @param $result_type - id nebo bool - vrati ID, jinak true nebo false
     *
     * @return mixed
     */
    public function Register($user, $password, $result_type = "id") {

        // prihodit zasifrovane heslo
        $user[TABLE_USER_COLUMN_PASSWORD_BCRYPT] = $this->bcryptPassword($password);

        // kontrola - musim overit, ze uzivatel neexistuje
        if ($this->existsUserByEmail($user["email"])) {
            // uzivatel existuje, tak vyhodit
            return false;
        }


        // provest insert pres PDO - zabezpeceni proti sql injection
        $table_name = TABLE_USERS_WEB;
        $user["created_date"] = date('Y-m-d H:i:s'); // doplnit aktualni datum a cas, abych nemusel pouzit expanded verzi

        $user_id = $this->DBInsert($table_name, $user);

        if ($result_type == "id") {
            return $user_id;
        }
        else {
            // chce boolean
            if ($user_id > 0)
                return true;
            else
                return false; // chyba
        }
    }

    public function isLogged() {
        $user_data = $this->getUserFromSession();

        if ($user_data != null) {
            // asi je prihlasen
            return true;
        }
        else {
            // neni prihlasen
            return false;
        }

        // pokud nevim, tak radeji ne
        return false;
    }


    /**
     * Vygeneruje token pro tohoto uzivatele.
     * @param $user_id
     */
    public function generateTokenForUserByID($user_id) {
        $user_id += 0;

        // 1. nacist aktualni data uzivatele
        $user = $this->getUserById($user_id);
        $token = $user["token"];
        $token_created_date = $user["token_created_date"];

        // jak dlouho bude jeste platny?
        $valid_hours = $this->howLongWillBeTokenValid($token_created_date);
        // echo "valid hours: ".$valid_hours;

        // pokud je to mensi, nez polovina platnosti, tak pregenerovat
        if ($valid_hours <= (DS1_USER_TOKEN_VALID_HOURS / 2)) {
            // pregenerovat novy token
            $token = $this->generateRandomToken();

            // ulozit token do DB
            $data = array();
            $data["token"] = $token;
            $data["token_created_date"] = date("Y-m-d H:i:s");

            $this->webUpdateUser($user_id, $data);

            // vratit token
            return $token;
        }
        else {
            // jeste polovinu doby to plati, tak ho muzu vratit
            return $token;
        }
    }

    /**
     * Overit, jestli je token validni.
     *
     * @param $user_id
     * @param $token
     * @return bool
     */
    public function isTokenValidForUser($user_id, $token_form) {
        $is_valid = true;

        // 1. nacist uzivatele
        $user = $this->getUserById($user_id);
        $token_db = $user["token"];
        $token_created_date = $user["token_created_date"];

        // 2. overit, jestli je token shodny
        if (trim($token_db) == trim($token_form)) {
            // ano je
            //echo "token db: $token_db,<br/> token form: $token_form<br/>";
        }
        else {
            $is_valid = false;
        }

        // 3. overit, ze je platny jeste aspon hodinu, minutu nebo sekundu
        $valid_seconds = $this->howLongWillBeTokenValid($token_created_date, "s");
        //echo "valid seconds: $valid_seconds <br/>";

        if ($valid_seconds > 1) {
            // musi to byt platne aspon 1s, abych to mohl zmeni
        }
        else {
            $is_valid = false;
            //echo "neni validni, token je moc stary";
        }

        return $is_valid;
    }

    /**
     * Jak dlouho bude jeste token validni?
     * @param return_type - h, m, s
     * @return int
     */
    public function howLongWillBeTokenValid($token_created_date = "", $return_type = "h") {
        // pokud je vstupni token prazdny, tak vratit 0
        if (trim($token_created_date) == "") {
            return 0;
        }

        $now = date("Y-m-d H:i:s");
        $diff_hours = $this->dateDifference($token_created_date, $now,"h");
        $diff_minutes = $this->dateDifference($token_created_date, $now, "i");
        $diff_seconds = $this->dateDifference($token_created_date, $now, "s");
        //echo "diff_seconds: ";
        //printr($diff_seconds);
        //printr($diff_minutes);
        //printr($diff_hours);

        // spocitat zbyvajici platnost v hodinach
        $valid_hours = DS1_USER_TOKEN_VALID_HOURS - $diff_hours;

        // kontrola na nulove nebo mensi hodnoty
        if ($diff_seconds <= 0 && $diff_minutes <= 0) {
            // nastala nejaka chyba
            $valid_hours = 0;
            return 0;
        }

        // POZOR: zaporne hodnoty tady uz nemusim resit, protoze mi to ukoncila podminka vyse
        if ($return_type == "h") {
            // chci vratit hodiny - uz to mam rovnou odectene
            return $valid_hours;
        }
        else if ($return_type == "m") {
            // celkova platnost - aktualni trvani
            $minutes = (DS1_USER_TOKEN_VALID_HOURS * 60) - ($diff_minutes);
            return $minutes;
        }
        else if ($return_type == "s") {
            // celkova platnost - aktualni trvani
            $seconds = (DS1_USER_TOKEN_VALID_HOURS * 3600) - ($diff_seconds);
            return $seconds;
        }
    }


    /**
     * Vygeneruje a vrati nahodny token.
     * @param int $length delka tokenu
     * @return string
     */
    public function generateRandomToken($length = 40) {
        $token = bin2hex(random_bytes($length));
        return $token;
    }

    /**
     * @param $date1 - starsi, token
     * @param $date2 - novejsi, now
     * @param string $result_type - h hours, i minutes, s seconds
     * @return int
     */
    function dateDifference($date1 , $date2 , $result_type = "s" )
    {
        if ($result_type == "h") {
            $hourdiff = round((strtotime($date2) - strtotime($date1))/3600, 1);
            return $hourdiff;
        }
        if ($result_type == "i" || $result_type == "m") {
            $mindiff = round((strtotime($date2) - strtotime($date1))/60, 1);
            return $mindiff;
        }
        if ($result_type == "s") {
            $secdiff = round((strtotime($date2) - strtotime($date1)), 1);
            return $secdiff;
        }
    }

    public function isTokenValidByCreatedDate($token_created_date) {
        // jenom overim, ze je to validni dle konfigurace = ze je jeste platny
        $now = date("Y-m-d H:i:s");


    }

    public function getCurrentUserID() {
        $user_data = $this->getCurrentUser();

        return $user_data["id"] + 0;
    }

    public function getCurrentUser() {

        if ($this->isLogged()) {
            $user_data = $this->getUserFromSession();
            // provedu aktualizaci
            $user_id = $user_data["id"] + 0;
            //echo $user_id;
            $user_data = $this->getUserById($user_id);

            // poslat do session
            $this->setUserToSession($user_data);

            return $user_data;
        }
        else {
            return null;
        }

    }

    public function bcryptPassword($password) {

        // zasifrovat - pouzivam compat lib, takze by to melo chodit vsude
        // https://github.com/ircmaxell/password_compat - je v souboru ds1_password_compat_lib.inc.php
        $password =  password_hash($password, PASSWORD_BCRYPT);

        return $password;
    }


    // ************************************************************************************
    // *********   START ADMIN     ********************************************************
    // ************************************************************************************

    /**
     * Test existence uzivatele specialne pro pridavani noveho obyvatele.
     *
     * @param $params - pole hodnot pro hledani
     * @return bool
     */
    public function adminExistsUzivatelByParams($uzivatel) {
        $table_name = TABLE_USERS_ADMIN;

        $where_array = array();

        // prihodit tam vsechny podminky
        if ($uzivatel != null){
            foreach ($uzivatel as $key => $value) {
                $where_array[] = $this->DBHelperGetWhereItem("$key", $uzivatel[$key]);
            }

            $limit_pom = "limit 1";
            $row = $this->DBSelectOne($table_name, "*", $where_array, $limit_pom);
            //echo "uziv by params";
            //printr($row);

            if ($row != null)
                return true;
            else
                return false;
        }

        return null;
    }

    /**
     * Admin metoda, takze tady NENI podminka na smazano.
     *
     * @param $id
     * @return mixed
     */
    public function adminGetItemByID($id) {
        $id += 0;

        $table_name = TABLE_USERS_ADMIN;
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("id", $id);
        $limit_pom = "limit 1";

        $row = $this->DBSelectOne($table_name, "*", $where_array, $limit_pom);
        //printr($row);

        return $row;
    }

    public function adminInsertItem($item) {
        $id = $this->DBInsert(TABLE_USERS_ADMIN, $item);
        return $id;
    }

    // vytvorit uzivatele dle insert formu
    public function adminInsertUser($item) {
        //printr($item);
        if (!key_exists("login", $item)) return;
        if (!key_exists("heslo", $item)) return;
        if (!key_exists("heslo2", $item)) return;

        // hesla nesouhlasi
        if ($item["heslo"] != $item["heslo2"]) return;

        // musim provest fixaci
        $user = array();
        $user["login"] = $item["login"];
        $user["password_bcrypt"] = $this->bcryptPassword($item["heslo"]);
        $user["datum_vytvoreni"] = date("Y-m-d H:i:s");

        $id = $this->DBInsert(TABLE_USERS_ADMIN, $user);
        return $id;
    }

    public function adminUpdateItem($id, $item) {
        $where_array = array();
        $where_array[] = array("column" => "id", "value" => $id, "symbol" => "=");

        $ok = $this->DBUpdate(TABLE_USERS_ADMIN, $where_array, $item, "limit 1");
        return $ok;
    }


    /**
     * Admin - nacist uzivatele. Bez smazanych.
     *
     * @param string $type - data nebo count
     * @param int $page
     * @param int $count_on_page
     * @param array $search_params_sql - primo do tvaru pro sql
     * @param string $order_by
     * @return mixed
     */
    public function adminLoadItems($type = "data", $page = 1, $count_on_page = 12, $where_array = array(), $order_by = "id", $order_by_direction = "asc")
    {

        if ($type == "data") {
            $columns = "*";

            if ($page <= 1) $page = 1;
            $from = ($page - 1) * $count_on_page + 0;
            $limit_pom = "limit $from, $count_on_page";
        } else {
            $columns = "count(*)";
            $limit_pom = "";
        }

        $table_name = TABLE_USERS_ADMIN;
        $count_on_page += 0;
        $order_by = $this->DBHelperFixColumnName($order_by);

        $order_by_pom = array();
        $order_by_pom[] = array("column" => $order_by, "sort" => $order_by_direction);
        $where_array = array();
        $where_array[] = array("column" => "smazano", "value" => 0, "symbol" => "=");       // skryt smazane uzivatele

        $rows = $this->DBSelectAll($table_name, $columns, $where_array, $limit_pom, $order_by_pom);
        //printr($rows);

        if ($type == "data") {
            // chci data - vratit data
            return $rows;
        } else {
            // chci jen count
            $count = @$rows[0]["count(*)"] + 0;
            //echo $count;
            return $count;
        }
    }

    // ************************************************************************************
    // *********   KONEC ADMIN     ********************************************************
    // ************************************************************************************

}