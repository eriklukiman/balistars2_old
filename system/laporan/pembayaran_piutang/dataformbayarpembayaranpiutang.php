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
  'pembayaran_piutang'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flag       = '';
$tanggalPembayaran = date('d-m-Y');
//$tanggalCair = date('d-m-Y');
extract($_REQUEST);


$sqlUpdate = $db->prepare('
  SELECT * FROM balistars_piutang 
  where noNota=? 
  order by sisaPiutang');
$sqlUpdate->execute([
  $noNota]);
$dataUpdate=$sqlUpdate->fetch();


?>
<form id="formPembayaranPiutang">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="noNota" value="<?=$noNota?>">
  <div class="row">
    <div class="col-sm-4">
      <div class="form-group">
        <label>Tanggal Order</label>
        <input type="text" name="tanggalPenjualan" id="tanggalPenjualan" class="form-control form-control-lg" value="<?=konversiTanggal($dataUpdate['tanggalPenjualan'])?>" readonly>
      </div>
      <div class="form-group">
        <label>Tanggal Pembayaran Piutang</label>
        <input type="text" name="tanggalPembayaran" id="tanggalPembayaran" class="form-control form-control-lg" style="margin-right: 3px;" required value="<?=$tanggalPembayaran?>" readonly>
      </div>
      <div class="form-group">
        <label>Grand Total Order (Rp)</label>
        <input type="text" name="grandTotal" class="form-control form-control-lg" value="<?=ubahToRp($dataUpdate['grandTotal'])?>" readonly>
      </div>
      <div class="form-group">
        <label>Piutang Awal (Rp)</label>
        <input type="text" name="sisaPiutangAwal" id="sisaPiutangAwal" class="form-control form-control-lg" value="<?=ubahToRp($dataUpdate['sisaPiutang'])?>" readonly>
      </div>
    </div>

    <div class="col-sm-4">
      <div class="form-group">
        <label>Jumlah Pembayaran (Rp)</label>
        <input type="text" name="jumlahPembayaran" id="jumlahPembayaran" class="form-control form-control-lg" placeholder="0" required onkeyup=" ubahToRp('#jumlahPembayaran'); showSemua();">
      </div>
      <div class="form-group">
        <label>Piutang Setelah Pembayaran (Rp)</label>
        <input type="text" name="sisaPiutang" id="sisaPiutang" class="form-control form-control-lg" placeholder="0" onkeyup="ubahToRp('#sisaPiutang')" readonly>
      </div>
      <div class="form-group">
        <label>Status Pembayaran (Rp)</label>
        <input type="text" name="statusPembayaran" id="statusPembayaran" class="form-control form-control-lg" placeholder="Belum Lunas" readonly>
      </div>
      <div class="form-group">
        <label>Kembalian (Rp)</label>
        <input type="text" name="kembalian" id="kembalian" placeholder="0" class="form-control form-control-lg" onkeyup="ubahToRp('#kembalian')" readonly>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="form-group">
        <label>Jenis Pembayaran</label>
        <select name="jenisPembayaran" id="jenisPembayaran" class="form-control select2" onchange="showJenisPembayaran()">
          <option value="">Pilih Jenis Pembayaran</option>
          <?php
          $arrayPembayaran=array('Cash','Transfer','PPN');
          for($i=0; $i<count($arrayPembayaran); $i++){
            ?>
            <option value="<?=$arrayPembayaran[$i]?>"> <?=$arrayPembayaran[$i]?> </option>
            <?php
          }
          ?>
        </select>
      </div>
      <div id="boxJenisPembayaran">  
      </div>
      <div class="form-group">
        <label>Biaya Admin (Rp)</label>
        <input type="text" name="biayaAdmin" id="biayaAdmin" placeholder="0" class="form-control form-control-lg" onkeyup="ubahToRp('#biayaAdmin')" value="0">
      </div>
      <div class="form-group">
        <label>PPH (Rp)</label>
        <input type="text" name="PPH" id="PPH" class="form-control form-control-lg" placeholder="0" onkeyup="ubahToRp('#PPH')" value="0">
      </div>
      <div class="form-group">
        <button type="button" class="btn btn-primary" onclick="prosesPembayaranPiutang();">
          <i class="fa fa-save"></i><br>Save
        </button>
      </div>
    </div>
  </div>
</form>


<div style="overflow-x: auto;">
  <table class="table table-custom table-hover">
    <thead class="alert alert-info">
      <th>Tanggal</th>
      <th>Grand Total</th>
      <th>Jumlah Pembayaran</th>
      <th>Sisa Piutang</th>
      <th>Bank Tujuan</th>
      <th>PPH</th>
      <th>Biaya Admin</th>
      <th>Aksi</th>
    </thead>
    <tbody id="dataDaftarPembayaranPiutangTersimpan">
    </tbody>
  </table>
</div>
