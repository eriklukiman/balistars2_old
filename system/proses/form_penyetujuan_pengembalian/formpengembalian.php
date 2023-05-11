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
    'form_penyetujuan_pengembalian'
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
    } else {
        $flag = 'tambah';
    }

    $idJabatan = intval($dataLogin['idJabatan']);
    $idPegawai = intval($dataLogin['idPegawai']);
    $area = $dataLogin['area'];

    $idCabangCakupan = [];

    if ($idJabatan === 9) {
        $tahapan = ['Kontrol Area', 'Reject Dari Headoffice'];

        $dataCabang = selectStatement(
            $db,
            'SELECT idCabang FROM balistars_cabang WHERE area = ?',
            [$area],
        );

        $idCabangCakupan = array_column($dataCabang, 'idCabang');
    } else if ($idJabatan === 1) {
        if ($idPegawai === 164) {
            $tahapan = ['Pak Swi'];
        } else {
            $tahapan = [];
        }
    } else if ($idJabatan === 2) {
        $tahapan = ['Headoffice'];
    } else {
        $tahapan = [];
    }

?>
    <form id="formPengembalian">
        <input type="hidden" name="flag" id="flag" value="<?= $flag ?>">
        <input type="hidden" name="idPengembalian" id="idPengembalian" value="<?= $idPengembalian ?>">

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
            </form>
        </div>
    </div>
    <?php
    if (in_array($dataUpdate['tahapan'], $tahapan)) {
    ?>
        <div class="row">
            <div class="col-md-8 form-group">
                <textarea name="keteranganPenyetujuan" id="keteranganPenyetujuan" rows="7" class="form-control"></textarea>
            </div>
            <div class="col-md-4 form-group">
                <label for="">DETAIL PENYETUJUAN</label>
                <div class="d-flex" style="flex-direction: column; gap: 10px">
                    <button type="button" class="w-75 btn btn-success" onclick="prosesPenyetujuan($(this), '<?= $idPengembalian ?>', 'Disetujui')">
                        <strong>DISETUJUI</strong>
                    </button>
                    <button type="button" class="w-75 btn btn-danger" onclick="prosesPenyetujuan($(this), '<?= $idPengembalian ?>', 'Reject')">
                        <strong>REJECT</strong>
                    </button>
                </div>
            </div>
        </div>
        <hr>
<?php
    }
}
