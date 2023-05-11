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
  'laporan_daily_mesin'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggalAwal=$tahun.'-'.$bulan.'-01';
$day=cal_days_in_month(CAL_GREGORIAN,$bulan,$tahun);
$tanggalAkhir=$tahun.'-'.$bulan.'-'.$day;
$tanggalHariIni=date('Y-m-d');
$tanggalPecah=explode('-', $tanggalHariIni);
$tanggalPecahHariIni=explode('-', $tanggalHariIni);


if($tanggalAkhir<=$tanggalHariIni || $tanggalAkhir>$tanggalPecah[0].'-'.$tanggalPecah[1].'-31' ){
  $tanggalPecah=explode('-', $tanggalAkhir);
}


$sqlBanyakCabang=$db->prepare('SELECT count(idCabang) as banyak 
  FROM balistars_cabang 
  where statusCabang=?');
$sqlBanyakCabang->execute(['Aktif']);
$dataBanyakCabang=$sqlBanyakCabang->fetch();
$banyakCabang=$dataBanyakCabang['banyak']-1;

$jenisPenjualan1=$jenisPenjualan;
$jenisPenjualan=strtolower($jenisPenjualan);

$sqlCabang=$db->prepare('SELECT * 
  FROM balistars_cabang 
  where statusCabang=? 
  and namaCabang not like ? 
  order by idCabang');
$sqlCabang->execute([
  'Aktif',
  '%head office%']);
$dataCabang=$sqlCabang->fetchAll();

$jenisPenjualanCapital=ucwords($jenisPenjualan);
$n=0;

?>
<table class="table table-bordered">
    <thead class="bg-info text-white">
      <tr>
        <th rowspan="2">Tanggal</th>
        <th colspan="<?=$banyakCabang?>" style="text-align: center;">Laporan Daily <?=$jenisPenjualanCapital?> Branch</th>
        <th rowspan="2">Total</th>
      </tr>
      <tr>
      <?php
      foreach ($dataCabang as $row) {
        $n++;
        ?>
          <td><?=$row['namaCabang']?></td>
        <?php
      }
      ?>
      </tr>
    </thead>
    <tbody>
      <?php 
      for ($i=1; $i <= $tanggalPecah[2]; $i++) { 
        if($i<10){
          $d='0'.$i;
        }
        else{
          $d=$i;
        }
        ?>
        <tr>
          <td><?=$d?></td>
      <?php
      $total=0;
      foreach ($dataCabang as $row) {
        ?>
          <td>
            <?php 
            if($jenisPenjualan=='indoor' || $jenisPenjualan=='outdoor' || $jenisPenjualan=='uv'){
              $sqlDaily=$db->prepare('SELECT SUM(luas) as total FROM balistars_performa_mesin_'.$jenisPenjualan.' 
                where tanggalPerforma=? 
                and idCabang=? 
                and statusPerforma'.$jenisPenjualan1.'=?');
              $sqlDaily->execute([
                $tanggalPecah[0].'-'.$tanggalPecah[1].'-'.$d, 
                $row['idCabang'],
                'Aktif']);
              $dataDaily=$sqlDaily->fetch();

              $daily=$dataDaily['total'];

              $sqlTambahan=$db->prepare('SELECT SUM(luas) as totalLuas 
                FROM balistars_performa_mesin_input 
                where idCabang=? 
                and tanggalPerforma=? 
                and jenisOrder=? 
                and statusMesinInput=?');
              $sqlTambahan->execute([
                $row['idCabang'], 
                $tanggalPecah[0].'-'.$tanggalPecah[1].'-'.$d,
                $jenisPenjualan1,
                'Aktif']);
              $dataTambahan=$sqlTambahan->fetch();
              $daily=$daily+$dataTambahan['totalLuas'];
            }
            elseif($jenisPenjualan=='laser'){
              $tanggalJalan=$tanggalPecah[0].'-'.$tanggalPecah[1].'-'.$d;
              $sqlDaily=$db->prepare('SELECT klikBefore, jumlahKlik as daily FROM balistars_performa_mesin_laser 
                where tanggalPerforma = ? 
                and idCabang=? 
                and statusKlik=? 
                order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
              $sqlDaily->execute([
                $tanggalJalan, 
                $row['idCabang'], 
                'Aktif']);
              $dataDailyAfter=$sqlDaily->fetch();
              $daily=$dataDailyAfter['daily']?? 0;
            }   
            else{
              $tanggalJalan=$tanggalPecah[0].'-'.$tanggalPecah[1].'-'.$d;
              $sqlDailyAfter=$db->prepare('SELECT klikBefore 
                FROM balistars_performa_mesin_'.$jenisPenjualan.' 
                where tanggalPerforma=? 
                and idCabang=? 
                order by tanggalPerforma DESC, idPerforma'.$jenisPenjualan1.' DESC limit 1');
              $sqlDailyAfter->execute([
                $tanggalJalan, 
                $row['idCabang']]);
              $dataDailyAfter=$sqlDailyAfter->fetch();
 
              $sqlDailyBefore=$db->prepare('SELECT klikBefore 
                FROM balistars_performa_mesin_'.$jenisPenjualan.' 
                where tanggalPerforma<? 
                and idCabang=? 
                order by tanggalPerforma DESC, idPerforma'.$jenisPenjualan1.' DESC limit 1');
              $sqlDailyBefore->execute([
                $tanggalJalan, 
                $row['idCabang']]);
              $dataDailyBefore=$sqlDailyBefore->fetch();

              if($dataDailyAfter['klikBefore']>0){
                if($jenisPenjualan!='laser'){
                  $daily=$dataDailyAfter['klikBefore']-$dataDailyBefore['klikBefore'];  
                }
              }
              else{
                $daily=0;
              }
              
            }
            echo $daily;
            $total=$total+$daily;
            ?>
          </td>
        <?php
      }
      ?>
      <td><?=($total)?></td>
      </tr>
      <?php
    }
    ?>
    </tbody>
  </table>
