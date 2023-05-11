<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';

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
  'form_pengeluaran_lain'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}
$idPengeluaranLain = '';
$readonly      = '';
$tanggalPengeluaranLain = date('Y-m-d');
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_pengeluaran_lain
  where idPengeluaranLain = ?');
$sqlUpdate->execute([$idPengeluaranLain]);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  $tanggalPengeluaranLain = $dataUpdate['tanggalPengeluaranLain']??'';
  $dataUpdate['nilai'] = ubahToRp($dataUpdate['nilai']);
}
?>
<form id="formPengeluaranLain">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idPengeluaranLain"  value="<?=$idPengeluaranLain?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tanggal Pengeluaran</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="yyyy-mm-dd">
        <input type="tanggal" class="form-control" name="tanggalPengeluaranLain" id="tanggalPengeluaranLain" value="<?=$tanggalPengeluaranLain?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <label>Bank</label>
      <select name="idBank" class="form-control select2" id="Bank" style="width: 100%;" required>
        <option value=""> Pilih Bank </option>
        <?php
        $sqlBank=$db->prepare('SELECT * FROM balistars_bank where statusBank=? and tipe=? order by namaBank');
        $sqlBank->execute(['Aktif',$tipe]);
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
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Kode Akunting</label>
      <select name="kodeAkunting" class="form-control select2" id="kodeAkunting" style="width: 100%;" required>
        <option value="0"> Pengeluaran Lain-lain</option>
        <?php
        $sqlKode=$db->prepare('SELECT * FROM balistars_kode_akunting where statusKodeAkunting=?  order by kodeAkunting');
        $sqlKode->execute(['Aktif']);
        $dataKode = $sqlKode->fetchAll();
        foreach($dataKode as $data){
          $selected=selected($data['kodeAkunting'],$dataUpdate['kodeAkunting']??'');
          ?>
          <option value="<?=$data['kodeAkunting']?>" <?=$selected?>><?=$data['keterangan']?></option>
          <?php
        }
        ?>
      </select>
    </div>
    <div class="col-sm-6">
      <label>Nilai</label>
       <input type="text" class="form-control" placeholder="nilai" name="nilai" id="nilai" onkeyup="ubahToRp('#nilai')" value="<?=$dataUpdate['nilai']??''?>">
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-12">
      <label>Keterangan</label>
      <input type="text" class="form-control" placeholder="keterangan" name="keterangan" id="keterangan" value="<?=$dataUpdate['keterangan']??''?>">
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesPengeluaranLain()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>