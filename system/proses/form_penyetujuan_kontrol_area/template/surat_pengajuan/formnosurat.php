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
    'form_penyetujuan_kontrol_area'
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

    [$idNoSurat, $noSurat] = selectStatement(
        $db,
        'SELECT idDataSuratPengajuan, `data` FROM balistars_data_surat_pengajuan WHERE kolom = ? AND idPengajuan = ?',
        ['noSurat', $idPengajuan],
        'fetch'
    );

    if ($isPreview) {
?>
        <div class="wrapper-preview">
            <div class="text">Nomor : </div>
            <input type="text" class="form-control" id="noSurat" data-id="<?= $idNoSurat ?>" data-col="noSurat" value="<?= $noSurat ?>" placeholder="Tekan ' Enter ' untuk menyimpan data..." data-row="1">
        </div>
    <?php
    } else {
    ?>
        <div class="wrapper-output">Nomor : <?= $noSurat; ?></div>
<?php
    }
}
?>