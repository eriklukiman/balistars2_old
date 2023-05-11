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
  'laporan_penjualan'
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


if($tipe=='Semua'){
  $parameter1 = ' AND tipePenjualan != ?';
} else{
  $parameter1 = ' AND tipePenjualan = ?';
}

if($idCabang=='0'){
  $parameter2 = ' AND balistars_penjualan.idCabang != ?';
} else{
  $parameter2 = ' AND balistars_penjualan.idCabang = ?';
}

$sql=$db->prepare('SELECT *, balistars_penjualan.timeStamp as waktuPenjualan 
  FROM balistars_penjualan 
  inner join balistars_cabang 
  on balistars_penjualan.idCabang=balistars_cabang.idCabang 
  where (balistars_penjualan.tanggalPenjualan between ? and ?)
  and balistars_penjualan.statusPenjualan=?'
  .$parameter1 
  .$parameter2 
  .'order by balistars_penjualan.tanggalPenjualan');
$sql->execute([
  $tanggalAwal,$tanggalAkhir,
  'Aktif',
  $tipe,
  $idCabang]);
$hasil = $sql->fetchAll();

$n = 1;
$totalPenjualan=0;
 $totalPPN=0;

foreach($hasil as $row){
  $totalPenjualan+=$row['grandTotal']-$row['nilaiPPN'];
  $totalPPN+=$row['nilaiPPN'];
  if($row['idCustomer']>0){
    $konsumen ='pelanggan';
  } else{
    $konsumen ='umum';
  }
  //$time=explode(' ',$row['waktuPenjualan']);
  ?>
  <tr>
    <td style="vertical-align: top;"><?=$n?></td>
    <td style="vertical-align: top;">
      <?php 
      if($dataCekMenu['tipeEdit']=='1'){
        if($row['statusFinalNota']!='final'){
       ?>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditBiaya" 
              style              = "color: white;"
              onclick = "editPenjualan('<?=$row['noNota']?>','<?=$row['tipePenjualan']?>','<?=$konsumen?>')">
        <i class="fa fa-edit"></i>
      </button>
      <?php 
        }
      }
      if($dataCekMenu['tipeDelete']=='1'){
       ?>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-success" 
              style="color: white;"
              onclick = "printNota('<?=$row['noNota']?>')" >
        <i class="fa fa-print"></i>
      </button>
      <?php 
      }
       ?>
    </td>
    <td><?=wordwrap(ubahTanggalIndo($row['tanggalPenjualan']),50,'<br>')?></td>
    <td><?=wordwrap($row['waktuPenjualan'],50,'<br>')?></td>
    <td><?=wordwrap($row['noNota'],50,'<br>')?></td>
    <td><?=wordwrap($row['noFakturPajak'],50,'<br>')?></td>
    <td><?=wordwrap($row['namaCabang'],50,'<br>')?></td>
    <td><?=wordwrap($row['namaCustomer'],50,'<br>')?></td>
    <td><?=wordwrap(ubahToRp($row['grandTotal']-$row['nilaiPPN']),50,'<br>')?></td>
    <td><?=wordwrap(ubahToRp($totalPenjualan),50,'<br>')?></td>
    <td><?=wordwrap(ubahToRp($row['nilaiPPN']),50,'<br>')?></td>
    <td><?=wordwrap(ubahToRp($totalPPN),50,'<br>')?></td>
    <td><?=wordwrap(ubahToRp($row['grandTotal']),50,'<br>')?></td>
    <td><?=wordwrap(ubahToRp($totalPenjualan+$totalPPN),50,'<br>')?></td>
   <?php 
    if($dataCekMenu['tipeA2']==1){
      ?>
      <td><?=wordwrap($row['tipePenjualan'],50,'<br>')?></td>
    <?php
    } ?>
  </tr>
<?php
$n++;
}
?>