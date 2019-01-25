<div class="container-fluid">

    <form method="post" action="<?php echo $form_submit_url; ?>">
        <input type="hidden" name="action" value="<?php echo $form_action_insert; ?>"/>

        <div class="row">
            <div class="col-md-12">

                <div class="card">
                    <div class="card-header">
                        Přidat uživatele
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <table class='table table-striped table-bordered'>
                                <tr>
                                    <th class='w-25'>Login</th>
                                    <td class='w-75'>
                                        <?php
                                            $login = "";
                                            if (isset($uzivatel_new["login"])) {
                                                $login = $uzivatel_new["login"];
                                            }
                                        ?>
                                        <input type="text" class="form-control" name="uzivatel[login]" value="<?php echo $login; ?>" required />
                                    </td>
                                </tr>
                                <tr>
                                    <th class='w-25'>Heslo</th>
                                    <td class='w-75'>
                                        <input type="password" class="form-control" name="uzivatel[heslo]" required />
                                    </td>
                                </tr>
                                <tr>
                                    <th class='w-25'>Heslo pro kontrolu</th>
                                    <td class='w-75'>
                                        <input type="password" class="form-control" name="uzivatel[heslo2]" required />
                                    </td>
                                </tr>
                            </table>

                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-left">

                                    <input type="submit" class="btn btn-primary btn-lg" value="Vytvořit uživatele" />

                                </div>
                                <div class="pull-right">
                                    <a href="<?php echo $url_uzivatele_list;?>" class="btn btn-default btn-lg">Zpět na seznam uživatelů</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div><!-- card -->

            </div> <!--col-md-12-->
        </div><!-- konec row-->


    </form>
</div>
