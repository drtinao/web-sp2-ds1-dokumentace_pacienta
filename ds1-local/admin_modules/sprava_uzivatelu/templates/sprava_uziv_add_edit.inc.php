<div class="container-fluid">
    <form method="post" action="<?php echo $form_submit_url; ?>">
        <input type="hidden" name="action" value="<?php echo $form_action_insert_role; ?>"/>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <?php echo "<h3>Přidání / úprava role"
                            . "</h3><br/>"; ?>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <table class='table table-striped table-bordered'>
                                <tr>
                                    <th class='w-25'>Login uživatele</th>
                                    <td class='w-75'>
                                        <input type="text" class="form-control" name="zadano[login]" list="vsechny_loginy_uzivatel" value="<?php
                                        if($uzivatel_jmeno_klik != -1){
                                            echo $uzivatel_jmeno_klik;
                                        }else{
                                            echo "";
                                        }?>" required />
                                        <datalist id="vsechny_loginy_uzivatel">
                                            <?php
                                            foreach($vsechny_loginy as $login) { ?>
                                                <option value="<?= $login ?>"><?= $login ?></option>
                                                <?php
                                            } ?>
                                        </datalist>
                                    </td>
                                </tr>
                                <tr>
                                    <th class='w-25'>Nová role</th>
                                    <td class='w-75'>
                                        <input type="text" class="form-control" name="zadano[role]" list="vsechny_role" required />
                                        <datalist id="vsechny_role">
                                            <?php
                                            foreach($vsechny_role as $role) { ?>
                                                <option value="<?= $role ?>"><?= $role ?></option>
                                                <?php
                                            } ?>
                                        </datalist>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-left">
                                    <input type="submit" class="btn btn-primary btn-lg" value="Přidělit roli" />
                                </div>
                                <div class="pull-right">
                                    <a href="<?php echo $url_uzivatele_list;?>" class="btn btn-default btn-lg">Zpět na výpis uživatelů</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
