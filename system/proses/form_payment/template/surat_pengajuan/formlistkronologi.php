<?php
include_once '../../../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsistatement.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
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

//MENGECEK DATA LOGIN PEGAWAI
$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

//MENGECEK APAKAH USER INI BERHAK MENGAKSES MENU INI
$sqlCekMenu = $db->prepare('SELECT * from balistars_user_detail 
  inner join balistars_menu_sub 
  on balistars_menu_sub.idMenuSub = balistars_user_detail.idMenuSub
  where balistars_user_detail.idUser = ?
  and namaFolder = ?');
$sqlCekMenu->execute([
    $idUserAsli,
    'form_penyetujuan_headoffice'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $sqlInformasi    = $db->query('SELECT * FROM balistars_information');
    $dataInformasi = $sqlInformasi->fetch();
    $logo            = $BASE_URL_HTML . '/assets/images/' . $dataInformasi['logo'];

    $idPengajuan = '';
    $isPreview = '';

    $id = '';

    extract($_POST);

    $isPreview = $isPreview === 'true' ? true : false;

?>
    <table>
        <tbody>
            <?php
            $listKronologi = selectStatement(
                $db,
                'SELECT idDataSuratPengajuan, `data`,`row` FROM balistars_data_surat_pengajuan WHERE kolom = ? AND idPengajuan = ? ORDER BY `row`',
                ['listKronologi', $idPengajuan],
            );

            $rowTerakhir = selectStatement(
                $db,
                'SELECT COALESCE(MAX(`row`), 0) + 1 as rowTerakhir FROM balistars_data_surat_pengajuan WHERE idPengajuan = ? AND kolom = ?',
                [$idPengajuan, 'listKronologi'],
                'fetch'
            )['rowTerakhir'];

            foreach ($listKronologi as $index => $list) {
            ?>
                <tr data-row="<?= $list['row'] ?>">
                    <td><?= $index + 1; ?>.</td>
                    <?php
                    if ($id === $list['idDataSuratPengajuan']) {
                    ?>
                        <td>
                            <input type="text" class="form-control" data-id="<?= $list['idDataSuratPengajuan'] ?>" data-col="listKronologi" data-row="<?= $rowTerakhir ?>" id="listKronologi#<?= $rowTerakhir ?>" value="<?= $list['data'] ?>" placeholder="Tekan ' Enter ' untuk menyimpan data...">
                        </td>
                    <?php
                    } else {
                    ?>
                        <td><?= $list['data']; ?>
                            <?php
                            if ($isPreview) {
                            ?>
                                <span class="btn-proses badge badge-danger" onclick="deleteList($(this),'listKronologi','<?= $idPengajuan ?>', '<?= $list['idDataSuratPengajuan'] ?>')"><i class="fas fa-trash"></i></span>
                                <span class="btn-proses badge badge-warning" onclick="getFormListKronologi('<?= $idPengajuan ?>', '<?= $list['idDataSuratPengajuan'] ?>')"><i class="fas fa-edit"></i></span>
                            <?php
                            }
                            ?>
                        </td>
                    <?php
                    }
                    ?>
                </tr>
            <?php
            }

            if ($isPreview) {
            ?>
                <tr>
                    <td><?= count($listKronologi) + 1; ?>.</td>
                    <td>
                        <input type="text" class="form-control" data-col="listKronologi" data-row="<?= $rowTerakhir ?>" id="listKronologi#<?= $rowTerakhir ?>" placeholder="Tekan ' Enter ' untuk menyimpan data...">
                    </td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
<?php
}
?>