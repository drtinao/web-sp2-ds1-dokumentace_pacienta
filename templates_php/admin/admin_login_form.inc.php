<div class="card-group">
    <div class="card p-4">
        <div class="card-body">

            <form method="post" action="<?php echo $form_submit_url; ?>">
                <input type="hidden" name="action" value="<?php echo $form_action; ?>"/>

                <?php
                    // vypsat zpravu
                    if ($msg != "") {
                        // vypis dle typu zpravy
                        if ($result_ok == false) {
                            echo "<div class=\"alert alert-danger\" role=\"alert\">$msg</div>";
                        }
                        else {
                            echo "<div class=\"alert alert-success\" role=\"alert\">$msg</div>";
                        }
                    }
                ?>

                <h1>Login</h1>
                <p class="text-muted">Přihlášení do administrace systému.</p>
                <div class="input-group mb-4">
                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                          <i class="icon-user"></i>
                                        </span>
                    </div>
                    <input type="text" name="login" class="form-control" placeholder="Username">
                </div>
                <div class="input-group mb-4">
                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                          <i class="icon-lock"></i>
                                        </span>
                    </div>
                    <input type="password" name="password" class="form-control" placeholder="Password">
                </div>

                <div class="input-group mb-4">
                    <!-- Google recaptcha -->
                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <input type="submit" class="btn btn-primary px-4" value="Login" />
                    </div>
                    <div class="col-6 text-right">
                        <!--
                        <button type="button" class="btn btn-link px-0">Forgot password?</button>
                        -->
                    </div>
                </div>
            </form>

        </div>
    </div>
    <div class="card text-white bg-primary py-5 d-md-down-none" style="width:44%">
        <div class="card-body text-center">
            <div>
                <h2>Registrace</h2>
                <p>Registrace do této administrace není možná. Přístupové údaje budou poskytnuty
                    automaticky.</p>
            </div>
        </div>
    </div>
</div>