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
    'form_payment'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $flag = '';

    extract($_POST);

    $dataUpdate = selectStatement(
        $db,
        'SELECT * FROM balistars_payment WHERE idPengajuan = ? AND jenisPengajuan = ?',
        [$idPengajuan, $jenisPengajuan],
        'fetch'
    );

    $dataTahapanHeadoffice = selectStatement(
        $db,
        'SELECT * FROM balistars_penyetujuan WHERE idPengajuan = ? AND tahapan = ? AND jenisPengajuan = ? AND statusPenyetujuan = ?',
        [$idPengajuan, 'Headoffice', $jenisPengajuan, 'Aktif'],
        'fetch'
    );


    $dateTimePenyetujuanTerakhir = new DateTime($dataTahapanHeadoffice['timeStamp']);
    $dateTimeSekarang = new DateTime();

    $lamaWaktu = dateDiffInTime(date_diff($dateTimePenyetujuanTerakhir, $dateTimeSekarang));

    if ($dataUpdate) {
        $status = insertStatement(
            $db,
            'UPDATE
                balistars_payment
            SET
                tanggal = ?,
                keterangan = ?,
                idUserEdit = ?
            WHERE
                idPayment = ?
            ',
            [
                konversiTanggal($tanggal),
                trim($keterangan),
                $idUserAsli,
                $dataUpdate['idPayment'],
            ]
        );

        if ($status) {
            $pesan = 'Proses Update Payment Berhasil';
        } else {
            $pesan = 'Proses Update Payment Gagal';
        }
    } else {

        $status = insertStatement(
            $db,
            'INSERT INTO
                balistars_payment
            SET
                idPengajuan = ?,
                tanggal = ?,
                jenisPengajuan = ?,
                lamaWaktu = ?,
                keterangan = ?,
                statusPayment = ?,
                idUser = ?
            ',
            [
                $idPengajuan,
                konversiTanggal($tanggal),
                $jenisPengajuan,
                $lamaWaktu,
                trim($keterangan),
                'Aktif',
                $idUserAsli
            ]
        );

        if ($status) {
            $pesan = 'Proses Update Payment Berhasil';

            $tabel = [
                'Additional' => 'balistars_pengajuan_additional',
                'Partisi' => 'balistars_pengajuan_additional',
                'Pengembalian' => 'balistars_pengajuan_pengembalian',
                'Petty Cash' => 'balistars_pengajuan_petty_cash'
            ];

            if (isset($tabel[$jenisPengajuan])) {
                $statusTahapan = updateStatement(
                    $db,
                    "UPDATE
                        {$tabel[$jenisPengajuan]}
                    SET
                        tahapan = ?
                    WHERE
                        id{$jenisPengajuan} = ?
                    ",
                    ['Final', $idPengajuan]
                );
            }
        } else {
            $pesan = 'Proses Update Payment Gagal';
        }
    }

    $data = [
        'status' => $status,
        'pesan' => $pesan
    ];

    echo json_encode($data);
}
