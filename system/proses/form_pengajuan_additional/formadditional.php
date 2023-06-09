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
    'form_pengajuan_additional'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $flag = '';
    $idAdditional = '';

    extract($_POST);

    $dataUpdate = selectStatement(
        $db,
        'SELECT * FROM balistars_pengajuan_additional WHERE idAdditional = ?',
        [$idAdditional],
        'fetch'
    );

    if ($dataUpdate) {
        $flag = 'update';
        $tahapan = $dataUpdate['tahapan'];

        $dataPenyetujuanTerakhir = selectStatement(
            $db,
            'SELECT * FROM balistars_penyetujuan WHERE idPengajuan = ? AND statusPenyetujuan = ? AND jenisPengajuan = ? ORDER BY idPenyetujuan DESC LIMIT 1',
            [$idAdditional, 'Aktif', 'Additional'],
            'fetch'
        );

        if ($dataPenyetujuanTerakhir['hasil'] === 'Reject' && $dataPenyetujuanTerakhir['tahapan'] === 'Headoffice') {
            $rejectHO = true;
        } else {
            $rejectHO = false;
        }
    } else {
        $flag = 'tambah';
        $tahapan = 'Kontrol Area';

        $rejectHO = false;
    }

?>
    <form id="formAdditional">
        <input type="hidden" name="flag" id="flag" value="<?= $flag ?>">
        <input type="hidden" name="idAdditional" id="idAdditional" value="<?= $idAdditional ?>">
        <?php
        if (($tahapan === 'Kontrol Area' && $rejectHO === false) || $tahapan === 'Reject') {
        ?>
            <div class="row">
                <div class="col-md-4 form-group">
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
                <div class="col-md-4 form-group">
                    <label for="namaCustomer">CUSTOMER</label>
                    <input type="text" class="form-control form-control-lg" name="namaCustomer" id="namaCustomer" placeholder="Nama Customer" value="<?= $dataUpdate['namaCustomer'] ?? '' ?>">
                </div>
                <div class="col-md-4 form-group">
                    <label for="biaya">BIAYA</label>
                    <input type="text" class="form-control form-control-lg" name="biaya" id="biaya" onkeyup="rupiah('#biaya')" placeholder="Biaya" value="<?= isset($dataUpdate['biaya']) ? ubahToRp($dataUpdate['biaya']) : '' ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="omset">OMSET</label>
                    <input type="text" class="form-control form-control-lg" name="omset" id="omset" onkeyup="rupiah('#omset')" placeholder="Omset" value="<?= isset($dataUpdate['omset']) ? ubahToRp($dataUpdate['omset']) : '' ?>">
                </div>
                <div class="col-md-4 form-group">
                    <label for="profit">MARGIN PROFIT</label>
                    <input type="text" class="form-control form-control-lg" id="profit" readonly placeholder="Profit" value="<?= isset($dataUpdate['profit']) ? ubahToRp($dataUpdate['profit']) : '' ?>">
                </div>
                <div class="col-md-4 form-group">
                    <label for="ratio">RATIO</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" id="ratio" placeholder="Ratio" value="<?= $dataUpdate['ratio'] ?? '' ?>" readonly>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                %
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        } else {
        ?>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="tglPengajuan">TGL PENGAJUAN</label>
                    <input type="text" class="form-control form-control-lg" id="tglPengajuan" value="<?= ubahTanggalIndo($dataUpdate['tglPengajuan']) ?>" disabled>
                </div>
                <div class="col-md-4 form-group">
                    <label for="namaCustomer">CUSTOMER</label>
                    <input type="text" class="form-control form-control-lg" disabled id="namaCustomer" placeholder="Nama Customer" value="<?= $dataUpdate['namaCustomer'] ?>">
                </div>
                <div class="col-md-4 form-group">
                    <label for="omset">OMSET</label>
                    <input type="text" class="form-control form-control-lg" disabled id="omset" onkeyup="rupiah('#omset')" placeholder="Omset" value="<?= ubahToRp($dataUpdate['omset']) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="biaya">BIAYA</label>
                    <input type="text" class="form-control form-control-lg" disabled id="biaya" onkeyup="rupiah('#biaya')" placeholder="Biaya" value="<?= ubahToRp($dataUpdate['biaya']) ?>">
                </div>
                <div class="col-md-4 form-group">
                    <label for="profit">PROFIT</label>
                    <input type="text" class="form-control form-control-lg" id="profit" disabled placeholder="Profit" value="<?= ubahToRp($dataUpdate['profit']) ?>">
                </div>
                <div class="col-md-4 form-group">
                    <label for="ratio">RATIO</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" id="ratio" placeholder="Ratio" value="<?= $dataUpdate['ratio'] ?>" disabled>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                %
                            </span>
                        </div>
                    </div>
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
                if (($tahapan === 'Kontrol Area' && $rejectHO === false) || $tahapan === 'Reject') {
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
            if ($tahapan === 'Kontrol Area') {
                if ($rejectHO === true) {
            ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-info-circle pr-4"></i><strong class="pr-2">INFO :</strong><span>Pengajuan Telah Di Reject Oleh Headoffice. Pengajuan akan ditindak lanjuti oleh Kontrol Area</span>
                    </div>
                <?php
                } else {
                ?>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle pr-4"></i><strong class="pr-2">INFO :</strong><span>Link yang diinputkan harus mencantumkan <code>"https"</code> pada awalannya <strong>( CONTOH : "https://google.com")</strong></span>
                    </div>
                <?php
                }
            } else if ($tahapan === 'Reject') {
                $tahapanReject = selectStatement(
                    $db,
                    'SELECT tahapan FROM balistars_penyetujuan WHERE idPengajuan = ? AND jenisPengajuan = ? AND statusPenyetujuan = ? AND hasil = ? ORDER BY idPenyetujuan DESC LIMIT 1',
                    [$idAdditional, 'Additional', 'Aktif', 'Reject'],
                    'fetch'
                )['tahapan'];
                ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-info-circle pr-4"></i><strong class="pr-2">INFO :</strong><span>Pengajuan Telah Di Reject Oleh <?= $tahapanReject; ?></span>
                </div>
            <?php
            } else {
            ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle pr-4"></i><strong class="pr-2">INFO :</strong><span>Pengajuan Telah Mencapai Tahap </span><strong class="text-uppercase"><?= $tahapan; ?></strong>
                </div>
            <?php
            }
            ?>
            <form id="formBuktiLampiran">
                <?php
                if (($tahapan === 'Kontrol Area' && $rejectHO === false) || $tahapan === 'Reject') {
                ?>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="linkPO">PO</label>
                            <div class="input-group">
                                <input type="text" name="linkPO" id="linkPO" class="input-link form-control" placeholder="Link PO" value="<?= $dataUpdate['linkPO'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkPO'] ?? '#' ?>" data-id="linkPO" class="btn <?= $dataUpdate['linkPO'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkSuratPenjamin">SURAT PENJAMIN</label>
                            <div class="input-group">
                                <input type="text" name="linkSuratPenjamin" id="linkSuratPenjamin" class="input-link form-control" placeholder="Link Surat Penjamin" value="<?= $dataUpdate['linkSuratPenjamin'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkSuratPenjamin'] ?? '#' ?>" data-id="linkSuratPenjamin" class="btn <?= $dataUpdate['linkSuratPenjamin'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkDP">DP</label>
                            <div class="input-group">
                                <input type="text" name="linkDP" id="linkDP" class="input-link form-control" placeholder="Link DP" value="<?= $dataUpdate['linkDP'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkDP'] ?? '#' ?>" data-id="linkDP" class="btn <?= $dataUpdate['linkDP'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="linkBuktiOrder">BUKTI ORDER</label>
                            <div class="input-group">
                                <input type="text" name="linkBuktiOrder" id="linkBuktiOrder" class="input-link form-control" placeholder="Link Bukti Order" value="<?= $dataUpdate['linkBuktiOrder'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkBuktiOrder'] ?? '#' ?>" data-id="linkBuktiOrder" class="btn <?= $dataUpdate['linkBuktiOrder'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkDesainCetakan">DESAIN / CETAKAN</label>
                            <div class="input-group">
                                <input type="text" name="linkDesainCetakan" id="linkDesainCetakan" class="input-link form-control" placeholder="Link Desain Cetakan" value="<?= $dataUpdate['linkDesainCetakan'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkDesainCetakan'] ?? '#' ?>" data-id="linkDesainCetakan" class="btn <?= $dataUpdate['linkDesainCetakan'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkNotaSupplier">NOTA SUPPLIER</label>
                            <div class="input-group">
                                <input type="text" name="linkNotaSupplier" id="linkNotaSupplier" class="input-link form-control" placeholder="Link Nota Supplier" value="<?= $dataUpdate['linkNotaSupplier'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkNotaSupplier'] ?? '#' ?>" data-id="linkNotaSupplier" class="btn <?= $dataUpdate['linkNotaSupplier'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="linkFoto">FOTO (SPG, Customer, dll)</label>
                            <div class="input-group">
                                <input type="text" name="linkFoto" id="linkFoto" class="input-link form-control" placeholder="Link Foto" value="<?= $dataUpdate['linkFoto'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkFoto'] ?? '#' ?>" data-id="linkFoto" class="btn <?= $dataUpdate['linkFoto'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkAbsensi">ABSENSI (SPG)</label>
                            <div class="input-group">
                                <input type="text" name="linkAbsensi" id="linkAbsensi" class="input-link form-control" placeholder="Link Absensi" value="<?= $dataUpdate['linkAbsensi'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkAbsensi'] ?? '#' ?>" data-id="linkAbsensi" class="btn <?= $dataUpdate['linkAbsensi'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkBuktiTransfer">BUKTI TF/TALANGAN</label>
                            <div class="input-group">
                                <input type="text" name="linkBuktiTransfer" id="linkBuktiTransfer" class="input-link form-control" placeholder="Link Bukti TF / Talangan" value="<?= $dataUpdate['linkBuktiTransfer'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkBuktiTransfer'] ?? '#' ?>" data-id="linkBuktiTransfer" class="btn <?= $dataUpdate['linkBuktiTransfer'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="linkLainnya">LAINNYA</label>
                            <div class="input-group">
                                <input type="text" name="linkLainnya" id="linkLainnya" class="input-link form-control" placeholder="Link Lainnya" value="<?= $dataUpdate['linkLainnya'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkLainnya'] ?? '#' ?>" data-id="linkLainnya" class="btn <?= $dataUpdate['linkLainnya'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                } else {
                ?>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="linkPO">PO</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkPO" class="input-link form-control" placeholder="Link PO" value="<?= $dataUpdate['linkPO'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkPO'] ?? '#' ?>" data-id="linkPO" class="btn <?= $dataUpdate['linkPO'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkSuratPenjamin">SURAT PENJAMIN</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkSuratPenjamin" class="input-link form-control" placeholder="Link Surat Penjamin" value="<?= $dataUpdate['linkSuratPenjamin'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkSuratPenjamin'] ?? '#' ?>" data-id="linkSuratPenjamin" class="btn <?= $dataUpdate['linkSuratPenjamin'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkDP">DP</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkDP" class="input-link form-control" placeholder="Link DP" value="<?= $dataUpdate['linkDP'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkDP'] ?? '#' ?>" data-id="linkDP" class="btn <?= $dataUpdate['linkDP'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="linkBuktiOrder">BUKTI ORDER</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkBuktiOrder" class="input-link form-control" placeholder="Link Bukti Order" value="<?= $dataUpdate['linkBuktiOrder'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkBuktiOrder'] ?? '#' ?>" data-id="linkBuktiOrder" class="btn <?= $dataUpdate['linkBuktiOrder'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkDesainCetakan">DESAIN / CETAKAN</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkDesainCetakan" class="input-link form-control" placeholder="Link Desain Cetakan" value="<?= $dataUpdate['linkDesainCetakan'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkDesainCetakan'] ?? '#' ?>" data-id="linkDesainCetakan" class="btn <?= $dataUpdate['linkDesainCetakan'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkNotaSupplier">NOTA SUPPLIER</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkNotaSupplier" class="input-link form-control" placeholder="Link Nota Supplier" value="<?= $dataUpdate['linkNotaSupplier'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkNotaSupplier'] ?? '#' ?>" data-id="linkNotaSupplier" class="btn <?= $dataUpdate['linkNotaSupplier'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="linkFoto">FOTO (SPG, Customer, dll)</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkFoto" class="input-link form-control" placeholder="Link Foto" value="<?= $dataUpdate['linkFoto'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkFoto'] ?? '#' ?>" data-id="linkFoto" class="btn <?= $dataUpdate['linkFoto'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkAbsensi">ABSENSI (SPG)</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkAbsensi" class="input-link form-control" placeholder="Link Absensi" value="<?= $dataUpdate['linkAbsensi'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkAbsensi'] ?? '#' ?>" data-id="linkAbsensi" class="btn <?= $dataUpdate['linkAbsensi'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkBuktiTransfer">BUKTI TF/TALANGAN</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkBuktiTransfer" class="input-link form-control" placeholder="Link Bukti TF / Talangan" value="<?= $dataUpdate['linkBuktiTransfer'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkBuktiTransfer'] ?? '#' ?>" data-id="linkBuktiTransfer" class="btn <?= $dataUpdate['linkBuktiTransfer'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="linkLainnya">LAINNYA</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkLainnya" class="input-link form-control" placeholder="Link Lainnya" value="<?= $dataUpdate['linkLainnya'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkLainnya'] ?? '#' ?>" data-id="linkLainnya" class="btn <?= $dataUpdate['linkLainnya'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php
            if (($tahapan === 'Kontrol Area' && $rejectHO === false)) {

                if ($flag === 'tambah') {
            ?>
                    <button type="button" class="btn btn-success" onclick="prosesAdditional($(this))">
                        <i class="fas fa-save pr-3"></i><strong>TAMBAH</strong>
                    </button>

                <?php
                } else if ($flag === 'update') {
                ?>
                    <button type="button" class="btn btn-primary" onclick="prosesAdditional($(this))">
                        <i class="fas fa-save pr-3"></i><strong>UPDATE</strong>
                    </button>
                <?php
                }
            } else if ($tahapan === 'Reject') {
                ?>
                <button type="button" class="btn btn-success" onclick="prosesAdditional($(this), true);pengajuanUlang($(this), '<?= $idAdditional ?>')">
                    <i class="fas fa-upload pr-3"></i><strong>AJUKAN ULANG</strong>
                </button>
            <?php
            }
            ?>
        </div>
    </div>
<?php
}
