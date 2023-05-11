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
  'konfirmasi_kas_kecil'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flag = '';
$idOrderKasKecil='';
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlKas=$db->prepare('SELECT * FROM balistars_kas_kecil_order inner join balistars_cabang on balistars_kas_kecil_order.idCabang=balistars_cabang.idCabang  where idOrderKasKecil=?');
$sqlKas->execute([$idOrderKasKecil]);
$data=$sqlKas->fetch();


?>
<form id="formKonfirmasiKasKecil">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idOrderKasKecil"  value="<?=$idOrderKasKecil?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">

  <div class="form-group row">
    <div class="col-sm-4">
      <label>Tanggal Order</label>
       <input type="text" class="form-control" id="tanggalOrder" value="<?=ubahTanggalIndo($data['tanggalOrder']??'')?>" readonly>
    </div>
    <div class="col-sm-4">
      <label>Cabang</label>
      <input type="text" class="form-control" id="idCabang" value="<?=$data['namaCabang']??''?>" readonly>
    </div>
    <div class="col-sm-4">
      <label>Nilai Order</label>
       <input type="text" class="form-control" id="nilai" name="nilai" value="<?=ubahToRp($data['nilai']??'')?>" readonly>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Keterangan Order</label>
      <textarea class="form-control" id="keterangan" disabled> <?=$data['keterangan']??''?></textarea>
    </div>
  </div>
  <div>
    <br> <hr>
  </div>
  <div class="form-group row">
    <div class="col-sm-6"> 
      <label>Nilai Approved</label>
      <input type="text" class="form-control" placeholder="Input Nominal" name="nilaiApproved" id="nilaiApproved" onkeyup="ubahToRp('#nilaiApproved')" value="<?=ubahToRp($data['nilaiApproved']??'')?>">
    </div>
    <div class="col-sm-6">
      <label>Bank Asal Transfer</label>
      <select name="bankAsalTransfer" id="bankAsalTransfer" class="form-control select2" style="width: 100%;" required>
        <option value=""> Pilih Bank </option>
        <?php
        $sqlBank=$db->prepare('SELECT * FROM balistars_bank where statusBank = ? order by namaBank');
        $sqlBank->execute(['Aktif']);
        $dataBank = $sqlBank->fetchAll();
        foreach($dataBank as $row){
          $selected=selected($row['idBank'],$data['bankAsalTransfer']??'');
          ?>
          <option value="<?=$row['idBank']?>" <?=$selected?>><?=$row['namaBank']?></option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Keterangan</label>
      <textarea class="form-control" id="keteranganApproval" name="keteranganApproval"> <?=$data['keteranganApproval']??''?></textarea>
    </div>
  </div> 
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesKonfirmasiKasKecil()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>
