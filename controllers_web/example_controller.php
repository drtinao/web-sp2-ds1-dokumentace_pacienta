<?php

namespace ds1\controllers_web;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use ds1\core\ds1_base_controller;


/**
 * Class example_controller
 *
 * Tento controller SLOUZI POUZE PRO UKAZKU A TESTY. Je soucasti zakladniho baliku se3
 * @package simple_eshop3\controllers_web
 */
class example_controller extends ds1_base_controller
{
    public function indexAction(Request $request, $page = 1)
    {
        // pristup k promenne pres request
        //$title = $request->attributes->get('title');
        //echo $title;

        // nacist simple eshop 3
        parent::indexAction($request, "");

        // takto se dostanu k parametrum z URL poslanym primo ?action=pokus
        //$action = $request->query->get('action');         // GET
        //$action = $request->request->get('action');     // POST
        //echo $action; exit;

        //$all = $request->attributes->all();
        //print_r($all); exit;


        //$zakaznik = $this->se3->goods->loadAllZakaznici();
        //print_r($zakaznik);


        $text = $this->ds1->getPokus();
        $params = array("jmeno" => "Martin", "text" => $text);

        //$html = $this->renderTwig("pokus.twig", $params, true);
        $html = $this->renderPhp("pokus.inc.php", $params, true);
        return $this->result("Example controller z objektu: $html");

        // vratit response
        //return $this->renderTwig("pokus.twig", $params);
    }


    /**
     * @Route("/blog", name="blog")
     */
    public function blogAction(Request $request)
    {
        return $this->result('blog - route /blog');
    }

    public function odpadAction(Request $request)
    {
        return new Response('odpad action - neni chyceno jinde');
    }

    /**
     * @Route("/blog/{slug}", name="blog")
     */
    public function blogSlugAction(Request $request, $slug)
    {
        return new Response("blog - route /blog - slug: $slug");
    }

    /**
     * Matches /pokus exactly
     *
     * @Route("/pokus", name="home_pokus")
     */
    public function pokusAction(Request $request)
    {
        return new Response('Pokus');
    }


    public function noYearAction(Request $request)
    {
        return new Response('Nebyl zadan rok.');
    }
}