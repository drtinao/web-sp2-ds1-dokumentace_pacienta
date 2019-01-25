<?php
namespace ds1\admin_modules\dokumentace_pacient;

use Symfony\Component\HttpFoundation\Request;

use ds1\core\ds1_base_controller;

class dokumentace_pacient_controller extends ds1_base_controller
{
    // timto rikam, ze je NUTNE PRIHLASENI ADMINA
    protected $admin_secured_forced = true; // vynuceno pro jistotu, ale mel by stacit kontext admin

    public function indexAction(Request $request, $page = "")
    {
        // zavolat metodu rodice, ktera provede obecne hlavni kroky a nacte parametry
        parent::indexAction($request, $page);


        // test, jestli je uzivatel prihlasen, pokud NE, tak redirect na LOGIN
        $this->checkAdminLogged();

        // objekt pro praci s pacienty
        $dokumentace_pacient = new dokumentace_pacient();
        $dokumentace_pacient->SetPDOConnection($this->ds1->GetPDOConnection());

        $action = $this->loadRequestParam($request,"action", "all", "pacient_list_all");

        // univerzalni content params
        $content_params = array();
        $content_params["base_url"] = $this->webGetBaseUrl();
        $content_params["base_url_link"] = $this->webGetBaseUrlLink();
        $content_params["page_number"] = $this->page_number;
        $content_params["route"] = $this->route;        // mam tam orders, je to automaticky z routingu
        $content_params["route_params"] = array();
        $content_params["controller"] = $this;

        // JMENA EXTERNICH ROUT
        $content_params["pokoje_pacient_name"] = "dokumentace_pacient";

        $content = "";

        // defaultni vysledek akce
        $result_msg = "";
        $result_ok = true;


        $prihlaseny_uzivatel_udaje = array(); //bude obsahovat údaje o přihlášeném uživateli
        $prihlaseny_uzivatel_udaje = $this->ds1->user_admin->getAdminUserFromSession(); //zjistím údaje o přihlášeném uživateli (zajímá mě jméno - práva pak vyčtu z DB)

      if($action == "pacient_list_all"){ //zobrazím dostupné info, které má přihlášená osoba právo zobrazit
          if($prihlaseny_uzivatel_udaje["login"] == "admin"){ //pokud přihlášený admin, tak ho přesměruji
              $admin_url =  $this->makeUrlByRoute($this->route, array("action" => "admin_no_access"));
              header("Location: $admin_url");
              exit();
          }

          $count_on_page = 50; //počet položek na stránce
            $where_array = array();
            // count_on_page a page se u prikazu count neuvazuje
            $total = $dokumentace_pacient->getDokumentaceByLogin("$prihlaseny_uzivatel_udaje[login]", "count", 1, 1, $where_array);
            $dokumentace_list = $dokumentace_pacient->getDokumentaceByLogin("$prihlaseny_uzivatel_udaje[login]","data", $this->page_number, $count_on_page, $where_array, "id", "asc");

          //vytvořím si pole pacientů, které bude obsahovat údaje ve formátu: jméno příjmení[id (pokud více stejné jméno)]
          $where_array = array();
          $limit_pom = "";
          $objekty_pacientu = $dokumentace_pacient->DBSelectAll(TABLE_OBYVATELE, "*", $where_array, $limit_pom, "");
          //odstraním pole v poli
          $jmena_pacientu_edit = array();
          foreach($objekty_pacientu as $row){
              //zjistím, jestli existuje více pacientů se stejným jménem
              $table_name = TABLE_OBYVATELE;
              $where_array = array();
              $podminky_hledani = array();
              $podminky_hledani["jmeno"] = $row["jmeno"];
              $podminky_hledani["prijmeni"] = $row["prijmeni"];
              foreach ($podminky_hledani as $key => $value) { //převedu na klíč -> hodnota
                  $where_array[] = $dokumentace_pacient->DBHelperGetWhereItem("$key", $podminky_hledani[$key]);
              }
              $limit_pom = "";
              $obyvatele_jmeno = $dokumentace_pacient->DBSelectAll($table_name, "*", $where_array, $limit_pom); //obsahuje obyvatele, které mají odpovídající jméno

              if(sizeof($obyvatele_jmeno) > 1){//pokud máme více pacientů se stejným jménem, pak přidáme [datum narození]
                  array_push($jmena_pacientu_edit, $row["jmeno"]." ".$row["prijmeni"]." [id = ".$row["id"]."]");
              }else{ //pacient s daným jménem je jeden, netřeba vypisovat id
                  array_push($jmena_pacientu_edit, $row["jmeno"]." ".$row["prijmeni"]);
              }
          }

          $content_params["napoveda_jmena_pacientu"] = $jmena_pacientu_edit;
          //vytáhnu si seznam názvů druhů dokumentace, ke kteým má přihlášená osoba přístup
          $seznam_druhu_dokumentace = $dokumentace_pacient->getDokumentaceDruhZapisuPristup($prihlaseny_uzivatel_udaje["login"]);
          $content_params["napoveda_typy_dokumentace"] = $seznam_druhu_dokumentace;
          $content_params["form_submit_url"] = $this->makeUrlByRoute($this->route);

            // vygenerovat strankovani - obecna metoda,
            $pagination_params["page_number"] = $this->page_number;
            $pagination_params["count"] = $count_on_page;
            $pagination_params["total"] = $total;
            $pagination_params["route"] = $this->route;
            $pagination_params["route_params"] = array("action" => $action);
            $pagination_html = $this->renderPhp("admin/partials/admin_pagination.inc.php", $pagination_params, true);
            // echo $pagination_html; exit;

            // parametry pro skript s obsahem, uz mam neco pripraveno, NESMIM NULOVAT
            $content_params["dokumentace_list_name"] = "všichni"; // dle filtru
            $content_params["dokumentace_detail_action"] = "pacient_dokumentace_detail";
            $content_params["dokumentace_edit_action"] = "pacient_dokumentace_edit";
            $content_params["dokumentace_update_prepare_action"] = "obyvatel_update_prepare";
            $content_params["dokumentace_count"] = $count_on_page;
            $content_params["dokumentace_total"] = $total;
            $content_params["dokumentace_list"] = $dokumentace_list;
            $content_params["pagination_html"] = $pagination_html;

            // url pro vytvoreni obyvatele
            $content_params["url_dokumentace_add"] = $this->makeUrlByRoute($this->route, array("action" => "dokumentace_add"));

            // search
            $content_params["form_search_submit_url"] = "";
            $content_params["form_search_action"] = "obyvatele_list_search";

            // vysledek nejake akce
            $content_params["result_msg"] = $result_msg;
            $content_params["result_ok"] = $result_ok;

            //uzivatelske jmeno prihlaseneho uzivatele (abych byl pote schopen rozlisit prava zobrazeni udaju)
            $content_params["prihlaseny_uzivatel"] = "$prihlaseny_uzivatel_udaje[login]";


            //musíme si z POSTu "vytáhnout" informace o zadaném obyvateli a typu dokumentace (ani jedno nemusí být zadáno nebo může být zadáno chybně!!!)
            $zadano_vyhledavani = $this->loadRequestParam($request, "uzivatel_hledani", "post", null);

             $content_params["vyhledavani_jmeno"] = $zadano_vyhledavani["jmeno"];
             $content_params["vyhledavani_typ_dokumentace"] = $zadano_vyhledavani["dokumentace_typ"];

             //pokud uživatel nezadal ani jméno, ani typ dokumentace... pak vypisuji veškerou dokumentaci, na kterou má právo
            if($zadano_vyhledavani["jmeno"] == "" && $zadano_vyhledavani["dokumentace_typ"] == ""){
                $content_params["info_tabulka_zobrazeno"] = "V tabulce aktuálně zobrazena dokumentace <b>VŠECH</b> přístupných typů, pro <b>VŠECHNY</b> pacienty";
            //pokud mám zadané jenom jméno (a ne typ dokumentace) NEBO mám zadané jméno i typ (zpracováváno i v else větvi)
            }else if(($zadano_vyhledavani["jmeno"] != "" && $zadano_vyhledavani["dokumentace_typ"] == "") || ($zadano_vyhledavani["jmeno"] != "" && $zadano_vyhledavani["dokumentace_typ"] != "")){
                //zjistím, jestli daný obyvatel existuje...

                //rozdělím si vstup dle mezer (v DB jméno a příjmení zvlášť)
                $rozdelene_jmeno = explode(" ", $zadano_vyhledavani["jmeno"]);
                if(sizeof($rozdelene_jmeno) == 2){ //pokud jsem rozdělením dle mezery získal dvě slova, pak můžu pokračovat (jinak něco špatně - potřebuji minimálně jméno a příjmení) = NEBYLO SPECIFIKOVANO ID
                    $table_name = TABLE_OBYVATELE;
                    $where_array = array();
                    $podminky_hledani = array();
                    $podminky_hledani["jmeno"] = $rozdelene_jmeno[0];
                    $podminky_hledani["prijmeni"] = $rozdelene_jmeno[1];

                    foreach ($podminky_hledani as $key => $value) { //převedu na klíč -> hodnota
                        $where_array[] = $dokumentace_pacient->DBHelperGetWhereItem("$key", $podminky_hledani[$key]);
                    }

                    $limit_pom = "";
                    $obyvatele_jmeno = $dokumentace_pacient->DBSelectAll($table_name, "*", $where_array, $limit_pom); //obsahuje obyvatele, které mají odpovídající jméno a příjmení
                    $id_uzivatel = array(); //pole bude obsahovat id pacienta (ů), jejichž záznamy budu chtít zobrazit
                    if(sizeof($obyvatele_jmeno) > 0){
                        foreach ($obyvatele_jmeno as $jeden_obyvatel){
                            array_push($id_uzivatel, $jeden_obyvatel["id"]);
                        }

                        $dokumentace_list_edit = array();
                        //přidám záznamy, které odpovídají
                        foreach($dokumentace_list as $zaznam){
                            if(in_array($zaznam["obyvatel_id"], $id_uzivatel)){
                                array_push($dokumentace_list_edit, $zaznam);
                            }
                        }
                        $content_params["dokumentace_list"] = $dokumentace_list_edit;

                        if($zadano_vyhledavani["dokumentace_typ"] == ""){
                            $content_params["info_tabulka_zobrazeno"] = "V tabulce aktuálně zobrazena dokumentace <b>VŠECH</b> přístupných typů, pro pacienta <b>$rozdelene_jmeno[0] $rozdelene_jmeno[1]</b>";
                        }else{
                            $content_params["info_tabulka_zobrazeno"] = "V tabulce aktuálně zobrazena dokumentace typu <b>$zadano_vyhledavani[dokumentace_typ]</b>, pro pacienta <b>$rozdelene_jmeno[0] $rozdelene_jmeno[1]</b>";
                        }
                    }else{
                        $content_params["info_tabulka_zobrazeno"] = "Požadovaný pacient (<b>$zadano_vyhledavani[jmeno]</b>) neexistuje -> v tabulce zobrazena veškerá přístupná dokumentace";
                    }
                }else if(sizeof($rozdelene_jmeno) == 5){ //pravděpodobně bylo specifikováno ID
                    $rozdelene_jmeno[4] = substr($rozdelene_jmeno[4], 0, -1); //odstraním ], zbyde jen ID na 4. indexu

                    $table_name = TABLE_OBYVATELE;
                    $where_array = array();
                    $podminky_hledani = array();
                    $podminky_hledani["jmeno"] = $rozdelene_jmeno[0];
                    $podminky_hledani["prijmeni"] = $rozdelene_jmeno[1];
                    $podminky_hledani["id"] = $rozdelene_jmeno[4];

                    foreach ($podminky_hledani as $key => $value) { //převedu na klíč -> hodnota
                        $where_array[] = $dokumentace_pacient->DBHelperGetWhereItem("$key", $podminky_hledani[$key]);
                    }

                    $limit_pom = "";
                    $obyvatele_jmeno = $dokumentace_pacient->DBSelectAll($table_name, "*", $where_array, $limit_pom); //obsahuje obyvatele, které mají odpovídající jméno a příjmení
                    $id_uzivatel = array(); //pole bude obsahovat id pacienta (ů), jejichž záznamy budu chtít zobrazit
                    foreach ($obyvatele_jmeno as $jeden_obyvatel){
                        array_push($id_uzivatel, $jeden_obyvatel["id"]);
                    }

                    if(sizeof($id_uzivatel) > 0){
                        $dokumentace_list_edit = array();
                        //přidám záznamy, které odpovídají
                        foreach($dokumentace_list as $zaznam){
                            if(in_array($zaznam["obyvatel_id"], $id_uzivatel)){
                                array_push($dokumentace_list_edit, $zaznam);
                            }
                        }
                        $content_params["dokumentace_list"] = $dokumentace_list_edit;
                        $content_params["info_tabulka_zobrazeno"] = "V tabulce aktuálně zobrazena dokumentace <b>VŠECH</b> typů, pro pacienta <b>$rozdelene_jmeno[0] $rozdelene_jmeno[1] $rozdelene_jmeno[2] $rozdelene_jmeno[3] $rozdelene_jmeno[4]]</b>";
                    }else{
                        $content_params["info_tabulka_zobrazeno"] = "Požadovaný pacient (<b>$zadano_vyhledavani[jmeno]</b>) neexistuje -> v tabulce zobrazena veškerá přístupná dokumentace";
                    }
                }else{
                    $content_params["info_tabulka_zobrazeno"] = "Požadovaný pacient (<b>$zadano_vyhledavani[jmeno]</b>) neexistuje -> v tabulce zobrazena veškerá přístupná dokumentace";
                }
            }else if($zadano_vyhledavani["jmeno"] == "" && $zadano_vyhledavani["dokumentace_typ"] != ""){ //pokud mám zadaný jen typ dokumentace
                //zjistím, jestli daný typ dokumentace existuje
                if(!in_array($zadano_vyhledavani["dokumentace_typ"], $seznam_druhu_dokumentace)){
                    $content_params["info_tabulka_zobrazeno"] = "Požadovaný typ dokumentace (<b>$zadano_vyhledavani[dokumentace_typ]</b>) neexistuje nebo k němu nemáte povolený přístup -> v tabulce zobrazena veškerá přístupná dokumentace";
                }else{
                    $dokumentace_list_edit = array();
                    //přidám záznamy, které odpovídají
                    foreach($dokumentace_list as $zaznam){
                        if($zaznam["dokumentace_druh_text"] == $zadano_vyhledavani["dokumentace_typ"]){
                            array_push($dokumentace_list_edit, $zaznam);
                        }
                    }
                    $content_params["dokumentace_list"] = $dokumentace_list_edit;
                    $content_params["info_tabulka_zobrazeno"] = "V tabulce aktuálně zobrazena dokumentace typu <b>$zadano_vyhledavani[dokumentace_typ]</b>, pro <b>VŠECHNY</b> pacienty";
                }
            }
            //pokud mám zadáno jméno i příjmení
          if($zadano_vyhledavani["jmeno"] != "" && $zadano_vyhledavani["dokumentace_typ"] != ""){
              //možné problémy se jménem byly již vyřešeny v přecházející větví

              //došlo k problému už při hledání jména?
              $existuje_jmeno = false;
               if($content_params["info_tabulka_zobrazeno"] == "Požadovaný pacient (<b>$zadano_vyhledavani[jmeno]</b>) neexistuje -> v tabulce zobrazena veškerá přístupná dokumentace"){
                   $existuje_jmeno = false;
              }else{
                   $existuje_jmeno = true;
               }

              //existuje dokumentace požadovaného typu?
              $existuje_dokumentace= false;

              if(!in_array($zadano_vyhledavani["dokumentace_typ"], $seznam_druhu_dokumentace)){
                  $existuje_dokumentace = false;
              }else{
                  $existuje_dokumentace = true;
              }

              //řeším výpisy nad tabulkou
              if(!$existuje_jmeno && !$existuje_dokumentace){
                  $content_params["info_tabulka_zobrazeno"] = "Neexistuje požadovaný pecient (<b>$zadano_vyhledavani[jmeno]</b>), ani požadovaný typ dokumentace (<b>$zadano_vyhledavani[dokumentace_typ]</b>) -> v tabulce zobrazena veškerá přístupná dokumentace";
              }else if($existuje_jmeno && !$existuje_dokumentace){
                  $content_params["info_tabulka_zobrazeno"] = "Požadovaný typ dokumentace (<b>$zadano_vyhledavani[dokumentace_typ]</b>) neexistuje nebo k němu nemáte povolený přístup -> v tabulce zobrazena veškerá přístupná dokumentace pro pacienta <b>$zadano_vyhledavani[jmeno]</b>";
              }else if(!$existuje_jmeno && $existuje_dokumentace){

              }else{//pacient i typ dokumentace existují, mám roztříděno dle pacienta - následuje roztřídění dle typu dokumentace
                  $dokumentace_list_edit2 = array();
                  //přidám záznamy, které odpovídají
                  foreach($dokumentace_list_edit as $zaznam){
                      if($zaznam["dokumentace_druh_text"] == $zadano_vyhledavani["dokumentace_typ"]){
                          array_push($dokumentace_list_edit2, $zaznam);
                      }
                  }
                  $content_params["dokumentace_list"] = $dokumentace_list_edit2;
                  $content_params["info_tabulka_zobrazeno"] = str_replace("VŠECH</b> typů", "</b> typu <b>".$zadano_vyhledavani["dokumentace_typ"]."</b>", $content_params["info_tabulka_zobrazeno"]);
              }
          }
            $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_pacient_list.inc.php", $content_params, true);

        }else if($action == "pacient_dokumentace_detail"){ //zobrazení detailu dokumentace
          if($prihlaseny_uzivatel_udaje["login"] == "admin"){ //pokud přihlášený admin, tak ho přesměruji
              $admin_url =  $this->makeUrlByRoute($this->route, array("action" => "admin_no_access"));
              header("Location: $admin_url");
              exit();
          }

              if (!isset($dokumentace_id)) {
                  $dokumentace_id = $this->loadRequestParam($request,"dokumentace_id", "all", -1);
              }

          //má uživatel právo upravit daný typ záznamu?
          $count_on_page = 50; //počet položek na stránce
          $where_array = array();
          $dokumentace_login = $dokumentace_pacient -> getDokumentaceByLogin("$prihlaseny_uzivatel_udaje[login]","data", $this->page_number, $count_on_page, $where_array, "id", "asc");
          $id_pristupne_dokumentace = array();
          foreach($dokumentace_login as $dokumentace){
              array_push($id_pristupne_dokumentace, $dokumentace["id"]);
          }

          if(!in_array($dokumentace_id, $id_pristupne_dokumentace)){ //pokud se uživatel pokouší zobrazit dokumentaci, ke které nemá mít přístup
              $error_url =  $this->makeUrlByRoute($this->route, array("action" => "error_zobrazeni_pravo"));
              header("Location: $error_url");
              exit();
          }

              // nacist udaje o dokumentaci
              $dokumentace = $dokumentace_pacient->getItemByID($dokumentace_id);

          //má uživatel právo upravit daný typ záznamu?
          
          if ($dokumentace_id > 0 && $dokumentace != null) {
              $content_params["url_dokumentace_list"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_list_all"));
              $content_params["dokumentace_id"] = $dokumentace_id;
              $content_params["dokumentace"] = $dokumentace;
              $content_params["dokumentace_typ_zapisu"] = $dokumentace_pacient->getDruhDokumentaceByIDDokumentace($dokumentace["dokumentace_typ_zapisu_id"]);
              $content_params["pravo_zobrazeni"] = $dokumentace_pacient->getDruhDokumentaceByIDDokumentace($dokumentace["dokumentace_typ_zapisu_id"]);

              $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_detail.inc.php", $content_params, true);
          }
          else {
              $result_msg = "Dokumentace nenalezena - ID nebylo získáno z URL nebo daná dokumentace neexistuje.";
              $result_ok = false;
          }
      }else if($action == "pacient_dokumentace_edit"){
          if($prihlaseny_uzivatel_udaje["login"] == "admin"){ //pokud přihlášený admin, tak ho přesměruji
              $admin_url =  $this->makeUrlByRoute($this->route, array("action" => "admin_no_access"));
              header("Location: $admin_url");
              exit();
          }

          if (!isset($dokumentace_id)) {
              $dokumentace_id = $this->loadRequestParam($request,"dokumentace_id", "all", -1);
          }

          //má uživatel právo upravit daný typ záznamu?
          $count_on_page = 50; //počet položek na stránce
          $where_array = array();
          $dokumentace_login = $dokumentace_pacient -> getDokumentaceByLogin("$prihlaseny_uzivatel_udaje[login]","data", $this->page_number, $count_on_page, $where_array, "id", "asc");
          $id_pristupne_dokumentace = array();
          foreach($dokumentace_login as $dokumentace){
              array_push($id_pristupne_dokumentace, $dokumentace["id"]);
          }

          if(!in_array($dokumentace_id, $id_pristupne_dokumentace)){ //pokud se uživatel pokouší zobrazit dokumentaci, ke které nemá mít přístup
              $error_url =  $this->makeUrlByRoute($this->route, array("action" => "error_zobrazeni_pravo"));
              header("Location: $error_url");
              exit();
          }

          // nacist udaje o dokumentaci
          $dokumentace = $dokumentace_pacient->getItemByID($dokumentace_id);

          if ($dokumentace_id > 0 && $dokumentace != null) {
              $content_params["dokumentace_id"] = $dokumentace_id;
              $content_params["dokumentace"] = $dokumentace;
              $content_params["dokumentace_typ_zapisu"] = $dokumentace_pacient->getDruhDokumentaceByIDDokumentace($dokumentace["dokumentace_typ_zapisu_id"]);
              $content_params["pravo_zobrazeni"] = $dokumentace_pacient->getDruhDokumentaceByIDDokumentace($dokumentace["dokumentace_typ_zapisu_id"]);
              $content_params["url_dokumentace_list"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_list_all"));
              $content_params["url_dokumentace_remove"] = $this->makeUrlByRoute($this->route, array("action" => "dokumentace_remove"));
              $content_params["form_submit_url"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_dokumentace_edit_finish"));

              $content_params["napoveda_pristupne_kategorie"] = $dokumentace_pacient->getDokumentaceDruhZapisuPristup($prihlaseny_uzivatel_udaje["login"]);
              $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_edit.inc.php", $content_params, true);
          }else{
              $result_msg = "Dokumentace nenalezena - ID nebylo získáno z URL nebo daná dokumentace neexistuje.";
              $result_ok = false;
          }
      }else if($action == "dokumentace_remove"){
          if($prihlaseny_uzivatel_udaje["login"] == "admin"){ //pokud přihlášený admin, tak ho přesměruji
              $admin_url =  $this->makeUrlByRoute($this->route, array("action" => "admin_no_access"));
              header("Location: $admin_url");
              exit();
          }

          $dokumentace_pacient -> removeDokumentace($_SESSION["zobrazeno_id"]);

          $content_params["url_dokumentace_list"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_list_all"));
          $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_delete_finish.inc.php", $content_params, true);
      }else if($action == "pacient_dokumentace_edit_finish"){
          if($prihlaseny_uzivatel_udaje["login"] == "admin"){ //pokud přihlášený admin, tak ho přesměruji
              $admin_url =  $this->makeUrlByRoute($this->route, array("action" => "admin_no_access"));
              header("Location: $admin_url");
              exit();
          }

          //vytáhnu si data zadaná uživatelem
          $zadano_vyhledavani = $this->loadRequestParam($request, "uzivatel_zadano", "post", null);
          $id_dokumentace = $_SESSION["zobrazeno_id"];
          $id_uzivatel = $_SESSION["uzivatel_id"];
          $id_obyvatel = $_SESSION["obyvatel_id"];

          //existuje zadaný druh dokumentace a má k němu přihlášený uživatel přístup?
          $id_dokumentace_typ = $dokumentace_pacient -> getDokumentaceTypIDByText($zadano_vyhledavani["typ"]);
          if($id_dokumentace_typ != NULL){ //pokud daný typ dokumentace existuje
              //zjistím, jestli má přihlášený uživatel právo uložit daný typ dokumentace
              //vytáhnu si seznam názvů druhů dokumentace, ke kterým má přihlášená osoba přístup
              $seznam_druhu_dokumentace = $dokumentace_pacient->getDokumentaceDruhZapisuPristup($prihlaseny_uzivatel_udaje["login"]);
              $uzivatel_pravo_ulozit = $dokumentace_pacient -> isPravoUlozitZaznamUzivatel($id_dokumentace_typ["nazev"], $seznam_druhu_dokumentace);

              if($uzivatel_pravo_ulozit){ //pokud má přihlášený uživatel právo upravit daný záznam
                  $dokumentace_pacient -> updateDokumentace($id_dokumentace, $id_uzivatel, $id_obyvatel, $id_dokumentace_typ["id"], $zadano_vyhledavani["text"]);
                  $content_params["url_dokumentace_list"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_list_all"));
                  $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_edit_result.inc.php", $content_params, true);
              }else{ //pokud uživatel nemá právo záznam uložit
                  $content_params["error_text"] = "Nemáte právo přidělit zvolený typ záznamu - příště zvolte některý z nabízených typů dokumentace...";
                  $content_params["url_dokumentace_list"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_list_all")); //odkaz na seznam dokumentace
                  $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_add_error.inc.php", $content_params, true);
              }
          }else{//daný typ dokumentace vůbec neexistuje
              $content_params["error_text"] = "Zvolený typ záznamu neexistuje - příště zvolte některý z nabízených typů dokumentace...";
              $content_params["url_dokumentace_list"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_list_all")); //odkaz na seznam dokumentace
              $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_add_error.inc.php", $content_params, true);
          }
      }else if($action == "dokumentace_add"){
          if($prihlaseny_uzivatel_udaje["login"] == "admin"){ //pokud přihlášený admin, tak ho přesměruji
              $admin_url =  $this->makeUrlByRoute($this->route, array("action" => "admin_no_access"));
              header("Location: $admin_url");
              exit();
          }

          $content_params["form_submit_url"] = $this->makeUrlByRoute($this->route);
          $content_params["form_action_insert_dokumentace"] = "dokumentace_add_review";

          $content_params["url_dokumentace_list"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_list_all"));

          //pro vytvoření napovídání při přidání dokumentace budu potřebovat jména pacientů (všechna), příjmení pacientů (všechna), druhů zápisu (jen ty, pro které má přihlášená osoba oprávnění)
          //vytáhnu si jména pacientů
          $where_array = array();
          $limit_pom = "";
          $jmena_pacientu = $dokumentace_pacient->DBSelectAll(TABLE_OBYVATELE, "jmeno", $where_array, $limit_pom, "");
          //odstraním pole v poli
          $jmena_pacientu_edit = array();
          foreach($jmena_pacientu as $row){
              array_push($jmena_pacientu_edit, $row["jmeno"]);
          }
          $content_params["jmena_pacientu"] = $jmena_pacientu_edit;

          //vytáhnu si příjmení pacientů
          $prijmeni_pacientu = $dokumentace_pacient->DBSelectAll(TABLE_OBYVATELE, "prijmeni", $where_array, $limit_pom, "");
          //odstraním pole v poli
          $prijmeni_pacientu_edit = array();
          foreach($prijmeni_pacientu as $row){
              array_push($prijmeni_pacientu_edit, $row["prijmeni"]);
          }
          $content_params["prijmeni_pacientu"] = $prijmeni_pacientu_edit;

          //vytáhnu si seznam názvů druhů dokumentace, ke kteým má přihlášená osoba přístup
          $seznam_druhu_dokumentace = $dokumentace_pacient->getDokumentaceDruhZapisuPristup($prihlaseny_uzivatel_udaje["login"]);
          $content_params["druhy_zapisu"] = $seznam_druhu_dokumentace;

          $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_add.inc.php", $content_params, true);
      }else if($action == "dokumentace_add_review"){
          if($prihlaseny_uzivatel_udaje["login"] == "admin"){ //pokud přihlášený admin, tak ho přesměruji
              $admin_url =  $this->makeUrlByRoute($this->route, array("action" => "admin_no_access"));
              header("Location: $admin_url");
              exit();
          }

          // nacist data
          $dokumentace_new = $this->loadRequestParam($request, "dokumentace", "post", null);

          //existuje zadaný druh dokumentace a má k němu přihlášený uživatel přístup?
          $id_dokumentace_zadano = $dokumentace_pacient -> getDokumentaceTypIDByText($dokumentace_new["druh_zapisu"]);
          if($id_dokumentace_zadano != NULL){ //pokud daný typ dokumentace existuje
              //zjistím, jestli má přihlášený uživatel právo uložit daný typ dokumentace
              //vytáhnu si seznam názvů druhů dokumentace, ke kteým má přihlášená osoba přístup
              $seznam_druhu_dokumentace = $dokumentace_pacient->getDokumentaceDruhZapisuPristup($prihlaseny_uzivatel_udaje["login"]);
              $uzivatel_pravo_ulozit = $dokumentace_pacient -> isPravoUlozitZaznamUzivatel($dokumentace_new["druh_zapisu"], $seznam_druhu_dokumentace);
              if($uzivatel_pravo_ulozit){ //pokud má přihlášený uživatel právo uložit daný záznam
                  $pacienti = $dokumentace_pacient -> getPacientByParams($dokumentace_new);

                  $content_params["pacienti"] = $pacienti; //data pacientů, které chci vypsat
                  $content_params["dokumentace_nova_post"] = $dokumentace_new; //informace, které uživatel zadal ve formuláři pro tvorbu dokumentace
                  $content_params["url_dokumentace_list"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_list_all")); //odkaz na návrat zpět (seznam dokumentace)

                  //vytvořím si odkaz pro přidání dokumentace pro každého pacienta
                  foreach($pacienti as $pacient_odkaz){
                      $content_params["dokumentace_add_final_prechod".$pacient_odkaz["id"]] = $this->makeUrlByRoute($this->route, array("action" => "dokumentace_add_final".$pacient_odkaz["id"]));
                  }

                  $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_add_review.inc.php", $content_params, true);
              }else{ //pokud uživatel nemá právo záznam uložit
                  $content_params["error_text"] = "Nemáte právo uložit zvolený typ záznamu - příště zvolte některý z nabízených typů dokumentace...";
                  $content_params["url_dokumentace_list"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_list_all")); //odkaz na seznam dokumentace
                  $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_add_error.inc.php", $content_params, true);
              }
          }else{//daný typ dokumentace vůbec neexistuje
              $content_params["error_text"] = "Zvolený typ záznamu neexistuje - příště zvolte některý z nabízených typů dokumentace...";
              $content_params["url_dokumentace_list"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_list_all")); //odkaz na seznam dokumentace
              $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_add_error.inc.php", $content_params, true);
          }
      }else if(strpos($action, 'dokumentace_add_final') !== false){
          if($prihlaseny_uzivatel_udaje["login"] == "admin"){ //pokud přihlášený admin, tak ho přesměruji
              $admin_url =  $this->makeUrlByRoute($this->route, array("action" => "admin_no_access"));
              header("Location: $admin_url");
              exit();
          }

          $action = str_replace("dokumentace_add_final","",$action); //bude obsahovat pouze id pacienta, kteremu chci dokumentaci pridelit

          $zadana_data_dokumentace = $_SESSION["dokumentace_nova_post"]; //údaje, které uživatel zadal ve formuláři pro vytváření záznamu dokumentace
          $pacient = $dokumentace_pacient -> getPacientByID($action); //údaje pacienta, kterému chce uživatel přidat dokumentaci

          $content_params["url_dalsi_zaznam"] = $this->makeUrlByRoute($this->route, array("action" => "dokumentace_add")); //odkaz na přidání dalšího záznamu
          $content_params["url_dokumentace_list"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_list_all")); //odkaz na seznam dokumentace

          $data_uzivatele = $dokumentace_pacient -> getUzivatelByLogin($prihlaseny_uzivatel_udaje["login"]);
          $dokumentace_pacient -> saveDokumentacePacient($zadana_data_dokumentace, $pacient, $data_uzivatele); //provedu uložení do DB

          $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_add_result.inc.php", $content_params, true);
      }else if($action == "error_zobrazeni_pravo"){
          if($prihlaseny_uzivatel_udaje["login"] == "admin"){ //pokud přihlášený admin, tak ho přesměruji
              $admin_url =  $this->makeUrlByRoute($this->route, array("action" => "admin_no_access"));
              header("Location: $admin_url");
              exit();
          }

          $content_params["error_text"] = "Nemáte právo zobrazit dokumentaci s daným id...";
          $content_params["url_dokumentace_list"] = $this->makeUrlByRoute($this->route, array("action" => "pacient_list_all")); //odkaz na seznam dokumentace
          $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_add_error.inc.php", $content_params, true);
      }else if($action == "admin_no_access"){
          $content = $this->renderPhp(DS1_DIR_ADMIN_MODULES_FROM_ADMIN . "dokumentace_pacient/templates/dokumentace_no_access.inc.php", $content_params, true);
      }

        // vypsat hlavni template
        $main_params = array();
        $main_params["content"] = $content;
        $main_params["result_msg"] = $result_msg;
        $main_params["result_ok"] = $result_ok;

        return $this->renderAdminTemplate($main_params);
    }
}