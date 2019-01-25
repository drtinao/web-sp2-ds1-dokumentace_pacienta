<?php

namespace ds1\admin_modules\sprava_uzivatelu;

use PDO;

class sprava_uzivatelu extends \ds1\core\ds1_base_model
{
    /**
     * Funkce vrátí údaje uživatele, který má požadovaný (zadaný) login.
     * @param $login login uživatele
     * @return mixed údaje uživatele z DB
     */
    public function getUzivatelByLogin($login){
        $table_name = TABLE_USERS_ADMIN;
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("login", $login);
        $limit_pom = "limit 1";
        $uzivatel_data = $this->DBSelectOne($table_name, "*", $where_array, $limit_pom);
        return $uzivatel_data;
    }

    /**
     * Metoda vrátí z DB řádek obsahující informace o roli požadovaného uživatele
     * @param $id_uzivatel id požadovaného uživatele v tabulce uživatelů
     * @return mixed řádek z DB obsahující informace o roli požadovaného uživatele
     */
    public function getRoleUzivatelByIDUzivatel($id_uzivatel){
        $table_name = TABLE_USERS_ADMIN;
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("id", $id_uzivatel);
        $limit_pom = "limit 1";
        $uzivatel_data = $this->DBSelectOne($table_name, "*", $where_array, $limit_pom);

        $table_name = TABLE_UZIVATELE_PRIDELENI_ROLI;
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("uzivatele_id", $uzivatel_data["id"]);
        $limit_pom = "limit 1";
        $prideleni_roli_data = $this->DBSelectOne($table_name, "*", $where_array, $limit_pom);

        $table_name = TABLE_UZIVATELSKE_ROLE;
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("id", $prideleni_roli_data["uzivatele_role_id"]);
        $limit_pom = "limit 1";
        $konkretni_role_data = $this->DBSelectOne($table_name, "*", $where_array, $limit_pom);

        return $konkretni_role_data;
    }

    /**
     * Metoda vrátí z DB řádky obsahující informace o typech dokumentace, ke kterým má přístup role s požadovaným názvem
     * @param $role_nazev název role, o níž chci informace zjistit
     * @return array řádky DB obsahující informace o typech dokumentace, ke kterým má přístup role s požadovaným názvem
     */
    public function getDruhyZapisuPristupByNazevRole($role_nazev){
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("nazev", $role_nazev);
        $limit_pom = "limit 1";
        $id_role = $this->DBSelectOne(TABLE_UZIVATELSKE_ROLE, "*", $where_array, $limit_pom);

        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("uzivatelske_role_id", $id_role["id"]);
        $druhy_zapisu_pristup_id = $this->DBSelectAll(TABLE_DRUH_ZAPISU_UZIVATELSKE_ROLE, "*", $where_array, "", "");

        $druhy_zapisu_pristup = array();
        foreach($druhy_zapisu_pristup_id as $id_pristup){
            $where_array = array();
            $where_array[] = $this->DBHelperGetWhereItem("id", $id_pristup["dokumentace_druh_zapisu_id"]);
            $druhy_zapisu_pristup_id_jedno = $this->DBSelectAll(TABLE_DOKUMENTACE_DRUH_ZAPISU, "*", $where_array, "", "");

            //nechceme pole v poli = foreach
            foreach($druhy_zapisu_pristup_id_jedno as $id_pristup){
                array_push($druhy_zapisu_pristup, $id_pristup);
            }
        }

        return $druhy_zapisu_pristup;
    }

    /**
     * Funkce přidělí uživateli s předaným id novou roli (také specifikovanou id)
     * @param $uzivatel_id id uživatele, kterému chceme přidat / upravit roli
     * @param $role_id id nové role, kterou chceme uživateli přidělit
     * @return int informace přidané do databáze
     */
    public function saveAdminPridelRoleDB($uzivatel_id, $role_id){
        //nejdříve si zjistím, jestli uživatel již má přidělenou nějakou roli
        $predchozi_role_existuje = false;

        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("uzivatele_id", $uzivatel_id);
        $limit_pom = "limit 1";
        $id_role = $this->DBSelectAll(TABLE_UZIVATELE_PRIDELENI_ROLI, "*", $where_array, $limit_pom);

        if(sizeof($id_role) > 0){
            $predchozi_role_existuje = true;
        }else{
            $predchozi_role_existuje = false;
        }

        if(!$predchozi_role_existuje) { //pokud neexistuje předchozí role, pak vytvořím nový záznam
            $item = array();
            $item["uzivatele_id"] = $uzivatel_id;
            $item["uzivatele_role_id"] = $role_id;
            $id = $this->DBInsert(TABLE_UZIVATELE_PRIDELENI_ROLI, $item);
        }else{ //předchozí role existuje, jenom updatuji existující záznam
            $item = array();
            $item["uzivatele_id"] = $uzivatel_id;
            $item["uzivatele_role_id"] = $role_id;
            $where_array = array();
            $where_array[] = array("column" => "uzivatele_id", "value" => $uzivatel_id, "symbol" => "=");
            $id = $this->DBUpdate(TABLE_UZIVATELE_PRIDELENI_ROLI, $where_array, $item, "limit 1");
        }
        return $id;
    }

    /**
     * Funkce vrátí id role specifikované názvem
     * @param $nazev_role název role, jejíž id chceme získat
     * @return mixed id specifikované role
     */
    public function getRoleIDByNazevRole($nazev_role){
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("nazev", $nazev_role);
        $limit_pom = "limit 1";
        $id_role = $this->DBSelectOne(TABLE_UZIVATELSKE_ROLE, "id", $where_array, $limit_pom);
        $id_role = $id_role["id"]; //odstraním pole

        return $id_role;
    }

    /**
     * Funkce vrátí veškerá možná příjmení z tabulky uživatlů (může být využito například jako nápověda při vyhledávání).
     * @return array pole obsahující příjmení uživatelů systému
     */
    public function getVsechnaPrijmeniUzivatele(){
        // $napoveda_prijmeni_uzivatelu
        $where_array = array();
        $limit_pom = "";
        $uzivatele_prijmeni = $this->DBSelectAll(TABLE_USERS_ADMIN, "prijmeni", $where_array, $limit_pom);

        $pole_vraceni = array();
        //odstraním pole v poli (chci jenom samotná příjmení)
        foreach($uzivatele_prijmeni as $prijmeni){
            if(!in_array($prijmeni["prijmeni"], $pole_vraceni)) {
                array_push($pole_vraceni, $prijmeni["prijmeni"]);
            }
        }

        return $pole_vraceni;
    }

    /**
     * Metoda vrátí data všech uživatelů z tabulky uživatelů
     * @return array pole obsahující informace o všech uživatelů
     */
    public function getDataVsechUzivatelu(){
        $where_array = array();
        $limit_pom = "";
        $data_uzivatelu = $this -> DBSelectAll(TABLE_USERS_ADMIN, "*", $where_array, $limit_pom, "");

        return $data_uzivatelu;
    }

    /**
     * Funkce vrátí data všech uživatelů, které mají specifikované příjmení z tabulky uživatelů
     * @param $prijmeni příjmení uživatelů, které chceme vypsat
     * @return array pole obsahující informace o uživatelích se zadaných příjmením
     */
    public function getDataUzivateluPrijmeni($prijmeni){
        // $napoveda_prijmeni_uzivatelu
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("prijmeni", $prijmeni);
        $limit_pom = "";
        $uzivatele_prijmeni = $this->DBSelectAll(TABLE_USERS_ADMIN, "*", $where_array, $limit_pom);

        return $uzivatele_prijmeni;
    }
}