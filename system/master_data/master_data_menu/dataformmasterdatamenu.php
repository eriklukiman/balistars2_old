<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';

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
  'master_data_menu'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}


$idMenu = '';
$readonly      = '';
extract($_REQUEST);

if ($flag == 'update') {
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_menu
  where idMenu = ?');
$sqlUpdate->execute([$idMenu]);
$dataUpdate = $sqlUpdate->fetch();

?>
<form id="formMasterDataMenu">
  <input type="hidden" name="flag" value="<?= $flag ?>">
  <input type="hidden" name="idMenu" value="<?= $idMenu ?>">
  <input type="hidden" name="parameterOrder" value="<?= $parameterOrder ?>">

  <div class="form-group row">
    <div class="col-sm-12">
      <label> Pilih Grup </label>
      <select name="namaKelompok" class="form-control select2" id="namaKelompok" style="width: 100%;" required>
        <option value="">Pilih Kelompok</option>
        <?php
        $arrayTipe = array('master_data', 'proses', 'laporan');
        for ($i = 0; $i < count($arrayTipe); $i++) {
          $selected = selected($arrayTipe[$i], $dataUpdate['namaKelompok'] ?? '');
        ?>
          <option value="<?= $arrayTipe[$i] ?>" <?= $selected ?>> <?= $arrayTipe[$i] ?> </option>
        <?php
        }
        ?>
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-12">
      <label> Menu</label>
      <input type="text" class="form-control" name="namaMenu" placeholder="Input Menu" id="namaMenu" value="<?= $dataUpdate['namaMenu'] ?? '' ?>">
    </div>
  </div>
  <div class="form-group row">
    <div class="col-md-12">
      <label>Index Menu</label>
      <input type="text" class="form-control" name="indexMenu" placeholder="Index Menu" id="indexMenu" onkeyup="ubahToRp('#indexMenu')" value="<?= $dataUpdate['indexMenu'] ?? '' ?>">
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-12">
      <label> Icon </label>
      <input type="text" class="form-control" name="icon" placeholder="Input icon" id="icon" value="<?= $dataUpdate['icon'] ?? '' ?>">
    </div>
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataMenu()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>