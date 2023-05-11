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
    'master_data_menu'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flag = '';
$idMenu = '';
$idMenuSub = '';

extract($_REQUEST);

$sqlUpdate = $db->prepare('SELECT * FROM balistars_menu_sub WHERE idMenuSub=?');
$sqlUpdate->execute([$idMenuSub]);
$dataUpdate = $sqlUpdate->fetch();

?>
<form id="formMasterDataMenuSub">
    <input type="hidden" name="idMenu" id="idMenu" value="<?= $idMenu ?>">
    <input type="hidden" name="flag" id="flag" value="<?= $flag ?>">
    <input type="hidden" name="idMenuSub" id="idMenuSub" value="<?= $idMenuSub ?>">
    <div class="form form-group row">
        <div class="col-sm-2">
            <label>INDEX</label>
            <input type="text" class="form-control" name="indexMenuSub" id="indexMenuSub" placeholder="Input Index Menu Sub" onkeyup="rupiah('#indexMenuSub')" value="<?= $dataUpdate['indexMenuSub'] ?? '' ?>">
        </div>
        <div class="col-sm-4">
            <label>NAMA MENU SUB</label>
            <input type="text" class="form-control" name="namaMenuSub" id="namaMenuSub" placeholder="input nama Menu Sub" value="<?= $dataUpdate['namaMenuSub'] ?? '' ?>">
        </div>
        <div class="col-sm-4">
            <label>NAMA FOLDER</label>
            <input type="text" name="namaFolder" class="form-control" id="namaFolder" placeholder="input nama folder" value="<?= $dataUpdate['namaFolder'] ?? '' ?>">
        </div>
        <div class="col-sm-2">
            <label>AKSI</label><br>
            <button type="button" class="btn btn-primary" onclick="prosesMasterDataMenuSub()">
                <strong>SIMPAN</strong>
            </button>
        </div>
    </div>
</form>


<div class="alert alert-success" role="alert">
    <i class="fa fa-list-ol pr-4"></i> <strong>DAFTAR MENU SUB</strong>
</div>
<div style="overflow-x: auto;">
    <table class="table table-custom table-hover">
        <thead class="alert alert-success">
            <th class="w-5">No</th>
            <th class="w-50">Sub Menu</th>
            <th class="w-15">Aksi</th>
        </thead>
        <tbody>
            <?php
            $sqlMenuUser = $db->prepare('SELECT * from balistars_menu_sub where idMenu=? and statusMenuSub=? order by indexMenuSub ');
            $sqlMenuUser->execute([$idMenu, 'Aktif']);
            $dataMenuUser = $sqlMenuUser->fetchALl();
            $n = 1;
            foreach ($dataMenuUser as $row) {
            ?>
                <tr>
                    <td><?= $row['indexMenuSub'] ?></td>
                    <td><?= $row['namaMenuSub'] ?></td>
                    <td>
                        <button type="button" class="btn btn-danger" onclick="deleteMenuSub('<?= $row['idMenuSub'] ?>',<?= $row['idMenu'] ?>)">
                            <i class="fa fa-trash"></i>
                        </button>
                        <button type="button" class="btn btn-warning" onclick="editMenuSub('<?= $row['idMenuSub'] ?>',<?= $row['idMenu'] ?>)">
                            <i class="fa fa-edit"></i>
                        </button>
                    </td>
                    </td>
                </tr>
            <?php
                $n++;
            }
            ?>
        </tbody>
    </table>
</div>