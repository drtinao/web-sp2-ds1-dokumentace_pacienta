<div class="container-fluid" ng-app="ds1">
    <?php
    echo "<h3>Přidání / úprava role - dokončeno"
        . "</h3><br/>";
    echo "Informace byly úspěšně zapsány do databáze - můžete přidělit další roli nebo se vrátit zpět na seznam uživatelů."
        . "<br/>";
    ; ?>
    <div style="text-align: center">
        <a href="<?php echo $url_dalsi_zaznam;?>" class="btn btn-primary btn-bg"><i class="icon-plus"></i> Přidělit další roli</a>
        <a href="<?php echo $url_uzivatele_list;?>" class="btn btn-primary btn-bg"><i class="icon-minus"></i> Zpět na seznam uživatelů</a>
    </div>
</div>