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
include_once $BASE_URL_PHP . '/system/proses/automailer.php';

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
    'form_pengajuan_petty_cash'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $flag = '';

    extract($_POST);

    if ($flag == 'cancel') {
        $sql = $db->prepare('UPDATE balistars_pengajuan_petty_cash SET statusPettyCash = ? WHERE idPettyCash=?');
        $status = $sql->execute(['Non Aktif', $idPettyCash]);

        if ($status) {
            $pesan = 'Proses Non Aktif Pengajuan Petty Cash Berhasil';
        } else {
            $pesan = 'Proses Non Aktif Pengajuan Petty Cash Gagal';
        }
    } else if ($flag === 'pengajuanUlang') {
        $sql = $db->prepare('UPDATE balistars_pengajuan_petty_cash SET tahapan = ?, attempt = attempt + 1, idUserEdit = ? WHERE idPettyCash=?');
        $status = $sql->execute(['Headoffice', $idUserAsli, $idPettyCash]);

        if ($status) {
            $pesan = 'Proses Pengajuan Ulang Petty Cash Berhasil';
        } else {
            $pesan = 'Proses Pengajuan Ulang Petty Cash Gagal';
        }
    } else {
        $listKolom = [];

        $outputValidasi = [];

        foreach ($listKolom as $kolom => $label) {
            if (!preg_match('/https\:\/\/[\w.]+.com\//', ${$kolom})) {
                $outputValidasi[] = $listKolom[$kolom];
            }
        }

        if (!empty($outputValidasi)) {
            $status = false;

            $strValidasi = join('",</strong><br/><strong>"', $outputValidasi);
            $pesan = 'Link Yang Terdapat Pada Inputan</strong> <br/><strong>"' . $strValidasi . '" <br/></strong>Tidak Valid !';
        } else {

            if ($flag === 'update') {
                $status = updateStatement(
                    $db,
                    'UPDATE
                        balistars_pengajuan_petty_cash
                    SET
                        namaProyek = ?,
                        namaPerusahaan = ?,
                        tglPengajuan = ?,
                        estimasiOmset = ?,
                        estimasiBiayaPengeluaran = ?,
                        nominal = ?,
                        biayaEksternal = ?,
                        noPO = ?,
                        keterangan = ?,
                        idUserEdit = ?
                    WHERE
                        idPettyCash = ?
                ',
                    [
                        $namaProyek,
                        $namaPerusahaan,
                        konversiTanggal($tglPengajuan),
                        ubahToInt($estimasiOmset),
                        ubahToInt($estimasiBiayaPengeluaran),
                        ubahToInt($nominal),
                        ubahToInt($biayaEksternal),
                        $noPO,
                        $keterangan,
                        $idUserAsli,
                        $idPettyCash
                    ]
                );

                if ($status) {
                    $pesan = 'Proses Update Pengajuan Petty Cash Berhasil';
                } else {
                    $pesan = 'Proses Update Pengajuan Petty Cash Gagal';
                }
            } else if ($flag === 'tambah') {
                $status = updateStatement(
                    $db,
                    'INSERT INTO
                        balistars_pengajuan_petty_cash
                    SET
                        namaProyek = ?,
                        namaPerusahaan = ?,
                        tglPengajuan = ?,
                        estimasiOmset = ?,
                        estimasiBiayaPengeluaran = ?,
                        nominal = ?,
                        biayaEksternal = ?,
                        noPO = ?,
                        keterangan = ?,
                        tahapan = ?,
                        idCabang = ?,
                        statusPettyCash = ?,
                        idUser = ?
                ',
                    [
                        $namaProyek,
                        $namaPerusahaan,
                        konversiTanggal($tglPengajuan),
                        ubahToInt($estimasiOmset),
                        ubahToInt($estimasiBiayaPengeluaran),
                        ubahToInt($nominal),
                        ubahToInt($biayaEksternal),
                        $noPO,
                        $keterangan,
                        'Headoffice',
                        $dataLogin['idCabang'],
                        'Aktif',
                        $idUserAsli
                    ]
                );

                if ($status) {
                    $pesan = 'Proses Tambah Pengajuan Petty Cash Berhasil';

                    $dataPegawaiPenerima = selectStatement(
                        $db,
                        'SELECT 
                            balistars_pegawai.* 
                        FROM 
                            balistars_pegawai
                            INNER JOIN balistars_user ON balistars_pegawai.idPegawai ON balistars_user.idPegawai
                            INNER JOIN balistars_user_detail ON balistars_user_detail.idUser ON balistars_user.idUser
                            INNER JOIN balistars_menu_sub ON balistars_user_detail.idMenuSub ON balistars_menu_sub.idMenuSub
                        WHERE 
                            balistars_pegawai.idJabatan = ?
                            AND balistars_menu_sub.namaFolder = ?',
                        [2, 'form_penyetujuan_headoffice'],
                        'fetch'
                    );

                    if ($dataPegawaiPenerima) {
                        if ($dataPegawaiPenerima['email'] !== '') {
                            sendEmailNotificationPengajuan(
                                $db,
                                $tokenCSRF,
                                $dataPegawaiPenerima['email'],
                                $dataPegawaiPenerima['namaPegawai'],
                                konversiTanggal($tglPengajuan),
                                'Headoffice',
                                $dataLogin['namaPegawai'],
                                'Additional'
                            );
                        }
                    }
                } else {
                    $pesan = 'Proses Tambah Pengajuan Petty Cash Gagal';
                }
            }
        }
    }

    $data = [
        'status' => $status,
        'pesan' => $pesan
    ];

    echo json_encode($data);
}
