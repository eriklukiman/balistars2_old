<?php
include_once 'library/konfigurasidatabase.php';
include_once 'library/konfigurasiurl.php';

session_start();

$idUser = '';

extract($_SESSION);

$sqlInformasi    = $db->query('SELECT * FROM balistars_information');
$dataInformasi = $sqlInformasi->fetch();
$logo            = 'assets/images/' . $dataInformasi['logo'];

//$sukses          = '';
extract($_REQUEST);

?>
<!DOCTYPE html>
<html>

<head>
  <title><?= $dataInformasi['nama'] ?></title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <meta name="description" content="None">
  <meta name="author" content="TempatKita Software">

  <link rel="icon" href="<?= $logo ?>" type="image/x-icon">
  <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/main2.css">
  <link rel="stylesheet" href="assets/css/color_skins.css">
  <link rel="stylesheet" href="assets/vendor/font-awesome/css/font-awesome.min.css">

  <link rel="stylesheet" href="assets/custom_css/loader.css">

  <link rel="stylesheet" href="<?= $BASE_URL_HTML ?>/assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= $BASE_URL_HTML ?>/assets/css/main2.css">
  <link rel="stylesheet" href="<?= $BASE_URL_HTML ?>/assets/css/color_skins.css">

</head>

<body class="theme-blue">
  <div id="wrapper">
    <div class="vertical-align-wrap">
      <div class="vertical-align-middle auth-main">
        <div class="auth-box">
          <div class="card">
            <div class="header">
              <p class="lead"><b>Login to your account</b></p>
            </div>
            <div class="body">
              <form class="form-auth-small" id="formLogin">
                <input type="hidden" id="flagNotif" value="<?= $flagNotif ?? '' ?>">
                <input type="hidden" id="idUser" name="idUser" value="<?= $idUser ?>">

                <div class="form-group">
                  <input type="text" class="form-control" name="username" id="username" placeholder="Username">
                </div>
                <div class="form-group">
                  <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                </div>
                <div class="form-group clearfix">
                  <label class="fancy-checkbox element-left">
                    <input type="checkbox" name="rememberme">
                    <span>Remember me</span>
                  </label>
                </div>
                <button type="button" class="btn btn-primary btn-lg btn-block" onclick="prosesLogin()">
                  Sign In
                </button>
                <div class="bottom">
                  <span class="helper-text m-b-10"><i class="fa fa-lock"></i> <a href="#">Forgot password?</a></span>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- JS UTAMA HALAMAN LOGIN -->

  <!-- END JS UTAMA HALAMAN LOGIN -->
  <script src="<?= $BASE_URL_HTML ?>/assets/bundles/libscripts.bundle.js"></script>
  <script src="<?= $BASE_URL_HTML ?>/assets/bundles/vendorscripts.bundle.js"></script>
  <script src="<?= $BASE_URL_HTML ?>/assets/bundles/mainscripts.bundle.js"></script>
  <!-- JS CUSTOM -->
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  <script src="<?= $BASE_URL_HTML ?>/assets/vendor/toastr/toastr.js"></script>
  <script src="assets/custom_js/notifikasi.js"></script>
  <script src="assets/custom_js/validasiform.js"></script>
  <script src="script22.js"></script>
  <!-- END JS CUSTOM -->
</body>

</html>