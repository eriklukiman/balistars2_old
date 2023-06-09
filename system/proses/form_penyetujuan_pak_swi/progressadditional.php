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
    basename(__DIR__)
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
    header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
} else {

    $flag = '';
    $idAdditional = '';

    extract($_POST);

    $idAdditional = $idPengajuan;

    $dataUpdate = selectStatement(
        $db,
        'SELECT * FROM balistars_pengajuan_additional WHERE idAdditional = ?',
        [$idAdditional],
        'fetch'
    );

    $dataFeedback = selectStatement(
        $db,
        'SELECT
            *
        FROM
        (
            (
                SELECT 
                    balistars_penyetujuan.keterangan,
                    balistars_penyetujuan.lamaWaktu,
                    balistars_penyetujuan.tahapan,
                    balistars_penyetujuan.hasil,
                    balistars_penyetujuan.timeStamp,
                    \'\' as tambahan,
                    balistars_penyetujuan.menit,
                    balistars_user.userName,
                    balistars_pegawai.namaPegawai
                FROM 
                    balistars_penyetujuan
                    INNER JOIN balistars_user ON balistars_penyetujuan.idUserPenyetuju = balistars_user.idUser
                    INNER JOIN balistars_pegawai ON balistars_user.idPegawai = balistars_pegawai.idPegawai
                WHERE 
                    balistars_penyetujuan.idPengajuan = ? 
                    AND balistars_penyetujuan.jenisPengajuan = ? 
                    AND balistars_penyetujuan.statusPenyetujuan = ?
                    ORDER BY balistars_penyetujuan.idPenyetujuan
            )
            UNION ALL
            (
                SELECT 
                    balistars_payment.keterangan, 
                    balistars_payment.lamaWaktu,
                    \'Payment\' as tahapan,
                    \'Disetujui\' as hasil, 
                    balistars_payment.timeStamp,
                    balistars_payment.tanggal as tambahan,
                    balistars_payment.menit,
                    balistars_user.userName,
                    balistars_pegawai.namaPegawai 
                FROM 
                    balistars_payment 
                    INNER JOIN balistars_user ON balistars_payment.idUser = balistars_user.idUser
                    INNER JOIN balistars_pegawai ON balistars_user.idPegawai = balistars_pegawai.idPegawai
                WHERE 
                    balistars_payment.idPengajuan = ? 
                    AND balistars_payment.jenisPengajuan = ?
                    AND balistars_payment.statusPayment = ?
            )
        ) data_progress
        ',
        [$idAdditional, 'Additional', 'Aktif', $idAdditional, 'Additional', 'Aktif']
    );

    $listTahapan = ['Kontrol Area', 'Pak Swi', 'Headoffice', 'Payment'];
?>
    <div class="row mt-2">
        <div class="col-md-12">
            <div class="timeline-steps ">
                <?php
                $isReject = false;
                $prevProgress = '';

                foreach ($listTahapan as $index => $tahapan) {

                    if ($isReject) {
                        $progress = 'dark';

                        $lineAfter = $progress;
                        $lineBefore = $progress;
                    } else {
                        if ($tahapan === 'Payment') {
                            $cekPayment = selectStatement(
                                $db,
                                'SELECT * FROM balistars_payment WHERE idPengajuan = ? AND jenisPengajuan = ? AND statusPayment = ?',
                                [$idAdditional, 'Additional', 'Aktif'],
                                'fetch'
                            );

                            if ($cekPayment) {
                                $progress = 'success';
                            } else {
                                $progress = 'secondary';
                            }

                            $lineAfter = $progress;
                            $lineBefore = $prevProgress;
                        } else {
                            $cekHasil = selectStatement(
                                $db,
                                'SELECT hasil FROM balistars_penyetujuan WHERE idPengajuan = ? AND jenisPengajuan = ? AND attempt = ? AND statusPenyetujuan = ? AND tahapan = ? ORDER BY idPenyetujuan DESC LIMIT 1',
                                [$idAdditional, 'Additional', $dataUpdate['attempt'], 'Aktif', $tahapan],
                                'fetch'
                            )['hasil'];

                            if ($cekHasil) {
                                if ($cekHasil === 'Disetujui') {
                                    $progress = 'success';

                                    $lineAfter = $progress;
                                    $lineBefore = $prevProgress;
                                } else if ($cekHasil === 'Reject') {
                                    $progress = 'danger';
                                    $isReject = true;

                                    $lineAfter = 'dark';
                                    $lineBefore = $prevProgress;
                                }
                            } else {
                                $progress = 'secondary';

                                $lineAfter = $progress;
                                $lineBefore = $prevProgress;
                            }
                        }
                    }


                    if ($index === 0) {
                        $timelineStyle = "--timeline-dot:var(--{$progress}); --timeline-line-after:var(--{$lineAfter})";
                    } else if ($index === count($listTahapan) - 1) {
                        $timelineStyle = "--timeline-dot:var(--{$progress}); --timeline-line-before:var(--{$lineBefore})";
                    } else {
                        $timelineStyle = "--timeline-dot:var(--{$progress}); --timeline-line-before:var(--{$lineBefore}); --timeline-line-after:var(--{$lineAfter})";
                    }

                    $prevProgress = $progress;
                ?>
                    <div class="timeline-step" style="<?= $timelineStyle ?>">
                        <div class="timeline-content" data-original-title="<?= $tahapan ?>">
                            <div class="inner-circle"></div>
                            <p class="h6 mt-3 mb-1 text-uppercase"><?= $tahapan; ?></p>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">

            <?php

            $tahapan = $dataUpdate['tahapan'];

            switch ($tahapan) {
                case 'Kontrol Area':
                case 'Pak Swi':
                case 'Headoffice':
            ?>
                    <div class="alert alert-secondary" role="alert">
                        <i class="fas fa-info-circle pr-4"></i><strong>PENGEMBALIAN MEMERLUKAN PERSETUJUAN : "<?= $tahapan; ?>"</strong>
                    </div>
                <?php
                    break;
                case 'Payment':
                ?>
                    <div class="alert alert-secondary" role="alert">
                        <i class="fas fa-info-circle pr-4"></i><strong>PENGEMBALIAN TELAH MENCAPAI TAHAP : "<?= $tahapan; ?>"</strong>
                    </div>
                <?php
                    break;
                case 'Final':

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
                ?>
                    <div class="alert alert-<?= $status ?>" role="alert">
                        <i class="fas fa-info-circle pr-4"></i><strong>PENGEMBALIAN SUDAH FINAL, AVERAGE POIN = <?= $average; ?></strong>
                    </div>
                <?php
                    break;

                case 'Reject':
                ?>
                    <div class="alert alert-secondary" role="alert">
                        <i class="fas fa-info-circle pr-4"></i><strong class="text-uppercase">PENGEMBALIAN TELAH DI <?= $tahapan; ?></strong>
                    </div>
            <?php
                    break;

                default:
                    break;
            }
            ?>
        </div>
    </div>
    <?php


    $listWarna = [
        'Kontrol Area' => 'red',
        'Pak Swi' => 'blue',
        'Headoffice' => 'warning',
        'Payment' => 'green',
    ];

    foreach ($dataFeedback as $index => $value) {
        [$date, $time] = explode(' ', getTimestamp('LOCALE', $value['timeStamp'])->format('Y-m-d H:i:s'));
        $strDate = ubahTanggalIndo($date);

        $poin = poinPengajuan($value['tahapan'], timeInMinutes($value['lamaWaktu']));
    ?>
        <div class="timeline-item <?= $listWarna[$value['tahapan']] ?>" date-is="<?= $strDate ?> <?= $time ?> - (<?= timeInMinutes($value['lamaWaktu']) ?> min)">
            <h5 class="text-uppercase">
                <?php
                if ($value['tahapan'] === 'Payment') {
                ?>
                    <strong>PROSES PAYMENT TELAH DILAKUKAN PER TANGGAL <?= ubahTanggalIndo($value['tambahan']); ?></strong>
                    <i class="fas fa-money-bill-wave text-success pr-3"></i>
                    <?php
                } else {
                    if ($value['hasil'] === 'Disetujui') {
                    ?>
                        <strong>PENGAJUAN TELAH DISETUJUI OLEH <?= $value['tahapan']; ?></strong>
                        <i class="fas fa-check-circle text-success pr-3"></i>
                    <?php
                    } else if ($value['hasil'] === 'Reject') {
                    ?>
                        <strong>PENGAJUAN TELAH DI REJECT OLEH <?= $value['tahapan']; ?></strong>
                        <i class="fas fa-times-circle text-danger pr-3"></i>
                <?php
                    }
                }
                ?>
            </h5>
            <span><i class="fas fa-user-circle pr-2"></i> <?= $value['namaPegawai']; ?> <a href="javascript:void(0);">@<?= $value['userName']; ?></a></span>
            <div class="msg">
                <p>
                    <?php
                    if ($value['keterangan'] === '') {
                        echo '<i style="opacity:.75">( Tidak Ada Pesan Yang Disampaikan )</i>';
                    } else {
                        echo $value['keterangan'];
                    }
                    ?>
                </p>
                <p>
                    <?php if (in_array($value['tahapan'], ['Kontrol Area', 'Headoffice', 'Payment'])) {
                    ?>
                        <strong>POIN :</strong>
                        <span class="badge badge-info"><?= $poin; ?></span>
                    <?php } ?>
                </p>
            </div>
        </div>
<?php
    }
}
