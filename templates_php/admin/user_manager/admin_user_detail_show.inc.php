<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            Detail uživatele - <?php echo "$uzivatel[jmeno] $uzivatel[prijmeni]"; ?>
        </div>
        <div class="card-body">
        <?php

            $titles = array();

            $titles["id"] = "ID";
            $titles["jmeno"] = "Jméno";
            $titles["prijmeni"] = "Příjmení";
            $titles["telefon"] = "Telefon";
            $titles["email"] = "Email";

            echo $this->helperPrintBasicTable($uzivatel, array("id", "jmeno", "prijmeni", "telefon", "email"), $titles);

            //printr($uzivatel);
        ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="pull-left">
                        <a href="<?php echo $url_uzivatel_update;?>" class="btn btn-primary btn-sm"><i class="icon-pencil"></i> Upravit</a>
                    </div>

                    <div class="pull-right">
                        <a href="<?php echo $url_uzivatele_list;?>" class="btn btn-link">Zpět na seznam uživatelů</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
