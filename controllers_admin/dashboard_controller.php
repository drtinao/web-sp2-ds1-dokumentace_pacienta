<?php
/**
 * Toto je DEFAULTNI dashboard ADMIN controller. Tento controller by se mel prepsat controllerem
 * v lokalni slozce ds1-local.
 */
namespace ds1\controllers_admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use ds1\core\ds1_base_controller;


class dashboard_controller extends ds1_base_controller
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


        // parametry pro skript s obsahem
        $content_params = array();

        // nacist obsah pro dashboard - pres twig sablonu
        $content_params["base_url_link"] = $this->webGetBaseUrlLink();
        //$content_params["form_submit_url"] = $this->makeUrlByRoute(SE3_ROUTE_ADMIN_LOGIN);
        //$content_params["form_action"] = "login_go";
        $content = $this->renderTwig("pages/admin_dashboard.twig", $content_params, true);

        // renderovat hlavni sablonu
        $main_params = array();
        $main_params["content"] = $content;
        $main_params["title"] = "Nástěnka";

        // vypsat hlavni template
        return $this->renderAdminTemplate($main_params);
        //return new Response("Admin dashboard ready");
    }

    /**
     * Sem to muze prijit v pripade: 404, nebo treba obrazku
     * @param Request $request
     * @param $action tady je obsazeno url
     * @return Response
     */
    public function odpadAction(Request $request, $action) {

        // 404 nebo Login
        return new Response("Chyba 404 - admin odpad action");
    }
}