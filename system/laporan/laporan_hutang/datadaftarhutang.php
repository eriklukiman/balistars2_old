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
  'laporan_hutang'
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

if($tipe=='Semua'){
  $parameter1 = ' AND tipePembelian != ?';
} else{
  $parameter1 = ' AND tipePembelian = ?';
}

$sqlHutang=$db->prepare('SELECT *, dataMain.idSupplier as idSupplier FROM
  (
    (SELECT SUM(pembayaran) as pembayaran, SUM(pembelian) as pembelian, namaSupplier, balistars_supplier.idSupplier 
      FROM balistars_supplier
      LEFT JOIN
      (
        (
          SELECT SUM(jumlahPembayaran) as pembayaran, 0 as pembelian , idSupplier
          FROM balistars_hutang 
          INNER JOIN balistars_pembelian 
          ON balistars_hutang.noNota=balistars_pembelian.noNota 
          WHERE (tanggalCair BETWEEN ? AND ?) 
          and statusHutang = ?'
          .$parameter1 
          .' GROUP BY idSupplier
        )
        UNION
        (
          SELECT  0 as pembayaran, SUM(grandTotal) as pembelian, idSupplier  
          FROM balistars_pembelian 
          WHERE (tanggalPembelian BETWEEN ? AND ?) 
          and status =?'
          .$parameter1 
          .'GROUP BY idSupplier
        )
      ) as dataHutang
      on dataHutang.idSupplier=balistars_supplier.idSupplier
      where balistars_supplier.statusSupplier=?
      GROUP BY balistars_supplier.idSupplier
    ) as dataMain
    LEFT JOIN
    (
      SELECT SUM(hutang) as hutangAwal, idSupplier FROM
        (
          (
            SELECT (0-SUM(jumlahPembayaran)) as hutang,  idSupplier 
            FROM balistars_hutang 
            INNER JOIN balistars_pembelian 
            ON balistars_hutang.noNota=balistars_pembelian.noNota 
            WHERE (tanggalCair BETWEEN ? AND ?) 
            and statusHutang=?'
            .$parameter1 
            .'GROUP BY idSupplier
          )
          UNION
          (
            SELECT SUM(grandTotal) as hutang, idSupplier  
            FROM balistars_pembelian 
            WHERE (tanggalPembelian BETWEEN ? AND ?) 
            and status=?'
            .$parameter1 
            .' GROUP BY idSupplier
          )
        ) as data
      GROUP BY idSupplier
    ) as dataAkumulasi
    ON dataMain.idSupplier=dataAkumulasi.idSupplier
  )
');
$sqlHutang->execute([
  $tanggalAwal,$tanggalAkhir,'Aktif',$tipe,
  $tanggalAwal,$tanggalAkhir,'Aktif',$tipe,
  'Aktif',
  $tanggalAwalSekali,$tanggalAkhirSekali,'Aktif',$tipe,
  $tanggalAwalSekali,$tanggalAkhirSekali,'Aktif',$tipe]);

$dataHutang=$sqlHutang->fetchAll();
//var_dump($sqlHutang->errorInfo());
$n=1;
$grandTotalDP=0;
$grandTotalHutang=0;
$grandTotalHutangAwal=0;
$grandTotalPembelian=0;
foreach($dataHutang as $row){
 ?>
 <tr>
   <td><?=$n?></td>
   <td><?=wordwrap($row['namaSupplier'],50,'<br>')?></td>
   <td><?=wordwrap(ubahToRp($row['hutangAwal']),50,'<br>')?></td>
   <td><?=wordwrap(ubahToRp($row['pembelian']),50,'<br>')?></td>
   <td><?=wordwrap(ubahToRp($row['pembayaran']),50,'<br>')?></td>
   <td><?=wordwrap(ubahToRp($row['hutangAwal']+$row['pembelian']-$row['pembayaran']),50,'<br>')?></td>
 </tr>
 <?php 
  $n++;
  $grandTotalDP=$grandTotalDP+$row['pembayaran']; 
  $grandTotalPembelian=$grandTotalPembelian+$row['pembelian'];
  $grandTotalHutang=$grandTotalHutang+$row['hutangAwal']+$row['pembelian']-$row['pembayaran'];
  $grandTotalHutangAwal += $row['hutangAwal'];
  }
  ?>
<tr>
  <td colspan="1"></td>
  <td><b>Grand Total</b></td>
  <td><?=ubahToRp($grandTotalHutangAwal)?></td>
  <td><?=ubahToRp($grandTotalPembelian)?></td>
  <td><?=ubahToRp($grandTotalDP)?></td>
  <td><?=ubahToRp($grandTotalHutang)?></td>
  <td></td>
</tr>