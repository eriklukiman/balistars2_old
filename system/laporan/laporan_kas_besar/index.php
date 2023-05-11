<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';

session_start();

$idUser    = '';
$tokenCSRF = '';

extract($_SESSION);

//DESKRIPSI ID USER
$idUserAsli = dekripsi($idUser, $kunciRahasia);

//MENGECEK APAKAH ID USER YANG LOGIN ADA PADA DATABASE
$sqlCekUser = $db->prepare('SELECT idUser from balistars_user where idUser=?');
$sqlCekUser->execute([$idUserAsli]);
$dataCekUser = $sqlCekUser->fetch();

//MENGECEK APAKAH USER INI BERHAK MENGAKSES MENU INI
$sqlCekMenu = $db->prepare('SELECT * from balistars_user_detail 
  inner join balistars_menu_sub 
  on balistars_menu_sub.idMenuSub = balistars_user_detail.idMenuSub
  where balistars_user_detail.idUser = ?
  and namaFolder = ?');
$sqlCekMenu->execute([
  $idUserAsli,
  'laporan_kas_besar'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}


$sqlInformasi    = $db->query('SELECT * FROM balistars_information');
$dataInformasi = $sqlInformasi->fetch();
$logo            = $BASE_URL_HTML.'/assets/images/'.$dataInformasi['logo'];

//informasi user login
$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

extract($_REQUEST);

?>
<!DOCTYPE html>
<html>
<head>
  <title>Kas Besar <?=$dataInformasi['nama']?></title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <meta name="description" content="Pos TempatKita">
  <meta name="author" content="TempatKita Software, develop by: Gusti Wijayakusuma">

  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/toastr/toastr.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/select2/select2.css"> 
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/datepicker/datepicker.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/main2.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/color_skins.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/loader.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="css/custom.css">
  <?php
  icon($logo);
  ?>
</head>

<!-- Loader Over Lay -->
<div class="overlay">
  <div class="overlay__inner">
    <div class="overlay__content"><span class="spinner"></span></div>
  </div>
</div>
<!-- End Loader -->

<body class="theme-blue" onload="dataDaftarKasBesar()">

  <div id="wrapper">
    <?php
    navBarTop($logo,$BASE_URL_HTML);
  navBarSide($dataLogin,$db,$idUserAsli,$BASE_URL_HTML);
    ?>
    <div id="main-content">
      <div class="container-fluid">
        <!-- Block Header -->
        <div class="block-header">
          <div class="row">
            <div class="col-sm-12">                        
              <h2><a href="javascript:void(0);" class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a><?= $dataInformasi['nama'] ?> <?= $dataLogin['namaCabang'] ?></h2>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#"><i class="icon-home"></i></a></li>
                <li class="breadcrumb-item active">Laporan Kas Besar</li>
              </ul>
            </div>            
            <div class="col-sm-12 text-right"></div>
          </div>
        </div>
        <!-- End Block Header -->
        <!-- Clearfix -->
        <div class="row clear-fix">
          <div class="col-sm-12">
            <div class="card planned_task">
              <!-- Header -->
              <div class="header">
                <div class="row">
                  <div class="col-sm-6">
                    <?php 
                    $style='';
                    if($dataCekMenu['tipeA2']=='0'){
                         $style = 'style="display: none;"';
                    }
                    ?>
                    <h5><i class="fa fa-list"></i> Daftar Kas Besar</h5>

                  </div>

                  <div class="col-sm-6">
                    <button type    = "button"
                            title   = "Print" 
                            class   = "btn btn-info bg-success border-success float-right ml-2" 
                            onclick = "printLaporan();">
                      <i class="fa fa-print">  PRINT</i>
                    </button>
                    <button type    = "button"
                            title   = "Export" 
                            class   = "btn btn-info float-right" 
                            onclick = "exportExcel();">
                      <i class="fa fa-print">  EXCEL</i>
                    </button>
                  </div>
                </div>
              </div>
              <!-- End Header -->
              <!-- Body -->
              <div class="body">
                <input type="hidden" id="parameterOrder">
                <div class="row form-group">
                  <div class="col-sm-3" >
                    <label>Rentang</label>
                    <input type="datepicker" class="form-control" name="rentang" id="rentang" onchange="dataDaftarKasBesar()">
                  </div>
                  <?php 
                  if($dataLogin['tipeUser']=='Headoffice'){
                   ?>
                  <div class="col-sm-3" >
                    <label>Cabang</label>
                    <select name="idCabang" id="idCabangSearch" class="form-control select2" style="width: 100%;" onchange="dataDaftarKasBesar()">
                      <?php
                      $sqlCabang=$db->prepare('SELECT * FROM balistars_cabang where statusCabang=? order by namaCabang');
                      $sqlCabang->execute(['Aktif']);
                      $dataCabang = $sqlCabang->fetchAll();
                      foreach($dataCabang as $data){
                        $selected=selected($data['idCabang'],$dataUpdate['idCabang']??'');
                        ?>
                        <option value="<?=$data['idCabang']?>" <?=$selected?>><?=$data['namaCabang']?></option>
                        <?php
                      }
                      ?>
                      <option value="0"> Semua Cabang </option>
                    </select>
                  </div>
                   <?php
                  } elseif($dataLogin['tipeUser']=='Kontrol Area'){
                    ?>
                  <div class="col-sm-2" >
                    <label>Cabang</label>
                    <select name="idCabang" id="idCabangSearch" class="form-control select2" style="width: 100%;" onchange="dataDaftarKasBesar()">
                      <?php
                      $sqlCabang=$db->prepare('SELECT * FROM balistars_cabang where statusCabang=? and area=? order by namaCabang');
                      $sqlCabang->execute(['Aktif',$dataLogin['area']]);
                      $dataCabang = $sqlCabang->fetchAll();
                      foreach($dataCabang as $data){
                        ?>
                        <option value="<?=$data['idCabang']?>"><?=$data['namaCabang']?></option>
                        <?php
                      }
                      ?>
                    </select>
                  </div>
                  <?php 
                  } else{
                   ?>
                   <input type="hidden" name="idCabang" id="idCabangSearch" value="<?=$dataLogin['idCabang']?>">
                   <?php 
                   } ?>
                   <div class="col-sm-2">
                    <label>Jenis Pembayaran</label>
                    <select name="jenisPembayaran" id="jenisPembayaranSearch" class="form-control select2"  onchange="dataDaftarKasBesar()" >
                      <?php
                      $arrayJenisPembayaran=array('Cash','Transfer','PPN','Semua');
                      $arrayJenisPembayaranVal=array('0','Transfer','-','Semua');
                      for($i=0; $i<count($arrayJenisPembayaran); $i++){
                        ?>
                        <option value="<?=$arrayJenisPembayaranVal[$i]?>" > <?=$arrayJenisPembayaran[$i]?> </option>
                        <?php
                      }
                      ?>
                    </select>
                  </div>
                  <div class="col-sm-2" <?=$style?>>
                    <label>Tipe</label>
                    <select name="tipe" id="tipeSearch" class="form-control select2"  onchange="dataDaftarKasBesar()" >
                      <?php
                      $arrayTipe=array('A1','A2','Semua');
                      for($i=0; $i<count($arrayTipe); $i++){
                        ?>
                        <option value="<?=$arrayTipe[$i]?>" > <?=$arrayTipe[$i]?> </option>
                        <?php
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div style="overflow-x: auto;">

                  <table class="table table-bordered table-custom">
                    <thead class="bg-info text-white">
                      <th style="width: 5%">
                        No
                      </th>
                      <th>
                        <button class="btn btn-sm btn-info" onclick="dataDaftarKasBesar()">
                          Tanggal
                        </button>
                      </th>
                      <th>
                        <button class="btn btn-sm btn-info" onclick="dataDaftarKasBesar()">
                          No Nota
                        </button>
                      </th>
                      <th style="text-align: center;">
                        <button class="btn btn-sm btn-info" onclick="dataDaftarKasBesar()">
                          Konsumen
                        </button>
                      </th>
                      <th>
                        <button class="btn btn-sm btn-info" onclick="dataDaftarKasBesar()">
                          Keterangan
                        </button>
                      </th>
                      <th>
                        <button class="btn btn-sm btn-info" onclick="dataDaftarKasBesar()">
                          Debet
                        </button>
                      </th>
                      <th>
                        <button class="btn btn-sm btn-info" onclick="dataDaftarKasBesar()">
                          Kredit
                        </button>
                      </th>
                      <th>
                        <button class="btn btn-sm btn-info" onclick="dataDaftarKasBesar()">
                          Saldo
                        </button>
                      </th>
                    </thead>
                    <tbody id="dataDaftarKasBesar"></tbody>
                  </table>
                </div>
              </div>
              <!-- End Body -->
            </div>
          </div>
        </div>
        <!-- End Clearfix -->
      </div>

      <footer>
        <?php
        footer();
        ?>
      </footer>
    </div>
  </div>


  <script src="<?=$BASE_URL_HTML?>/assets/bundles/libscripts.bundle.js"></script>    
  <script src="<?=$BASE_URL_HTML?>/assets/bundles/vendorscripts.bundle.js"></script>
  <script src="<?=$BASE_URL_HTML?>/assets/bundles/mainscripts.bundle.js"></script>
  <script src="<?=$BASE_URL_HTML?>/assets/vendor/select2/select2.min.js"></script>
  <script src="<?=$BASE_URL_HTML?>/assets/vendor/datepicker/datepicker.min.js"></script>
  <script src="<?=$BASE_URL_HTML?>/assets/vendor/toastr/toastr.js"></script>
  <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

  <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  <script src="https://kit.fontawesome.com/1f9ba5c1a4.js" crossorigin="anonymous"></script>

  <script src="<?=$BASE_URL_HTML?>/assets/custom_js/validasiform.js"></script>
  <script src="<?=$BASE_URL_HTML?>/assets/custom_js/accounting.min.js"></script>
  <script src="<?=$BASE_URL_HTML?>/assets/custom_js/rupiah.js"></script>
  <script src="<?=$BASE_URL_HTML?>/assets/custom_js/angka.js"></script>
  <script src="js/kasbesar.js"></script>
 
</body>
</html>