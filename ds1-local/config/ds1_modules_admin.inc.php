<?php

    $modules_admin = array();

    // ************************************************************************************
    // **************   Modul obyvatele          ******************************************

    // novy modul
    $module = array();
    $module["name"] = "obyvatele";
    $module["title"] = "Obyvatelé";
    $module["route_name"] = "obyvatele";
    $module["route_path"] = "/plugin/$module[name]";
    $module["route"] = array("controller_name" => "obyvatele_controller", "controller_action" => "indexAction");

    // pridat modul
    $modules_admin[] = $module;

    // **************   KONEC Modul obyvatele          ************************************
    // ************************************************************************************

    // ************************************************************************************
    // **************   Modul pokoje        ***********************************************

    // novy modul
    $module = array();
    $module["name"] = "pokoje";
    $module["title"] = "Pokoje";
    $module["route_name"] = "pokoje";
    $module["route_path"] = "/plugin/$module[name]";
    $module["route"] = array("controller_name" => "pokoje_controller", "controller_action" => "indexAction");

    // pridat modul
    $modules_admin[] = $module;

    // **************   KONEC Modul pokoje         ****************************************
    // ************************************************************************************
	
	
	// ************************************************************************************
    // **************   Modul dokumentace (Drtina - A17B0202P)	***************************

	// novy modul
    $module = array();
    $module["name"] = "dokumentace_pacient";
    $module["title"] = "Dokumentace";
    $module["route_name"] = "dokumentace_pacient";
    $module["route_path"] = "/plugin/$module[name]";
    $module["route"] = array("controller_name" => "dokumentace_pacient_controller", "controller_action" => "indexAction");

    // pridat modul
    $modules_admin[] = $module;
	// **************   KONEC Modul dokumentace        ************************************
    // ************************************************************************************

    // ************************************************************************************
    // **************   Modul správa uživatelů (Drtina - A17B0202P)	***************************

    // novy modul
    $module = array();
    $module["name"] = "sprava_uzivatelu";
    $module["title"] = "Správa uživatelů";
    $module["route_name"] = "sprava_uzivatelu";
    $module["route_path"] = "/plugin/$module[name]";
    $module["route"] = array("controller_name" => "sprava_uzivatelu_controller", "controller_action" => "indexAction");

    // pridat modul
    $modules_admin[] = $module;
    // **************   KONEC Modul správa uživatelů        ************************************
    // ************************************************************************************
    return $modules_admin;