<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
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
  'pembelian_giro'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flag = '';
$tanggalCair = date('d-m-Y');
extract($_REQUEST);

if($tanggalCair=="" || $tanggalCair=="0" || $tanggalCair=="0000-00-00"){
  $tanggalCair=date('d-m-Y');
}

$sql=$db->prepare('
    SELECT * FROM balistars_pembelian 
    where noNota =?');
$sql->execute([
    $noNota]);
$data=$sql->fetch();

$sqlUpdate = $db->prepare('
  SELECT * FROM balistars_hutang
  where noNota=?');
$sqlUpdate->execute([
  $noNota]);
$dataUpdate=$sqlUpdate->fetch();

if($dataUpdate){
  if($dataUpdate['tanggalCair']=="" || $dataUpdate['tanggalCair']=="0" || $dataUpdate['tanggalCair']=="0000-00-00"){
  $tanggalCair=date('d-m-Y');
  } else{
  $tanggalCair = konversiTanggal($dataUpdate['tanggalCair']??'');
  }
}

?>
<form id="formPembelianGiro">
  <input type="hidden" name="dataNoNota"  value="<?=$dataNoNota?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idSupplier"  value="<?=$idSupplier?>">
  <input type="hidden" name="tipePembelian"  value="<?=$tipePembelian?>">
  <input type="hidden" name="tanggalAwal"  value="<?=$tanggalAwal?>">
  <input type="hidden" name="sisaPembelian"  value="<?=$sisaPembelian?>">
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Nama Supplier</label>
       <input type="text" class="form-control" id="noNota" value="<?=$data['namaSupplier']??''?>" readonly>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-12"> 
      <label>Tanggal</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalCair" id="tanggalCair" value="<?=$tanggalCair?>"  autocomplete="off" >
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
      <label>Bank Asat Transfer</label>
      <select name="bankAsalTransfer" id="bankAsalTransfer" class="form-control select2">
        <option value=""> Pilih Bank</option>
        <?php
        $sqlBank=$db->prepare('SELECT * FROM balistars_bank where statusBank=? order by namaBank');
        $sqlBank->execute(['Aktif']);
        $dataBank=$sqlBank->fetchAll();
        foreach($dataBank as $row){
          $selected=selected($row['idBank'],$dataUpdate['bankAsalTransfer']);
          ?>
          <option value="<?=$row['idBank']?>" <?=$selected?>> <?=$row['namaBank']?> </option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Nomor Giro</label>
      <input type="text" class="form-control" placeholder="Input Nomor faktur Pajak" name="noGiro" id="noGiro" value="<?=$data['noGiro']??''?>">
    </div>
  </div> 
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesPembelianGiro()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>
