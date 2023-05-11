<?php
include_once '../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
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

//MENGECEK APAKAH USER INI BERHAK MENGAKSES MENU INI
$sqlCekMenu = $db->prepare('SELECT * from balistars_user_detail 
  inner join balistars_menu_sub 
  on balistars_menu_sub.idMenuSub = balistars_user_detail.idMenuSub
  where balistars_user_detail.idUser = ?
  and namaFolder = ?');
$sqlCekMenu->execute([
  $idUserAsli,
  'laporan_pembelian'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

foreach (['A1', 'A2'] as $tipePembayaran) {

?>
    <p> <strong>========== TAMBAH PELUNASAN GIRO "<?= $tipePembayaran; ?>" ==========</strong></p>
    <?php

    for ($tahun = 2018; $tahun < 2023; $tahun++) {

        for ($bulan = 1; $bulan <= 12; $bulan++) {

            $tanggalAwal = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01';
            $tanggalAkhir = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

            $sqlPelunasan = $db->prepare(
                'INSERT INTO 
                    balistars_dpgiro (idSupplier, tanggalCairDp, periode, dp, idBank, noGiro, tipePembelian, jenisGiro, idUser)
                SELECT
                    *
                FROM
                (
                    SELECT
                        balistars_pembelian.idSupplier,
                        balistars_hutang.tanggalCair,
                        ? as periode,
                        SUM(balistars_pembelian.grandTotal) - COALESCE(total_dpgiro.totalDP,0) as dp,
                        balistars_hutang.bankAsalTransfer,
                        balistars_hutang.noGiro,
                        balistars_pembelian.tipePembelian,
                        \'Pelunasan\' as jenisGiro,
                        -- ID USER \'teguhkeren\'
                        18 as idUser
                    FROM 
                        balistars_pembelian 
                        LEFT JOIN (
                            SELECT 
                                SUM(dp) as totalDP, 
                                idSupplier, 
                                periode, 
                                noGiro, 
                                tanggalCairDp, 
                                tipePembelian 
                            FROM 
                                balistars_dpgiro 
                            WHERE 
                                ( balistars_dpgiro.periode BETWEEN ? AND ?)
                                AND tipePembelian = ?
                                AND jenisGiro = ?
                                GROUP BY idSupplier
                        ) as total_dpgiro ON balistars_pembelian.idSupplier = total_dpgiro.idSupplier
                        LEFT JOIN balistars_supplier ON balistars_pembelian.idSupplier = balistars_supplier.idSupplier
                        LEFT JOIN balistars_hutang ON balistars_pembelian.noNota = balistars_hutang.noNota
                    WHERE 
                        (balistars_pembelian.tanggalPembelian BETWEEN ? AND ?)
                        AND balistars_pembelian.tipePembelian = ?
                        AND balistars_pembelian.statusPembelian = ?
                        AND balistars_pembelian.idSupplier != ?
                        GROUP BY balistars_pembelian.idSupplier
                ) as total_data
                WHERE
                    noGiro IS NOT NULL
        '
            );
            // var_dump($sqlPelunasan->fetchAll());
            $status = $sqlPelunasan->execute([
                $tanggalAwal,
                $tanggalAwal, $tanggalAkhir,
                $tipePembayaran,
                'DP',
                $tanggalAwal, $tanggalAkhir,
                $tipePembayaran,
                'Lunas',
                0
            ]);

            if ($status == true) {
    ?>
                <p><strong>==> PERIODE <?= $tanggalAwal; ?> s/d <?= $tanggalAkhir; ?> : BERHASIL</strong></p>
            <?php
            } else {
            ?>
                <p><strong>==> PERIODE <?= $tanggalAwal; ?> s/d <?= $tanggalAkhir; ?> : GAGAL</strong></p>
                <div>
                    <code style="color:red">
                        <?= var_dump($sqlPelunasan->errorInfo()); ?>
                    </code>
                </div>
    <?php
            }
        }
    }

    ?>
    <br>
<?php
}

