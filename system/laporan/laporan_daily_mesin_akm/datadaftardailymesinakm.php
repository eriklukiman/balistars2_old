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
  'laporan_daily_mesin_akm'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$tanggalAkhir=date('Y-m-d'); 
$bulanAkhir=date('m');
$tahunAkhir=date('Y');
$tanggalAkhir=date('d');

extract($_REQUEST);

function tanggalJalanAwal($bulan,$tahun)
{
  if($bulan<10){
    $bulan="0".$bulan;
  }
  return $tahun."-".$bulan."-01";
}
function tanggalJalanAkhir($bulan,$tahun)
{
  if($bulan<10){
    $bulan="0".$bulan;
  }
  return $tahun."-".$bulan."-31";
}


$sqlBanyakCabang=$db->prepare('SELECT count(idCabang) as banyak 
  FROM balistars_cabang 
  where statusCabang=?');
$sqlBanyakCabang->execute(['Aktif']);
$dataBanyakCabang=$sqlBanyakCabang->fetch();
$banyakCabang=$dataBanyakCabang['banyak']-1;

$jenisPenjualan1=$jenisPenjualan;
$jenisPenjualan=strtolower($jenisPenjualan);

if($jenisPenjualan=='laser' || $jenisPenjualan=='bw'){
  $parameter="jumlahKlik";
}
else{
  $parameter="luas";
}

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
$total=0;

?>
<table class="table table-bordered">
    <thead class="bg-info text-white">
      <tr>
      <th style="font-size: 20px; text-align: center;" colspan="<?=count($dataCabang)+2?>">Summary Daily Report <?=$jenisPenjualan?> Bulan <?=namaBulan($bulanAkhir)?></th>
      </tr>
      <tr>
        <th rowspan="2">Bulan/Tanggal</th>
        <th colspan="<?=count($dataCabang)?>">Cabang</th>
        <th rowspan="2">Total</th>
      </tr>
      <tr>
      <?php
      $jenisPenjualanCapital=ucwords($jenisPenjualan);
      foreach ($dataCabang as $row) {
        $n++;
        ?>
        <th><?=$row['namaCabang']?></th>
        <?php
      }
      ?>
      </tr>
    </thead>
    <tbody>
      <?php  
    for ($i=1; $i <= $bulanAkhir ; $i++) { 
      $total=0;
      $tanggalAwalJalan=tanggalJalanAwal($i,$tahunAkhir);
      $tanggalAkhirJalan=tanggalJalanAkhir($i,$tahunAkhir);
      ?>
      <tr>
        <td><?=namaBulan($i)?></td>
        <?php
        if($i<$bulanAkhir){
          foreach ($dataCabang as $row) {
            if($jenisPenjualan=='indoor' || $jenisPenjualan=='outdoor' || $jenisPenjualan=='uv'){
              $sqlDaily=$db->prepare('SELECT SUM(luas) as total 
                FROM balistars_performa_mesin_'.$jenisPenjualan.' 
                where (tanggalPerforma between ? and ?) 
                and idCabang=? 
                and statusPerforma'.$jenisPenjualan1.'=?');
              $sqlDaily->execute([
                $tanggalAwalJalan,$tanggalAkhirJalan,
                $row['idCabang'],
                'Aktif']);
              $dataDaily=$sqlDaily->fetch();

              $daily=$dataDaily['total'];

              $sqlTambahan=$db->prepare('SELECT SUM(luas) as totalLuas 
                FROM balistars_performa_mesin_input 
                where idCabang=? 
                and (tanggalPerforma between ? and ?) 
                and jenisOrder=? 
                and statusMesinInput=?');
              $sqlTambahan->execute([
                $row['idCabang'], 
                $tanggalAwalJalan,$tanggalAkhirJalan,
                $jenisPenjualan1,
                'Aktif']);
              $dataTambahan=$sqlTambahan->fetch();
              $daily=$daily+$dataTambahan['totalLuas'];
            }
            elseif($jenisPenjualan=='laser'){
              $sqlDaily=$db->prepare('SELECT sum(jumlahKlik) as daily 
                FROM balistars_performa_mesin_laser 
                where (tanggalPerforma between ? and ?) 
                and idCabang=? 
                and statusKlik=? 
                order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
              $sqlDaily->execute([$tanggalAwalJalan,$tanggalAkhirJalan, $row['idCabang'],'Aktif']);
              $dataDaily=$sqlDaily->fetch();
              $daily=$dataDaily['daily'];
            }
            else{
              $daily=0; 
              $sqlDailyAfter=$db->prepare('SELECT klikBefore 
                FROM balistars_performa_mesin_'.$jenisPenjualan.' 
                where tanggalPerforma<=? 
                and idCabang=? 
                order by tanggalPerforma DESC, idPerforma'.$jenisPenjualan1.' DESC limit 1');
              $sqlDailyAfter->execute([
                $tanggalAkhirJalan, 
                $row['idCabang']]);
              $dataDailyAfter=$sqlDailyAfter->fetch();

              $sqlDailyBefore=$db->prepare('SELECT klikBefore 
                FROM balistars_performa_mesin_'.$jenisPenjualan.' 
                where tanggalPerforma<? 
                and idCabang=? 
                order by tanggalPerforma DESC, idPerforma'.$jenisPenjualan1.' DESC limit 1');
              $sqlDailyBefore->execute([
                $tanggalAwalJalan, 
                $row['idCabang']]);
              $dataDailyBefore=$sqlDailyBefore->fetch();

              $daily=$dataDailyAfter['klikBefore']-$dataDailyBefore['klikBefore'];
            }
            if($daily>0){
            ?>
            <td><?=($daily)?></td>
            <?php
            }
            else{
              ?>
              <td>0</td>
              <?php
            }
            $total=$total+$daily;
          }
        ?>
        <td><?=$total?></td> 
        <?php
        }
        else{
          ?>
          <td class="bg-secondary" style="opacity: 50%;" colspan="<?=count($dataCabang)+1?>"></td>
          <?php
        }
        ?>
      </tr>
      <?php
    }
    if($bulanAkhir==date('m') && $tahunAkhir==date('Y')){
      $tanggalBerhenti=$tanggalAkhir;
    }
    else if($bulanAkhir>date('m') && $tahunAkhir>=date('Y')){
      $tanggalBerhenti=0;
    }
    else{
      $tanggalBerhenti=cal_days_in_month(CAL_GREGORIAN,$bulanAkhir,$tahunAkhir);
    }
    $arrayTotal = [];
    $tanggalAwal=$tahunAkhir."-".$bulanAkhir."-01";
    $total=0;
    for ($i=1; $i <= $tanggalBerhenti; $i++) {
      ?>
      <tr>
        <td><?=$i?></td>
        <?php
        $k=0;
        foreach ($dataCabang as $row) { 

          $arrayTotal[$k] = 0;

          if($jenisPenjualan=='indoor' || $jenisPenjualan=='outdoor' || $jenisPenjualan=='uv'){

            $sqlDaily=$db->prepare('SELECT SUM(luas) as total 
              FROM balistars_performa_mesin_'.$jenisPenjualan.' 
              where (tanggalPerforma=?) 
              and idCabang=? 
              and statusPerforma'.$jenisPenjualan1.'=?');
            $sqlDaily->execute([
              $tanggalAwal,
              $row['idCabang'],
              'Aktif']);
            $dataDaily=$sqlDaily->fetch();
            $daily=$dataDaily['total'];

            $sqlTambahan=$db->prepare('SELECT SUM(luas) as totalLuas 
              FROM balistars_performa_mesin_input 
              where idCabang=? 
              and (tanggalPerforma=?) 
              and jenisOrder=? 
              and statusMesinInput=?');
            $sqlTambahan->execute([
              $row['idCabang'], 
              $tanggalAwal,
              $jenisPenjualan1,
              'Aktif']);
            $dataTambahan=$sqlTambahan->fetch();
            $daily=$daily+$dataTambahan['totalLuas'];
          } 
          elseif($jenisPenjualan=='laser'){
            $tanggalJalan=$tanggalAwal;
            $sqlDaily=$db->prepare('SELECT klikBefore, jumlahKlik as  daily 
              FROM balistars_performa_mesin_laser 
              where tanggalPerforma = ? 
              and idCabang=? 
              and statusKlik=? 
              order by tanggalPerforma DESC, idPerformaLaser DESC limit 1');
            $sqlDaily->execute([$tanggalJalan, $row['idCabang'], 'Aktif']);
            $dataDailyAfter=$sqlDaily->fetch();
            $daily=$dataDailyAfter['daily'];
          }         
          else{
            $tanggalJalan=$tanggalAwal;

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
              if($jenisPenjualan=='laser'){
                $daily=$daily;
              } else{
                $daily=$dataDailyAfter['klikBefore']-$dataDailyBefore['klikBefore'];  
              }
            }
            else{
              $daily=0;
            }
          }
          $arrayTotal[$k]+=$daily;

          ?>
          <td><?=($arrayTotal[$k])?></td>
          <?php
          $total=$total+$daily;
          $k++;
        }
        ?>
        <td><?=$total?> </td>
      </tr>
      <?php
      $tanggalAwal=waktuBesok($tanggalAwal);
    }
    ?>
    </tbody>
  </table>
