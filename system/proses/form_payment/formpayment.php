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
    $idPengajuan = '';

    extract($_POST);

    $dataUpdate = selectStatement(
        $db,
        'SELECT * FROM balistars_payment WHERE idPengajuan = ? AND jenisPengajuan = ?',
        [$idPengajuan, $jenisPengajuan],
        'fetch'
    );

    if ($dataUpdate) {
        $flag = 'update';
    } else {
        $flag = 'tambah';
    }

?>
    <form id="formAdditional">
        <input type="hidden" name="flag" id="flag" value="<?= $flag ?>">
        <input type="hidden" name="idPengajuan" id="idPengajuan" value="<?= $idPengajuan ?>">

        <div class="row">
            <div class="form-group col-md-3">
                <label for="tglPayment" class="col-form-label">DETAIL PAYMENT</label>
            </div>
            <div class="form-group col-md-9">
                <div class="input-group date" data-date-autoclose="true" data-provide="datepicker" data-date-format="dd-mm-yyyy">
                    <input type="text" class="form-control form-control-lg" name="tglPayment" id="tglPayment" value="<?= isset($dataUpdate['tglPayment']) ? konversiTanggal($dataUpdate['tglPayment']) : date('d-m-Y') ?>" autocomplete="off">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fa fa-calendar"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">

            </div>
            <div class="form-group col-md-9">
                <textarea name="keterangan" id="keterangan" rows="7" class="form-control" placeholder="Keterangan Payment"><?= $dataUpdate['keterangan'] ?? ''; ?></textarea>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12 text-right">
                <button type="button" class="btn btn-success h-100" onclick="prosesPayment($(this), '<?= $idPengajuan ?>', '<?= $jenisPengajuan ?>')">
                    <i class="fas fa-check-circle pr-3"></i><strong>SAVE</strong>
                </button>
            </div>
        </div>
    </form>
<?php
}
