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
    basename(__DIR__)
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $flag = '';

    extract($_POST);

    $config = [
        'Additional' => [
            'tabel' => 'balistars_pengajuan_additional',
            'id' => 'idAdditional',
            'status' => 'statusAdditional',
            'listKelanjutan' => [
                "Disetujui" => [
                    "Kontrol Area" => "Pak Swi",
                    "Pak Swi" => "Headoffice",
                    "Headoffice" => "Payment",
                ],
                "Reject" => [
                    "Kontrol Area" => "Reject",
                    "Pak Swi" => "Reject",
                    "Headoffice" => "Kontrol Area",
                ]
            ]
        ],
        'Partisi' => [
            'tabel' => 'balistars_pengajuan_partisi',
            'id' => 'idPartisi',
            'status' => 'statusPartisi',
            'listKelanjutan' => [
                "Disetujui" => [
                    "Kontrol Area" => "Pak Swi",
                    "Pak Swi" => "Headoffice",
                    "Headoffice" => "Payment",
                ],
                "Reject" => [
                    "Kontrol Area" => "Reject",
                    "Pak Swi" => "Reject",
                    "Headoffice" => "Kontrol Area",
                ]
            ]
        ],
        'Pengembalian' => [
            'tabel' => 'balistars_pengajuan_pengembalian',
            'id' => 'idPengembalian',
            'status' => 'statusPengembalian',
            'listKelanjutan' => [
                "Disetujui" => [
                    "Kontrol Area" => "Pak Swi",
                    "Pak Swi" => "Headoffice",
                    "Headoffice" => "Payment",
                ],
                "Reject" => [
                    "Kontrol Area" => "Reject",
                    "Pak Swi" => "Reject",
                    "Headoffice" => "Kontrol Area",
                ]
            ]
        ],
        'Petty Cash' => [
            'tabel' => 'balistars_pengajuan_petty_cash',
            'id' => 'idPettyCash',
            'status' => 'statusPettyCash',
            'listKelanjutan' => [
                "Disetujui" => [
                    "Headoffice" => "Payment",
                ],
                "Reject" => [
                    "Headoffice" => "Reject",
                ]
            ]
        ],
    ];

    if (isset($config[$jenisPengajuan])) {

        ['tabel' => $tabel, 'id' =>  $colID, 'status' => $colStatus, 'listKelanjutan' => $listKelanjutan] = $config[$jenisPengajuan];

        $dataPengajuan = selectStatement(
            $db,
            "SELECT * FROM {$tabel} WHERE {$colID} = ?",
            [$idPengajuan],
            "fetch"
        );

        $dataPenyetujuanTerakhir = selectStatement(
            $db,
            "SELECT * FROM balistars_penyetujuan WHERE idPengajuan = ? AND jenisPengajuan = ? AND statusPenyetujuan = ? ORDER BY idPenyetujuan DESC LIMIT 1",
            [$idPengajuan, $jenisPengajuan, "Aktif"],
            "fetch"
        );

        $tahapanSebelumnya = $dataPengajuan["tahapan"];
        $tahapanBaru = $listKelanjutan[$hasil][$tahapanSebelumnya];

        $dataTahapanSebelumReject = selectStatement(
            $db,
            "SELECT * FROM balistars_penyetujuan WHERE idPengajuan = ? AND tahapan = ? AND jenisPengajuan = ? AND statusPenyetujuan = ?",
            [$idPengajuan, $tahapanSebelumnya, $jenisPengajuan, "Aktif"],
            "fetch"
        );

        if ($dataPenyetujuanTerakhir) {
            $dateTimePenyetujuanTerakhir = new DateTime($dataPenyetujuanTerakhir["timeStamp"]);
        } else {
            $dateTimePenyetujuanTerakhir = new DateTime($dataPengajuan["timeStamp"]);
        }

        $dateTimeSekarang = getTimestamp('DATABASE');

        $lamaWaktu = dateDiffInTime(date_diff($dateTimePenyetujuanTerakhir, $dateTimeSekarang));

        $listDomain = ['legugendong.com'];

        $SERV_NAME = $_SERVER['SERVER_NAME'];
        $REQ_SCHEME = $_SERVER['REQUEST_SCHEME'];

        if (in_array($SERV_NAME, $listDomain) && $REQ_SCHEME === 'https') {
            $lamaWaktu = secondsToTime(timeInSeconds($lamaWaktu) - 3600);
        }

        $status = insertStatement(
            $db,
            "INSERT INTO
                balistars_penyetujuan
            SET
                idPengajuan = ?,
                tahapan = ?,
                jenisPengajuan = ?,
                hasil = ?,
                lamaWaktu = ?,
                attempt = ?,
                menit = ?,
                keterangan = ?,
                idUserPenyetuju = ?,
                statusPenyetujuan = ?,
                idUser = ?
        ",
            [
                $idPengajuan,
                $tahapanSebelumnya,
                $jenisPengajuan,
                $hasil,
                $lamaWaktu,
                $dataPengajuan['attempt'],
                timeInMinutes($lamaWaktu),
                trim($keterangan),
                $idUserAsli,
                "Aktif",
                $idUserAsli
            ]
        );

        if ($status == true) {
            $statusTahapan = updateStatement(
                $db,
                "UPDATE
                    {$tabel}
                SET
                    tahapan = ?
                WHERE
                    {$colID} = ?",
                [$tahapanBaru, $idPengajuan]
            );
        }

        if ($hasil === "Reject") {
            if ($status) {
                $pesan = "Proses Reject {$jenisPengajuan} Berhasil";
            } else {
                $pesan = "Proses Reject {$jenisPengajuan} Gagal";
            }
        } else if ($hasil === "Disetujui") {
            if ($status) {
                $pesan = "Proses Penyetujuan {$jenisPengajuan} Berhasil";
            } else {
                $pesan = "Proses Penyetujuan {$jenisPengajuan} Gagal";
            }
        }
    } else {
        $status = false;
        $pesan = "Jenis Pengajuan Tidak Terdaftar";
    }


    $data = [
        "status" => $status,
        "pesan" => $pesan
    ];

    echo json_encode($data);
}
