<div class="container-fluid" ng-app="ds1">
    <?php
    echo "<h3>Přidání dokumentace - dokončeno"
        . "</h3><br/>";
    echo "Dokumentace byla úspěšně přidána do databáze - můžete přidat další záznam nebo se vrátit zpět na výpis dokumentace."
        . "<br/>";
    ; ?>
    <div style="text-align: center">
    <a href="<?php echo $url_dalsi_zaznam;?>" class="btn btn-primary btn-bg"><i class="icon-plus"></i> Přidat další záznam</a>
    <a href="<?php echo $url_dokumentace_list;?>" class="btn btn-primary btn-bg"><i class="icon-minus"></i> Zpět na seznam dokumentace</a>
    </div>
</div>