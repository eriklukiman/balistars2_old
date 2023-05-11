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

if($tahun2<$tahun1){
  echo"Rentang yang anda pilih salah!!";
}
else if($tahun2==$tahun1 && $bulan2<$bulan1){
  echo"Rentang yang anda pilih salah!!";
}
else{
  $rentang = (intval($bulan2) - intval($bulan1))+($tahun2-$tahun1)*12; 
  // if($rentang<=0 && $tahun2!=$tahun1){
  //   $rentang = $rentang + ($tahun2-$tahun1)*12;
  // }

  $indexBulan = intval($bulan1);
  $namaBulan = array("","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
  $tanggalAwal = array();
  $tanggalAkhir = array();
  $tahunPeriode = array();

  for($i=0; $i<=$rentang; $i++){
    if($indexBulan>12){
      $indexBulan=1;
      $tahun1++;
    }
    $tahunPeriode[$i] = $tahun1;
    $bulanPilih[$i] = str_pad($i, 2, '0', STR_PAD_LEFT);
    $bulanPilihNama[$i] = $namaBulan[$indexBulan];
    $indexBulan=$indexBulan+1;
  }

  for($i=0; $i<count($bulanPilih); $i++){
    $tanggalAwal[$i] = $tahunPeriode[$i]."-".$bulanPilih[$i]."-01";
    $tanggalAkhir[$i] = $tahunPeriode[$i]."-".$bulanPilih[$i]."-31";
  }
  if($tipePenjualan=="A1" || $tipePenjualan=="A2"){
    $sqlPiutang=$db->prepare('SELECT (MIN(sisaPiutang)) as piutang FROM balistars_piutang inner join balistars_penjualan on balistars_piutang.noNota=balistars_penjualan.noNota where balistars_penjualan.idCabang=?  and (balistars_penjualan.tanggalPenjualan between ? and ?) and balistars_penjualan.tipePenjualan=? group by balistars_piutang.noNota');
  }
  else{
    $sqlPiutang=$db->prepare('SELECT MIN(sisaPiutang) as piutang FROM balistars_piutang inner join balistars_penjualan on balistars_piutang.noNota=balistars_penjualan.noNota where balistars_penjualan.idCabang=?  and (balistars_penjualan.tanggalPenjualan between ? and ?) group by balistars_piutang.noNota');
  }
  ?>
  <table class="table table-bordered " style="width: 100%">
    <thead class="bg-info text-white">
      <tr>
        <th style="width: 5%;">No</th>
        <th style="width: 15%;">Cabang</th>
        <?php
        for($i=0; $i<count($bulanPilihNama); $i++){
          ?>
          <th><?=$bulanPilihNama[$i]?> (Rp)</th>
          <?php
        }
        ?>
        <th>Total Piutang</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $n=1;
      $totalPiutangGlobal=0;
      $dataCabang=$db->query('SELECT * FROM balistars_cabang order by namaCabang');
      foreach($dataCabang as $row){
        ?>
        <tr>
          <td><?=$n?></td>
          <td><?=$row['namaCabang']?></td>
          <?php
          $totalPiutang=0;
          for($i=0; $i<count($bulanPilihNama); $i++){
            if($tipePenjualan=="A1" || $tipePenjualan=="A2"){
              $sqlPiutang->execute([$row['idCabang'], $tanggalAwal[$i], $tanggalAkhir[$i],$tipePenjualan]);
            }
            else{
              $sqlPiutang->execute([$row['idCabang'], $tanggalAwal[$i], $tanggalAkhir[$i]]);
            }
            $dataPiutang=$sqlPiutang->fetchAll();
            $piutang=0;
            foreach ($dataPiutang as $cek) {
              $piutang=$piutang+$cek['piutang'];
            }
            ?>
            <td><?=ubahToRp($piutang)?></td>
            <?php
            $totalPiutang+=$piutang;
          }
          ?>
          <td><?=ubahToRp($totalPiutang)?></td>
        </tr>
        <?php
        $n++;
        $totalPiutangGlobal+=$totalPiutang;
      }
      ?>
      <tr>
        <td></td>
        <td colspan=<?=($rentang+2)?>>Total Piutang Global</td>
        <td><?=ubahToRp($totalPiutangGlobal)?></td>
      </tr>
    </tbody>
  </table>
  <?php
}
?>