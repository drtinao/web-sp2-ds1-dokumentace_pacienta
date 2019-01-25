<?php
/**
 * Toto je DEFAULTNI user manager controller.
 * Pouzije se tento controller, pokud nebyl prepsan lokalnim ze slozky ds1-local.
 */
namespace ds1\controllers_admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use ds1\core\ds1_base_controller;


class user_manager_controller extends ds1_base_controller
{
    // timto rikam, ze je NUTNE PRIHLASENI ADMINA
    protected $admin_secured_forced = true; // vynuceno pro jistotu, ale mel by stacit kontext admin

    public function indexAction(Request $request, $page = "")
    {
        // zavolat metodu rodice, ktera provede obecne hlavni kroky a nacte parametry
        parent::indexAction($request, $page);

        // KONTROLA ZABEZPECENI
        // test, jestli je uzivatel prihlasen, pokud NE, tak redirect na LOGIN
        $this->checkAdminLogged();

        // pomocne pro test bcryptu
        //$pom = $this->ds1->user_manager->bcryptPassword("admin");
        //echo $pom; exit;

        // info o uzivateli
        $uzivatel_id = $this->loadRequestParam($request,"uzivatel_id", "all", -1);
        if ($uzivatel_id > 0) {
            $uzivatel = $this->ds1->user_manager->getUserById($uzivatel_id);
        }

        // AKCE
        // action - typ akce
        $action = $this->loadRequestParam($request,"action", "all", "uzivatele_list_all");
        //echo "action: ".$action;

        // default zpravy
        $result_ok = true;
        $result_msg = "";

        // univerzalni content params
        $content_params = array();
        $content_params["base_url_link"] = $this->webGetBaseUrlLink();
        $content_params["page_number"] = $this->page_number;
        $content_params["route"] = $this->route;        // mam tam orders, je to automaticky z routingu
        $content_params["route_params"] = array();
        $content_params["controller"] = $this;

        $content = "";

        // AKCE - VYPISY

        // opravdu vytvorit uzivatele
        if ($action == "uzivatel_add_go") {
            // nacist data
            $uzivatel_new = $this->loadRequestParam($request, "uzivatel", "post", null);
            // printr($uzivatel_new); exit;

            // kontrola, ze neexistuje
            $pom = array();
            $pom["login"] = $uzivatel_new["login"];
            $existuje = $this->ds1->user_manager->adminExistsUzivatelByParams($pom);
            //printr($existuje);

            if (!$existuje) {
                // mohu zkusit vytvorit - musim volat tuto metodu, aby prosly kontroly
                $uzivatel_id = $this->ds1->user_manager->adminInsertUser($uzivatel_new);

                if ($uzivatel_id > 0) {
                    // ok
                    // prepnout na editaci uzivatele
                    $action = "uzivatel_update_prepare";
                }
                else {
                    // chyba
                    $result_ok = false;
                    $result_msg = "Uživatele se nepodařilo vytvořit. Souhlasí Vám hesla?";
                    $action = "uzivatel_add_prepare";
                }
            }
            else {
                $result_ok = false;
                $result_msg = "Tento uživatel již existuje. Nemohu ho přidat.";
                $action = "uzivatele_list_all";
            }
        }

        // formular pro vytvoreni noveho uzivatele
        if ($action == "uzivatel_add_prepare") {

            // prisel v parametru, data pro predvyplneni formu
            if (!isset($uzivatel_new)) $uzivatel_new = array();

            // parametry pro skript s obsahem - POZOR: nesmim je vynulovat, uz mam pripravenou cast
            $content_params["form_submit_url"] = $this->makeUrlByRoute($this->route);
            $content_params["form_action_insert"] = "uzivatel_add_go";
            $content_params["url_uzivatele_list"] = $this->makeUrlByRoute($this->route, array("action" => "uzivatele_list_all"));
            $content_params["uzivatel_new"] = $uzivatel_new;

            $content = $this->renderPhp("admin/user_manager/admin_user_insert_form.inc.php", $content_params, true);
        }

        if ($action == "uzivatel_update_go") {
            $uzivatel_new = $this->loadRequestParam($request, "uzivatel", "post", null);

            if ($uzivatel_id > 0 && $uzivatel_new != null) {
                // mohu provest update
                //printr($uzivatel_new); exit;

                // provest update
                $ok = $this->ds1->user_manager->adminUpdateItem($uzivatel_id, $uzivatel_new);

                if ($ok) {
                    $result_ok = true;
                    $result_msg = "Změny uživatele byly uloženy.";
                }
                else {
                    $result_ok = false;
                    $result_msg = "Změny uživatele se nepovedlo uložit.";
                }
            }

            // presun do detailu
            $action = "uzivatel_detail_show";
        }

        if ($action == "uzivatel_update_prepare") {

            if (!isset($uzivatel_id)) {
                // pokud nemam uzivatele, tak nactu z URL. Jinak uz ho MAM treba z INSERTu
                $uzivatel_id = $this->loadRequestParam($request,"uzivatel_id", "all", -1);
            }

            // musim si ho znova nacist kuli insertu
            if ($uzivatel_id > 0) {
                $uzivatel = $this->ds1->user_manager->getUserById($uzivatel_id);
            }

            if ($uzivatel_id > 0 && $uzivatel != null) {
                // vypis
                // parametry pro skript s obsahem - POZOR: nesmim je vynulovat, uz mam pripravenou cast
                $content_params["uzivatel_id"] = $uzivatel_id;
                $content_params["uzivatel"] = $uzivatel;
                $content_params["form_submit_url"] = $this->makeUrlByRoute($this->route);
                $content_params["form_action_update_go"] = "uzivatel_update_go";
                $content_params["url_uzivatele_list"] = $this->makeUrlByRoute($this->route, array("action" => "uzivatele_list_all"));

                $content = $this->renderPhp("admin/user_manager/admin_user_update_form.inc.php", $content_params, true);
            }
            else {
                $result_msg = "Uzivatel nenalezen - ID nebylo získáno z URL nebo uzivatel neexistuje.";
                $result_ok = false;

                $action = "uzivatele_list_all";
            }
        }

        if ($action == "uzivatel_detail_show") {
            // info o uzivateli si MUSIM nacist znova, pokud je to z insertu nebo updatu
            if ($uzivatel_id > 0) {
                $uzivatel = $this->ds1->user_manager->getUserById($uzivatel_id);
            }

            if ($uzivatel_id > 0 && $uzivatel != null) {
                // vypis
                $content_params["uzivatel"] = $uzivatel;
                $content_params["url_uzivatel_update"] = $this->makeUrlByRoute($this->route, array("action" => "uzivatel_update_prepare", "uzivatel_id" => $uzivatel_id));
                $content_params["url_uzivatele_list"] = $this->makeUrlByRoute($this->route, array("action" => "uzivatele_list_all"));

                $content = $this->renderPhp("admin/user_manager/admin_user_detail_show.inc.php", $content_params, true);
            }
            else {
                $result_msg = "Uživatel nenalezen - ID nebylo získáno z URL.";
                $action = "uzivatele_list_all";
            }
        }

        if ($action == "uzivatele_list_all") {
            // vypsat vsechny uzivatele

            $count = 50;
            $where_array = array();
            // count_on_page a page se u prikazu count neuvazuje
            $total = $this->ds1->user_manager->adminLoadItems("count", 1, 1, $where_array);
            //echo "total: $total"; exit;

            // vygenerovat strankovani - obecna metoda
            $pagination_params["page_number"] = $this->page_number;
            $pagination_params["count"] = $count;
            $pagination_params["total"] = $total;
            $pagination_params["route"] = $this->route;
            $pagination_params["route_params"] = array();
            $pagination_html = $this->renderPhp("admin/partials/admin_pagination.inc.php", $pagination_params, true);
            // echo $pagination_html; exit;

            // parametry pro skript s obsahem
            $content_params["uzivatele_list_name"] = "všichni"; // dle filtru
            $content_params["action_uzivatel_detail"] = "uzivatel_detail_show";
            $content_params["action_uzivatel_update_prepare"] = "uzivatel_update_prepare";
            $content_params["users_count"] = $count;
            $content_params["users_total"] = $total;
            //$content_params["search_params"] = $search_params;
            $content_params["uzivatele_list"] = $this->ds1->user_manager->adminLoadItems("data", $this->page_number, $count, $where_array, "prijmeni", "asc");
            $content_params["pagination_html"] = $pagination_html;

            $content_params["url_uzivatel_add_prepare"] = $this->makeUrlByRoute($this->route, array("action" => "uzivatel_add_prepare"));

            $content = $this->renderPhp("admin/user_manager/admin_users_list.inc.php", $content_params, true);
        }

        // renderovat hlavni sablonu
        $main_params = array();
        $main_params["result_ok"] = $result_ok;
        $main_params["result_msg"] = $result_msg;
        $main_params["content"] = $content;
        $main_params["title"] = "Registrovaní uživatelé";

        // vypsat hlavni template
        return $this->renderAdminTemplate($main_params);

        // pomocne
        //return new Response("Admin users eshop controller");
    }
}