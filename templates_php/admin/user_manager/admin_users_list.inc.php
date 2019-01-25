<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            Seznam uživatelů - <?php echo $uzivatele_list_name; ?>

            <div class="pull-right">
                <!-- odkaz pro pridani uzivatele -->
                <a href="<?php echo $url_uzivatel_add_prepare;?>" class="btn btn-primary btn-sm"><i class="icon-plus"></i> Přidat uživatele</a>
            </div>
        </div>
        <div class="card-body">

            <?php

            if ($uzivatele_list != null) {
                echo "<table class='table table-condensed table-bordered table-striped table-hover'>";
                echo "<tr>
                                    <th>#</th>
                                    <th>příjmení</th>
                                    <th>jméno</th>
                                    <th>telefon</th>
                                    <th>email</th>
                                    <th>datum vytvoření</th>
                                    <th>&nbsp;</th>
                                </tr>";

                foreach ($uzivatele_list as $user) {
                    // detail uzivatele
                    $route_params = array();
                    $route_params["action"] = $action_uzivatel_detail;
                    $route_params["uzivatel_id"] = $user["id"];
                    $url_detail = $this->makeUrlByRoute($route, $route_params);

                    // priprava editace
                    $route_params = array();
                    $route_params["action"] = $action_uzivatel_update_prepare;
                    $route_params["uzivatel_id"] = $user["id"];
                    $url_update_prepare = $this->makeUrlByRoute($route, $route_params);

                    echo "<tr>";
                        echo "<td>$user[id]</td>";
                        echo "<td>$user[prijmeni]</td>";
                        echo "<td>$user[jmeno]</td>";
                        echo "<td>$user[telefon]</td>";
                        echo "<td>$user[email]</td>";
                        echo "<td>$user[datum_vytvoreni]</td>";
                        echo "<td>
                                  <a href=\"$url_detail\" class='btn btn-primary btn-sm'><i class=\"icon-layers\"></i></a>
                                  &nbsp;&nbsp;
                                  <a href=\"$url_update_prepare\" class='btn btn-primary btn-sm'><i class=\"icon-pencil\"></i></a>

                              </td>";

                    echo "</tr>";
                }

                echo "</table>";
            }
            else {
                echo "Žádní uživatelé nenalezeni.";
            }


            // stranovani
            echo $pagination_html;
            ?>
        </div>
    </div>
</div>