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
  'laporan_penyusutan_aktiva'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggalAwal=$tahun.'-01-01';
$tanggalAkhir=$tahun.'-'.$bulan.'-31';

$arrayAktiva = array('1313','1314','1316');
$arrayAktivaNama = array('Mesin dan perlengkapan','Kendaraan','Inventaris dan Perlengkapan');
$arrayPenyusutan =array();
$arrayBatch=array();
for ($i=0; $i < count($arrayAktiva) ; $i++) {
  $totalHargaSatuan=0;
  $totalPerolehan=0;
  $totalASebelum=0;
  $totalPenyusutan=0;
  $totalASesudah=0;
  $totalNilaiBuku=0;
?>
  <table class=" table table-bordered" style="vertical-align: middle;">
    <thead>
      <td colspan="13"><b>CCout : <?=$arrayAktiva[$i]?> (<?=$arrayAktivaNama[$i]?>)</b></td>
    </thead>
    <thead class="bg-info text-white">
      <th width="5%">No</th>
      <th>Kode Ak.</th>
      <th>Nama Aktiva Tetap</th>
      <th>Qty</th>
      <th>Unit</th>
      <th>Harga Satuan</th>
      <th>harga Perolehan</th>
      <th>Ak.Peny <?=$tahun-1?></th>
      <th>B.Penyusutan <?=$tahun?></th>
      <th>Ak.Peny <?=$tahun?></th>
      <th>Nilai Buku</th>
      <th>Tanggal Perolehan</th>
      <th>Aksi</th>
    </thead>
    <tbody>
      <?php 
      $n=0; 
      $sqlMesin=$db->prepare('SELECT * 
        FROM balistars_pembelian_mesin_detail 
        inner join balistars_pembelian_mesin 
        on balistars_pembelian_mesin_detail.noNota=balistars_pembelian_mesin.noNota 
        where tanggalPembelian<=? 
        and kodeAkunting=? 
        and statusCancel=? 
        order by tanggalPembelian DESC');
      $sqlMesin->execute([
        $tanggalAkhir,
        $arrayAktiva[$i],
        "oke"]);
      $dataMesin=$sqlMesin->fetchAll();
      foreach ($dataMesin as $row) {
        if($row['tanggalJual']!=Null){
          $tgl = date('d');
          $dateTanggalJual = new DateTime($row['tanggalJual']);
          $dateTanggalSekarang = new DateTime($tahun.'-'.$bulan.'-'.$tgl);

          if($dateTanggalJual<=$dateTanggalSekarang){
            $hargaSatuan=0;
            $nilai=0;
            $penyusutanASebelum=0;
            $penyusutan=0;
            $penyusutanASesudah=0;
            $nilaiBuku=0;
          }
          else{
            if($row['jenisPPN']=="Include"){
              $hargaSatuan=($row['hargaSatuan']*100/110);
            }
            else{
              $hargaSatuan=$row['hargaSatuan'];
            }
            $nilai=$row['qty']*$hargaSatuan;
            $tanggalPembelian=explode('-', $row['tanggalPembelian']);
            $penyusutan=$nilai/($row['lamaPenyusutan']*12);
            $penyusutanSatuan=$penyusutan;
            $tahunPembelain=$tanggalPembelian[0];
            $selisihTahun=$tahun-$tahunPembelain;

            if($selisihTahun>0){
              $selisihBulan=(12-$tanggalPembelian[1])+(($selisihTahun-1)*12)+1;
            }
            else{
              $selisihBulan=0;
            }
            if($selisihBulan>($row['lamaPenyusutan']*12)){
              $selisihBulan=$row['lamaPenyusutan']*12;
            }
            $penyusutanASebelum=$penyusutan*$selisihBulan;
            $tahunAkhir=$tahunPembelain+$row['lamaPenyusutan'];

            if($tahunAkhir<$tahun){
              $penyusutanSatuan=0;
            }
            if($tanggalPembelian[0]==$tahun){

             $penyusutan=$penyusutanSatuan*($bulan-$tanggalPembelian[1]+1);

            }
            elseif ($tahunAkhir==$tahun) {
             if($bulan<$tanggalPembelian[1]){
                $penyusutan=$penyusutanSatuan*$bulan;
              }
              else{
                $penyusutan=$penyusutanSatuan*($tanggalPembelian[1]-1);
              }

            }
            else{
              $penyusutan=$penyusutanSatuan*$bulan;
            }
            $penyusutanASesudah=$penyusutanASebelum+$penyusutan;
            $nilaiBuku=$nilai-$penyusutanASesudah;
            $n++;
          }
        }
        else{
          if($row['jenisPPN']=="Include"){
            $hargaSatuan=($row['hargaSatuan']*100/110);
          }
          else{
            $hargaSatuan=$row['hargaSatuan'];
          }
          $nilai=$row['qty']*$hargaSatuan;
          $tanggalPembelian=explode('-', $row['tanggalPembelian']);
          $penyusutan=$nilai/($row['lamaPenyusutan']*12);
          $penyusutanSatuan=$penyusutan;
          $tahunPembelain=$tanggalPembelian[0];
          $selisihTahun=$tahun-$tahunPembelain;

          if($selisihTahun>0){
            $selisihBulan=(12-$tanggalPembelian[1])+(($selisihTahun-1)*12)+1;
          }
          else{
            $selisihBulan=0;
          }
          if($selisihBulan>($row['lamaPenyusutan']*12)){
            $selisihBulan=$row['lamaPenyusutan']*12;
          }
          $penyusutanASebelum=$penyusutan*$selisihBulan;
          $tahunAkhir=$tahunPembelain+$row['lamaPenyusutan'];

          if($tahunAkhir<$tahun){
            $penyusutanSatuan=0;
          }
          if($tanggalPembelian[0]==$tahun){

           $penyusutan=$penyusutanSatuan*($bulan-$tanggalPembelian[1]+1);

          }
          elseif ($tahunAkhir==$tahun) {
           if($bulan<$tanggalPembelian[1]){
              $penyusutan=$penyusutanSatuan*$bulan;
            }
            else{
              $penyusutan=$penyusutanSatuan*($tanggalPembelian[1]-1);
            }

          }
          else{
            $penyusutan=$penyusutanSatuan*$bulan;
          }
          $penyusutanASesudah=$penyusutanASebelum+$penyusutan;
          $nilaiBuku=$nilai-$penyusutanASesudah;
          $n++;
        }
        ?>
        <tr>
          <td><?=$n?></td>
          <td><?=$row['noNota']?></td>
          <td><?=$row['namaBarang']?></td>
          <td><?=$row['qty']?></td>
          <td>Unit</td>
          <td><?=ubahToRp($hargaSatuan)?></td>
          <td><?=ubahToRp($nilai)?></td>
          <td><?=ubahToRp($penyusutanASebelum)?></td>
          <td><?=ubahToRp($penyusutan)?></td>
          <td><?=ubahToRp($penyusutanASesudah)?></td>
          <td><?=ubahToRp($nilaiBuku)?></td>
          <td><?=ubahTanggalIndo($row['tanggalPembelian'])?></td>
          <td>
            <?php 
            if($row['tanggalJual']==NULL){
             ?>
              <button 
                type="button" 
                class="btn btn-warning" 
                onclick="jualAktiva('<?=$row['idPembelianDetail']?>')"  
                style="color: white;">
                  <i class="fa fa-shopping-cart"></i>
              </button>
              <?php 
              }
               ?>
          </td>
        </tr>           
        <?php
        $totalHargaSatuan+=$hargaSatuan;
        $totalPerolehan+=$nilai;
        $totalASebelum+=$penyusutanASebelum;
        $totalPenyusutan+=$penyusutan;
        $totalASesudah+=$penyusutanASesudah;

      }
      $arrayBatch[0][]=$totalPenyusutan;
      $arrayBatch[1][]=$totalASebelum;
      $arrayBatch[2][]=$totalASesudah;
      $arrayBatch[3][]=$totalPerolehan;
      ?>
      <tr>
        <td colspan="5" style="text-align: right;"><b>Total</b></td>
        <td><?=ubahToRp($totalHargaSatuan)?></td>
        <td><?=ubahToRp($totalPerolehan)?></td>
        <td><?=ubahToRp($totalASebelum)?></td>
        <td><?=ubahToRp($totalPenyusutan)?></td>
        <td><?=ubahToRp($totalASesudah)?></td>
        <td><?=ubahToRp($totalNilaiBuku)?></td>
        <td colspan="2"></td>
      </tr> 
    </tbody>
  </table> 

<?php
}
$namaBiaya = array('Biaya Penyusutan', 'Penyusutan Akumulasi '.($tahun-1), 'Penyusutan Akumulasi '.$tahun, 'Biaya Perolehan' );
?>
<table class="table table-bordered" style="width: 50%;">
  <?php  
  for ($k=0; $k < count($arrayBatch) ; $k++) { 
  ?>
    <tr>
      <td colspan="2"><?=$namaBiaya[$k]?></td>
    </tr>
    <?php  
    for ($i=0; $i < count($arrayAktivaNama) ; $i++) { 
      ?>
      <tr>
        <td><?=$arrayAktivaNama[$i]?></td>
        <td style="text-align: right;">Rp <?=ubahToRp($arrayBatch[$k][$i])?></td>
      </tr>
      <?php
    }
    ?>
    <tr>
      <td></td>
      <td style="border-top-width: 5px; text-align: right;">Rp <?=ubahToRp(array_sum($arrayBatch[$k]))?></td>
    </tr>
  <?php
  }
  ?>
</table> 