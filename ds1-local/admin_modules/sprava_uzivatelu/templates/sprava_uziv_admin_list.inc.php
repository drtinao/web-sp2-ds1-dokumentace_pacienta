<div class="container-fluid" ng-app="ds1">
    <div class="card">
        <div class="card-header">
            <div class="pull-left">
                <form method="post" action="<?php echo $form_submit_url; ?>">
                    <th>Příjmení uživatele: &nbsp;</th><input type="text" value="<?php echo $vyhledavani_prijmeni; ?>" name="uzivatel_hledani[prijmeni]" list="prijmeni_uzivatelu">
                    <datalist id="prijmeni_uzivatelu">
                        <?php
                        foreach($napoveda_prijmeni_uzivatelu as $prijmeni) { ?>
                            <option value="<?= $prijmeni ?>"><?= $prijmeni ?></option>
                            <?php
                        } ?>
                    </datalist>
                    <input type="submit" class="btn btn-primary btn-sm" value="Hledat" />
                </form>
                <?php echo $info_tabulka_zobrazeno ?>
            </div>
            <div class="pull-right">
                <!-- odkaz pro pridani dokumentace -->
                <a href="<?php echo $url_pridat_role;?>" class="btn btn-primary btn-sm"><i class="icon-plus"></i> Přidělit / upravit roli</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php
                // vypis informací uživatelích systému
                if ($uzivatele_info != null) {
                    echo "<table class='table table-sm table-bordered table-striped table-hover'>";
                    echo "<tr>
                                    <th>#</th>
                                    <th>login</th>
                                    <th>jméno</th>
                                    <th>příjmení</th>
                                    <th>telefon</th>
                                    <th>email</th>
                                    <th>role</th>
                                    <th>upravit roli</th>
                                </tr>";
                    foreach($uzivatele_info as $uzivatel_info){
                        echo "<tr>";
                        echo "<td>$uzivatel_info[id]</td>";
                        echo "<td>$uzivatel_info[login]</td>";
                        echo "<td>$uzivatel_info[jmeno]</td>";
                        echo "<td>$uzivatel_info[prijmeni]</td>";
                        echo "<td>$uzivatel_info[telefon]</td>";
                        echo "<td>$uzivatel_info[email]</td>";

                        $data_role = $uzivatel_info["role_data"];
                        echo "<td>$data_role[nazev]</td>";

                        // detail dokumentace
                        $route_params = array();
                        $route_params["action"] = $role_edit;
                        $route_params["login_uzivatel"] = $uzivatel_info["login"];
                        $url_detail = $this->makeUrlByRoute($route, $route_params);

                        echo "<td><a href=\"$url_detail\" class='btn btn-primary btn-sm'><i class=\"icon-pencil\"></i></a></td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    echo "</div>";
                    echo "<div class=\"row\">
                       <div class=\"col-md-8 offset-md-2 \">";
                    echo "</div></div>";
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