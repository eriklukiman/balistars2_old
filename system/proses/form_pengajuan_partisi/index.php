<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsistatement.php';
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
    'form_pengajuan_partisi'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $sqlInformasi    = $db->query('SELECT * FROM balistars_information');
    $dataInformasi = $sqlInformasi->fetch();
    $logo            = $BASE_URL_HTML . '/assets/images/' . $dataInformasi['logo'];

    extract($_GET);

?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Form Pengajuan Partisi <?= $dataInformasi['nama'] ?></title>
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

        <link rel="stylesheet" href="css/custom.css">
        <?php
        icon($logo);
        ?>
    </head>

    <!-- Loader Over Lay -->
    <div class="overlay">
        <div class="overlay__inner">
            <div class="overlay__content"><span class="spinner"></span></div>
        </div>
    </div>
    <!-- End Loader -->

    <body class="theme-blue" onload="
        getFormPartisi('');
        dataDaftarPartisi('');
    ">

        <div id="wrapper">
            <?php
            navBarTop($logo, $BASE_URL_HTML);
            navBarSide($dataLogin, $db, $idUserAsli, $BASE_URL_HTML);
            ?>
            <div id="main-content">
                <div class="container-fluid">
                    <!-- Block Header -->
                    <div class="block-header">
                        <div class="row">
                            <div class="col-sm-12">
                                <h2><a href="javascript:void(0);" class="btn btn-xs btn-link btn-toggle-fullwidth"><i class="fa fa-arrow-left"></i></a>Form Pengajuan Partisi <?= $dataInformasi['nama'] ?></h2>
                                <ul class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#"><i class="icon-home"></i></a></li>
                                    <li class="breadcrumb-item">Pengajuan</li>
                                    <li class="breadcrumb-item active">Form Pengajuan Partisi</li>
                                </ul>
                            </div>
                            <div class="col-sm-12 text-right"></div>
                        </div>
                    </div>
                    <!-- End Block Header -->
                    <!-- Clearfix -->
                    <div class="row clear-fix">
                        <div class="col-sm-12">
                            <div class="card planned_task">
                                <!-- Header -->
                                <div class="header">
                                    <div class="row">
                                        <div class="col-sm-9">
                                            <h5><i class="fas fa-sticky-note pr-4"></i> <strong>FORM PENGAJUAN PARTISI</strong> </h5>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Header -->
                                <!-- Body -->
                                <div class="body">

                                    <div id="boxFormPartisi"></div>
                                    <hr>
                                    <div class="container-fluid border rounded p-3 mb-3 bg-light">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="rentang">RENTANG</label>
                                                <div class="input-group">
                                                    <input type="datepicker" class="form-control form-control-lg" name="rentang" id="rentang" onchange="dataDaftarPartisi()">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary" disabled>
                                                            <i class="fa fa-calendar"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="status">STATUS</label>
                                                <select name="status" id="status" class="select2 form-control" onchange="dataDaftarPartisi()">
                                                    <option value="">SEMUA</option>
                                                    <?php
                                                    $opsi = ['Sudah Diproses', 'Belum Diproses', 'Reject'];

                                                    foreach ($opsi as $index => $value) {
                                                    ?>
                                                        <option value="<?= $value ?>"><?= $value; ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="dataDaftarPartisi" style="overflow-x: auto;"></div>

                                    <!-- End Body -->
                                </div>
                            </div>
                        </div>
                        <!-- End Clearfix -->
                    </div>

                    <footer>
                        <?php
                        footer();
                        ?>
                    </footer>
                </div>
            </div>

            <div id="modalProgressPartisi" class="modal fade" role="dialog">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="col-sm-11">
                                <h5 class="modal-title">
                                    <strong>PROGRESS PENGAJUAN PARTISI</strong>
                                </h5>
                            </div>
                            <div class="col-sm-1 text-right">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <div class="modal-body" id="boxProgressPartisi"></div>
                    </div>
                </div>
            </div>


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
            <script src="js/partisi.js"></script>
            <script type="text/javascript">
                $("#rentang").daterangepicker({
                    locale: {
                        format: 'DD-MM-YYYY'
                    },
                    startDate: moment().startOf('week'),
                    endDate: moment().endOf('week'),
                });
                $('select.select2').select2();
            </script>
    </body>

    </html>
<?php
}
?>