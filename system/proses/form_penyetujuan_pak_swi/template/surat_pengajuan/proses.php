<?php
include_once '../../../../../library/konfigurasiurl.php';
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
    'form_penyetujuan_pak_swi'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $flag = '';

    extract($_POST);

    $expKolom = explode('@', $kolom)[0];
    if (in_array($expKolom, ['listTransaksi', 'noSurat', 'listKronologi'])) {
        if ($flag === 'input') {
            $dataInput = selectStatement(
                $db,
                'SELECT * FROM balistars_data_surat_pengajuan WHERE idDataSuratPengajuan = ?',
                [$id],
                'fetch'
            );

            if ($dataInput) {
                $status = updateStatement(
                    $db,
                    'UPDATE
                    balistars_data_surat_pengajuan
                SET
                    data = ?,
                    idUserEdit = ?
                WHERE
                    idDataSuratPengajuan = ?',
                    [$data, $idUserAsli, $id]
                );

                if ($status) {
                    $pesan = 'Proses Ubah Data Berhasil';
                } else {
                    $pesan = 'Proses Ubah Data Gagal';
                }
            } else {
                $status = updateStatement(
                    $db,
                    'INSERT INTO
                    balistars_data_surat_pengajuan
                SET
                    row = ?,
                    kolom = ?,
                    data = ?,
                    idPengajuan = ?,
                    tahapan = ?,
                    idUser = ?',
                    [$row, $kolom, $data, $idPengajuan, 'Pak Swi', $idUserAsli]
                );

                if ($status) {
                    $pesan = 'Proses Input Data Berhasil';
                } else {
                    $pesan = 'Proses Input Data Gagal';
                }
            }
        } else if ($flag === 'delete') {
            $status = deleteStatement(
                $db,
                'DELETE FROM balistars_data_surat_pengajuan WHERE idDataSuratPengajuan = ?',
                [$id]
            );

            if ($status) {
                $pesan = 'Proses Delete Data Berhasil';
            } else {
                $pesan = 'Proses Delete Data Gagal';
            }
        }

        switch ($expKolom) {
            case 'listKronologi':
                $execFunc = 'getFormListKronologi';
                break;
            case 'listTransaksi':
                $execFunc = 'getFormListTransaksi';
                break;
            case 'noSurat':
                $execFunc = 'getFormNoSurat';
                break;
        }
    } else {
        $status = false;
        $pesan = 'Kolom Tidak Valid';

        $execFunc = '';
    }



    $data = [
        "status" => $status,
        "pesan" => $pesan,
        'execFunc' => $execFunc
    ];

    echo json_encode($data);
}
