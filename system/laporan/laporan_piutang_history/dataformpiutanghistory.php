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
  'laporan_piutang_history'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();
$idCabang = $dataLogin['idCabang'];
  

extract($_REQUEST);


$sqlUpdate = $db->prepare('SELECT idPiutang, namaCustomer, jumlahPembayaran, sisaPiutang, balistars_piutang.jenisPembayaran as jenisPembayaranPiutang, balistars_piutang.bankTujuanTransfer as bankTujuan 
  FROM balistars_piutang
  inner join balistars_penjualan
  on balistars_piutang.noNota=balistars_penjualan.noNota
  WHERE idPiutang=?');
$sqlUpdate->execute([$idPiutang]);
$dataUpdate=$sqlUpdate->fetch();
if($dataUpdate['bankTujuan']=='0'){
  $bankTujuan = 'Cash';
}
elseif($dataUpdate['bankTujuan']=='-'){
  $bankTujuan = 'PPN Bayar Dinas';
}
else{
  $sql = $db->prepare('SELECT namaBank 
    FROM balistars_Bank
    WHERE idBank=?');
  $sql->execute([$dataUpdate['bankTujuan']]);
  $data=$sql->fetch();
  $bankTujuan=$data['namaBank'];
}
?>

<form id="formPiutangHistory">
  <input type="hidden" name="idPiutang" id="idPiutang" value="<?=$idPiutang?>">
  <div class="row">
    <div class="form-group col-md-4">
        <label class="col-form-label">Nama Customer:</label>
        <input type="text" class="form-control" id="namaCustomer" value="<?=$dataUpdate['namaCustomer']?>" readonly>
      </div>
    <div class="form-group col-md-4">
        <label class="col-form-label">Down Payment:</label>
        <input type="text" class="form-control" id="jumlahPembayaran" value="<?=ubahToRp($dataUpdate['jumlahPembayaran'])?>" readonly>
     </div> 
    <div class="form-group col-md-4">
        <label class="col-form-label">Sisa Piutang :</label>
        <input type="text" class="form-control" id="sisaPiutang" value="<?=ubahToRp($dataUpdate['sisaPiutang'])?>" readonly>
     </div> 
     <div class="col-md-12">
      <label class="col-form-label">DATA SEBELUMNYA</label>
      <div class="row">
        <div class="form-group col-md-6">
            <label class="col-form-label">Jenis Pembayaran:</label>
            <input type="text" class="form-control" id="jenisPembayaran" value="<?=$dataUpdate['jenisPembayaranPiutang']?>" readonly>
         </div>
        <div class="form-group col-md-6">
            <label class="col-form-label">Bank Transfer:</label>
            <input type="text" class="form-control" id="bankTujuanTransfer" value="<?=$bankTujuan?>" readonly>
         </div>
      </div>
     </div>
     <div class="col-md-12">
      <label class="col-form-label">DATA PERUBAHAN</label>
      <div class="row">
        <div class="form-group col-md-6">
          <label class="col-form-label">Jenis Pembayaran</label>
          <select name="jenisPembayaran" id="jenisPembayaranSearch" class="form-control select2" onchange="showJenisPembayaran()">
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
        <div id="boxJenisPembayaran" class="form-group col-md-6">
        </div>
      </div>
     </div>
     <div class="form-group col-md-6">
       
     </div>
    <div class="form-group col-md-6">
      <button type="button" class="btn btn-primary" onclick="prosesPiutangHistory()">
        <i class="fa fa-save"></i> <br> Save
      </button>
    </div>
  </div>
</form>
<br>
