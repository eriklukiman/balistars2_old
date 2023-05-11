<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
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
  'master_data_biaya_klik'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idBiayaKlik = '';
$readonly      = '';
$tanggalBiaya = date('d-m-Y');
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_biaya_klik
  where idBiayaKlik = ?');
$sqlUpdate->execute([$idBiayaKlik]);
$dataUpdate = $sqlUpdate->fetch();
if($dataUpdate){
  $tanggalBiaya = konversiTanggal($dataUpdate['tanggalBiaya']??'');
  $dataUpdate['jumlahBiaya'] = ubahToRp($dataUpdate['jumlahBiaya']??'');
}

?>
<form id="formMasterDataBiayaKlik">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idBiayaKlik"  value="<?=$idBiayaKlik?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-6">
      <label>Cabang</label>
      <select name="idCabang" class="form-control select2" style="width: 100%;" required>
        <option value=""> Pilih Cabang </option>
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
      </select>
    </div>
    <div class="col-sm-6">
      <label>Tanggal Biaya</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control form-control-lg" name="tanggalBiaya" id="tanggalBiaya" value="<?=$tanggalBiaya?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
  </div>  
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Jumlah Biaya</label>
      <input type="text" class="form-control" placeholder="Jumlah Biaya" name="jumlahBiaya" id="jumlahBiaya" onkeyup="ubahToRp('#jumlahBiaya')" value="<?=$dataUpdate['jumlahBiaya']??''?>">
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataBiayaKlik()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>