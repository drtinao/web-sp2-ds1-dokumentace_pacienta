<div class="container-fluid" ng-app="ds1">
    <div class="card-header">
        <?php echo "<h3>Přidání / úprava role - kontrola informací"
            . "</h3><br/>"; ?>
    </div>
    <?php
    echo "<table class='table table-sm table-striped table-bordered' style='max-width: 800px;'>";
    echo "<tr>
                                    <td><b>id</b></td>
                                    <td>$uzivatel_data[id]</td>
                                    </tr>
                                    <tr>
                                    <td><b>jméno</b></td>
                                    <td>$uzivatel_data[jmeno]</td>
                                    </tr>   
                                    <tr>
                                    <td><b>příjmení</b></td>
                                    <td>$uzivatel_data[prijmeni]</td>
                                    </tr>
                                    <tr>
                                    <td><b>telefon</b></td>
                                    <td>$uzivatel_data[telefon]</td>
                                    </tr>
                                    <tr>
                                    <td><b>email</b></td>
                                    <td>$uzivatel_data[email]</td>
                                    </tr>
                                    <tr>
                                    <td><b>datum vytvoření</b></td>
                                    <td>$uzivatel_data[datum_vytvoreni]</td>
                                    </tr>
                                    <tr>
                                    <td><b>předchozí role</b></td>
                                    <td>$uzivatel_data[predchozi_role]</td>
                                    </tr>
                                    <tr>
                                    <td><b>předchozí role - typy dokumentace</b></td>
                                    <td>$uzivatel_data[predchozi_role_typy]</td>
                                    </tr>
                                    <tr>
                                    <td><b>nová role</b></td>
                                    <td>$uzivatel_data[nova_role]</td>
                                    </tr>
                                    <tr>
                                    <td><b>nová role - typy dokumentace</b></td>
                                    <td>$uzivatel_data[nova_role_typy]</td>
                                    </tr>                                                         
                                </tr>";
    echo
    "</table>";
    //potřebné věci si uložím do session a načtu v dalším templatu
    $_SESSION["uzivatel_id"] = "$uzivatel_data[id]";
    $_SESSION["nova_role_id"] = "$uzivatel_data[nova_role_id]";
    ?>
    <div style="text-align: center">
        <a href="<?php echo $url_add_result;?>" class="btn btn-primary btn-bg"><i class="icon-plus"></i> ANO, přidělit novou roli</a>
        <a href="<?php echo $url_uzivatele_list;?>" class="btn btn-primary btn-bg"><i class="icon-minus"></i> NE, zrušit akci a vrátit se zpět</a>
    </div>
</div>
