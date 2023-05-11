<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
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
    'form_penyetujuan_additional'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $flag = '';

    extract($_POST);

    $dataPengajuan = selectStatement(
        $db,
        'SELECT * FROM balistars_pengajuan_additional WHERE idAdditional = ?',
        [$idAdditional],
        'fetch'
    );

    $dataPenyetujuanTerakhir = selectStatement(
        $db,
        'SELECT * FROM balistars_penyetujuan WHERE idPengajuan = ? AND jenisPengajuan = ? AND statusPenyetujuan = ? ORDER BY idPenyetujuan DESC LIMIT 1',
        [$idAdditional, 'Additional', 'Aktif'],
        'fetch'
    );

    $listKelanjutan = [
        'Disetujui' => [
            'Kontrol Area' => 'Pak Swi',
            'Reject Dari Headoffice' => 'Pak Swi',
            'Pak Swi' => 'Headoffice',
            'Headoffice' => 'Payment',
        ],
        'Reject' => [
            'Kontrol Area' => 'Reject',
            'Reject Dari Headoffice' => 'Reject',
            'Pak Swi' => 'Reject',
            'Headoffice' => 'Reject Dari Headoffice',
        ]
    ];

    $tahapanSebelumnya = $dataPengajuan['tahapan'];
    $tahapanBaru = $listKelanjutan[$hasil][$tahapanSebelumnya];

    $dataTahapanSebelumReject = selectStatement(
        $db,
        'SELECT * FROM balistars_penyetujuan WHERE idPengajuan = ? AND tahapan = ? AND jenisPengajuan = ? AND statusPenyetujuan = ?',
        [$idAdditional, $tahapanSebelumnya, 'Additional', 'Aktif'],
        'fetch'
    );

    if ($dataPenyetujuanTerakhir) {
        $dateTimePenyetujuanTerakhir = new DateTime($dataPenyetujuanTerakhir['timeStamp']);
    } else {
        $dateTimePenyetujuanTerakhir = new DateTime($dataPengajuan['timeStamp']);
    }

    $dateTimeSekarang = new DateTime();

    $lamaWaktu = dateDiffInTime(date_diff($dateTimePenyetujuanTerakhir, $dateTimeSekarang));

    if ($dataTahapanSebelumReject) {

        $lamaWaktu = averageTime([$lamaWaktu, strval($dataTahapanSebelumReject['lamaWaktu'])]);
        $status = updateStatement(
            $db,
            'UPDATE
                balistars_penyetujuan
            SET
                hasil = ?,
                lamaWaktu = ?,
                idUserPenyetuju = ?,
                timeStamp = CURRENT_TIMESTAMP(),
                idUserEdit = ?
            WHERE
                idPenyetujuan = ?
            ',
            [
                $hasil,
                $lamaWaktu,
                $idUserAsli,
                $idUserAsli,
                $dataTahapanSebelumReject['idPenyetujuan']
            ]
        );
    } else {
        $status = insertStatement(
            $db,
            'INSERT INTO
                balistars_penyetujuan
            SET
                idPengajuan = ?,
                tahapan = ?,
                jenisPengajuan = ?,
                hasil = ?,
                lamaWaktu = ?,
                keterangan = ?,
                idUserPenyetuju = ?,
                statusPenyetujuan = ?,
                idUser = ?
            ',
            [
                $idAdditional,
                $tahapanSebelumnya,
                'Additional',
                $hasil,
                $lamaWaktu,
                trim($keterangan),
                $idUserAsli,
                'Aktif',
                $idUserAsli
            ]
        );
    }

    if ($status == true) {
        $statusTahapan = updateStatement(
            $db,
            'UPDATE
                balistars_pengajuan_additional
            SET
                tahapan = ?
            WHERE
                idAdditional = ?',
            [$tahapanBaru, $idAdditional]
        );
    }

    if ($hasil === 'Reject') {
        if ($status) {
            $pesan = 'Proses Reject Additional Berhasil';
        } else {
            $pesan = 'Proses Reject Additional Gagal';
        }
    } else if ($hasil === 'Disetujui') {
        if ($status) {
            $pesan = 'Proses Penyetujuan Additional Berhasil';
        } else {
            $pesan = 'Proses Penyetujuan Additional Gagal';
        }
    }


    $data = [
        'status' => $status,
        'pesan' => $pesan
    ];

    echo json_encode($data);
}
