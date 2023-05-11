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
  'form_transfer_bank'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idTransferBank = '';
$readonly      = '';
$tanggalTransfer = date('d-m-Y');
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_bank_transfer
  where idTransferBank = ?');
$sqlUpdate->execute([$idTransferBank]);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  $tanggalTransfer = konversiTanggal($dataUpdate['tanggalTransfer']??'');
  $dataUpdate['nilaiTransfer'] = ubahToRp($dataUpdate['nilaiTransfer']);
}
?>
<form id="formTransferBank">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idTransferBank"  value="<?=$idTransferBank?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tanggal Transfer</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalTransfer" id="tanggalTransfer" value="<?=$tanggalTransfer?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <label>Nilai Transfer</label>
       <input type="text" class="form-control" placeholder="Nilai Transfer" name="nilaiTransfer" id="nilaiTransfer" onkeyup="ubahToRp('#nilaiTransfer')" value="<?=$dataUpdate['nilaiTransfer']??''?>">
    </div>
  </div>  
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Bank Asal</label>
      <select name="idBankAsal" class="form-control select2" id="bankAsal" style="width: 100%;" required>
        <option value=""> Pilih Bank </option>
        <?php
        $sqlBank=$db->prepare('SELECT * FROM balistars_bank where statusBank=? and tipe=? order by namaBank');
        $sqlBank->execute(['Aktif',$tipe]);
        $dataBank = $sqlBank->fetchAll();
        foreach($dataBank as $data){
          $selected=selected($data['idBank'],$dataUpdate['idBankAsal']??'');
          ?>
          <option value="<?=$data['idBank']?>" <?=$selected?>><?=$data['namaBank']?></option>
          <?php
        }
        ?>
      </select>
    </div>
    <div class="col-sm-6">
      <label>Bank tujuan</label>
      <select name="idBankTujuan" class="form-control select2" id="bankTujuan" style="width: 100%;" required>
        <option value=""> Pilih Bank </option>
        <?php
        $sqlCabang=$db->prepare('SELECT * FROM balistars_bank where statusBank=? and tipe=? order by namaBank');
        $sqlCabang->execute(['Aktif',$tipe]);
        $dataCabang = $sqlCabang->fetchAll();
        foreach($dataCabang as $data){
          $selected=selected($data['idBank'],$dataUpdate['idBankTujuan']??'');
          ?>
          <option value="<?=$data['idBank']?>" <?=$selected?>><?=$data['namaBank']?></option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Keterangan</label>
      <input type="text" class="form-control" placeholder="keterangan" name="keterangan" id="keterangan" value="<?=$dataUpdate['keterangan']??''?>">
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesTransferBank()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>