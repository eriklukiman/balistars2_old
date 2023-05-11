<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/library/fungsistatement.php';

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
    'form_payment'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $flag = '';
    $idPettyCash = '';

    extract($_POST);

    $idPettyCash = $idPengajuan;

    $dataUpdate = selectStatement(
        $db,
        'SELECT * FROM balistars_pengajuan_petty_cash WHERE idPettyCash = ?',
        [$idPettyCash],
        'fetch'
    );

    if ($dataUpdate) {
        $flag = 'update';
    } else {
        $flag = 'tambah';
    }

?>
    <form id="formPettyCash">
        <input type="hidden" name="flag" id="flag" value="<?= $flag ?>">
        <input type="hidden" name="idPettyCash" id="idPettyCash" value="<?= $idPettyCash ?>">

        <div class="row">
            <div class="col-md-4 form-group">
                <label for="tglPengajuan">TGL PENGAJUAN</label>
                <input type="text" class="form-control form-control-lg" id="tglPengajuan" value="<?= ubahTanggalIndo($dataUpdate['tglPengajuan']) ?>" disabled>
            </div>
            <div class="col-md-4 form-group">
                <label for="namaProyek">NAMA PROYEK</label>
                <input type="text" class="form-control form-control-lg" id="namaProyek" placeholder="Nama Proyek" value="<?= $dataUpdate['namaProyek'] ?>" disabled>
            </div>
            <div class="col-md-4 form-group">
                <label for="namaPerusahaan">NAMA PERUSAHAAN</label>
                <input type="text" class="form-control form-control-lg" id="namaPerusahaan" placeholder="Nama Perusahaan" value="<?= $dataUpdate['namaPerusahaan'] ?>" disabled>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 form-group">
                <label for="estimasiOmset">ESTIMASI OMSET</label>
                <input type="text" class="form-control form-control-lg" id="estimasiOmset" placeholder="Estimasi Omset" value="<?= ubahToRp($dataUpdate['estimasiOmset']) ?>" disabled>
            </div>
            <div class="col-md-4 form-group">
                <label for="estimasiBiayaPengeluaran">ESTIMIASI BIAYA YANG DIKELUARKAN</label>
                <input type="text" class="form-control form-control-lg" id="estimasiBiayaPengeluaran" placeholder="Estimasi Biaya Pengeluaran" value="<?= ubahToRp($dataUpdate['estimasiBiayaPengeluaran']) ?>" disabled>
            </div>
            <div class="col-md-4 form-group">
                <label for="nominal">REQUEST PETTY CASH</label>
                <input type="text" class="form-control form-control-lg" id="nominal" placeholder="Nominal Petty Cash" value="<?= ubahToRp($dataUpdate['nominal']) ?>" disabled>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 form-group">
                <label for="noPO">NO. PO (ISI JIKA ADA)</label>
                <input type="text" class="form-control form-control-lg" id="noPO" placeholder="No. PO" value="<?= $dataUpdate['noPO'] ?>" disabled>
            </div>
            <div class="col-md-8 form-group">
                <label for="keterangan">KETERANGAN / ISI NAMA BAHAN</label>
                <input type="text" class="form-control form-control-lg" id="keterangan" placeholder="Keterangan" value="<?= $dataUpdate['keterangan'] ?>" disabled>
            </div>
        </div>
    </form>
    <br />
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <label class="col-form-label">
                        <i class="fas fa-code-branch pr-3"></i><strong>LINK LAMPIRAN</strong>
                    </label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form id="formBuktiLampiran">

            </form>
        </div>
    </div>
    <hr>
<?php
}
