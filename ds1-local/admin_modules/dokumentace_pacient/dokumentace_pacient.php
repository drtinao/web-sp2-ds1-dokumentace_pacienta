<?php

namespace ds1\admin_modules\dokumentace_pacient;

use ds1\admin_modules\dokumentace\dokumentace;
use PDO;

class dokumentace_pacient extends \ds1\core\ds1_base_model
{
    /**
     * Vrátí pole obsahující řádky dokumentace z DB, které má právě přihlášená osoba právo zobrazit
     *
     * @param string $login_prihlaseneho_uzivatele - login právě přihlášeného uživatele
     * @param string $type - data nebo count
     * @param int $page
     * @param int $count_on_page
     * @param string $order_by
     * @return mixed
     */
    public function getDokumentaceByLogin($login_prihlaseneho_uzivatele, $type = "data", $page = 1, $count_on_page = 12, $where_array = array(), $order_by = "id", $order_by_direction = "asc")
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

        $order_by = $this->DBHelperFixColumnName($order_by);

        $order_by_pom = array();
        $order_by_pom[] = array("column" => $order_by, "sort" => $order_by_direction);

        $rows = array();


        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("login", $login_prihlaseneho_uzivatele);
        $limit_pom = "limit 1";
        $ds1_uzivatele_prihlaseny = $this->DBSelectOne(TABLE_USERS_ADMIN, "*", $where_array, $limit_pom);

        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("uzivatele_id", $ds1_uzivatele_prihlaseny["id"]);
        $limit_pom = "";
        //atributy: uzivatele_id, uzivatele_role_id, datum_vytvoreni
        $rows_uzivatele_prideleni_roli = $this->DBSelectAll(TABLE_UZIVATELE_PRIDELENI_ROLI, "*", $where_array, $limit_pom, ""); //pole, které obsahuje informace o tom, jakému uživateli byla přidělena která role

        //uživatel může mít více rolí -> všechny jeho role přidám do pole
        $uzivatelske_role_id = array();
        foreach ($rows_uzivatele_prideleni_roli as $row){
            array_push($uzivatelske_role_id, $row["uzivatele_role_id"]);
        }

        //pro každou roli si zjistíme druhy zápisu, které má daná osoba právo zobrazit
        $druhy_dokumentace_id = array(); //id druhů zápisu dokumentace, která má přihlášená osoba právo zobrazit
        foreach ($uzivatelske_role_id as $row){
            //atributy: dokumentace_druh_zapisu_id, uzivatelske_role_id
            $where_array = array();
            $where_array[] = $this->DBHelperGetWhereItem("uzivatelske_role_id", $row);
            array_push($druhy_dokumentace_id, $this->DBSelectAll(TABLE_DRUH_ZAPISU_UZIVATELSKE_ROLE, "*", $where_array, $limit_pom, "")); //pole, které obsahuje informace o tom, která uživatelská role má přístup k jakému typu záznamu
        }


        //zajímají mě id druhů dokumentace, které může daná osoba zobrazit -> vytáhnu si id do pole
        $druhy_dokumentace_id_split = array();
        foreach($druhy_dokumentace_id as $row){
            foreach($row as $sec_row) {
                if (!in_array($sec_row["dokumentace_druh_zapisu_id"], $druhy_dokumentace_id_split)) {
                    array_push($druhy_dokumentace_id_split, $sec_row["dokumentace_druh_zapisu_id"]);
                }
            }
        }

        //zjistím, ke kterým konkrétním dokumentacím má mít přihlášený uživatel přístup
        $dokumentace_pole = array();
        foreach($druhy_dokumentace_id_split as $id_druh_dokumentace){
            $where_array = array();
            $where_array[] = $this->DBHelperGetWhereItem("dokumentace_typ_zapisu_id", $id_druh_dokumentace);
            $dokumentace_pole[] = $this->DBSelectAll(TABLE_DOKUMENTACE, "*", $where_array, $limit_pom, $order_by_pom);
        }

        //dokumentace je uložena jako pole polí (protože volám víckrát fci DBHelperGetWhereItem?) -> odstraním "podpole" pro lepší práci
        foreach($dokumentace_pole as $row){
            foreach($row as $sec_row){
                //přidám informace, které chci zobrazit v tabulce
                $where_array = array();
                $where_array[] = $this->DBHelperGetWhereItem("id", $sec_row["uzivatel_id"]);
                $limit_pom = "limit 1";
                $tabulka_uzivatelu_radek = $this->DBSelectOne(TABLE_USERS_ADMIN, "*", $where_array, $limit_pom);

                $where_array = array();
                $where_array[] = $this->DBHelperGetWhereItem("id", $sec_row["obyvatel_id"]);
                $limit_pom = "limit 1";
                $tabulka_obyvatel_radek = $this->DBSelectOne(TABLE_OBYVATELE, "*", $where_array, $limit_pom);

                $sec_row["uzivatel_jmeno"] = $tabulka_uzivatelu_radek["jmeno"];
                $sec_row["uzivatel_prijmeni"] = $tabulka_uzivatelu_radek["prijmeni"];
                $sec_row["obyvatel_jmeno"] = $tabulka_obyvatel_radek["jmeno"];
                $sec_row["obyvatel_prijmeni"] = $tabulka_obyvatel_radek["prijmeni"];
                $sec_row["obyvatel_zkratka_pojistovny"] = $tabulka_obyvatel_radek["pojistovna_zkratka"];
                $sec_row["obyvatel_datum_narozeni"] = $tabulka_obyvatel_radek["datum_narozeni"];

                $where_array = array();
                $where_array[] = $this->DBHelperGetWhereItem("id", $sec_row["dokumentace_typ_zapisu_id"]);
                $limit_pom = "limit 1";
                $tabulka_dokumentace_radek = $this->DBSelectOne(TABLE_DOKUMENTACE_DRUH_ZAPISU, "*", $where_array, $limit_pom);
                $sec_row["dokumentace_druh_text"] = $tabulka_dokumentace_radek["nazev"];
                $sec_row["dokumentace_druh_barva_hex"] = $tabulka_dokumentace_radek["barva_hex"];
                $sec_row["dokumentace_druh_tucne"] = $tabulka_dokumentace_radek["tucne"];
                $sec_row["dokumentace_druh_kurziva"] = $tabulka_dokumentace_radek["kurziva"];

                $rows[] = $sec_row;
            }
        }

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

    /**
     * Funkce vrátí údaje z DB pro dokumentaci se zvoleným id
     * @param $id id dokumentace, o které chceme zjistit informace
     * @return mixed údaje o zvolené dokumentaci
     */
    public function getItemByID($id) {
        $id += 0;

        $table_name = TABLE_DOKUMENTACE;
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("id", $id);
        $limit_pom = "limit 1";

        $row = $this->DBSelectOne($table_name, "*", $where_array, $limit_pom);

        return $row;
    }

    /**
     * Vrátí z DB informace o druhu dokumentace, která má zvolené id.
     * @param $id id dokumentace
     * @return mixed
     */
    public function getDruhDokumentaceByIDDokumentace($id) {
        $id += 0;

        $table_name = TABLE_DOKUMENTACE_DRUH_ZAPISU;
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("id", $id);
        $limit_pom = "limit 1";

        $row = $this->DBSelectOne($table_name, "*", $where_array, $limit_pom);

        return $row;
    }

    /**
     * Vrátí pole druhů zápisu, ke kterým má přihlášená osoba přístup (názvy - zdravotni_data atp.)
     * @param $login_prihlaseneho_uzivatele login právě přihlášeného uživatele
     * @return array pole názvů druhů zápisu, ke kterým má přihlášená osoba přístup
     */
    public function getDokumentaceDruhZapisuPristup($login_prihlaseneho_uzivatele){
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("login", $login_prihlaseneho_uzivatele);
        $limit_pom = "limit 1";
        $ds1_uzivatele_prihlaseny = $this->DBSelectOne(TABLE_USERS_ADMIN, "*", $where_array, $limit_pom);

        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("uzivatele_id", $ds1_uzivatele_prihlaseny["id"]);
        $limit_pom = "";
        //atributy: uzivatele_id, uzivatele_role_id, datum_vytvoreni
        $rows_uzivatele_prideleni_roli = $this->DBSelectAll(TABLE_UZIVATELE_PRIDELENI_ROLI, "*", $where_array, $limit_pom, ""); //pole, které obsahuje informace o tom, jakému uživateli byla přidělena která role

        //uživatel může mít více rolí -> všechny jeho role přidám do pole
        $uzivatelske_role_id = array();
        foreach ($rows_uzivatele_prideleni_roli as $row){
            array_push($uzivatelske_role_id, $row["uzivatele_role_id"]);
        }

        //pro každou roli si zjistíme druhy zápisu, které má daná osoba právo zobrazit
        $druhy_dokumentace_id = array(); //id druhů zápisu dokumentace, která má přihlášená osoba právo zobrazit
        foreach ($uzivatelske_role_id as $row){
            //atributy: dokumentace_druh_zapisu_id, uzivatelske_role_id
            $where_array = array();
            $where_array[] = $this->DBHelperGetWhereItem("uzivatelske_role_id", $row);
            array_push($druhy_dokumentace_id, $this->DBSelectAll(TABLE_DRUH_ZAPISU_UZIVATELSKE_ROLE, "*", $where_array, $limit_pom, "")); //pole, které obsahuje informace o tom, která uživatelská role má přístup k jakému typu záznamu
        }


        //zajímají mě id druhů dokumentace, které může daná osoba zobrazit -> vytáhnu si id do pole
        $druhy_dokumentace_id_split = array();
        foreach($druhy_dokumentace_id as $row){
            foreach($row as $sec_row) {
                if (!in_array($sec_row["dokumentace_druh_zapisu_id"], $druhy_dokumentace_id_split)) {
                    array_push($druhy_dokumentace_id_split, $sec_row["dokumentace_druh_zapisu_id"]);
                }
            }
        }

        //zjistím, ke kterým konkrétním dokumentacím má mít přihlášený uživatel přístup
        $dokumentace_pole = array();

        foreach($druhy_dokumentace_id_split as $id_druh_dokumentace){
            $where_array = array();
            $where_array[] = $this->DBHelperGetWhereItem("id", $id_druh_dokumentace);
            $dokumentace_pole[] = $this->DBSelectAll(TABLE_DOKUMENTACE_DRUH_ZAPISU, "nazev", $where_array, $limit_pom, "");
        }

        //odstraním pole v poli
        $nazvy_pristupnych_udaju = array();
        foreach($dokumentace_pole as $row){
            foreach($row as $sec_row){
                $nazvy_pristupnych_udaju[] = $sec_row["nazev"];
            }
        }

        return $nazvy_pristupnych_udaju;
    }

    /**
     * Vrátí řádky DB s pacienty, kteří odpovídají podmínkám vstupního pole (jméno + příjmení)
     *
     * @param $dokumentace - pole hodnot zadaných ve formuláři pro přidání dokumentace
     * @return pole řádku DB s pacienty
     */
    public function getPacientByParams($dokumentace) {
        $table_name = TABLE_OBYVATELE;
        $where_array = array();
        $podminky_hledani = array();
        $podminky_hledani["jmeno"] = $dokumentace["jmeno"];
        $podminky_hledani["prijmeni"] = $dokumentace["prijmeni"];

        foreach ($podminky_hledani as $key => $value) { //převedu na klíč -> hodnota
            $where_array[] = $this->DBHelperGetWhereItem("$key", $podminky_hledani[$key]);
        }

        $limit_pom = "";
        $obyvatele_jmeno = $this->DBSelectAll($table_name, "*", $where_array, $limit_pom); //obsahuje obyvatele, které mají odpovídající jméno

        return $obyvatele_jmeno;
    }

    /**
     * Vrátí záznam z DB pro daného pacienta (zjištěno dle zadaného ID)
     * @param $id_pacient id požadovaného pacienta v tabulce pacientů
     * @return mixed záznam z tabulky pacientu pro daného pacienta
     */
    public function getPacientByID($id_pacient){
        $table_name = TABLE_OBYVATELE;
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("id", $id_pacient);
        $limit_pom = "limit 1";
        $pacient_data = $this->DBSelectOne($table_name, "*", $where_array, $limit_pom);
        return $pacient_data;
    }

    /**
     * Funkce uloží dokumentaci do DB.
     * @param $data_dokumentace data dokumentace, která uživatel vepsal (jméno pacienta, příjmení pacienta, druh zápisu, text zápisu)
     * @param $pacient_data data pacienta, kterému má být dokumentace přiřazena
     * @param $uzivatel_data data uživatele, který dokumentaci přidal
     */
    public function saveDokumentacePacient($data_dokumentace, $pacient_data, $uzivatel_data){
        $item = array();
        $item["uzivatel_id"] = $uzivatel_data["id"];
        $item["obyvatel_id"] = $pacient_data["id"];
        $item["zapis"] = $data_dokumentace["text_zapisu"];
        date_default_timezone_set('Europe/Prague');
        $item["datum_vytvoreni"] = date("Y-m-d")." ".date("H:i:s");
        $dokumentace_typ = $this -> getDokumentaceTypIDByText($data_dokumentace["druh_zapisu"]);
        $item["dokumentace_typ_zapisu_id"] = $dokumentace_typ["id"];

        $id = $this->DBInsert(TABLE_DOKUMENTACE, $item);
        return $id;
    }

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
     * Funkce vrátí detailní informace o druhu zápisu, který chce uživatel vložit (z tabulky dokumentace_druh_zapisu).
     * @param $druh_zapisu_text druh zápisu (psychicky_stav atd.)
     * @return mixed údaje zápisu z DB
     */
    public function getDokumentaceTypIDByText($druh_zapisu_text){
        $table_name = TABLE_DOKUMENTACE_DRUH_ZAPISU;
        $where_array = array();
        $where_array[] = $this->DBHelperGetWhereItem("nazev", $druh_zapisu_text);
        $limit_pom = "limit 1";
        $dokumentace_druh_zapisu_data = $this->DBSelectOne($table_name, "*", $where_array, $limit_pom);
        return $dokumentace_druh_zapisu_data;
    }

    /**
     * Funkce provede aktualizaci záznamu dokumentace v DB a vrátí upravené informace.
     * @param $id_dokumentace id dokumentace, kterou chceme upravit
     * @param $uzivatel_id id uživatele, kterého se dokumentace týká
     * @param $obyvatel_id id obyvatele, kterého se dokumentace týká
     * @param $novy_typ_dokumentace_id id typu dokumentace (možno změnit...)
     * @param $novy_text_dokumentace nový text dokumentace
     * @return bool
     */
    public function updateDokumentace($id_dokumentace, $uzivatel_id, $obyvatel_id, $novy_typ_dokumentace_id, $novy_text_dokumentace){
        $item = array();
        $item["id"] = $id_dokumentace;
        $item["uzivatel_id"] = $uzivatel_id;
        $item["obyvatel_id"] = $obyvatel_id;
        $item["dokumentace_typ_zapisu_id"] = $novy_typ_dokumentace_id;
        $item["zapis"] = $novy_text_dokumentace;

        $where_array = array();
        $where_array[] = array("column" => "id", "value" => $id_dokumentace, "symbol" => "=");
        $updated = $this->DBUpdate(TABLE_DOKUMENTACE, $where_array, $item, "limit 1");

        return $updated;
    }

    /**
     * Smaže z DB dokumentaci s daným id
     * @param $id_dokumentace id vybrané dokumentace
     */
    public function removeDokumentace($id_dokumentace){
        $where_array = array();
        $where_array[] = array("column" => "id", "value" => $id_dokumentace, "symbol" => "=");
        $updated = $this->DBDelete(TABLE_DOKUMENTACE, $where_array, "limit 1");
    }

    /**
     * Funkce zjistí, zda má uživatel právo uložit zvolený typ záznamu.
     * @param $zadano_uzivatel typ dokumentace, který se uživatel pokouší uložit
     * @param $uzivatel_pravo_typ pole názvů typů dokumentace, které má uživatel právo uložit
     * @return bool true, pokud má uživatel právo daný typ dokumentace uložit - jinak false
     *     */
    public function isPravoUlozitZaznamUzivatel($zadano_uzivatel, $uzivatel_pravo_typ){
        if(in_array($zadano_uzivatel, $uzivatel_pravo_typ)) {
            return true;
        }else{
            return false;
        }
    }
}