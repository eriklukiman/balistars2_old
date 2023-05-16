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

?>

<div class="form-group col-sm-12">
    <label>Menu Sub</label>
    <select id="idMenuSub" class="form-control select2">
        <option value="">Pilih Menu Sub</option>
        <?php
        $sqlMenuSub = $db->prepare(
            'SELECT 
                * 
            FROM 
                balistars_menu_sub 
            WHERE
                statusMenuSub=? 
                AND idMenu=? 
                AND idMenuSub NOT IN (
                    SELECT 
                        idMenuSub 
                    FROM 
                        balistars_user_detail 
                    WHERE 
                        idUser = ?
                ) 
            ORDER BY indexMenuSub'
        );
        $sqlMenuSub->execute(['Aktif', $idMenu, $idUserAccount]);
        $dataMenuSub = $sqlMenuSub->fetchAll();

        foreach ($dataMenuSub as $row) {
        ?>
            <option value="<?= $row['idMenuSub'] ?>">
                <?= $row['namaMenuSub'] ?>
            </option>
        <?php
        }
        ?>
    </select>
</div>