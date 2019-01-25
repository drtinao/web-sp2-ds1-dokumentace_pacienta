<div class="container-fluid">
    <?php
    echo "<h3>Detail dokumentace #$dokumentace[id]"
        . "</h3><br/>";
    ?>
        <div class="row">
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

                    if(array_key_exists($key, $dokumentace)) {
                        echo "$dokumentace[$key]";
                    }else if(array_key_exists($key, $dokumentace_typ_zapisu)){
                        echo "$dokumentace_typ_zapisu[$key]";
                    }
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
                echo "<textarea rows=\"30\" cols=\"95\" readonly>";
                echo $dokumentace["zapis"];
                echo "</textarea>";
                echo "</table>";
            }
            ?>
        </div>
    <div style="text-align: center">
        <a href="<?php echo $url_dokumentace_list;?>" class="btn btn-primary btn-bg"></i> Zpět na seznam dokumentace</a>
    </div>
</div>
