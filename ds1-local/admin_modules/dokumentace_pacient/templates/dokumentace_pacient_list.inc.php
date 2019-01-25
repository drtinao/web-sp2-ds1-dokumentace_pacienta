<div class="container-fluid" ng-app="ds1">
    <div class="card">
        <div class="card-header">
            <div class="pull-left">
                <form method="post" action="<?php echo $form_submit_url; ?>">
                    <th>Jméno pacienta: &nbsp;</th><input type="text" value="<?php echo $vyhledavani_jmeno; ?>" name="uzivatel_hledani[jmeno]" list="jmena_pacientu">
                        <datalist id="jmena_pacientu">
                            <?php
                            foreach($napoveda_jmena_pacientu as $jmeno) { ?>
                                <option value="<?= $jmeno ?>"><?= $jmeno ?></option>
                                <?php
                            } ?>
                        </datalist>

                    &nbsp;
                        <th>Typ dokumentace: &nbsp;</th>
                        <input type="text" value="<?php echo $vyhledavani_typ_dokumentace; ?>" name="uzivatel_hledani[dokumentace_typ]" list="typy_dokumentace">
                        <datalist id="typy_dokumentace">
                            <?php
                            foreach($napoveda_typy_dokumentace as $typ_dokumentace) { ?>
                                <option value="<?= $typ_dokumentace ?>"><?= $typ_dokumentace ?></option>
                                <?php
                            } ?>
                        </datalist>
                        </th>
                    &nbsp;
                    <input type="submit" class="btn btn-primary btn-sm" value="Hledat" />
                </form>
                <?php echo $info_tabulka_zobrazeno ?>
            </div>
            <div class="pull-right">
                <!-- odkaz pro pridani dokumentace -->
                <a href="<?php echo $url_dokumentace_add;?>" class="btn btn-primary btn-sm"><i class="icon-plus"></i> Přidat dokumentaci</a>
            </div>
        </div>

        <div class="row">
                <div class="col-md-12">
                    <?php
                    // vypis informací o dokumentaci
                    if ($dokumentace_list != null) {
                        echo "<table class='table table-sm table-bordered table-striped table-hover'>";


                        echo "<tr>
                                    <th>#</th>
                                    <th>příjmení</th>
                                    <th>jméno</th>
                                    <th>datum narození</th>
                                    <th>zkratka pojišťovny</th>
                                    <th>dokumentace - text</th>
                                    <th>dokumentace - druh</th>
                                    <th>přidal - jméno</th>
                                    <th>přidal - příjmení</th>
                                    <th>úpravy</th>
                                </tr>";

                        foreach($dokumentace_list as $dokumentace_konkretni_zaznam){ //projdu dokumentaci a zjistím, jestli má uživatel práva pro zobrazení daného záznamu prihlaseny_uzivatel
                            echo "<tr>";
                            //mám potřebná data (dokumentace, info o uživateli, info o pacientovi) a je příhlášen uživatel, který má právo zobrazit - vypíšu řádku se záznamem
                            echo "<td>$dokumentace_konkretni_zaznam[id]</td>";
                            echo "<td>$dokumentace_konkretni_zaznam[obyvatel_prijmeni]</td>"; //jméno pacienta
                            echo "<td>$dokumentace_konkretni_zaznam[obyvatel_jmeno]</td>"; //příjmení pacienta

                            echo "<td>".$controller->helperFormatDate($dokumentace_konkretni_zaznam["obyvatel_datum_narozeni"])."</td>";//datum narození pacienta
                            echo "<td>$dokumentace_konkretni_zaznam[obyvatel_zkratka_pojistovny]</td>"; //zkratka pojišťovny pacienta

                            $barva_hex = $dokumentace_konkretni_zaznam["dokumentace_druh_barva_hex"]; //vytáhneme si barvu, která bude použita na podbarvení textu
                            $tucne = $dokumentace_konkretni_zaznam["dokumentace_druh_tucne"]; //zjistíme, zda má být pro daný záznam použito točné písmo
                            $kurziva = $dokumentace_konkretni_zaznam["dokumentace_druh_kurziva"]; //zjistíme, zda má být pro daný záznam použita kurzíva

                            $zastupny_znak_tucne = "";
                            $zastupny_znak_kurziva = "";

                            if($tucne == 1){ //tučnost zapnuta
                                $zastupny_znak_tucne = "<b>";
                            }

                            if($kurziva == 1){ //kurziva zapnuta
                                $zastupny_znak_kurziva = "<i>";
                            }

                            //pokud je zápis příliš dlouhý, pak vypíšu jenom jeho úryvek
                            if(strlen($dokumentace_konkretni_zaznam["zapis"]) > 22){
                                $uryvek = substr($dokumentace_konkretni_zaznam["zapis"], 0, 22)."...";
                                echo "<td>$zastupny_znak_tucne$zastupny_znak_kurziva<span style=\"color: $barva_hex;\">$uryvek</span></>"; //konkrétní zápis v dokumentaci
                            }else{
                                echo "<td>$zastupny_znak_tucne$zastupny_znak_kurziva<span style=\"color: $barva_hex;\">$dokumentace_konkretni_zaznam[zapis]</span></>"; //konkrétní zápis v dokumentaci
                            }
                            echo "<td>$dokumentace_konkretni_zaznam[dokumentace_druh_text]</td>"; //typ zápisu v dokumentaci
                            echo "<td>$dokumentace_konkretni_zaznam[uzivatel_jmeno]</td>"; //jméno uživatele, který záznam přidal
                            echo "<td>$dokumentace_konkretni_zaznam[uzivatel_prijmeni]</td>"; //příjmení uživatele, který záznam přidal


                            // detail dokumentace
                            $route_params = array();
                            $route_params["action"] = $dokumentace_detail_action;
                            $route_params["dokumentace_id"] = $dokumentace_konkretni_zaznam["id"];
                            $url_detail = $this->makeUrlByRoute($route, $route_params);


                            //úprava dokumentace
                            // detail dokumentace
                            $route_params2 = array();
                            $route_params2["action"] = $dokumentace_edit_action;
                            $route_params2["dokumentace_id"] = $dokumentace_konkretni_zaznam["id"];
                            $url_detail2 = $this->makeUrlByRoute($route, $route_params2);

                           echo "<td>
                                  <a href=\"$url_detail\" class='btn btn-primary btn-sm'><i class=\"icon-layers\"></i></a>
                                  &nbsp;&nbsp;
                                  <a href=\"$url_detail2\" class='btn btn-primary btn-sm'><i class=\"icon-pencil\"></i></a>
                              </td>";

                            echo "</tr>";
                        }

                        echo "</table>";
                        echo "</div>";

                        // stranovani
                        echo "<div class=\"row\">
                       <div class=\"col-md-8 offset-md-2 \">";

                        echo $pagination_html;

                        echo "</div></div>";
                        // konec strankovani
                    }
                    else {
                        echo "<div class=\"col-md-12\">";

                        echo "<div class=\"alert alert-danger fade show\" role=\"alert\">
                               Žádná dokumentace nenalezena.
                         </div>";

                        echo "</div>";
                    }


                    ?>
                </div>
            </div>
        </div>