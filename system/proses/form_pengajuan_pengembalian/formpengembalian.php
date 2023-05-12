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
    'form_pengajuan_pengembalian'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $flag = '';
    $idPengembalian = '';

    extract($_POST);

    $dataUpdate = selectStatement(
        $db,
        'SELECT * FROM balistars_pengajuan_pengembalian WHERE idPengembalian = ?',
        [$idPengembalian],
        'fetch'
    );

    if ($dataUpdate) {
        $flag = 'update';
        $tahapan = $dataUpdate['tahapan'];

        $dataPenyetujuanTerakhir = selectStatement(
            $db,
            'SELECT * FROM balistars_penyetujuan WHERE idPengajuan = ? AND statusPenyetujuan = ? AND jenisPengajuan = ? ORDER BY idPenyetujuan DESC LIMIT 1',
            [$idPengembalian, 'Aktif', 'Pengembalian'],
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
    <form id="formPengembalian">
        <input type="hidden" name="flag" id="flag" value="<?= $flag ?>">
        <input type="hidden" name="idPengembalian" id="idPengembalian" value="<?= $idPengembalian ?>">
        <?php
        if (($tahapan === 'Kontrol Area' && $rejectHO === false) || $tahapan === 'Reject') {
        ?>
            <div class="row">
                <div class="col-md-3">
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
                    <label for="namaCustomer">CUSTOMER</label>
                    <input type="text" class="form-control form-control-lg" name="namaCustomer" id="namaCustomer" placeholder="Nama Customer" value="<?= $dataUpdate['namaCustomer'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label for="jumlahTransaksi">JUMLAH TRANSAKSI</label>
                    <input type="text" class="form-control form-control-lg" name="jumlahTransaksi" id="jumlahTransaksi" onkeyup="rupiah('#jumlahTransaksi')" placeholder="Jumlah Transaksi" value="<?= isset($dataUpdate['jumlahTransaksi']) ? ubahToRp($dataUpdate['jumlahTransaksi']) : '' ?>">
                </div>
                <div class="col-md-3">
                    <label for="totalPengembalian">TOTAL PENGEMBALIAN</label>
                    <input type="text" class="form-control form-control-lg" name="totalPengembalian" id="totalPengembalian" onkeyup="rupiah('#totalPengembalian')" placeholder="Total Pengembalian" value="<?= isset($dataUpdate['totalPengembalian']) ? ubahToRp($dataUpdate['totalPengembalian']) : '' ?>">
                </div>
            </div>
        <?php
        } else {
        ?>
            <div class="row">
                <div class="col-md-3">
                    <label for="tglPengajuan">TGL PENGAJUAN</label>
                    <input type="text" class="form-control form-control-lg" id="tglPengajuan" value="<?= ubahTanggalIndo($dataUpdate['tglPengajuan']) ?>" disabled>
                </div>
                <div class="col-md-3 form-group">
                    <label for="namaCustomer">CUSTOMER</label>
                    <input type="text" class="form-control form-control-lg" id="namaCustomer" placeholder="Nama Customer" value="<?= $dataUpdate['namaCustomer'] ?>" disabled>
                </div>
                <div class="col-md-3">
                    <label for="jumlahTransaksi">JUMLAH TRANSAKSI</label>
                    <input type="text" class="form-control form-control-lg" id="jumlahTransaksi" onkeyup="rupiah('#jumlahTransaksi')" placeholder="Jumlah Transaksi" value="<?= ubahToRp($dataUpdate['jumlahTransaksi']) ?>" disabled>
                </div>
                <div class="col-md-3">
                    <label for="totalPengembalian">TOTAL PENGEMBALIAN</label>
                    <input type="text" class="form-control form-control-lg" id="totalPengembalian" onkeyup="rupiah('#totalPengembalian')" placeholder="Total Pengembalian" value="<?= ubahToRp($dataUpdate['totalPengembalian']) ?>" disabled>
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
                    [$idPengembalian, 'Pengembalian', 'Aktif', 'Reject'],
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
                            <label for="linkSuratPengajuan">SURAT PENGAJUAN</label>
                            <div class="input-group">
                                <input type="text" name="linkSuratPengajuan" id="linkSuratPengajuan" class="input-link form-control" placeholder="Link Surat Pengajuan" value="<?= $dataUpdate['linkSuratPengajuan'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkSuratPengajuan'] ?? '#' ?>" data-id="linkSuratPengajuan" class="btn <?= $dataUpdate['linkSuratPengajuan'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkSuratPernyataanCustomer">SURAT PERNYATAAN CUSTOMER</label>
                            <div>
                                <button class="btn btn-secondary w-100" type="button"><strong>DATA AKAN DIINPUT OLEH KONTROL AREA</strong></button>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkNotaPenjualan">NOTA PENJUALAN</label>
                            <div class="input-group">
                                <input type="text" name="linkNotaPenjualan" id="linkNotaPenjualan" class="input-link form-control" placeholder="Link Nota Penjualan" value="<?= $dataUpdate['linkNotaPenjualan'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkNotaPenjualan'] ?? '#' ?>" data-id="linkNotaPenjualan" class="btn <?= $dataUpdate['linkNotaPenjualan'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="linkBuktiTransfer">BUKTI TRANSFER</label>
                            <div class="input-group">
                                <input type="text" name="linkBuktiTransfer" id="linkBuktiTransfer" class="input-link form-control" placeholder="Link Bukti Transfer" value="<?= $dataUpdate['linkBuktiTransfer'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkBuktiTransfer'] ?? '#' ?>" data-id="linkBuktiTransfer" class="btn <?= $dataUpdate['linkBuktiTransfer'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkBuktiPotongPPH">BUKTI POTONG PPH</label>
                            <div class="input-group">
                                <input type="text" name="linkBuktiPotongPPH" id="linkBuktiPotongPPH" class="input-link form-control" placeholder="Link Bukti Potong PPH" value="<?= $dataUpdate['linkBuktiPotongPPH'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkBuktiPotongPPH'] ?? '#' ?>" data-id="linkBuktiPotongPPH" class="btn <?= $dataUpdate['linkBuktiPotongPPH'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkBuktiPotongPPN">BUKTI POTONG PPN</label>
                            <div class="input-group">
                                <input type="text" name="linkBuktiPotongPPN" id="linkBuktiPotongPPN" class="input-link form-control" placeholder="Link Bukti Potong PPN" value="<?= $dataUpdate['linkBuktiPotongPPN'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkBuktiPotongPPN'] ?? '#' ?>" data-id="linkBuktiPotongPPN" class="btn <?= $dataUpdate['linkBuktiPotongPPN'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="linkRincianPenjualanExcel">RINCIAN PENJUALAN EXCEL</label>
                            <div class="input-group">
                                <input type="text" name="linkRincianPenjualanExcel" id="linkRincianPenjualanExcel" class="input-link form-control" placeholder="Link Rincian Penjualan Excel" value="<?= $dataUpdate['linkRincianPenjualanExcel'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkRincianPenjualanExcel'] ?? '#' ?>" data-id="linkRincianPenjualanExcel" class="btn <?= $dataUpdate['linkRincianPenjualanExcel'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkBuktiChatCustomer">BUKTI CHAT CUSTOMER</label>
                            <div class="input-group">
                                <input type="text" name="linkBuktiChatCustomer" id="linkBuktiChatCustomer" class="input-link form-control" placeholder="Link Bukti Chat Customer" value="<?= $dataUpdate['linkBuktiChatCustomer'] ?? '' ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkBuktiChatCustomer'] ?? '#' ?>" data-id="linkBuktiChatCustomer" class="btn <?= $dataUpdate['linkBuktiChatCustomer'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
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
                            <label for="linkSuratPengajuan">SURAT PENGAJUAN</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkSuratPengajuan" class="input-link form-control" placeholder="Link Surat Pengajuan" value="<?= $dataUpdate['linkSuratPengajuan'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkSuratPengajuan'] ?? '#' ?>" data-id="linkSuratPengajuan" class="btn <?= $dataUpdate['linkSuratPengajuan'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkSuratPernyataanCustomer">SURAT PERNYATAAN CUSTOMER</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkSuratPernyataanCustomer" class="input-link form-control" placeholder="Link Surat Pernyataan Customer" value="<?= $dataUpdate['linkSuratPernyataanCustomer'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkSuratPernyataanCustomer'] ?? '#' ?>" data-id="linkSuratPernyataanCustomer" class="btn <?= $dataUpdate['linkSuratPernyataanCustomer'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkNotaPenjualan">NOTA PENJUALAN</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkNotaPenjualan" class="input-link form-control" placeholder="Link Nota Penjualan" value="<?= $dataUpdate['linkNotaPenjualan'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkNotaPenjualan'] ?? '#' ?>" data-id="linkNotaPenjualan" class="btn <?= $dataUpdate['linkNotaPenjualan'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="linkBuktiTransfer">BUKTI TRANSFER</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkBuktiTransfer" class="input-link form-control" placeholder="Link Bukti Transfer" value="<?= $dataUpdate['linkBuktiTransfer'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkBuktiTransfer'] ?? '#' ?>" data-id="linkBuktiTransfer" class="btn <?= $dataUpdate['linkBuktiTransfer'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkBuktiPotongPPH">BUKTI POTONG PPH</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkBuktiPotongPPH" class="input-link form-control" placeholder="Link Bukti Potong PPH" value="<?= $dataUpdate['linkBuktiPotongPPH'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkBuktiPotongPPH'] ?? '#' ?>" data-id="linkBuktiPotongPPH" class="btn <?= $dataUpdate['linkBuktiPotongPPH'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkBuktiPotongPPN">BUKTI POTONG PPN</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkBuktiPotongPPN" class="input-link form-control" placeholder="Link Bukti Potong PPN" value="<?= $dataUpdate['linkBuktiPotongPPN'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkBuktiPotongPPN'] ?? '#' ?>" data-id="linkBuktiPotongPPN" class="btn <?= $dataUpdate['linkBuktiPotongPPN'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="linkRincianPenjualanExcel">RINCIAN PENJUALAN EXCEL</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkRincianPenjualanExcel" class="input-link form-control" placeholder="Link Rincian Penjualan Excel" value="<?= $dataUpdate['linkRincianPenjualanExcel'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkRincianPenjualanExcel'] ?? '#' ?>" data-id="linkRincianPenjualanExcel" class="btn <?= $dataUpdate['linkRincianPenjualanExcel'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="linkBuktiChatCustomer">BUKTI CHAT CUSTOMER</label>
                            <div class="input-group">
                                <input type="text" disabled id="linkBuktiChatCustomer" class="input-link form-control" placeholder="Link Bukti Chat Customer" value="<?= $dataUpdate['linkBuktiChatCustomer'] ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkBuktiChatCustomer'] ?? '#' ?>" data-id="linkBuktiChatCustomer" class="btn <?= $dataUpdate['linkBuktiChatCustomer'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
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
        <div class="col-md-6">
            <?php
            if ($tahapan === 'Kontrol Area' && $rejectHO === false) {

                if ($flag === 'tambah') {
            ?>
                    <button type="button" class="btn btn-success" onclick="prosesPengembalian($(this))">
                        <i class="fas fa-save pr-3"></i><strong>TAMBAH</strong>
                    </button>

                <?php
                } else if ($flag === 'update') {
                ?>
                    <button type="button" class="btn btn-primary" onclick="prosesPengembalian($(this))">
                        <i class="fas fa-save pr-3"></i><strong>UPDATE</strong>
                    </button>
                <?php
                }
            } else if ($tahapan === 'Reject') {
                ?>
                <button type="button" class="btn btn-success" onclick="prosesPengembalian($(this), true);pengajuanUlang($(this), '<?= $idPengembalian ?>')">
                    <i class="fas fa-upload pr-3"></i><strong>AJUKAN ULANG</strong>
                </button>
            <?php
            }
            ?>
        </div>

    </div>
<?php
}
