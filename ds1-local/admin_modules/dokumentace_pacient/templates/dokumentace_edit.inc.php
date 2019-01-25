<div class="container-fluid">
    <?php
    $_SESSION["zobrazeno_id"] = "$dokumentace[id]";
    $_SESSION["uzivatel_id"] = "$dokumentace[uzivatel_id]";
    $_SESSION["obyvatel_id"] = "$dokumentace[obyvatel_id]";

    echo "<h3>Detail dokumentace #$dokumentace[id]"
        . "</h3><br/>";
    ?>
    <div class="row">
        <form method="post" action="<?php echo $form_submit_url; ?>">
        <?php
        $text_columns = array();
        $text_columns["nazev"] = "Kategorie";
        $text_columns["zapis"] = "Zápis";

        if ($text_columns != null) {
            echo "<table class='table table-sm table-striped table-bordered' style='max-width: 700px;'>";

            foreach ($text_columns as $key => $value) {
                echo "<tr>";
                echo "<th class='w-20'>$value</th>";
                echo "<td class='w-30'>";

                echo "<input type=\"text\" value=\"";
                if(array_key_exists($key, $dokumentace)) {
                    echo "$dokumentace[$key]";
                }else if(array_key_exists($key, $dokumentace_typ_zapisu)){
                    echo "$dokumentace_typ_zapisu[$key]";
                }
                echo "\" name=\"uzivatel_zadano[typ]\" list=\"napoveda_kategorie\" required>";
                ?>
                <datalist id="napoveda_kategorie">
                    <?php
                    foreach($napoveda_pristupne_kategorie as $kategorie) { ?>
                <option value="<?= $kategorie ?>"><?= $kategorie ?></option>
            <?php
            } ?>
            </datalist>
            <?php
                echo "</td>";
                echo "</tr>";
                break;
            }

            echo "<tr>";
            echo "<th class='w-20'>Vytvořeno</th>";
            echo "<td class='w-30'>";
            $rozdeleny_datetime = explode(" ", $dokumentace["datum_vytvoreni"]);
            $edit = $controller->helperFormatDate($rozdeleny_datetime[0]);
            echo "$edit $rozdeleny_datetime[1]";
            echo "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<th class='w-20'>Text zápisu</th>";
            echo "<td class='w-30'>";
            echo "<textarea rows=\"30\" cols=\"95\" name=\"uzivatel_zadano[text]\">";
            echo $dokumentace["zapis"];

            echo "</textarea>";
            echo "</table>";
        }
        ?>
            <div style="text-align: center">
                <input type="submit" class="btn btn-primary btn-bg" value=" Uložit změny" />
                <a href="<?php echo $url_dokumentace_remove;?>" class="btn btn-primary btn-bg"><i class="icon-minus"></i> Smazat záznam</a>
                <a href="<?php echo $url_dokumentace_list;?>" class="btn btn-primary btn-bg"></i> Zpět na seznam dokumentace</a>
            </div>
        </form>
    </div>
</div>