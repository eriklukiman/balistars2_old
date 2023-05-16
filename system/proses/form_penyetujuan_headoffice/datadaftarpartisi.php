<?php

include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsistatement.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
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

    extract($_POST);

    $idJabatan = intval($dataLogin['idJabatan']);
    $idPegawai = intval($dataLogin['idPegawai']);

    $tahapan = ['Headoffice'];

    $dataCabang = selectStatement(
        $db,
        'SELECT idCabang FROM balistars_cabang WHERE area = ?',
        [$area],
    );

    $idCabangCakupan = array_column($dataCabang, 'idCabang');


?>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th class="text-center">NO</th>
                <th class="text-center">AKSI</th>
                <th class="text-center">CUSTOMER</th>
                <th class="text-center">TGL PENGAJUAN</th>
                <th class="text-center">BIAYA</th>
                <th class="text-center">LAMA PARTISI</th>
                <th class="text-center">KETERANGAN</th>
                <th class="text-center">STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $tanggal = explode(' - ', $rentang);

            if (empty($tahapan)) {
            ?>
                <tr>
                    <td class="text-center table-active" colspan="8"><i class="fas fa-exclamation-circle pr-4"></i><strong>MAAF, ANDA TIDAK MEMILIKI AKSES UNTUK MELAKUKAN PENYETUJUAN</strong></td>
                </tr>
                <?php
            } else {

                if (isset($tanggal[0]) && isset($tanggal[1])) {

                    $questionMarkTahapan = join(',', array_fill(0, count($tahapan), '?'));

                    if ($area === '') {
                        $parameter['cabang'] = '';
                    } else {
                        $questionMark = join(',', array_fill(0, count($idCabangCakupan), '?'));
                        $parameter['cabang'] = "AND idCabang IN ({$questionMark})";
                    }

                    $tanggalAwal = konversiTanggal($tanggal[0]);
                    $tanggalAkhir = konversiTanggal($tanggal[1]);

                    $dataPartisi = selectStatement(
                        $db,
                        "SELECT 
                            balistars_pengajuan_partisi.*,
                            balistars_user.userName
                        FROM 
                            balistars_pengajuan_partisi
                            INNER JOIN balistars_user ON balistars_pengajuan_partisi.idUser = balistars_user.idUser
                        WHERE 
                            balistars_pengajuan_partisi.statusPartisi = ?
                            AND (balistars_pengajuan_partisi.tglPengajuan BETWEEN ? AND ?)
                            {$parameter['cabang']}
                    ",
                        array_merge(
                            [
                                'Aktif',  $tanggalAwal, $tanggalAkhir
                            ],
                            $idCabangCakupan
                        )
                    );

                    $n = 1;

                    $isDataDisplayed = false;
                    foreach ($dataPartisi as $row) {
                        $skip = false;

                        switch ($status) {
                            case 'Belum Diproses':
                                if ($row['tahapan'] === 'Headoffice') {
                                    $skip = false;
                                } else {
                                    $skip = true;
                                }
                                break;
                            case 'Disetujui':
                                $cekHasil = selectStatement(
                                    $db,
                                    'SELECT * FROM balistars_penyetujuan WHERE idPengajuan = ? AND jenisPengajuan = ? AND statusPenyetujuan = ? AND tahapan = ? ORDER BY idPenyetujuan DESC LIMIT 1',
                                    [$row['idPartisi'], $jenisPengajuan, 'Aktif', 'Headoffice'],
                                    'fetch'
                                )['hasil'];


                                if ($cekHasil === 'Disetujui') {
                                    $skip = false;
                                } else {
                                    $skip = true;
                                }

                                break;
                            case 'Reject':
                                $cekHasil = selectStatement(
                                    $db,
                                    'SELECT * FROM balistars_penyetujuan WHERE idPengajuan = ? AND jenisPengajuan = ? AND statusPenyetujuan = ? AND tahapan = ? ORDER BY idPenyetujuan DESC LIMIT 1',
                                    [$row['idPartisi'], $jenisPengajuan, 'Aktif', 'Headoffice'],
                                    'fetch'
                                )['hasil'];

                                if ($cekHasil === 'Reject') {
                                    $skip = false;
                                } else {
                                    $skip = true;
                                }

                                break;

                            default:
                                $skip = false;
                                break;
                        }


                        if ($skip) continue;

                        $isDataDisplayed = true || $isDataDisplayed;
                ?>
                        <tr>
                            <td class="text-center"><?= $n ?></td>
                            <td class="text-center" class="align-middle">
                                <button type="button" class="btn btn-info" onclick="getFormPenyetujuan('<?= $jenisPengajuan ?>','<?= $row['idPartisi'] ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php
                                if (in_array('Pak Swi', $tahapan)) {
                                ?>
                                    <button type="button" class="btn btn-success" onclick="prosesPenyetujuan($(this),'<?= $jenisPengajuan ?>', '<?= $row['idPartisi'] ?>', 'Disetujui')">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                <?php
                                }
                                ?>
                            </td>
                            <td class="text-center"><?= $row['namaCustomer'] ?></td>
                            <td class="text-center"><?= ubahTanggalIndo($row['tglPengajuan']) ?></td>
                            <td class="text-right">Rp <?= ubahToRp($row['biaya']) ?></td>
                            <td class="text-right"><?= $row['lamaPartisi'] ?></td>
                            <td class="text-right"><?= $row['keteranganPembelian'] ?></td>
                            <td class="text-center">
                                <?php
                                $dataFeedback = selectStatement(
                                    $db,
                                    "SELECT
                                            tahapan,
                                            lamaWaktu
                                        FROM
                                            balistars_penyetujuan
                                        WHERE
                                            idPengajuan = ?,
                                            AND jenisPengajuan = ?
                                            AND statusPenyetujuan = ?
                                            AND tahapan IN ({$questionMarkTahapan})
                                            AND idUserPenyetuju = ?
                                        ",
                                    array_merge([
                                        $row['idPengembalian'],
                                        'Pengembalian',
                                        'Aktif',
                                    ], $tahapan, [$idUserAsli])
                                );

                                if (count($dataFeedback) === 0) {
                                    $status = 'info';
                                } else {
                                    $poin = array_map(function ($tahapan, $lamaWaktu) {
                                        if (in_array($tahapan, ['Kontrol Area', 'Headoffice', 'Payment'])) {
                                            return poinPengajuan($tahapan, timeInMinutes($lamaWaktu));
                                        }
                                    }, array_column($dataFeedback, 'tahapan'), array_column($dataFeedback, 'lamaWaktu'));

                                    $poin = array_filter($poin, function ($nilai) {
                                        return !is_null($nilai);
                                    });

                                    $average = array_sum($poin) / count($poin);
                                    $status = statusAveragePoin($average);
                                }
                                ?>
                                <button type="button" class="btn btn-<?= $status ?>" onclick="showProgressPenyetujuan('<?= $jenisPengajuan ?>','<?= $row['idPengembalian'] ?>')"><strong>CEK STATUS</strong></button>
                            </td>
                        </tr>
                    <?php
                        $n++;
                    }
                    if ($isDataDisplayed === false) {
                    ?>
                        <tr>
                            <td class="text-center table-active" colspan="8"><i class="fas fa-info-circle pr-4"></i><strong>DATA TIDAK DITEMUKAN</strong></td>
                        </tr>
            <?php
                    }
                }
            }
            ?>
        </tbody>
    </table>

<?php
}
