<?php
/**
 * Toto je DEFAULTNI homepage controller. Tento controller by se mel prepsat controllerem
 * v lokalni slozce ds1-local.
 */
namespace ds1\controllers_web;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use simple_eshop3\core\ds1_base_controller;


class homepage_controller extends ds1_base_controller
{
    public function indexAction(Request $request, $page = "")
    {
        // zavolat metodu rodice, ktera provede obecne hlavni kroky a nacte parametry
        parent::indexAction($request, $page);

        // TODO podle parametru si mohu pripadne zavolat dalsi metody v Controlleru

        // renderovat hlavni sablonu
        $main_params = array();
        $main_params["content"] = "Default hompeage controller";
        $main_params["title"] = "Titulek";

        // vypsat hlavni template
        return $this->renderMainTemplate($main_params);
    }
}