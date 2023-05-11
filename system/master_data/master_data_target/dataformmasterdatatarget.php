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
  'master_data_target'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idTarget = '';
$readonly      = '';
$tanggalAwal = date('d-m-Y');
$tanggalAkhir = date('d-m-Y');
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_target
  where idTarget = ?');
$sqlUpdate->execute([$idTarget]);
$dataUpdate = $sqlUpdate->fetch();

if($dataUpdate){
  $tanggalAwal = konversiTanggal($dataUpdate['tanggalAwal']??'');
  $tanggalAkhir = konversiTanggal($dataUpdate['tanggalAkhir']??'');
  $dataUpdate['target'] = ubahToRp($dataUpdate['target']);
}
?>
<form id="formMasterDataTarget">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idTarget"  value="<?=$idTarget?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-6">
      <label>Jenis Penjualan</label>
      <select name="idJenisPenjualan" class="form-control select2" id="jenis" style="width: 100%;" required>
        <option value=""> Pilih Jenis Penjualan </option>
        <?php
        $sqlJenisPenjualan=$db->prepare('SELECT * FROM balistars_jenis_penjualan where statusJenisPenjualan=?');
        $sqlJenisPenjualan->execute(['Aktif']);
        $dataJenisPenjualan = $sqlJenisPenjualan->fetchAll();
        foreach($dataJenisPenjualan as $data){
          $selected=selected($data['idJenisPenjualan'],$dataUpdate['idJenisPenjualan']??'');
          ?>
          <option value="<?=$data['idJenisPenjualan']?>" <?=$selected?>><?=$data['jenisPenjualan']?></option>
          <?php
        }
        ?>
      </select>
    </div>
    <div class="col-sm-6">
      <label>Cabang</label>
      <select name="idCabang" class="form-control select2" id="cabang" style="width: 100%;" required>
        <option value=""> Pilih Cabang </option>
        <?php
        $sqlCabang=$db->prepare('SELECT * FROM balistars_cabang where statusCabang=? order by namaCabang');
        $sqlCabang->execute(['Aktif']);
        $dataCabang = $sqlCabang->fetchAll();
        foreach($dataCabang as $data){
          $selected=selected($data['idCabang'],$dataUpdate['idCabang']??'');
          ?>
          <option value="<?=$data['idCabang']?>" <?=$selected?>><?=$data['namaCabang']?></option>
          <?php
        }
        ?>
      </select>
    </div>
  </div>  
  <div class="form-group row">
    <div class="col-sm-6">
      <label>Tanggal Awal Target</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalAwal" id="tanggalAwal" value="<?=$tanggalAwal?>"  autocomplete="off">
        <div class="input-group-append">                                            
          <button class="btn btn-outline-secondary" type="button">
            <i class="fa fa-calendar"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <label>Tanggal Akhir Target</label>
      <div class="input-group date"  data-date-autoclose="true" data-provide="datepicker"  data-date-format="dd-mm-yyyy">
        <input type="tanggal" class="form-control" name="tanggalAkhir" id="tanggalAkhir" value="<?=$tanggalAkhir?>"  autocomplete="off">
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
      <label>Target</label>
      <input type="text" class="form-control" placeholder="target" name="target" id="target" onkeyup="ubahToRp('#target')" value="<?=$dataUpdate['target']??''?>">
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataTarget()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>