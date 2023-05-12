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
    'form_penyetujuan_pak_swi'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $sqlInformasi    = $db->query('SELECT * FROM balistars_information');
    $dataInformasi = $sqlInformasi->fetch();
    $logo            = $BASE_URL_HTML . '/assets/images/' . $dataInformasi['logo'];

    $id = '';

    extract($_GET);

    $idPengajuan = htmlspecialchars($id, ENT_QUOTES);
    $isPreview = isset($preview);
?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Form Penyetujuan Kontrol Area <?= $dataInformasi['nama'] ?></title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta name="description" content="Pos TempatKita">
        <meta name="author" content="TempatKita Software, develop by: Gusti Wijayakusuma">

        <link rel="stylesheet" href="<?= $BASE_URL_HTML ?>/assets/vendor/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?= $BASE_URL_HTML ?>/assets/vendor/toastr/toastr.min.css">
        <link rel="stylesheet" href="<?= $BASE_URL_HTML ?>/assets/vendor/select2/select2.css">
        <link rel="stylesheet" href="<?= $BASE_URL_HTML ?>/assets/vendor/datepicker/datepicker.min.css">
        <link rel="stylesheet" href="<?= $BASE_URL_HTML ?>/assets/css/main2.css">
        <link rel="stylesheet" href="<?= $BASE_URL_HTML ?>/assets/css/color_skins.css">
        <link rel="stylesheet" href="<?= $BASE_URL_HTML ?>/assets/css/loader.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

        <link rel="stylesheet" href="style.css">
        <?php
        icon($logo);
        ?>
    </head>

    <body>
        <input type="hidden" id="idPengajuan" value="<?= $idPengajuan ?>">
        <?php
        if ($idPengajuan === '') {
        ?>
            <section id="error">
                <div class="container shadow-lg border">
                    <div><i class="far fa-times-circle text-danger fa-10x"></i></div>
                    <div style="font-size: 1.2rem;"><strong><span>ID PENGAJUAN TIDAK DITEMUKAN</span></strong></div>
                    <button class="btn btn-outline-danger" onclick="window.close()"><strong>KEMBALI KE HALAMAN SEBELUMNYA</strong></button>
                </div>
            </section>
        <?php
        } else {

            $dataPengajuan = selectStatement(
                $db,
                'SELECT 
                    balistars_pengajuan_pengembalian.*, 
                    balistars_cabang.namaCabang
                FROM
                    balistars_pengajuan_pengembalian
                    INNER JOIN balistars_cabang ON balistars_pengajuan_pengembalian.idCabang = balistars_cabang.idCabang
                WHERE   
                    balistars_pengajuan_pengembalian.idPengembalian = ?
            ',
                [$idPengajuan],
                'fetch'
            );
        ?>
            <section id="banner">
                <img src="<?= $BASE_URL_HTML ?>/assets/images/banner_balistars.png" alt="Banner">
            </section>
            <section id="header">
                <div class="title">
                    <span class="text">SURAT PENGEMBALIAN DANA</span>
                </div>
                <div class="nomor">
                    <?php
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
                            <input type="text" class="form-control" id="noSurat" data-id="<?= $idNoSurat ?>" data-col="noSurat" value="<?= $noSurat ?>" placeholder="Tekan ' Shift + Enter ' untuk menyimpan data..." data-row="1">
                        </div>
                    <?php
                    } else {
                    ?>
                        <div class="wrapper-output">Nomor : <?= $noSurat; ?></div>
                    <?php
                    }
                    ?>
                </div>
            </section>
            <section id="body">
                <div class="template-text">
                    Melalui surat ini saya menyampaikan kepada management perihal adanya pengembalian dana transfer dari konsumen di cabang <strong><?= $dataPengajuan['namaCabang']; ?></strong> dengan kronologi sebagai berikut :
                </div>
                <div class="list-kronologi">

                </div>
                <div class="list-transaksi">

                </div>
                <div class="template-text">
                    Demikian yang dapat kami sampaikan untuk selanjutnya dapat ditindak lanjutin proses sebagaimana mestinya dan tidak lupa saya ucapkan terima kasih kepada management.
                </div>
            </section>
            <section id="footer">
                <table>
                    <tbody>
                        <tr>
                            <td colspan="2">Denpasar, 15 Agustus 2023</td>
                        </tr>
                        <tr>
                            <td>Pemohon</td>
                            <td>Approval</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php
                        $area = selectStatement(
                            $db,
                            'SELECT area FROM balistars_cabang WHERE idCabang = ?',
                            [$dataPengajuan['idCabang']],
                            'fetch'
                        )['area'];

                        $namaKontrolArea = selectStatement(
                            $db,
                            'SELECT 
                                namaPegawai 
                            FROM 
                                balistars_pegawai 
                            WHERE 
                                idJabatan = ? 
                                AND idCabang IN (
                                    SELECT
                                        idCabang
                                    FROM
                                        balistars_cabang
                                    WHERE
                                        area = ?
                                )
                            ',
                            [9, $area],
                            'fetch'
                        )['namaPegawai'];
                        ?>
                        <tr>
                            <td><?= $namaKontrolArea; ?></td>
                            <td>I Wayan Muktadhala Swi Adnyana</td>
                        </tr>
                        <tr>
                            <td><i>Control Area <?= ucfirst($area); ?></i></td>
                            <td>
                                <i>
                                    Pimpinan Balistars Group
                                </i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
            <section id="tools">
                <button class="btn btn-danger" type="button" title="Tutup Halaman" onclick="window.close()"><i class="fas fa-times-circle pr-4"></i><strong>TUTUP HALAMAN INI</strong></button>
            </section>
        <?php
        }
        ?>

        <script src="<?= $BASE_URL_HTML ?>/assets/bundles/libscripts.bundle.js"></script>
        <script src="<?= $BASE_URL_HTML ?>/assets/bundles/vendorscripts.bundle.js"></script>
        <script src="<?= $BASE_URL_HTML ?>/assets/bundles/mainscripts.bundle.js"></script>
        <script src="<?= $BASE_URL_HTML ?>/assets/vendor/select2/select2.min.js"></script>
        <script src="<?= $BASE_URL_HTML ?>/assets/vendor/datepicker/datepicker.min.js"></script>
        <script src="<?= $BASE_URL_HTML ?>/assets/vendor/toastr/toastr.js"></script>

        <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <script src="https://kit.fontawesome.com/1f9ba5c1a4.js" crossorigin="anonymous"></script>

        <script src="<?= $BASE_URL_HTML ?>/assets/custom_js/validasiform.js"></script>
        <script src="<?= $BASE_URL_HTML ?>/assets/custom_js/accounting.min.js"></script>
        <script src="<?= $BASE_URL_HTML ?>/assets/custom_js/rupiah.js"></script>
        <script src="<?= $BASE_URL_HTML ?>/assets/custom_js/angka.js"></script>
        <script src="script.js"></script>
    </body>

    </html>
<?php
}
?>