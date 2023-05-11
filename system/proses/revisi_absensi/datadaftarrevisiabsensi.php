<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';

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

//MENGECEK APAKAH USER INI BERHAK MENGAKSES MENU INI
$sqlCekMenu = $db->prepare('SELECT * from balistars_user_detail 
  inner join balistars_menu_sub 
  on balistars_menu_sub.idMenuSub = balistars_user_detail.idMenuSub
  where balistars_user_detail.idUser = ?
  and namaFolder = ?');
$sqlCekMenu->execute([
    $idUserAsli,
    'revisi_absensi'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggal = explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]);

$sqlLibur = $db->prepare(
    'SELECT 
        hariLibur 
    FROM 
        balistars_produktivity 
    WHERE 
        (tanggalProduktivity BETWEEN ? AND ?) 
        AND idCabang=? 
        AND statusProduktivity=?'
);
$sqlLibur->execute([
    $tanggalAwal, $tanggalAkhir,
    $idCabang,
    'Aktif'
]);

$dataLibur = $sqlLibur->fetchAll();

if (count($dataLibur) > 0) {
    $disabled = '';
    $hariLibur = [];

    foreach ($dataLibur as $index => $data) {
        $hariLibur = array_merge($hariLibur, explode(',', $data['hariLibur']));
    }
} else {
    $disabled = 'disabled';
    $hariLibur = [];
}

//** excecute sql revisi **
$execute1 = [
    $idCabang,
    'Hari Kerja'
];

$execute2 = [
    $idCabang,
    'Hari Libur',
];

$tandaTanya = [];

foreach ($hariLibur as $index => $value) {
    $tandaTanya[] = '?';
    $execute1[] = $value;
    $execute2[] = $value;
}

$joinTandaTanya = join(',', $tandaTanya);

$sqlRevisi = $db->prepare('
  (
    SELECT 
        *  
    FROM 
        balistars_absensi 
        INNER JOIN balistars_pegawai ON balistars_absensi.idPegawai=balistars_pegawai.idPegawai
    WHERE 
        balistars_absensi.idCabang=? 
        AND jenisPoin=?
        AND tanggalDatang IN (' . $joinTandaTanya . ')
  )
  UNION 
  (
    SELECT 
        *  
    FROM 
        balistars_absensi 
        INNER JOIN balistars_pegawai ON balistars_absensi.idPegawai=balistars_pegawai.idPegawai
    WHERE 
        balistars_absensi.idCabang=? 
        AND jenisPoin = ? 
        AND tanggalDatang NOT IN (' . $joinTandaTanya . ')
  )
   ');
$execute = array_merge($execute1, $execute2);
$sqlRevisi->execute($execute);
$dataRevisi = $sqlRevisi->fetchAll();


$n = 1;
foreach ($dataRevisi as $row) {
?>
    <tr>
        <td><?= $n ?></td>
        <td><?= wordwrap($row['namaPegawai'], 50, '<br>') ?></td>
        <td><?= wordwrap(ubahTanggalIndo($row['tanggalDatang']), 50, '<br>') ?></td>
        <td><?= wordwrap($row['jenisPoin'], 50, '<br>') ?></td>
    </tr>
<?php
    $n++;
}
?>
<tr>
    <td colspan="3"></td>
    <td>
        <button type="button" title="Edit" class="btn btn-info tombolEditCabang" style="color: white;" onclick="prosesRevisiAbsensi()" <?= $disabled ?>>
            <i class="fa fa-save"> </i> Proses
        </button>
    </td>
</tr>