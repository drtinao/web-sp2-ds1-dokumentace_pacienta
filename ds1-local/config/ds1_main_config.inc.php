<?php
/**
 * Tento skript obsahuje univerzalni veci pro dev i produkci.
 */

    // ****************************************************************************************
    // ***********    START CESTY A ADRESARE      *********************************************
    // ****************************************************************************************

        // cesta z homepage webu do rootu projektu
        define("DS1_PROJECT_ROOT_FROM_WEB", '../');
        define("DS1_PROJECT_ROOT_FROM_ADMIN", '../../');


        // zvolit project root dle contextu - definuje se primo v index.php
        if (DS1_CONTEXT == DS1_CONTEXT_VALUE_WEB) {
            define("DS1_PROJECT_ROOT", DS1_PROJECT_ROOT_FROM_WEB);
        } else {
            define("DS1_PROJECT_ROOT", DS1_PROJECT_ROOT_FROM_ADMIN);
        }

        // cesta z PROJECT_ROOT
        define("DS1_DIR_ROOT", "ds1-core/");               // hlavni repozitar
        define("DS1_DIR_ROOT_LOCAL", "ds1-local/");   // lokalni modifikace a konfigurace

        // cesta k admin modules z admina
        define("DS1_DIR_ADMIN_MODULES_FROM_ADMIN", DS1_PROJECT_ROOT_FROM_ADMIN.DS1_DIR_ROOT_LOCAL."admin_modules/");

        // adresar s twigem
        define("DS1_DIR_TEMPLATES_TWIG_LOCAL", DS1_PROJECT_ROOT.DS1_DIR_ROOT_LOCAL."templates_twig/");
        define("DS1_DIR_TEMPLATES_TWIG_CACHE", DS1_PROJECT_ROOT."cache/twig/");

        // adresar s twigem pro admina - VOLBA ADRESARE PRO ADMINA - musi byt prave jeden
        define("DS1_DIR_TEMPLATES_TWIG_ADMIN", DS1_PROJECT_ROOT.DS1_DIR_ROOT."templates_twig/");

        // adresar s lokalnimi sablonami
        define("DS1_DIR_TEMPLATES_PHP_LOCAL", DS1_PROJECT_ROOT.DS1_DIR_ROOT_LOCAL."templates_php/");

        // adresar s globalnimi sablonami - typicky pro ADMINA
        define("DS1_DIR_TEMPLATES_PHP_GLOBAL", DS1_PROJECT_ROOT.DS1_DIR_ROOT."templates_php/");

        // WEB - volba hlavni sablony
        define("DS1_MAIN_TEMPLATE_WEB", "sablona.twig");
        define("DS1_MAIN_TEMPLATE_WEB_USE_TWIG", true);

        // ADMIN - volba hlavni sablony
        define("DS1_MAIN_TEMPLATE_ADMIN", "ds1_admin.twig"); // funkcni demo - kompletni: DS1_admin_demo_all.twig
        define("DS1_MAIN_TEMPLATE_ADMIN_LOGIN", "ds1_admin_login.twig"); // specialni login template
        define("DS1_MAIN_TEMPLATE_ADMIN_EMPTY", "ds1_admin_empty.twig"); // prazdna stranka napr. pro tisk
        define("DS1_MAIN_TEMPLATE_ADMIN_USE_TWIG", true);


    // ****************************************************************************************
    // ***********    KONEC CESTY A ADRESARE      *********************************************
    // ****************************************************************************************

    // ****************************************************************************************
    // ***********    START DB      ***********************************************************
    // ****************************************************************************************

        // NAZVY TABULEK - DS1
        define("TABLE_PREFIX", "ds1_");

        // uzivatele, kteri pracuji s aplikaci
        define("TABLE_USERS_ADMIN", TABLE_PREFIX."uzivatele");

        // obyvatele = pacienti
        define("TABLE_OBYVATELE", TABLE_PREFIX."obyvatele");

        // obyvatele na pokojich
        define("TABLE_OBYVATELE_NA_POKOJICH", TABLE_PREFIX."obyvatele_na_pokojich");

        // pokoje
        define("TABLE_POKOJE", TABLE_PREFIX."pokoje");
        define("TABLE_SKUPINY_POKOJU", TABLE_PREFIX."skupiny_pokoju");

        // NAZVY SLOUPCU V DB, pokud jsou treba
        define("TABLE_USER_COLUMN_PASSWORD_BCRYPT", "password_bcrypt");

        // dodatky pro modul dokumentace (Drtina - A17B0202P)
        define("TABLE_DOKUMENTACE", "dokumentace");
        define("TABLE_DOKUMENTACE_DRUH_ZAPISU", "dokumentace_druh_zapisu");
        define("TABLE_DRUH_ZAPISU_UZIVATELSKE_ROLE", "druh_zapisu_uzivatelske_role");
        define("TABLE_UZIVATELSKE_ROLE", "uzivatelske_role");
        define("TABLE_UZIVATELE_PRIDELENI_ROLI", "uzivatele_prideleni_roli");

    // ****************************************************************************************
    // ***********    KONEC DB      ***********************************************************
    // ****************************************************************************************


    // ****************************************************************************************
    // ***********    START OSTATNI      ******************************************************
    // ****************************************************************************************

        // jmeno klice v session
        define("DS1_SESSION_MAIN_KEY", 'ds1.cz');

        // klice k recaptche
        define("RECAPTCHA_CHECK_URL", "https://www.google.com/recaptcha/api/siteverify");
        define("RECAPTCHA_SITE_KEY", "");
        define("RECAPTCHA_SECRET_KEY", "");

        define("DS1_USER_TOKEN_VALID_HOURS", 24); // kolik hodin je platny token u uzivatele


        // ADMIN - POCET RADKU NA STRANCE
        define("DS1_ADMIN_PAGE_ITEMS_COUNT", 20);

    // ****************************************************************************************
    // ***********    KONEC OSTATNI      ******************************************************
    // ****************************************************************************************



    // ****************************************************************************************
    // ***********    START ROUTES       ******************************************************
    // ****************************************************************************************


        // **************************   ADMIN *****************************************************
        // admin login a logout
        define("DS1_ROUTE_ADMIN_LOGIN", "admin-login");
        define("DS1_ROUTE_ADMIN_LOGOUT", "admin-logout");


    // ****************************************************************************************
    // ***********    KONEC ROUTES       ******************************************************
    // ****************************************************************************************







