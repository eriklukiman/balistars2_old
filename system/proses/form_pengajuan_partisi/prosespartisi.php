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
    'form_pengajuan_partisi'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $flag = '';

    extract($_POST);

    if ($flag == 'cancel') {
        $sql = $db->prepare('UPDATE balistars_pengajuan_partisi SET statusPartisi = ? WHERE idPartisi=?');
        $status = $sql->execute(['Non Aktif', $idPartisi]);

        if ($status) {
            $pesan = 'Proses Non Aktif Pengajuan Partisi Berhasil';
        } else {
            $pesan = 'Proses Non Aktif Pengajuan Partisi Gagal';
        }
    } else if ($flag === 'pengajuanUlang') {
        $sql = $db->prepare('UPDATE balistars_pengajuan_partisi SET tahapan = ?, attempt = attempt + 1, idUserEdit = ? WHERE idPartisi=?');
        $status = $sql->execute(['Kontrol Area', $idUserAsli, $idPartisi]);

        if ($status) {
            $pesan = 'Proses Pengajuan Ulang Partisi Berhasil';
        } else {
            $pesan = 'Proses Pengajuan Ulang Partisi Gagal';
        }
    } else {
        $listKolom = [
            'linkSuratPartisi' => 'Surat Partisi',
            'linkBuktiOrder' => 'Bukti Order',
            'linkPenawaran' => 'Penawaran',
            'linkFoto' => 'Foto',
            'linkPerbandingan' => 'Perbandingan',
            'linkLainnya' => 'Lainnya',
        ];

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
                        balistars_pengajuan_partisi
                    SET
                        namaCustomer = ?,
                        tglPengajuan = ?,
                        biaya = ?,
                        lamaPartisi = ?,
                        keteranganPembelian = ?,
                        linkSuratPartisi = ?,
                        linkBuktiOrder = ?,
                        linkPenawaran = ?,
                        linkFoto = ?,
                        linkPerbandingan = ?,
                        linkLainnya = ?,
                        idUserEdit = ?
                    WHERE
                        idPartisi = ?
                ',
                    [
                        $namaCustomer,
                        konversiTanggal($tglPengajuan),
                        ubahToInt($biaya),
                        $lamaPartisi,
                        $keteranganPembelian,
                        $linkSuratPartisi,
                        $linkBuktiOrder,
                        $linkPenawaran,
                        $linkFoto,
                        $linkPerbandingan,
                        $linkLainnya,
                        $idUserAsli,
                        $idPartisi
                    ]
                );

                if ($status) {
                    $pesan = 'Proses Update Pengajuan Partisi Berhasil';
                } else {
                    $pesan = 'Proses Update Pengajuan Partisi Gagal';
                }
            } else if ($flag === 'tambah') {
                $status = updateStatement(
                    $db,
                    'INSERT INTO
                        balistars_pengajuan_partisi
                    SET
                        namaCustomer = ?,
                        tglPengajuan = ?,
                        biaya = ?,
                        lamaPartisi = ?,
                        keteranganPembelian = ?,
                        linkSuratPartisi = ?,
                        linkBuktiOrder = ?,
                        linkPenawaran = ?,
                        linkFoto = ?,
                        linkPerbandingan = ?,
                        linkLainnya = ?,
                        tahapan = ?,
                        idCabang = ?,
                        statusPartisi = ?,
                        idUser = ?
                ',
                    [
                        $namaCustomer,
                        konversiTanggal($tglPengajuan),
                        ubahToInt($biaya),
                        $lamaPartisi,
                        $keteranganPembelian,
                        $linkSuratPartisi,
                        $linkBuktiOrder,
                        $linkPenawaran,
                        $linkFoto,
                        $linkPerbandingan,
                        $linkLainnya,
                        'Kontrol Area',
                        $dataLogin['idCabang'],
                        'Aktif',
                        $idUserAsli
                    ]
                );

                if ($status) {
                    $pesan = 'Proses Tambah Pengajuan Partisi Berhasil';

                    $dataPegawaiPenerima = selectStatement(
                        $db,
                        'SELECT 
                            balistars_pegawai.* 
                        FROM 
                            balistars_pegawai
                            INNER JOIN balistars_cabang ON balistars_pegawai.idCabang = balistars_pegawai.idCabang
                        WHERE 
                            balistars_cabang.area = ? 
                            AND balistars_pegawai.idJabatan = ?',
                        [$dataLogin['area'], 9],
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
                                'Kontrol Area ' . $dataLogin['area'],
                                $dataLogin['namaPegawai'],
                                'Additional'
                            );
                        }
                    }
                } else {
                    $pesan = 'Proses Tambah Pengajuan Partisi Gagal';
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
