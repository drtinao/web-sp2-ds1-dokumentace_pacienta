<?php

namespace ds1\core;

// symfony session
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;

class ds1 extends ds1_base_model
{
    public $loader = null;

    public $user_admin = null; // objekt admina
    public $user_manager = null; // sprava uzivatelu

    /** @var ds1_session */
    public $session = null;

    // todo
    public $helper = null;

    // moduly pro admina a pro web
    public $modules_admin = null;
    public $modules_web = null;

    // musi byt public, aby k tomu mohli vsichni primo
    public $symfony_url_generator = null;



    /**
     * se3 constructor.
     * @param $se3_loader - simple eshop 3 loader
     * @param $symfony_url_generator UrlGenerator
     */
    public function __construct($ds1_loader, $symfony_url_generator = null)
    {
        $this->loader = $ds1_loader;

        // symfony url generator - generuje url dle definice routes
        $this->symfony_url_generator = $symfony_url_generator;

        // vlastni session na obaleni
        $this->session = new ds1_session();
        $this->session->start();

        // vytvorit ostatni objekty, ktere budu potrebovat asi vzdy
        // pripadne by je slo prenest do index.php jako zavislosti
        $this->user_admin = new ds1_user_admin();

        $this->user_manager = new ds1_user_manager();

    }

    public function setModulesForAdmin($modules) {
        $this->modules_admin = $modules;
    }

    public function getModulesForAdmin() {
        return $this->modules_admin;
    }

    /**
     *  Prepsat connect u rodice
     */
    public function Connect()
    {
        // zavolat rodice
        parent::Connect();

        // doplnit neco vlastniho
        if ($this->GetPDOConnection() != null) {
            $connection = $this->GetPDOConnection();

            //echo "mam connection"; exit;
            // poslat to mym objektum
            $this->user_admin->SetPDOConnection($connection);
            $this->user_manager->SetPDOConnection($connection);
        }
    }

    public function getPokus() {
        return "pokus z ds1";
    }
}