<div class="container-fluid">
    <form method="post" action="<?php echo $form_submit_url; ?>">
        <input type="hidden" name="action" value="<?php echo $form_action_insert_dokumentace; ?>"/>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <?php echo "<h3>Přidání dokumentace"
                            . "</h3><br/>"; ?>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <table class='table table-striped table-bordered'>
                                <tr>
                                    <th class='w-25'>Jméno pacienta</th>
                                    <td class='w-75'>
                                        <input type="text" class="form-control" name="dokumentace[jmeno]" list="jmena_pacientu" required />
                                        <datalist id="jmena_pacientu">
                                            <?php
                                            foreach($jmena_pacientu as $jmeno) { ?>
                                                <option value="<?= $jmeno ?>"><?= $jmeno ?></option>
                                                <?php
                                            } ?>
                                        </datalist>
                                    </td>
                                </tr>
                                <tr>
                                    <th class='w-25'>Příjmení pacienta</th>
                                    <td class='w-75'>
                                        <input type="text" class="form-control" name="dokumentace[prijmeni]" list="prijmeni_pacientu" required />
                                        <datalist id="prijmeni_pacientu">
                                            <?php
                                            foreach($prijmeni_pacientu as $prijmeni) { ?>
                                                <option value="<?= $prijmeni ?>"><?= $prijmeni ?></option>
                                                <?php
                                            } ?>
                                        </datalist>
                                    </td>
                                </tr>
                                <tr>
                                    <th class='w-25'>Druh zápisu</th>
                                    <td class='w-75'>
                                        <input type="text" class="form-control" name="dokumentace[druh_zapisu]" list="druhy_zapisu_dokumentace" required />
                                        <datalist id="druhy_zapisu_dokumentace">
                                            <?php
                                            foreach($druhy_zapisu as $druh_zapisu) { ?>
                                                <option value="<?= $druh_zapisu ?>"><?= $druh_zapisu ?></option>
                                                <?php
                                            } ?>
                                        </datalist>
                                    </td>
                                </tr>
                                <tr>
                                    <th class='w-25'>Text zápisu</th>
                                    <td class='w-75'>
                                        <input type="text" class="form-control" name="dokumentace[text_zapisu]" required />
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-left">
                                    <input type="submit" class="btn btn-primary btn-lg" value="Přidat dokumentaci" />
                                </div>
                                <div class="pull-right">
                                    <a href="<?php echo $url_dokumentace_list;?>" class="btn btn-default btn-lg">Zpět na výpis dokumentace</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
