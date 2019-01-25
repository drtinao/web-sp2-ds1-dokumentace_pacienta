<?php

namespace ds1\core;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Twig_Loader_Filesystem;
use Twig_Environment;

// nedelam extends Controller, protoze nemam ostatni sluzby
class ds1_base_controller
{
    protected $ds1_loader = null;

    /** @var ds1 */
    public $ds1 = null;

    /** @var Twig_Environment */
    public $twig = null;


    // musi byt protected, aby se k tomu dalo dostat z oddedeneho objektu
    // parametry z url
    protected $page = "";
    protected $page_number = 1; // stranka v ramci strankovani
    protected $action = "";
    protected $action2 = "";

    protected $route = "";
    protected $route_params = array();

    // standardne to neni zabezpecene
    protected $admin_secured = false;
    protected $admin_secured_forced = false;    // jestli je vynuceno zabezpeceni
    protected $admin_public_forced = false;     // jestli je vynucen verejny pristup, napr. pro nejake exporty?

    /** Tato metoda se muze zavolat ze vsech Controlleru a provede nacteni Simple eshop 3
     * a provede zakladni pripravu napr. action, action2 apod.
     * POZOR: CONSTRUCT NELZE POUZIT
     * @param $request
     */
    public function indexAction(Request $request, $page = "")
    {
        // nacist simple eshop 3
        $this->init($request);

        // ANALYZA DALSICH PARAMETRU
        if ($page != "") {
            $this->page = $page;    // ulozit page primo
        }
        else {
            // fixace - podivat se, jestli page nemam jen v parametru
            $page_pom = $this->loadRequestParam($request, "page", "all", "");
            if ($page_pom != "") {
                $this->page = $page_pom;
            }
        }

        // STRANKOVANI START - NESAHAT
        // pro verejny web - jinak nez pres atributy to nejde
        $page_number = $request->attributes->get("page_number") ;  // pokud nejde page primo
        if ($page_number != "") {
            $this->page_number = $page_number + 0;    // ulozit page_number pod stranka
        }
        else {
            // zkusit dohledat jinak - pro ADMINA
            // cislo stranky
            $page_number = $this->loadRequestParam($request, "page_number", "all", 1);

            //  $page_number = 1;
            $this->page_number = $page_number;
        }
        // STRANKOVANI KONEC - NESAHAT

        // pokus page
        //echo "page pom: ".$this->page.", page_number: ".$this->page_number; exit;

        // route a route params si presunout od requestu
        $this->route = $request->attributes->get("_route");
        $this->route_params = $request->attributes->get("_route_params");

        // zkusit nacist dalsi parametry
        $this->action = $this->loadRequestParam($request,"action");
        $this->action2 = $this->loadRequestParam($request,"action2");
    }


    /**
     * Nacte parametr podle jmena z GET nebo POST, podle toho, kde je k dispozici
     * @param $type = all, get, post
     * @return $pom
     */
    public function loadRequestParam($request, $name, $type = "all", $default = "") {

        $pom = "";
        if ($type == "get") {
            $pom = $request->query->get($name, $default);         // GET
        }
        else if ($type == "post") {
            $pom = $request->request->get($name, $default);     // POST
        }
        else if ($type == "all") {

            if (isset($request->query))
                $pom = $request->query->get($name, "");         // GET

            if ($pom == "" && isset($request->request))
                $pom = $request->request->get($name, $default);     // POST
        }

        return $pom;
    }


    /**
     * @return string - IP adresa
     */
    public function getIp() {

        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }


    /**
     * Pomocna metoda pro stazeni obsahu z URL. Pro recaptchu i ostatni veci.
     * Zkousi ruznymi zpusoby ziskat data.
     * @param $url
     * @return bool|mixed|string
     */
    public function file_get_contents_from_url($url) {

        // napred standardni file_get_contents
        $content = @file_get_contents($url);
        if (trim($content) != "") {
            return $content;
        }

        // pokud nejde, tak zkusim curl
        if (trim($content) == "") {
            // zkusim pres curl
            //echo "zkousim curl";
            if (!function_exists('curl_init')){
                return "";
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'SimpleEshop3-api');
            $content = curl_exec($ch);
            $info = curl_getinfo($ch);

            // pokud chci vypisovat debug
            if ($content === false || $info['http_code'] != 200 && DS1_DOMAIN_IN_PRODUCTION == false) {
                $content = "No cURL data returned for $url [". $info['http_code']. "]";
                if (curl_error($ch))
                    $content .= "\n". curl_error($ch);

                echo $content;
            }

            // zavrit handle
            curl_close($ch);
        }

        return $content;
    }

    /**
     * Nacte twig, pokud neni k dispozici.
     */
    public function loadTwigIfRequired()
    {
        if ($this->twig == null) {

            // echo DS1_DIR_TEMPLATES_TWIG_LOCAL; exit;
            if (DS1_CONTEXT == DS1_CONTEXT_VALUE_WEB) {
                // je to pro web
                $loader = new Twig_Loader_Filesystem(DS1_DIR_TEMPLATES_TWIG_LOCAL);
            }
            else if (DS1_CONTEXT == DS1_CONTEXT_VALUE_ADMIN) {
                // je to pro admina
                if (file_exists(DS1_DIR_TEMPLATES_TWIG_ADMIN)) {
                    // cely admin bude z lokalni nebo globalni slozky, NELZE MICHAT u TWIGU
                    $loader = new Twig_Loader_Filesystem(DS1_DIR_TEMPLATES_TWIG_ADMIN);
                }
            }

            // bez cache
            //$this->twig = new Twig_Environment($loader);

            // s cache
            $this->twig = new Twig_Environment($loader, array(
                'cache' => DS1_DIR_TEMPLATES_TWIG_CACHE,
                'auto_reload' => true));
        }

    }


    /**
     * Nacist simple eshop 3 z atributu requestu
     * @param $request Request
     */
    public function init(Request $request) {

        // 1. primo DS1
        $ds1 = $request->attributes->get("ds1");
        if ($ds1 != null) $this->ds1 = $ds1;

        // 2. nastavit vsem objektum - controller se pouzije pro pristup k datum
        if ($this->ds1->user_admin != null)
            $this->ds1->user_admin->setController($this);


        // 3. DEFAULTNI ZABEZPECENI - pokud je context ADMIN, tak je to defaultne zabezpecene nebo je to vynuceno
        if (DS1_CONTEXT == DS1_CONTEXT_VALUE_ADMIN) {
            $this->admin_secured = true;
        }
        else {
            $this->admin_secured = false;
        }

        // VYNUCENO ZABEZPECENI
        if ($this->admin_secured_forced) {
            $this->admin_secured = true;
        }

        // VYNUCEN VEREJNY PRISTUP
        if ($this->admin_public_forced) {
            $this->admin_secured = false;
        }

        // KONTROLA PRIHLASENEHO ADMINA U ZABEZPECENEHO PRISTUPU
        if ($this->admin_secured) {
            // echo "secured";
            $this->checkAdminLogged();
        }
        else {
            // SEM SE DOSTANE PUBLIC WEB
            //echo "public"; exit;
        }
    }


    public function redirectUser($url) {
        header("Location: ".$url);
        exit; // musi tady zustat, aby se uz nic jineho neprovadelo
    }


    /**
     * Tato metoda zkontroluje, jestli je admin prihlasen a pokud ne,
     * tak ho posle na login page.
     */
    public function checkAdminLogged() {

        if ($this->ds1->user_admin->isAdminLogged()) {
            // ano, je prihlasen - je to OK, nechat ho
            // nemusim nic delat
        }
        else {
            // NE, neni prihlasen - redirect na login page
            // echo "ADMIN NENI PRIHLASEN. NELZE POKRACOVAT.";

            // NATVRDO HO PRESMEROVAT na LOGIN PAGE PRO ADMINA
            //$admin_login_url = $this->makeUrlByRoute(DS1_ROUTE_ADMIN_LOGIN); // nepridava tam index.php
            $admin_login_url = $this->webGetBaseUrlLink(). "login";
            //echo $admin_login_url; exit;

            $this->redirectUser($admin_login_url);
        }
    }

    /**
     * Renderuj twig sablonu.
     *
     * @param $template_url
     * @param array $params - parametry pro sablonu
     * @param bool $return_html
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderTwig($template_url, $params = array(), $return_html = false)
    {
        $this->loadTwigIfRequired();

        $html = $this->twig->render($template_url, $params);

        if ($return_html) {
            return $html;
        }
        else {
            // vratit response
            return new Response($html);
        }
    }

    /**
     * @param $template_url
     * @param array $params - parametry pro sablonu
     * @param bool $return_html
     * @return Response
     */
    public function renderPhp($template_url, $params = array(), $return_html = false)
    {
        $html = $this->renderPhpTemplate($template_url, $params);

        if ($return_html)
        {
            return $html;
        }
        else {
            // vratit response
            return new Response($html);
        }
    }


    private function renderPhpTemplate($template_url, $params = array()) {
        $template_path = DS1_DIR_TEMPLATES_PHP_LOCAL . $template_url;

        // pokud neexistuje lokalni, tak zkusit globalni verzi
        if (!file_exists($template_path)) {
            $template_path = DS1_DIR_TEMPLATES_PHP_GLOBAL . $template_url;

            // zkusit samostatne
            if (!file_exists($template_path)) {
                $template_path = $template_url;
                if (file_exists($template_path)) {
                    // je to ok
                }
            }
        }



        // text existence sablony
        if (file_exists($template_path) && !is_dir($template_path))
        {
            // extrahovat promenne z parametru
            extract($params);

            // start generovani sablony
            ob_start();
            include $template_path;
            $html = ob_get_clean(); // konec generovani sablony a ulozeni do promenne

            return $html;
        }
        else {
            if (DS1_DOMAIN_IN_PRODUCTION)
                return "Error: template not found";
            else
                return "Error: template (url: $template_url, path: $template_path) not found";
        }
    }


    /**
     * Vygenerovat URL dle routy.
     * @param $route
     * @param $route_params
     * @param $absolute = false - jestli ma pridat domenu a https
     * @return string
     */
    public function makeUrlByRoute($route, $route_params = array(), $absolute = false) {
        $url = $this->ds1->symfony_url_generator->generate($route, $route_params, $absolute);
        return $url;
    }


    /**
     * Vrati primo true nebo false. Funguje pro frontend i admina.
     */
    public function isRecaptchaOK() {
        // overit captchu
        $success = true; // defaultne je to ok
        $g_recaptcha_response = @$_POST["g-recaptcha-response"]; // pozor, vsude si musim hlidat tento klic
        $user_remote_ip = $this->getIp();
        $google_url = RECAPTCHA_CHECK_URL."?secret=".RECAPTCHA_SECRET_KEY."&response=$g_recaptcha_response&remoteip=$user_remote_ip";
        $google_response = $this->file_get_contents_from_url($google_url);

        if ($google_response != "")
        {
            $google_response = @json_decode($google_response);
            // print_r($google_response);

            if (isset($google_response->success)) {
                $success = $google_response->success;
            }
            else
                $success = false;

            if (trim($success) == "") $success = false;
            //echo $success;
        }
        else
        {
            $success = false;
        }

        return $success;
    }


    /**
     * Pomocna metoda, ktera upravi string pro url
     * @param $str
     */
    public function fixStringForUrl($str) {
        $url = $str;

        $url = strtolower($url);
        $url = strip_tags($url);
        $url = stripslashes($url);
        $url = html_entity_decode($url);

        # Remove quotes (can't, etc.)
        $url = str_replace('\'', '', $url);

        # Replace non-alpha numeric with hyphens
        $match = '/[^a-z0-9]+/';
        $replace = '-';
        $url = preg_replace($match, $replace, $url);

        $url = trim($url, '-');

        return $url;
    }

    /**
     * Vrati zaklad URL pro tento web. Vraci cestu bez index.php
     */
    public function webGetBaseUrl()
    {
        // do params prihodit base_url - pouziva se pro css a js
        // https://
        if (DS1_DOMAIN_USE_HTTPS)
            $base_url = "https://";
        else $base_url = "http://";

        // domena - prvni je napr. localhost a druhe cesta na lokalhostu
        $base_url .= DS1_DOMAIN_URL.DS1_DOMAIN_RELATIVE_PATH_ADD."/";

        return $base_url;
    }

    /**
     * Vrati zaklad URL pro tento web. Vraci cestu S index.php
     */
    public function webGetBaseUrlLink()
    {
        $base_url = $this->webGetBaseUrl();
        //echo "base url: $base_url"; exit;

        // pridat index.php pokud nejsou hezke URL nebo pokud jde o admina, tam to nepotrebuji a zatim to zlobi
        if (DS1_DOMAIN_USE_FRIENDLY_URL == false || DS1_CONTEXT == DS1_CONTEXT_VALUE_ADMIN)
            $base_url .= "index.php/";

        return $base_url;
    }

    /**
     * Explicitne vyhodi cast index.php, aby cesta k obrazku nesla pres routing.
     * @return string - abs nebo rel
     */
    public function webGetBaseUrlForFiles($type = "rel")
    {
        if ($type == "rel") {

            // jenom cesta na dane domene s lomitkem na zacatku
            $base_url = DS1_DOMAIN_RELATIVE_PATH_ADD."/";
            return $base_url;
        }
        else
        {
            // absolute
            $base_url = $this->webGetBaseUrl();
            //echo "base url pom: $base_url ///";
            $base_url = str_replace("index.php/", "", $base_url);
        }


        return $base_url;
    }


    /**
     * Generovani hlavni sablony pro ADMINA.
     *
     * @param array $params - parametry hlavni sablony
     * @param string $type - default nebo login nebo empty
     * @return Response
     */
    public function renderAdminTemplate($params = array(), $type = "default")
    {
        // posledni kontrola u vypisu sablony ADMINA:
        if ($this->ds1->user_admin->isAdminLogged() == false) {

            // NENI PRIHLASEN, to je potencialni problem
            if ($this->admin_public_forced != true) {
                // vypada to na chybu, tak to radeji ukoncit, aby se mi tam nikdo nedostal
                echo "<h1>503 - chyba serveru</h1>";
                echo "<p>Popis chyby: chyba v renderAdminTemplate.</p>";
                exit;
            }
        }

        // pridat menu vlevo
        $subtemplate_params = array();
        $subtemplate_params["base_url_link"] = $this->webGetBaseUrlLink();
        $subtemplate_params["controller"] = $this;
        $params["admin_leftmenu"] = $this->renderPhp("admin/partials/admin_leftmenu.inc.php", $subtemplate_params, true);


        // pridat breadcrumbs ze sablony, ktera si je dopocita
        $subtemplate_params = array();
        $subtemplate_params["base_url_link"] = $this->webGetBaseUrlLink();
        $params["admin_breadcrumbs"] = $this->renderPhp("admin/partials/admin_breadcrumbs.inc.php", $subtemplate_params, true);


        // kontrola povinnych atributu
        if ($params == null) $params = array();
        if (!isset($params["content"])) $params["content"] = "";

        if (!isset($params["title"])) $params["title"] = "";
        if (!isset($params["meta_description"])) $params["meta_description"] = "";
        if (!isset($params["meta_keywords"])) $params["meta_keywords"] = "";

        // specialne pro admina
        if (!isset($params["admin_breadcrumbs"])) $params["admin_breadcrumbs"] = "";
        if (!isset($params["alert_number_new"])) $params["alert_number_new"] = 0;       // pocet novych obj.

        // pouzit twig?
        if (DS1_MAIN_TEMPLATE_ADMIN_USE_TWIG)
        {
            $this->loadTwigIfRequired();

            // extract parametry - tady nedelam
            //extract($params);

            // pridat do parametru
            $params["base_url"] = $this->webGetBaseUrl();
            $params["base_url_link"] = $this->webGetBaseUrlLink();

            // info o aktualni route
            $params["route"] = $this->route;
            $params["route_params"] = $this->route_params;

            // zmenit pro admina
            if ($type == "default") {
                // STANDARDNI ADMINISTRACE
                $template_url = DS1_MAIN_TEMPLATE_ADMIN;
            }
            else if ($type == "login") {
                // POUZE LOGIN TEMPLATE
                $template_url = DS1_MAIN_TEMPLATE_ADMIN_LOGIN;
            }
            else  if ($type == "empty") {
                // prazdna
                $template_url = DS1_MAIN_TEMPLATE_ADMIN_EMPTY;
            }

            $html = $this->twig->render($template_url, $params);
            return $this->result($html);
        }
    }

    /**
     * Vrati Response pro navrat z controlleru
     *
     * @param $content
     * @param $type
     * @param $encoding
     * @return Response $Response
     */
    public function result($content, $type = "", $encoding = "") {
        return new Response($content);
    }


    // ***************************************************************************************************
    // ***********    START POMOCNE METODY PRO URYCHLENI PRACE   *****************************************
    // ***************************************************************************************************

    /**
     * Automaticka metoda, ktera prevede datum z defaultniho formatu DB do formatu pro CR.
     *
     * @param $date_string - datum nebo datum a cas
     * @return false|string|void
     */
    public function helperFormatDateAuto($date_string)
    {
        // pokud prazdne, tak vratim prazdny retezec
        if (trim($date_string) == "") return;
        if (trim($date_string) == "0000-00-00 00:00:00") return;
        if (trim($date_string) == "0000-00-00") return;

        // pro experimenty
        //$date = date_create_from_format("Y-m-d", "1990-01-01");

        // prevedu vstupni datum
        $input_format = "Y-m-d"; // standardni date z DB
        $date = date_create_from_format($input_format, $date_string);

        if ($date == false) {
            //echo "nepovedlo prevest";

            // zkusit, jestli to neni datetime
            $input_format = "Y-m-d H:i:s";
            $date = date_create_from_format($input_format, $date_string);
            if ($date != false) {
                $output_format = 'j. n. Y H:i';
                $date_for_output = date_format($date, $output_format);

                return $date_for_output;
            }

            return;
        }
        else {
            $output_format = 'j. n. Y';
            $date_for_output = date_format($date, $output_format);
            return $date_for_output;
        }
    }

    /**
     * Striktni univerzalni metoda, ktera prevede datum z defaultniho formatu DB do formatu pro CR.
     *
     * @param $date_string
     * @param string $input_format
     * @param string $output_format
     * @return false|string|void
     */
    public function helperFormatDate($date_string, $input_format = "Y-m-d", $output_format = 'j. n. Y') {

        // pokud prazdne, tak vratim prazdny retezec
        if (trim($date_string) == "") return;
        if (trim($date_string) == "0000-00-00 00:00:00") return;
        if (trim($date_string) == "0000-00-00") return;

        // pro experimenty
        //$date = date_create_from_format("Y-m-d", "1990-01-01");

        // prevedu vstupni datum
        $date = date_create_from_format($input_format, $date_string);

        if ($date == false) {
            //echo "nepovedlo prevest";
            return;
        }
        else {
            $date_for_output = date_format($date, $output_format);
            return $date_for_output;
        }
    }

    /**
     * Testuje, jestli je datum v minulosti.
     * @param $date
     */
    public function helperIsDateInPast($date) {

    }

    /**
     * Pomocna metoda pro zobrazeni dat v tabulce. Často se opakuje.
     *
     * @param $data
     * @param array $selected_columns
     * @param $titles
     * @param bool $return_html
     * @return string
     */
    public function helperPrintBasicTable($data, $selected_columns = array(), $titles, $return_html = true) {

        $content_params["base_url_link"] = $this->webGetBaseUrlLink();
        $content_params["data"] = $data;
        $content_params["selected_columns"] = $selected_columns;
        $content_params["titles"] = $titles;

        $content = $this->renderPhp("partials/print_basic_table.inc.php", $content_params, true);

        // vratit vysledek
        if ($return_html) return $content;
        else {
            echo $content;
        }
    }

    public function helperPrintBasicTableRecords($data, $selected_columns = array(), $titles, $return_html = true) {

        $content_params["base_url_link"] = $this->webGetBaseUrlLink();
        $content_params["data"] = $data;
        $content_params["selected_columns"] = $selected_columns;
        $content_params["titles"] = $titles;

        $content = $this->renderPhp("partials/print_basic_table_records.inc.php", $content_params, true);

        // vratit vysledek
        if ($return_html) return $content;
        else {
            echo $content;
        }
    }

    // ***************************************************************************************************
    // ***********    KONEC POMOCNE METODY PRO URYCHLENI PRACE   *****************************************
    // ***************************************************************************************************
}