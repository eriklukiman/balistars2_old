<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once 'fungsinomor.php';

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
  'hutang_mesin'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flag       = '';
$readonly      = '';
$idHutangMesin ='';
$noNota     ='';
$tanggalPembayaran = date('d-m-Y');
//$tanggalCair = date('d-m-Y');
extract($_REQUEST);

$sqlPembelian = $db->prepare('
  SELECT * FROM balistars_pembelian_mesin 
  where noNota=?');
$sqlPembelian->execute([$noNota]);
$dataPembelian=$sqlPembelian->fetch();

$sqlUpdate = $db->prepare('
  SELECT * FROM balistars_hutang_mesin 
  where idHutangMesin=?');
$sqlUpdate->execute([
  $idHutangMesin]);
$dataUpdate=$sqlUpdate->fetch();

$tanggalPembelian=konversiTanggal($dataPembelian['tanggalPembelian']);
$totalPembayaranAwal=0;
if($dataUpdate){
  $sql=$db->prepare('
    SELECT * FROM balistars_hutang_mesin 
    where noNota=? 
    and statusCair=? 
    and tanggalPembayaran<=? 
    order by tanggalPembayaran');
  $sql->execute([
    $noNota,
    "Cair",
    konversiTanggal($tanggalPembayaran)]);
  $hasil=$sql->fetchAll();

  foreach ($hasil as $cek) {
    if($cek['idHutangMesin']==$idHutangMesin){
      break;
    }
    $totalPembayaranAwal=$totalPembayaranAwal+$cek['jumlahPembayaran'];
  }
  $jumlahPembayaran=ubahToRp($dataUpdate['jumlahPembayaran']);
  $tanggalCair=konversiTanggal($dataUpdate['tanggalCair']);
  $tanggalPembayaran=konversiTanggal($dataUpdate['tanggalPembayaran']);
  //$flag="update";
}
else{
  $sqlHutang=$db->prepare('
    SELECT SUM(jumlahPembayaran) as totalPembayaran 
    FROM balistars_hutang_mesin 
    where noNota=? 
    and statusCair=?');
  $sqlHutang->execute([
    $noNota,
    "Cair"]);
  $dataHutang=$sqlHutang->fetch();
  $totalPembayaranAwal=$dataHutang['totalPembayaran'];
  $jumlahPembayaran='';
  $tanggalCair=date('d-m-Y');
}

?>
<form id="formBayarPembelianMesin">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idHutangMesin" value="<?=$idHutangMesin?>">
  <input type="hidden" name="noNota" value="<?=$noNota?>">
  <input type="hidden" name="jenisPembayaran" value="Giro">
  <div class="row">
    <div class="col-sm-6">
      <div class="form-group">
        <label>Tanggal Order <?=$noNota?></label>
        <input type="text" name="tanggalPembelian" id="tanggalPembelian" class="form-control" value="<?=$tanggalPembelian?>" readonly>
      </div>
      <div class="form-group">
        <label>Tanggal Pembayaran Hutang</label>
        <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
          <input type="tanggal" class="form-control" name="tanggalPembayaran" id="tanggalPembayaran" value="<?=$tanggalPembayaran?>"  autocomplete="off" >
          <div class="input-group-append">                                            
            <button class="btn btn-outline-secondary" type="button">
              <i class="fa fa-calendar"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Grand Total Order (Rp)</label>
        <input type="text" name="grandTotal" id="grandTotal" class="form-control form-control-lg" value="<?=ubahToRp($dataPembelian['grandTotal'])?>" readonly>
      </div>
      <div class="form-group">
        <label>Hutang Awal (Rp)</label>
        <input type="text" name="sisaHutangAwal" id="sisaHutangAwal" class="form-control" value="<?=ubahToRp($dataPembelian['grandTotal']-$totalPembayaranAwal)?>" readonly>
      </div>
      <label>No Giro</label>
      <div class="form-group">
         <input type="text" name="noGiro" id="noGiro" class="form-control" value="<?=$dataUpdate['noGiro']?>" placeholder="input no giro">
      </div>
    </div>

    <div class="col-sm-6">
      <div class="form-group">
        <label>Jumlah Pembayaran (Rp)</label>
        <input type="text" name="jumlahPembayaran" id="jumlahPembayaran" class="form-control" value="<?=$jumlahPembayaran?>" placeholder="0" onkeyup=" ubahToRp('#jumlahPembayaran'); showSisaHutang();">
      </div>
      <div class="form-group">
        <label>Tanggal cair</label>
        <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
          <input type="tanggal" class="form-control" name="tanggalCair" id="tanggalCair" value="<?=$tanggalCair?>"  autocomplete="off" >
          <div class="input-group-append">                                           
            <button class="btn btn-outline-secondary" type="button">
              <i class="fa fa-calendar"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Bank Asal Transfer</label>
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
      <div class="form-group">
        <label>Hutang Setelah Pembayaran (Rp)</label>
        <input type="text" name="sisaHutang" id="sisaHutang" value="<?=ubahToRp($dataPembelian['grandTotal']-$totalPembayaranAwal-$dataUpdate['jumlahPembayaran'])?>" class="form-control" placeholder="0" readonly>
      </div>
      <div class="form-group">
        <button type="button" class="btn btn-primary" onclick="prosesBayarPembelianMesin();">
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
      <th>Hutang Awal</th>
      <th>Bank Asal Transfer</th>
      <th>Jumlah Pembayaran</th>
      <th>No Giro</th>
      <th>Tanggal Cair</th>
      <th>Status Pembayaran</th>
      <th>Sisa Hutang</th>
      <th>Aksi</th>
    </thead>
    <tbody id="dataDaftarPembayaranMesinTersimpan">
    </tbody>
  </table>
</div>
