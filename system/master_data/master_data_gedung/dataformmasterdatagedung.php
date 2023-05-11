<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';

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
  'master_data_gedung'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idGedung = '';
$readonly      = '';
extract($_REQUEST);

if($flag == 'update'){
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_gedung
  where idGedung = ?');
$sqlUpdate->execute([$idGedung]);
$dataUpdate = $sqlUpdate->fetch();

?>
<form id="formMasterDataGedung">
  <input type="hidden" name="flag" value="<?=$flag?>">
  <input type="hidden" name="idGedung"  value="<?=$idGedung?>">
  <input type="hidden" name="parameterOrder" value="<?=$parameterOrder?>">


  <div class="form-group row">
    <div class="col-sm-6">
      <label>Nama Gedung</label>
      <input type="text" class="form-control" name="namaGedung" placeholder="Nama Gedung" id="namaGedung" value="<?=$dataUpdate['namaGedung']??''?>">
    </div>
    <div class="col-sm-6">
      <label>Cabang</label>
      <select name="idCabang" class="form-control select2" style="width: 100%;" required>
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
    <div class="col-sm-12">
      <label>Keterangan</label>
      <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="keterangan" value="<?=$dataUpdate['keterangan']??''?>">
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataGedung()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>