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
  'form_biaya_sub'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flag       = '';
$readonly      = '';
$idPenjualanDetail ='';
$idBiaya     ='';
$nilaiPembayaran='';
$tanggalPembayaran = date('d-m-Y');
//$tanggalCair = date('d-m-Y');

extract($_REQUEST);

$sqlPenjualan = $db->prepare('
  SELECT * FROM balistars_penjualan_detail 
  inner join balistars_penjualan 
  on balistars_penjualan_detail.noNota=balistars_penjualan.noNota 
  where idPenjualanDetail=?');
$sqlPenjualan->execute([$idPenjualanDetail]);
$dataPenjualan=$sqlPenjualan->fetch();

$sqlUpdate = $db->prepare('
  SELECT * FROM balistars_biaya_sub 
  where idBiaya=?');
$sqlUpdate->execute([$idBiaya]);
$dataUpdate=$sqlUpdate->fetch();

if($dataUpdate){
  $nilaiPembayaran=ubahToRp($dataUpdate['nilaiPembayaran']);
  $tanggalPembayaran=konversiTanggal($dataUpdate['tanggalPembayaran']);
}

?>
<form id="formBiayaSub">
  <input type="hidden" name="idPenjualanDetail" value="<?=$idPenjualanDetail?>">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idCabang" value="<?=$dataPenjualan['idCabang']?>">
  <input type="hidden" name="idBiaya" value="<?=$idBiaya?>">
  <input type="hidden" name="jumlahPenjualanCash" value="<?=$nilaiPembayaran?>">
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Projek</label>
      <input type="text" class="form-control" name="project" value="<?=$dataPenjualan['namaBahan']?>" readonly>
    </div>
    <div class="col-sm-6">
      <label>Nilai Penjualan</label>
      <input type="text" name="nilaiPenjualan" value="<?=ubahToRp($dataPenjualan['nilai'])?>" class="form-control" readonly>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tanggal Pembayaran</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalPembayaran" id="tanggalPembayaran" value="<?=$tanggalPembayaran?>"  autocomplete="off" >
        <div class="input-group-append">             
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <label>Nama Supplier</label>
      <input type="text" class="form-control" name="namaSupplier" value="<?=$dataUpdate['namaSupplier']?>" placeholder="Input Nama Supplier" required>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Jumlah Pembayaran</label>
      <input type="text" name="nilaiPembayaran" id="nilaiPembayaran" class="form-control" value="<?=$nilaiPembayaran?>" placeholder="0" onkeyup=" ubahToRp('#nilaiPembayaran');">
    </div>
    <div class="col-sm-6">
      <label>Keterangan</label>
      <input type="text" class="form-control" name="keterangan" value="<?=$dataUpdate['keterangan']?>" placeholder="Input keterangan" required>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-6"></div>
    <div class="col-sm-6">
      <button type="button" class="btn btn-primary" onclick="prosesInputBiayaSub();">
        <i class="fa fa-save"></i><br>Save
      </button>
    </div>
  </div>
</form>


<div style="overflow-x: auto;">
  <table class="table table-custom table-hover">
    <thead class="alert alert-info">
      <th>Tanggal Pembayaran</th>
      <th>Supplier</th>
      <th>Jumlah Pembayaran</th>
      <th>Keterangan</th>
      <th>Aksi</th>
    </thead>
    <tbody id="dataDaftarBiayaSubTersimpan">
    </tbody>
  </table>
</div>
