<?= $this->extend('templates/starting_page_layout'); ?>

<?= $this->section('navaction') ?>
<a href="<?= base_url('/'); ?>" class="btn btn-primary pull-right pl-3">
    <i class="material-icons mr-2">qr_code</i>
    Scan QR
</a>
<?= $this->endSection() ?>

<?= $this->section('content'); ?>
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
        <div class="d-flex align-items-center justify-content-center w-100">
            <div class="row justify-content-center w-100">
                <div class="col-md-8 col-lg-6 col-xxl-4">
                    <div class="card mb-0">
                        <div class="card-body">
                        <h3 class="text-center mb-4"><b>E-Presensi</b></h3>
                            <?= view('\App\Views\admin\_message_block') ?>
                            <form action="<?= url_to('login') ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <?php if (!empty($config) && $config->validFields === ['email']) : ?>
                                        <label for="emailInput" class="form-label"><?= lang('Auth.email') ?></label>
                                        <input type="email" class="form-control <?= session('errors.login') ? 'is-invalid' : '' ?>" id="emailInput" name="login" autofocus>
                                        <div class="invalid-feedback">
                                            <?= session('errors.login') ?>
                                        </div>
                                    <?php else : ?>
                                        <label for="loginInput" class="form-label"><?= lang('Auth.emailOrUsername') ?></label>
                                        <input type="text" class="form-control <?= session('errors.login') ? 'is-invalid' : '' ?>" id="loginInput" name="login" autofocus>
                                        <div class="invalid-feedback">
                                            <?= session('errors.login') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-4">
                                    <label for="passwordInput" class="form-label">Password</label>
                                    <input type="password" class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" id="passwordInput" name="password">
                                    <div class="invalid-feedback">
                                        <?= session('errors.password') ?>
                                    </div>
                                </div>
                                <?php if (!empty($config) && $config->allowRemembering) : ?>
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input primary" type="checkbox" name="remember" id="rememberMe" <?= old('remember') ? 'checked' : '' ?>>
                                            <label class="form-check-label text-dark" for="rememberMe">
                                                <?= lang('Auth.rememberMe') ?>
                                            </label>
                                        </div>
                                        <?php if (!empty($config) && $config->activeResetter) : ?>
                                            <a class="text-primary fw-bold" href="<?= url_to('forgot') ?>"><?= lang('Auth.forgotYourPassword') ?></a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-primary w-100 py-3 fs-4 mb-4 rounded-2"><?= lang('Auth.loginAction') ?></button>
                                <div class="d-flex align-items-center justify-content-center">
                                    <p class="fs-4 mb-0 fw-bold">&copy; <?= date('Y') ?> Raphael</p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
