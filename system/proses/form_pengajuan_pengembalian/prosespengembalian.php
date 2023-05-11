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
    'form_pengajuan_pengembalian'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $flag = '';

    extract($_POST);

    if ($flag == 'cancel') {
        $sql = $db->prepare('UPDATE balistars_pengajuan_pengembalian SET statusPengembalian = ?, idUserEdit = ? WHERE idPengembalian=?');
        $status = $sql->execute(['Non Aktif', $idUserAsli, $idPengembalian]);

        if ($status) {
            $pesan = 'Proses Non Aktif Pengajuan Pengembalian Berhasil';
        } else {
            $pesan = 'Proses Non Aktif Pengajuan Pengembalian Gagal';
        }
    } else if ($flag === 'pengajuanUlang') {
        $sql = $db->prepare('UPDATE balistars_pengajuan_pengembalian SET tahapan = ?, idUserEdit = ? WHERE idPengembalian=?');
        $status = $sql->execute(['Kontrol Area', $idUserAsli, $idPengembalian]);

        if ($status) {
            $pesan = 'Proses Pengajuan Ulang Pengembalian Berhasil';
        } else {
            $pesan = 'Proses Pengajuan Ulang Pengembalian Gagal';
        }
    } else {
        $listKolom = [
            'linkSuratPengajuan' => 'Surat Pengajuan',
            'linkSuratPernyataanCustomer' => 'Surat Pernyataan Customer',
            'linkNotaPenjualan' => 'Nota Penjualan',
            'linkBuktiTransfer' => 'Bukti Transfer',
            'linkBuktiPotongPPH' => 'Bukti Potong PPH',
            'linkBuktiPotongPPN' => 'Bukti Potong PPN',
            'linkRincianPenjualanExcel' => 'Rincian Penjualan Excel',
            'linkBuktiChatCustomer' => 'Bukti Chat Customer'
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
                        balistars_pengajuan_pengembalian
                    SET
                        namaCustomer = ?,
                        tglPengajuan = ?,
                        jumlahTransaksi = ?,
                        totalPengembalian = ?,
                        linkSuratPengajuan = ?,
                        linkSuratPernyataanCustomer = ?,
                        linkNotaPenjualan = ?,
                        linkBuktiTransfer = ?,
                        linkBuktiPotongPPH = ?,
                        linkBuktiPotongPPN = ?,
                        linkRincianPenjualanExcel = ?,
                        linkBuktiChatCustomer = ?,
                        idUserEdit = ?
                    WHERE
                        idPengembalian = ?
                ',
                    [
                        $namaCustomer,
                        konversiTanggal($tglPengajuan),
                        ubahToInt($jumlahTransaksi),
                        ubahToInt($totalPengembalian),
                        $linkSuratPengajuan,
                        $linkSuratPernyataanCustomer,
                        $linkNotaPenjualan,
                        $linkBuktiTransfer,
                        $linkBuktiPotongPPH,
                        $linkBuktiPotongPPN,
                        $linkRincianPenjualanExcel,
                        $linkBuktiChatCustomer,
                        $idUserAsli,
                        $idPengembalian
                    ]
                );

                if ($status) {
                    $pesan = 'Proses Update Pengajuan Pengembalian Berhasil';
                } else {
                    $pesan = 'Proses Update Pengajuan Pengembalian Gagal';
                }
            } else if ($flag === 'tambah') {
                $status = updateStatement(
                    $db,
                    'INSERT INTO
                        balistars_pengajuan_pengembalian
                    SET
                        namaCustomer = ?,
                        tglPengajuan = ?,
                        jumlahTransaksi = ?,
                        totalPengembalian = ?,
                        linkSuratPengajuan = ?,
                        linkSuratPernyataanCustomer = ?,
                        linkNotaPenjualan = ?,
                        linkBuktiTransfer = ?,
                        linkBuktiPotongPPH = ?,
                        linkBuktiPotongPPN = ?,
                        linkRincianPenjualanExcel = ?,
                        linkBuktiChatCustomer = ?,
                        tahapan = ?,
                        idCabang = ?,
                        statusPengembalian = ?,
                        idUser = ?
                ',
                    [
                        $namaCustomer,
                        konversiTanggal($tglPengajuan),
                        ubahToInt($jumlahTransaksi),
                        ubahToInt($totalPengembalian),
                        $linkSuratPengajuan,
                        $linkSuratPernyataanCustomer,
                        $linkNotaPenjualan,
                        $linkBuktiTransfer,
                        $linkBuktiPotongPPH,
                        $linkBuktiPotongPPN,
                        $linkRincianPenjualanExcel,
                        $linkBuktiChatCustomer,
                        'Kontrol Area',
                        $dataLogin['idCabang'],
                        'Aktif',
                        $idUserAsli
                    ]
                );

                if ($status) {
                    $pesan = 'Proses Tambah Pengajuan Pengembalian Berhasil';
                } else {
                    $pesan = 'Proses Tambah Pengajuan Pengembalian Gagal';
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
