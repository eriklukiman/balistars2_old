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
    'form_pengajuan_petty_cash'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $flag = '';
    $idPettyCash = '';

    extract($_POST);

    $dataUpdate = selectStatement(
        $db,
        'SELECT * FROM balistars_pengajuan_petty_cash WHERE idPettyCash = ?',
        [$idPettyCash],
        'fetch'
    );

    if ($dataUpdate) {
        $flag = 'update';
        $tahapan = $dataUpdate['tahapan'];
    } else {
        $flag = 'tambah';
        $tahapan = 'Headoffice';
    }

?>
    <form id="formPettyCash">
        <input type="hidden" name="flag" id="flag" value="<?= $flag ?>">
        <input type="hidden" name="idPettyCash" id="idPettyCash" value="<?= $idPettyCash ?>">
        <?php
        if ($tahapan === 'Headoffice' || $tahapan === 'Reject') {
        ?>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label for="tglPengajuan">TGL PENGAJUAN</label>
                    <div class="input-group date" data-date-autoclose="true" data-provide="datepicker" data-date-format="dd-mm-yyyy">
                        <input type="text" class="form-control form-control-lg" name="tglPengajuan" id="tglPengajuan" value="<?= isset($dataUpdate['tglPengajuan']) ? konversiTanggal($dataUpdate['tglPengajuan']) : date('d-m-Y') ?>" autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fa fa-calendar"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 form-group">
                    <label for="namaProyek">NAMA PROYEK</label>
                    <input type="text" class="form-control form-control-lg" name="namaProyek" id="namaProyek" placeholder="Nama Proyek" value="<?= $dataUpdate['namaProyek'] ?? '' ?>">
                </div>
                <div class="col-md-3 form-group">
                    <label for="namaPerusahaan">NAMA PERUSAHAAN</label>
                    <input type="text" class="form-control form-control-lg" name="namaPerusahaan" id="namaPerusahaan" placeholder="Nama Perusahaan" value="<?= $dataUpdate['namaPerusahaan'] ?? '' ?>">
                </div>
                <div class="col-md-3 form-group">
                    <label for="noPO">NO. PO (ISI JIKA ADA)</label>
                    <input type="text" class="form-control form-control-lg" name="noPO" id="noPO" placeholder="No. PO" value="<?= $dataUpdate['noPO'] ?? '' ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label for="estimasiOmset">ESTIMASI OMSET</label>
                    <input type="text" class="form-control form-control-lg" name="estimasiOmset" id="estimasiOmset" placeholder="Estimasi Omset" onkeyup="rupiah('#estimasiOmset')" value="<?= isset($dataUpdate['estimasiOmset']) ? ubahToRp($dataUpdate['estimasiOmset']) : '' ?>">
                </div>
                <div class="col-md-3 form-group">
                    <label for="estimasiBiayaPengeluaran">TOTAL ESTIMASI BIAYA</label>
                    <input type="text" class="form-control form-control-lg" name="estimasiBiayaPengeluaran" id="estimasiBiayaPengeluaran" onkeyup="rupiah('#estimasiBiayaPengeluaran')" placeholder="Estimasi Biaya Pengeluaran" value="<?= isset($dataUpdate['estimasiBiayaPengeluaran']) ? ubahToRp($dataUpdate['estimasiBiayaPengeluaran']) : '' ?>">
                </div>
                <div class="col-md-3 form-group">
                    <label for="nominal">REQUEST PETTY CASH</label>
                    <input type="text" class="form-control form-control-lg" name="nominal" id="nominal" onkeyup="rupiah('#nominal')" placeholder="Nominal Petty Cash" value="<?= isset($dataUpdate['nominal']) ? ubahToRp($dataUpdate['nominal']) : '' ?>">
                </div>
                <div class="col-md-3 form-group">
                    <label for="biayaEksternal">BIAYA EKSTERNAL</label>
                    <input type="text" class="form-control form-control-lg" name="biayaEksternal" id="biayaEksternal" onkeyup="rupiah('#biayaEksternal')" placeholder="Biaya Eksternal" value="<?= isset($dataUpdate['nominal']) ? ubahToRp($dataUpdate['biayaEksternal']) : '' ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="keterangan">KETERANGAN / ISI NAMA BAHAN</label>
                    <input type="text" class="form-control form-control-lg" name="keterangan" id="keterangan" placeholder="Keterangan" value="<?= $dataUpdate['keterangan'] ?? '' ?>">
                </div>
            </div>
        <?php
        } else {
        ?>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label for="tglPengajuan">TGL PENGAJUAN</label>
                    <input type="text" class="form-control form-control-lg" id="tglPengajuan" value="<?= ubahTanggalIndo($dataUpdate['tglPengajuan']) ?>" disabled>
                </div>
                <div class="col-md-3 form-group">
                    <label for="namaProyek">NAMA PROYEK</label>
                    <input type="text" class="form-control form-control-lg" id="namaProyek" placeholder="Nama Proyek" value="<?= $dataUpdate['namaProyek'] ?>" disabled>
                </div>
                <div class="col-md-3 form-group">
                    <label for="namaPerusahaan">NAMA PERUSAHAAN</label>
                    <input type="text" class="form-control form-control-lg" id="namaPerusahaan" placeholder="Nama Perusahaan" value="<?= $dataUpdate['namaPerusahaan'] ?>" disabled>
                </div>
                <div class="col-md-3 form-group">
                    <label for="noPO">NO. PO (ISI JIKA ADA)</label>
                    <input type="text" class="form-control form-control-lg" id="noPO" placeholder="Nominal Petty Cash" value="<?= $dataUpdate['noPO'] ?>" disabled>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label for="estimasiOmset">ESTIMASI OMSET</label>
                    <input type="text" class="form-control form-control-lg" id="estimasiOmset" placeholder="Estimasi Omset" value="<?= ubahToRp($dataUpdate['estimasiOmset']) ?>" disabled>
                </div>
                <div class="col-md-3 form-group">
                    <label for="estimasiBiayaPengeluaran">TOTAL ESTIMASI BIAYA</label>
                    <input type="text" class="form-control form-control-lg" id="estimasiBiayaPengeluaran" placeholder="Estimasi Biaya Pengeluaran" value="<?= ubahToRp($dataUpdate['estimasiBiayaPengeluaran']) ?>" disabled>
                </div>
                <div class="col-md-3 form-group">
                    <label for="nominal">REQUEST PETTY CASH</label>
                    <input type="text" class="form-control form-control-lg" id="nominal" placeholder="Nominal Petty Cash" value="<?= ubahToRp($dataUpdate['nominal']) ?>" disabled>
                </div>
                <div class="col-md-3 form-group">
                    <label for="biayaEksternal">BIAYA EKSTERNAL</label>
                    <input type="text" class="form-control form-control-lg" id="biayaEksternal" placeholder="Biaya Eksternal" value="<?= ubahToRp($dataUpdate['biayaEksternal']) ?>" disabled>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="keterangan">KETERANGAN / ISI NAMA BAHAN</label>
                    <input type="text" class="form-control form-control-lg" id="keterangan" placeholder="Keterangan" value="<?= $dataUpdate['keterangan'] ?>" disabled>
                </div>
            </div>
        <?php
        }
        ?>
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
                <?php
                if ($tahapan === 'Headoffice' || $tahapan === 'Reject') {
                ?>
                    <div class="col-md-6 text-right">
                        <button type="button" class="btn btn-info" onclick="$('#formBuktiLampiran input').val('')"><strong>RESET FORM</strong></button>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="card-body">
            <?php
            if ($tahapan === 'Reject') {
                $tahapanReject = selectStatement(
                    $db,
                    'SELECT tahapan FROM balistars_penyetujuan WHERE idPengajuan = ? AND jenisPengajuan = ? AND statusPenyetujuan = ? AND hasil = ? ORDER BY idPenyetujuan DESC LIMIT 1',
                    [$idPettyCash, 'Petty Cash', 'Aktif', 'Reject'],
                    'fetch'
                )['tahapan'];
            ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-info-circle pr-4"></i><strong class="pr-2">INFO :</strong><span>Pengajuan Telah Di Reject Oleh <?= $tahapanReject; ?></span>
                </div>
                <?php
            } else {
                if ($flag === 'update') {
                ?>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle pr-4"></i><strong class="pr-2">INFO :</strong><span>Pengajuan Telah Mencapai Tahap </span><strong class="text-uppercase"><?= $tahapan; ?></strong>
                    </div>
            <?php
                }
            }
            ?>
            <form id="formBuktiLampiran">

            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php
            if ($tahapan === 'Headoffice') {

                if ($flag === 'tambah') {
            ?>
                    <button type="button" class="btn btn-success" onclick="prosesPettyCash($(this))">
                        <i class="fas fa-save pr-3"></i><strong>TAMBAH</strong>
                    </button>

                <?php
                } else if ($flag === 'update') {
                ?>
                    <button type="button" class="btn btn-primary" onclick="prosesPettyCash($(this))">
                        <i class="fas fa-save pr-3"></i><strong>UPDATE</strong>
                    </button>
                <?php
                }
            } else if ($tahapan === 'Reject') {
                ?>
                <button type="button" class="btn btn-success" onclick="prosesPettyCash($(this), true);pengajuanUlang($(this), '<?= $idPettyCash ?>')">
                    <i class="fas fa-upload pr-3"></i><strong>AJUKAN ULANG</strong>
                </button>
            <?php
            }
            ?>
        </div>
    </div>
<?php
}
