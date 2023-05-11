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
  'setor_penjualan_cash'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}


extract($_REQUEST);

$sqlUpdate  = $db->prepare('
  SELECT * FROM balistars_setor_penjualan_cash 
  inner join balistars_bank 
  on balistars_setor_penjualan_cash.idBank=balistars_bank.idBank 
  where idSetor=? ');
$sqlUpdate->execute([
  $idSetor]);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  $dataUpdate['jumlahSetor']=ubahToRp($dataUpdate['jumlahSetor']??'');
  $tanggalSetor=konversiTanggal($dataUpdate['tanggalSetor']??'');
}

?>
<form id="formSetorPenjualanCash">
  <input type="hidden" name="flag" id="flag" value="<?=$flag?>">
  <input type="hidden" name="idSetor"  value="<?=$dataUpdate['idSetor']?>">

  <div class="form-group row">
    <div class="col-sm-12">
      <label>Tanggal Setor</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalSetor" id="tanggalSetor" value="<?=$tanggalSetor?>"  autocomplete="off">
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
      <label>Jumlah setor</label>
      <input type="text" class="form-control" placeholder="Nilai Order" name="jumlahSetor" id="jumlahSetor" onkeyup="ubahToRp('#jumlahSetor')" value="<?=$dataUpdate['jumlahSetor']?>">
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Bank Tujuan Setor</label>
      <select name="idBank" class="form-control select2" id="idBank" style="width: 100%;" required>
        <?php
        $sqlBank=$db->prepare('SELECT * FROM balistars_bank where statusBank=? order by namaBank');
        $sqlBank->execute(['Aktif']);
        $dataBank = $sqlBank->fetchAll();
        foreach($dataBank as $data){
          $selected=selected($data['idBank'],$dataUpdate['idBank']??'');
          ?>
          <option value="<?=$data['idBank']?>" <?=$selected?>><?=$data['namaBank']?></option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesSetorPenjualanCash()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>