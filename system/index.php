<?php
include_once '../library/konfigurasiurl.php'; 
include_once $BASE_URL_PHP.'/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP.'/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP.'/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP.'/system/fungsinavigasi.php';

session_start();

$idUser    = '';
$tokenCSRF = '';

extract($_SESSION);

//DESKRIPSI ID USER
$idUserAsli = dekripsi($idUser,$kunciRahasia);

//MENGECEK APAKAH ID USER YANG LOGIN ADA PADA DATABASE
$sqlCekUser = $db->prepare('SELECT idUser from balistars_user where idUser=?');
$sqlCekUser->execute([$idUserAsli]);
$dataCekUser = $sqlCekUser->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if(!$dataCekUser){
  header('location:'.$BASE_URL_HTML.'/?flagNotif=gagal');
}


$sqlInformasi    = $db->query('SELECT * FROM balistars_information');
$dataInformasi = $sqlInformasi->fetch();
$logo            = $BASE_URL_HTML.'/assets/images/'.$dataInformasi['logo'];

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Dasboard | <?=$dataInformasi['nama']?></title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <meta name="description" content="BSA">
  <meta name="author" content="TempatKita Software, develop by: Gusti Wijayakusuma">

  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/main2.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/color_skins.css">
  <?php
  icon($logo);
  ?>
</head>
<body class="theme-blue">

<div id="wrapper">
  <?php
  navBarTop($logo,$BASE_URL_HTML);
  navBarSide($dataLogin,$db,$idUserAsli,$BASE_URL_HTML);
  ?>
  <div id="main-content">
    <div class="container-fluid">
      <div class="block-header">
        <div class="row">
          <div class="col-lg-5 col-md-8 col-sm-12">                        
            <h2><a href="javascript:void(0);" class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a> <?=$dataInformasi['nama']?> Cabang <?=$dataLogin['namaCabang']?> </h2>
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><a href="#"><i class="icon-home"></i></a></li>
              <li class="breadcrumb-item active">Dashboard</li>
            </ul>
          </div>            
          <div class="col-lg-7 col-md-4 col-sm-12 text-right"></div>
        </div>
      </div>
      <div class="row clear-fix">
        <div class="col-lg-12 col-md-12">
          <div class="card planned_task">
            <div class="header">
              <h2 style="color: #17a3b8">Welcome</h2>
            </div>
            <div class="body">
            </div>
          </div>
        </div>
      </div>
    </div>
    <footer>
      <?php
      footer();
      ?>
    </footer>
  </div>
</div>

<!-- Javascript -->
<script src="<?=$BASE_URL_HTML?>/assets/bundles/libscripts.bundle.js"></script>    
<script src="<?=$BASE_URL_HTML?>/assets/bundles/vendorscripts.bundle.js"></script>
<script src="<?=$BASE_URL_HTML?>/assets/bundles/mainscripts.bundle.js"></script>
<script src="https://kit.fontawesome.com/1f9ba5c1a4.js" crossorigin="anonymous"></script>
</body>
</html>