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

    $tahapan = $dataUpdate['tahapan'];

    $listTahapan = [
        'Kontrol Area' => ['secondary', 'secondary', 'secondary', 'secondary'],
        'Pak Swi' => ['danger', 'secondary', 'secondary', 'secondary'],
        'Headoffice' => ['danger', 'primary', 'secondary', 'secondary'],
        'Payment' => ['danger', 'primary', 'warning', 'secondary'],
        'Final' => ['danger', 'primary', 'warning', 'success'],
        'Reject Dari Headoffice' => ['danger', 'secondary', 'secondary', 'secondary'],
        'Reject' => ['dark', 'dark', 'dark', 'dark'],
    ];

    $listProgress = $listTahapan[$tahapan];

?>
    <div class="progress">
        <?php
        foreach ($listProgress as $progress) {
        ?>
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $progress ?>" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        <?php
        }
        ?>
    </div>

    <div class="d-flex" style="width:100%; height:50px; padding-top:5px">
        <div class="text-25" style="width:25%;  text-align:center">
            <strong>KONTROL AREA</strong>
        </div>
        <div class="text-50" style="width:25%;  text-align:center">
            <strong>PAK SWI</strong>
        </div>
        <div class="text-75" style="width:25%; text-align:center">
            <strong>HEADOFFICE</strong>
        </div>
        <div class="text-100" style="width:25%; text-align:center">
            <strong>PAYMENT</strong>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">

            <div class="alert alert-secondary" role="alert">
                <?php
                switch ($tahapan) {
                    case 'Kontrol Area':
                    case 'Pak Swi':
                    case 'Headoffice':
                ?>
                        <i class="fas fa-info-circle pr-4"></i><strong>PENGEMBALIAN MEMERLUKAN PERSETUJUAN : "<?= $tahapan; ?>"</strong>
                    <?php
                        break;
                    case 'Payment':
                    ?>
                        <i class="fas fa-info-circle pr-4"></i><strong>PENGEMBALIAN TELAH MENCAPAI TAHAP : "<?= $tahapan; ?>"</strong>
                    <?php
                        break;
                    case 'Final':
                    ?>
                        <i class="fas fa-info-circle pr-4"></i><strong>PENGEMBALIAN SUDAH FINAL</strong>
                    <?php
                        break;

                    case 'Reject':
                    case 'Reject Dari Headoffice':
                    ?>
                        <i class="fas fa-info-circle pr-4"></i><strong class="text-uppercase">PENGEMBALIAN TELAH DI <?= $tahapan; ?></strong>
                <?php
                        break;

                    default:
                        break;
                }
                ?>
            </div>
        </div>
    </div>
<?php
}
