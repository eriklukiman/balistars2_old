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
  'master_data_user'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$idUserAccount = '';
$readonly      = '';
extract($_REQUEST);

if ($flag == 'update') {
  $readonly = 'disabled';
}

$sqlUpdate  = $db->prepare('SELECT * from balistars_user
  inner join balistars_pegawai 
    on balistars_pegawai.idPegawai = balistars_user.idPegawai
  where balistars_user.idUser = ?');
$sqlUpdate->execute([$idUserAccount]);
$dataUpdate = $sqlUpdate->fetch();

?>
<form id="formMasterDataUser">
  <input type="hidden" name="flag" value="<?= $flag ?>">
  <input type="hidden" name="idUserAccount" value="<?= $idUserAccount ?>">
  <input type="hidden" name="parameterOrder" value="<?= $parameterOrder ?>">


  <div class="form-group">
    <label><i class="fa fa-user"></i> Pilih Pegawai</label>
    <select id="idPegawai" name="idPegawai" class="form-control select2">
      <?php
      $sqlPegawai  = $db->prepare('SELECT * FROM balistars_pegawai where statusPegawai=?
        order by namaPegawai');
      $sqlPegawai->execute([
        'Aktif'
      ]);
      $dataPegawai = $sqlPegawai->fetchAll();
      foreach ($dataPegawai as $row) {
        $selected = selected($row['idPegawai'], $dataUpdate['idPegawai'] ?? '');
      ?>
        <option value="<?= $row['idPegawai'] ?>" <?= $selected ?>>
          <?= $row['namaPegawai'] ?>
        </option>
      <?php
      }
      ?>
    </select>
  </div>
  <div class="form-group">
    <label><i class="fa fa-user"></i> Pilih Tipe User</label>
    <select name="tipeUser" class="form-control select2" style="width: 100%;" required>

      <?php
      $arrayTipe = array('Headoffice', 'Kontrol Area', 'Kepala Cabang', 'Field Auditor', 'Kasir', 'Operator', 'Advertising', 'Absensi');
      for ($i = 0; $i < count($arrayTipe); $i++) {
        $selected = selected($arrayTipe[$i], $dataUpdate['tipeUser'] ?? '');
      ?>
        <option value="<?= $arrayTipe[$i] ?>" <?= $selected ?>> <?= $arrayTipe[$i] ?> </option>
      <?php
      }
      ?>
    </select>
  </div>
  <div class="form-group">
    <label><i class="fa fa-user"></i> User Name <span id="labelUserName">Tidak Boleh Kosong</span></label>
    <input type="text" class="form-control" id="userName" name="userName" placeholder="User Name" value="<?= $dataUpdate['userName'] ?? '' ?>">
  </div>
  <div class="form-group">
    <label><i class="fa fa-key"></i> Password (Tidak Boleh Kosong) <span id="labelPassword">Tidak Boleh Kosong</span></label>
    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
  </div>
  <div class="form-group">
    <button type="button" class="btn btn-primary" onclick="prosesMasterDataUser()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>
</form>