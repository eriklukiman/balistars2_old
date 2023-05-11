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
  'laporan_biaya_card'
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
$grandTotal=0;
$grandTotalA1=0;

$execute = array("Aktif",1161,1313,1314,1316,1323,1324,1326,2112,3140,6110,6130,6141,6211,6212,6215,6216,6345,6346,6350,6351,6380,6391);
$parameter=' and kodeAkunting!=? ';
for ($i=1; $i < count($execute)-1 ; $i++) { 
  $parameter = $parameter.' and kodeAkunting!=?';
}

$sqlKode=$db->prepare('SELECT kodeAkunting, keterangan 
  from balistars_kode_akunting 
  where statusKodeAkunting=?'
  .$parameter
  .' order by kodeAkunting');
$sqlKode->execute($execute);
$dataKode=$sqlKode->fetchAll();

foreach($dataKode as $row){
?>
  <span style="font-weight: bold;">#<?=$row['kodeAkunting']?> - <?=$row['keterangan']?></span>
  <table class="table table-responsive">
    <thead>
      <th>Tanggal</th>
      <th>Jurnal</th>
      <th>Keterangan</th>
      <?php  
      if($dataCekMenu['tipeA2']=="1"){
      ?>
      <th>Nilai A1</th>
      <th>Nilai A2</th>
      <?php
      }
      else{
      ?>
      <th>Nilai</th>
      <?php
      }
      ?>
    </thead>
    <tbody>
      <?php
      $subTotalA1=0;
      $subTotalA2=0;
      if($idCabang=="0"){
        $sqlDetailBiaya=$db->prepare('SELECT tanggalBiaya, balistars_biaya.noNota, keterangan, nilai, tipeBiaya 
          from balistars_biaya 
          inner join balistars_biaya_detail 
          on balistars_biaya.noNota=balistars_biaya_detail.noNota 
          where (tanggalBiaya between ? and ?) 
          and balistars_biaya_detail.statusCancel=? 
          and kodeAkunting=? 
          and statusBiaya=?');
        $sqlDetailBiaya->execute([
          $tanggalAwal, $tanggalAkhir, 
          "oke", 
          $row['kodeAkunting'],
          "Aktif"]);
      }
      else{
      $sqlDetailBiaya=$db->prepare('SELECT tanggalBiaya, balistars_biaya.noNota, keterangan, nilai, tipeBiaya 
        from balistars_biaya 
        inner join balistars_biaya_detail 
        on balistars_biaya.noNota=balistars_biaya_detail.noNota 
        where (tanggalBiaya between ? and ?) 
        and idCabang=? 
        and balistars_biaya_detail.statusCancel=? 
        and kodeAkunting=? 
        and statusBiaya=?');
      $sqlDetailBiaya->execute([
        $tanggalAwal, $tanggalAkhir, 
        $idCabang, 
        "oke", 
        $row['kodeAkunting'],
        "Aktif"]);
      } 
      $dataDetail=$sqlDetailBiaya->fetchAll();
      foreach($dataDetail as $row){
        ?>
        <tr>
          <?php
          if($dataCekMenu['tipeA2']=="1"){
            ?>
          <td><?=ubahTanggalIndo($row['tanggalBiaya'])?></td>
          <td><?=$row['noNota']?></td>
          <td><?=wordwrap($row['keterangan'],50,'<br>')?></td>
          <?php
            if($row['tipeBiaya']=='A1'){
            ?>
              <td><?=ubahToRp($row['nilai'])?></td>
              <td>-</td>
              <?php
              $subTotalA1+=$row['nilai'];
            }
            else{
              ?>
              <td>-</td>
              <td><?=ubahToRp($row['nilai'])?></td>
              <?php
              $subTotalA2+=$row['nilai'];
            }
          }
          else {
            if($row['tipeBiaya']=='A1'){
            ?>
              <td><?=ubahTanggalIndo($row['tanggalBiaya'])?></td>
              <td><?=$row['noNota']?></td>
              <td><?=wordwrap($row['keterangan'],50,'<br>')?></td>
              <td><?=ubahToRp($row['nilai'])?></td>
              <?php
              $subTotalA1+=$row['nilai'];
            }
          }
          ?>
        </tr>
        <?php
      }
      ?>
      <tr>
        <td></td>
        <td></td>
        <td>Sub Total</td>
        <?php 
        if($dataCekMenu['tipeA2']=="1"){
        ?>
          <td><?=ubahToRp($subTotalA1)?></td>
          <td><?=ubahToRp($subTotalA2)?></td>
        <?php
        } else{
          ?>
          <td><?=ubahToRp($subTotalA1)?></td> 
          <?php
        } ?>
        
      </tr>
      <tr>
        <td></td>
        <td></td>
        <td>Total</td>
        <?php 
        if($dataCekMenu['tipeA2']=="1"){
        ?>
          <td><?=ubahToRp($subTotalA1+$subTotalA2)?></td>
        <?php
        } else{
          ?>
        <td><?=ubahToRp($subTotalA1)?></td>
          <?php
        } ?>
        <td></td>
      </tr>
    </tbody>
  </table>
  <br><br>
  <?php
  $grandTotal=$grandTotal+$subTotalA1+$subTotalA2;
  $grandTotalA1=$grandTotalA1+$subTotalA1;
}
?>
<table class="table table-responsive">
  <tr>
    <td colspan="2" style="width: 150px;"></td>
    <td>Grand Total</td>
    <?php
    if($dataCekMenu['tipeA2']=="1"){
        ?>
        <td><?=ubahToRp($grandTotal)?></td>
        <?php
        } else{
          ?>
        <td><?=ubahToRp($grandTotalA1)?></td>
          <?php
        } ?>
    
  </tr>
</table>