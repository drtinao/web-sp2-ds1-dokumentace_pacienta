<div class="container-fluid" ng-app="ds1">
            <?php
            $_SESSION["dokumentace_nova_post"]=$dokumentace_nova_post;
            $_SESSION["pacienti"]=$pacienti;
            ?>
    <div class="card-header">
        <?php echo "<h3>Přidání dokumentace - kontrola informací"
            . "</h3><br/>"; ?>
    </div>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <?php
        //vytvořím záložku pro každého pacienta se zvoleným jménem
        foreach($pacienti as $pacient1) {
            $index_prochazeny_pacient1 = array_search($pacient1, $pacienti); ?>
            <li class="nav-item">
                <a class="<?php if($index_prochazeny_pacient1 == 0){
                    echo "nav-link active";
                }else{
                    echo "nav-link";
                } ?>" id="<?php echo $index_prochazeny_pacient1."id" ?>" data-toggle="tab" href="#<?php echo $pacient1["id"] ?>" role="tab" aria-controls="zaklad" aria-selected="true">pacient: <?php echo $pacient1["id"] ?></a>
            </li>
            <?php
        } ?>
    </ul>
    <div class="tab-content" id="myTabContent">
        <?php
        if(sizeof($pacienti) > 0){ //pokud v DB existuje alespoň jeden pacient se zadaným jménem
        //vytvořím záložku pro každého pacienta se zvoleným jménem
        foreach($pacienti as $pacient) {
        $index_prochazeny_pacient = array_search($pacient, $pacienti);
        ?>

        <div class="<?php if ($index_prochazeny_pacient == 0) {
            echo "tab-pane fade show active";
        } else {
            echo "tab-pane fade";
        } ?>" id="<?php echo $pacient["id"] ?>" role="tabpanel" aria-labelledby="zaklad-tab">
            <?php
            $obcansky_prukaz_platnost_vypis = $controller->helperFormatDate($pacient["op_platnost_do"]);
            $datum_narozeni_vypis = $controller->helperFormatDate($pacient["datum_narozeni"]);
            echo "<table class='table table-sm table-striped table-bordered' style='max-width: 500px;'>";
            echo "<tr>
                                    <td><b>id</b></td>
                                    <td>$pacient[id]</td>
                                    </tr>
                                    <tr>
                                    <td><b>jméno</b></td>
                                    <td>$pacient[jmeno]</td>
                                    </tr>   
                                    <tr>
                                    <td><b>příjmení</b></td>
                                    <td>$pacient[prijmeni]</td>
                                    </tr>
                                    <tr>
                                    <td><b>rodné příjmení</b></td>
                                    <td>$pacient[rodne_prijmeni]</td>
                                    </tr>
                                    <tr>
                                    <td><b>datum narození</b></td>
                                    <td>$datum_narozeni_vypis</td>
                                    </tr>
                                    <tr>
                                    <td><b>tituly před</b></td>
                                    <td>$pacient[tituly_pred]</td>
                                    </tr>
                                    <tr>
                                    <td><b>tituly za</b></td>
                                    <td>$pacient[tituly_za]</td>
                                    </tr>
                                    <tr>
                                    <td><b>rodné číslo</b></td>
                                    <td>$pacient[rodne_cislo]</td>
                                    </tr>
                                    <tr>
                                    <td><b>místo narození</b></td>
                                    <td>$pacient[misto_narozeni]</td>
                                    </tr>
                                    <tr>
                                    <td><b>pojišťovna zkratka</b></td>
                                    <td>$pacient[pojistovna_zkratka]</td>
                                    </tr>      
                                    <tr>
                                    <td><b>číslo pojištěnce</b></td>
                                    <td>$pacient[cislo_pojistence]</td>
                                    </tr>  
                                    <tr>
                                    <td><b>adresa - ulice</b></td>
                                    <td>$pacient[adresa_ulice]</td>
                                    </tr>
                                    <tr>
                                    <td><b>adresa - číslo popisné</b></td>
                                    <td>$pacient[adresa_cp]</td>
                                    </tr>
                                    <tr>
                                    <td><b>adresa - město</b></td>
                                    <td>$pacient[adresa_mesto]</td>
                                    </tr>
                                    <tr>
                                    <td><b>občanský průkaz</b></td>
                                    <td>$pacient[op]</td>
                                    </tr>
                                    <tr>
                                    <td><b>občanský průkaz - platnost do</b></td>
                                    <td>$obcansky_prukaz_platnost_vypis</td>
                                    </tr>
                                    <tr>
                                    <td><b>stav</td>
                                    <td>$pacient[stav]</td>
                                    </tr>                                      
                                </tr>";
            echo "</table>";
            ?>
            <div style="text-align: center">
                <a href="<?php $result = "dokumentace_add_final_prechod".$pacient["id"]; echo $$result;?>" class="btn btn-primary btn-bg"><i class="icon-plus"></i> ANO, přidat dokumentaci k danému pacientovi</a>
                <a href="<?php echo $url_dokumentace_list;?>" class="btn btn-primary btn-bg"><i class="icon-minus"></i> NE, zpět na seznam dokumentace</a>
            </div>
            <?php
            echo "</div>";
            ?>
            <?php }
        }else{ //pokud v DB neexistuje pacient se zadaným jménem a příjmením
            echo "V databázi neexistuje pacient se zadaným jménem a příjmením, můžete se vrátit zpět na seznam dokumentace"; ?>
            <div style="text-align: center">
            <a href="<?php echo $url_dokumentace_list;?>" class="btn btn-primary btn-bg"><i class="icon-minus"></i> Zpět na seznam dokumentace</a>
            </div>
            <?php
        }
        ?>
</div>
