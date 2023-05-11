<?php

include_once '../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';

function getExecutedTime(Closure $fun)
{
    $start = microtime(true);
    $fun();
    $end = microtime(true);

    $time = $end - $start;

    return 'Waktu eksekusi : ' . ($time / 1000) . ' milidetik';
}

$timeUpdateHariLibur = getExecutedTime(function () use ($db) {

    $sqlProduktivitas = $db->prepare('SELECT * FROM balistars_produktivity WHERE statusProduktivity = ?');
    $sqlProduktivitas->execute(['Aktif']);
    $dataProduktivitas = $sqlProduktivitas->fetchAll();

    foreach ($dataProduktivitas as $index => $value) {

        if ($value['hariLibur'] !== '') {

            echo '<p><strong> ===== ID PRODUKTIVITY : ' . $value['idProduktivity'] . ' (' . ubahTanggalIndo($value['tanggalProduktivity']) . ') ===== </strong></p> ';
            echo '<p><span> => ID CABANG    : ' . $value['idCabang'] . '</span></p>';
            echo '<p><span> => DAFTAR LIBUR : "' . $value['hariLibur'] . '"</span></p>';

            $hariLibur = join(',', array_map(function ($tgl) {
                return "'{$tgl}'";
            }, explode(',', $value['hariLibur'])));

            $sqlUpdate = $db->prepare(
                'UPDATE 
                    balistars_absensi 
                SET
                    jenisPoin = ?
                WHERE
                    idCabang = ?
                    AND tanggalDatang IN (' . $hariLibur . ')'
            );

            $status = $sqlUpdate->execute(['Hari Libur', $value['idCabang']]);
            $rowCount = $sqlUpdate->rowCount();

            if ($status == true) {
                echo '<p><strong> < &check; > BERHASIL. ' . $rowCount . ' ABSENSI SUDAH TER UPDATE</strong></p>';
            } else {
                var_dump($sqlUpdate->errorInfo());
                echo '<p><strong> < &times; > GAGAL</strong></p>';
            }

            echo '======================================================================= <br>';
        }
    }
});

echo '<code>' . $timeUpdateHariLibur . '</code><br>';
echo '======================================================================= <br><br><br>';


$timeUpdateHariKerja = getExecutedTime(function () use ($db) {

    echo '======================================================================= <br>';
    echo '<p><strong> ====== UPDATE ABSENSI UNTUK JENIS POIN HARI KERJA ===== </strong></p> ';
    $sqlHariKerja = $db->prepare(
        'UPDATE
            balistars_absensi
        SET
            jenisPoin = ?
        WHERE
            jenisPoin = ?'
    );

    $status = $sqlHariKerja->execute(['Hari Kerja', '']);
    $rowCount = $sqlHariKerja->rowCount();

    if ($status == true) {
        echo '<p><span> => STATUS  ( &check; )  : BERHASIL. ' . $rowCount . ' ABSENSI TELAH TER UPDATE</span></p>';
    } else {
        var_dump($sqlHariKerja->errorInfo());
        echo '<p><span> => STATUS  ( &times; )  : GAGAL. </span></p>';
    }
});

echo '======================================================================= <br>';
echo '<code>' . $timeUpdateHariKerja . '</code><br>';
echo '======================================================================= <br><br><br>';
