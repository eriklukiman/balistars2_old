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
    $idPartisi = '';

    extract($_POST);

    $idPartisi = $idPengajuan;

    $dataUpdate = selectStatement(
        $db,
        'SELECT * FROM balistars_pengajuan_partisi WHERE idPartisi = ?',
        [$idPartisi],
        'fetch'
    );

    if ($dataUpdate) {
        $flag = 'update';
    } else {
        $flag = 'tambah';
    }

?>
    <form id="formPartisi">
        <input type="hidden" name="flag" id="flag" value="<?= $flag ?>">
        <input type="hidden" name="idPartisi" id="idPartisi" value="<?= $idPartisi ?>">

        <div class="row">
            <div class="col-md-3 form-group">
                <label for="tglPengajuan">TGL PENGAJUAN</label>
                <input type="text" class="form-control form-control-lg" id="tglPengajuan" value="<?= ubahTanggalIndo($dataUpdate['tglPengajuan']) ?>" disabled>
            </div>
            <div class="col-md-3 form-group">
                <label for="namaCustomer">CUSTOMER</label>
                <input type="text" class="form-control form-control-lg" disabled id="namaCustomer" placeholder="Nama Customer" value="<?= $dataUpdate['namaCustomer'] ?>">
            </div>
            <div class="col-md-3 form-group">
                <label for="lamaPartisi">LAMA PARTISI</label>
                <input type="text" class="form-control form-control-lg" id="lamaPartisi" placeholder="Lama Partisi" disabled value="<?= $dataUpdate['lamaPartisi'] ?>">
            </div>
            <div class="col-md-3 form-group">
                <label for="biaya">BIAYA</label>
                <input type="text" class="form-control form-control-lg" id="biaya" onkeyup="rupiah('#biaya')" placeholder="Biaya" disabled value="<?= ubahToRp($dataUpdate['biaya']) ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 form-group">
                <label for="keteranganPembelian">KETERANGAN PEMBELIAN</label>
                <input type="text" class="form-control form-control-lg" id="keteranganPembelian" placeholder="Keterangan Pembelian" disabled value="<?= $dataUpdate['keteranganPembelian'] ?>">
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
                        <label for="linkSuratPartisi">SURAT PARTISI</label>
                        <div class="input-group">
                            <input type="text" disabled id="linkSuratPartisi" class="input-link form-control" placeholder="Link Surat Partisi" value="<?= $dataUpdate['linkSuratPartisi'] ?>">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkSuratPartisi'] ?? '#' ?>" data-id="linkSuratPartisi" class="btn <?= $dataUpdate['linkSuratPartisi'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>
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
                        <label for="linkPenawaran">PENAWARAN</label>
                        <div class="input-group">
                            <input type="text" disabled id="linkPenawaran" class="input-link form-control" placeholder="Link Penawaran" value="<?= $dataUpdate['linkPenawaran'] ?>">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkPenawaran'] ?? '#' ?>" data-id="linkPenawaran" class="btn <?= $dataUpdate['linkPenawaran'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="linkFoto">FOTO</label>
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
                        <label for="linkPerbandingan">PERBANDINGAN</label>
                        <div class="input-group">
                            <input type="text" disabled id="linkPerbandingan" class="input-link form-control" placeholder="Link Perbandingan" value="<?= $dataUpdate['linkPerbandingan'] ?>">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <a target="_blank" tabindex="-1" href="<?= $dataUpdate['linkPerbandingan'] ?? '#' ?>" data-id="linkPerbandingan" class="btn <?= $dataUpdate['linkPerbandingan'] ? 'btn-danger' : 'btn-secondary' ?>"><i class="fas fa-external-link-alt"></i></a>
                                </span>
                            </div>
                        </div>
                    </div>
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
            </form>
        </div>
    </div>
    <hr>
<?php
}
