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
  'form_mesin_input'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

$idPerforma = '';
$tanggalPerforma = date('d-m-Y');
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * FROM balistars_performa_mesin_input 
  where idPerforma=?');
$sqlUpdate->execute([$idPerforma]);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  $tanggalPerforma = konversiTanggal($dataUpdate['tanggalPerforma']??'');
  $dataUpdate['qty'] = ubahToRp($dataUpdate['qty']??'');
  $dataUpdate['luas'] = ubahToRp($dataUpdate['luas']??'');
}

?>
<form id="formMesinInput">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idPerforma"  value="<?=$idPerforma?>">
  <input type="hidden" name="idCabang"  value="<?=$dataLogin['idCabang']?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">

  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tanggal Performa</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalPerforma" id="tanggalPerforma" value="<?=$tanggalPerforma?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <label>Jenis Order</label>
      <select name="jenisOrder" class="form-control select2" id="jenisOrder">
        <?php
        $arrayjenisOrder=array('Indoor','Outdoor','UV');
        for($i=0; $i<count($arrayjenisOrder); $i++){
          $selected=selected($arrayjenisOrder[$i],$dataUpdate['jenisOrder']??'');
          ?>
          <option value="<?=$arrayjenisOrder[$i]?>" <?=$selected?>> <?=$arrayjenisOrder[$i]?> </option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-6">
      <label> No Nota </label>
      <input type="text" class="form-control" placeholder="Input No Nota" name="noNota" id="noNota" value="<?=$dataUpdate['noNota']??''?>">
    </div>
    <div class="col-sm-6">
      <label>Nama Customer</label>
      <input type="text" class="form-control" placeholder="Input nama Customer" name="namaCustomer" id="namaCustomer" value="<?=$dataUpdate['namaCustomer']??''?>">
    </div>
  </div>  
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Nama Bahan</label>
      <input type="text" class="form-control" placeholder="Input nama Bahan" name="namaBahan" id="namaBahan" value="<?=$dataUpdate['namaBahan']??''?>">
    </div>
    <div class="col-sm-6">
      <label>Ukuran (cmxcm)</label>
      <input type="text" class="form-control" onkeyup="showLuas()" placeholder="Input Ukuran" name="ukuran" id="ukuran" value="<?=$dataUpdate['ukuran']??''?>">
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-6">
      <label>QTY</label>
      <input type="text" class="form-control" onkeyup="showLuas()" placeholder="Input qty" name="qty" id="qty" value="<?=$dataUpdate['qty']??''?>">
    </div>
    <div class="col-sm-6">
      <label>Luas</label>
      <input type="text" class="form-control" placeholder="0" name="luas" id="luas" value="<?=$dataUpdate['luas']??''?>" readonly>
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMesinInput()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>