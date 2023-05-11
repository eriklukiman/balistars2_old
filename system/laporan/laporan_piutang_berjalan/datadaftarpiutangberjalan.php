<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';

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
  'laporan_piutang_berjalan'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggal = explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]); 
$tanggalAwalSekali = '2015-01-01';
$tanggalAkhirSekali = waktuKemarin($tanggalAwal);

if($idCabang==0){
  $parameter1 =' and balistars_penjualan.idCabang !=?';
} else{
  $parameter1 =' and balistars_penjualan.idCabang =?';
}
if($tipe=='Semua'){
  $parameter2 =' and balistars_penjualan.tipePenjualan !=?';
} else{
  $parameter2 =' and balistars_penjualan.tipePenjualan =?';
}

$sqlPiutang=$db->prepare('
  SELECT *, dataAkumulasi.idCustomer as idCustomer 
  FROM balistars_customer 
  RIGHT JOIN
  (SELECT SUM(penjualan-pembayaran) as piutangAwal, SUM(penjualanAwal) as pjl, idCustomer 
    FROM (
      (
        SELECT SUM(grandTotal) as penjualan, 0 as pembayaran, 0 as penjualanAwal, idCustomer 
        FROM balistars_penjualan 
        WHERE (tanggalPenjualan BETWEEN ? AND ?) 
        AND statusFinalNota=?
        AND statusPenjualan=?'
        .$parameter1
        .$parameter2
        .'GROUP BY idCustomer
      )
      UNION
      (
        SELECT 0 as penjualan, SUM(jumlahPembayaran) as pembayaran, 0 as penjualanAwal, idCustomer 
        FROM balistars_penjualan 
        INNER JOIN balistars_piutang 
        ON balistars_penjualan.noNota=balistars_piutang.noNota 
        WHERE  (tanggalPembayaran BETWEEN ? AND ?) 
        AND statusFinalNota=?
        AND balistars_penjualan.statusPenjualan=?'
        .$parameter1
        .$parameter2
        .' GROUP BY idCustomer
      )
      UNION
      (
        SELECT 0 as penjualan, 0 as pembayaran, SUM(grandTotal) as penjualanAwal, idCustomer 
        FROM balistars_penjualan 
        WHERE (tanggalPenjualan BETWEEN ? AND ?) 
        AND statusFinalNota=?
        AND statusPenjualan=?'
        .$parameter1
        .$parameter2
        .'GROUP BY idCustomer
      )
    ) AS data2
    GROUP BY idCustomer
  ) AS dataAkumulasi
  ON balistars_customer.idCustomer=dataAkumulasi.idCustomer
  LEFT JOIN
  (SELECT SUM(penjualan) as penjualan, SUM(pembayaran) as pembayaran, idCustomer FROM (
      (
        SELECT SUM(grandTotal) as penjualan, 0 as pembayaran, idCustomer 
        FROM balistars_penjualan 
        WHERE (tanggalPenjualan BETWEEN ? AND ?) 
        AND statusFinalNota=?
        AND statusPenjualan=?'
        .$parameter1
        .$parameter2
        .'GROUP BY idCustomer
      )
      UNION
      (
        SELECT 0 as penjualan, SUM(jumlahPembayaran) as pembayaran, idCustomer 
        FROM balistars_penjualan 
        INNER JOIN balistars_piutang 
        ON balistars_penjualan.noNota=balistars_piutang.noNota 
        WHERE (tanggalPembayaran BETWEEN ? AND ?) 
        AND statusFinalNota=?
        AND balistars_penjualan.statusPenjualan=?'
        .$parameter1 
        .$parameter2 
        .' GROUP BY idCustomer
      )
    ) AS data1
    GROUP BY idCustomer
  ) AS dataMain
  ON dataAkumulasi.idCustomer=dataMain.idCustomer
');

$sqlPiutang->execute([
  $tanggalAwalSekali,$tanggalAkhirSekali,'final','Aktif',$idCabang,$tipe,
  $tanggalAwalSekali,$tanggalAkhirSekali,'final','Aktif',$idCabang,$tipe,
  $tanggalAwal,$tanggalAkhir,'final','Aktif', $idCabang, $tipe,
  $tanggalAwal,$tanggalAkhir,'final','Aktif', $idCabang, $tipe,
  $tanggalAwal,$tanggalAkhir,'final','Aktif', $idCabang, $tipe]);
$dataPiutang=$sqlPiutang->fetchAll();

$n=1;
$piutang = 0;
$grandTotalPiutang=0;
$grandTotalPenjualan=0;
$grandTotalPembayaran=0;
$grandTotalAwal=0;
foreach($dataPiutang as $row){
  $piutang=($row['piutangAwal']+$row['penjualan']-$row['pembayaran']);
  $grandTotalAwal+=$row['piutangAwal'];
  $grandTotalPiutang+=$piutang;
  $grandTotalPenjualan+=$row['penjualan'];
  $grandTotalPembayaran+=$row['pembayaran'];
  if($row['idCustomer']==0){
    $row['idCustomer']='';
  }
  if($row['namaCustomer']==''){
    $row['namaCustomer']='Umum '.$row['idCustomer'];
  }
 ?>

<tr>
  <td><?=$n?></td>
  <td><?=wordwrap($row['namaCustomer'],50,'<br>')?></td>
  <td>Rp <?=wordwrap(ubahToRp($row['piutangAwal']),50,'<br>')?></td>
  <td>Rp <?=wordwrap(ubahToRp($row['penjualan']),50,'<br>')?></td>
  <td>Rp <?=wordwrap(ubahToRp($row['pembayaran']),50,'<br>')?></td>
  <td>Rp <?=wordwrap(ubahToRp($piutang),50,'<br>')?></td>
</tr>
  <?php
  $n++;
 }
 ?>
<tr>
  <td colspan="2" style="text-align: right;"><b>Grand Total</b></td>
  <td><?=ubahToRp($grandTotalAwal)?></td>
  <td><?=ubahToRp($grandTotalPenjualan)?></td>
  <td><?=ubahToRp($grandTotalPembayaran)?></td>
  <td><?=ubahToRp($grandTotalPiutang)?></td>
</tr>
