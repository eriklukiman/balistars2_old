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
  'master_data_user'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$sqlUser = $db->prepare('SELECT * from balistars_user
  inner join jabatan
  on jabatan.idJabatan = user.idJabatan
  inner join pegawai
  on pegawai.idPegawai = user.idPegawai
  where user.idUser = ?');
$sqlUser->execute([$idUserAccount]);
$dataUser = $sqlUser->fetch();
?>

<input type="hidden" id="idUserAccount" value="<?= $idUserAccount ?>">
<input type="hidden" id="idPegawai" value="<?= $idPegawai ?>">
<div class="form-row">
  <div class="form-group col-sm-5">
    <label> Menu <span id="labelMenu">Tidak Boleh Kosong</span></label>
    <select id="idMenu" class="form-control select2" onchange="kirimMenu('<?=$idUserAccount?>')">
      <option value="">Pilih Menu</option>
      <?php
      $sqlMenu = $db->prepare('SELECT * from balistars_menu WHERE statusMenu = ? order by indexMenu');
      $sqlMenu->execute(['Aktif']);
      $dataMenu = $sqlMenu->fetchAll();
      foreach ($dataMenu as $row) {
      ?>
        <option value="<?= $row['idMenu'] ?>">
          <?= $row['namaMenu'] ?>
        </option>
      <?php
      }
      ?>
    </select>
  </div>
  <div id="formMenuSub"></div>
  <div class="form-group col-sm-2">
    <label><i class="fa fa-save"></i> Aksi</label><br>
    <button type="button" class="btn btn-primary btn-lg" onclick="prosesMenuUser()">
      <i class="fa fa-save"></i> Simpan
    </button>
  </div>
</div>

<div class="alert alert-success" role="alert">
  <h6><i class="fa fa-list-ol"></i> Daftar Menu Yang Dipilih</h6>
</div>
<div style="overflow-x: auto;">
  <table class="table table-custom table-hover">
    <thead class="alert alert-success">
      <th class="w-5">No</th>
      <th class="w-15">Aksi</th>
      <th class="w-30">Menu</th>
      <th class="w-50">Sub Menu</th>
      <th class="w-50">Tipe Menu</th>
    </thead>
    <tbody>
      <?php
      $sqlMenuUser = $db->prepare('SELECT * from balistars_user_detail LEFT JOIN balistars_menu ON balistars_user_detail.idMenu=balistars_menu.idMenu LEFT JOIN balistars_menu_sub ON balistars_user_detail.idMenuSub=balistars_menu_sub.idMenuSub where idUser=? order by balistars_menu_sub.namaMenuSub ASC');
      $sqlMenuUser->execute([$idUserAccount]);
      $dataMenuUser = $sqlMenuUser->fetchALl();
      $n = 1;
      foreach ($dataMenuUser as $row) {
      ?>
        <tr>
          <td><?= $n ?></td>
          <td>
            <button type="button" class="btn btn-danger" onclick="deleteMenu('<?= $row['idUserDetail'] ?>','<?= $idUserAccount ?>')">
              <i class="fa fa-trash"></i>
            </button>
          </td>
          <td><?= $row['namaMenu'] ?></td>
          <td><?= $row['namaMenuSub'] ?></td>
          <td>
            <?php
            $btn1 = 'btn btn-success';
            $btn2 = 'btn btn-success';
            $btn3 = 'btn btn-success';
            if ($row['tipeEdit'] != '1') {
              $btn1 = 'btn btn-danger';
            }
            if ($row['tipeDelete'] != '1') {
              $btn2 = 'btn btn-danger';
            }
            if ($row['tipeA2'] != '1') {
              $btn3 = 'btn btn-danger';
            }

            ?>

            <button type="button" title="Edit" class="<?= $btn1 ?>" style="color: white;" onclick="prosesTipeEdit('<?= $row['idUserDetail'] ?>')">
              <i>Edit</i>
            </button>
            <button type="button" title="Hapus" class="<?= $btn2 ?>" onclick="prosesTipeDelete('<?= $row['idUserDetail'] ?>')">
              <i>Delete</i>
            </button>
            <button type               = "button" 
              title              = "Edit"
              class              = "<?=$btn3?>"  
              style              = "color: white;"
              onclick = "prosesTipeA2('<?=$row['idUserDetail']?>')">
              <i>A2</i>
            </button>
          </td>
        </tr>
      <?php
        $n++;
      }
      ?>
    </tbody>
  </table>
</div>