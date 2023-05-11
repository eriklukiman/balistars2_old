<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once 'fungsikomparation.php';

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
  'laporan_kunjungan'
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

$tanggalAwalPecah=explode('-', $tanggalAwal);
$tanggalAkhirPecah=explode('-', $tanggalAkhir);

$bulan=$tanggalAwalPecah[1];
$tahun=$tanggalAwalPecah[0];

$tanggal1=$tanggalAwalPecah[2];
$tanggal2=$tanggalAkhirPecah[2];
?>

<table class="table table-bordered table-custom">
  <thead class="bg-info text-white">
    <th>Bulan/Cabang</th>
    <?php
    $sqlCabang=$db->prepare('SELECT * 
      FROM balistars_cabang 
      where statusCabang=? 
      and namaCabang not like ? 
      order by idCabang');
    $sqlCabang->execute([
      'Aktif',
      '%head office%']);
    $dataCabang=$sqlCabang->fetchAll();
    $i=0;
    foreach($dataCabang as $row){
      ?>
      <th><?=$row['namaCabang']?></th>
      <?php
    }
    ?>
  </thead> 
  <tbody>
    <?php  
      for ($i=1; $i <=$bulan ; $i++) {
        if($i<10){
          $bln="0".$i;
        }
        else{
          $bln=$i;
        }
        $tanggalAwal=$tahun."-".$bln."-".$tanggal1; 
        $tanggalAkhir=$tahun."-".$bln."-".$tanggal2;
        ?>
        <tr>
          <td><?=namaBulan($i)?></td>
          <?php  
            foreach($dataCabang as $row){
              $nilai=0;
              $nilai=fungsiNilai($tanggalAwal,$tanggalAkhir,$jenis,$row['idCabang'],$db);
              ?>
              <td><?=$nilai?></td>
              <?php
            }
          ?>  
        </tr>
        <?php
      }
    ?>
  </tbody>
</table>
 