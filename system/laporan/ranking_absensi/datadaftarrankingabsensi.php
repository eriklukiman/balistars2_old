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
  'ranking_absensi'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggalAkhir=$tanggal;
$tanggalAkhir=konversiTanggal($tanggalAkhir);
$tanggalPecah=explode('-', $tanggalAkhir);
$tanggalAwal=$tanggalPecah[0].'-'.$tanggalPecah[1].'-01';
$hariAkhir=(int)$tanggalPecah[2];

function cekHariLibur($hariLibur,$tanggalAkhir)
{
  $cek=0;
  for ($i=0; $i<count($hariLibur) ; $i++) { 
    if($hariLibur[$i]<=$tanggalAkhir && $hariLibur[$i]!=''){
      $cek++;
    }
  }
  return $cek;
}

function dayOff($startDate, $endDate)
{
  $start = new DateTime($startDate);
  $end = new DateTime($endDate);
  $days = $start->diff($end, true)->days;

  $sundays = intval($days / 7) + ($start->format('N') + $days % 7 >= 7);

  return $sundays;
}

$banyakHariLibur = array();
$n=0;
$sqlLibur=$db->prepare('
  SELECT hariLibur, idCabang 
  FROM balistars_produktivity 
  where (tanggalProduktivity BETWEEN ? AND ?)
  and statusProduktivity=?');
$sqlLibur->execute([
  $tanggalAwal,
  $tanggalAkhir,
  'Aktif']);
$dataLibur=$sqlLibur->fetchAll();

$parameterSql = '';
$parameterCabang = array();
$cabangTemp = 0;

foreach ($dataLibur as $cek) {
  $hariLibur=explode(',', $cek['hariLibur']); 
  if($dataLibur){
    $banyakHariLibur[]=cekHariLibur($hariLibur,$tanggalAkhir);
  }
  else{
    $banyakHariLibur[]=0;
  }
  $parameterCabang[] = $cek['idCabang'];
  $parameterSql = $parameterSql.' WHEN balistars_pegawai.idCabang = '.$cek['idCabang'].' THEN ? ';
}

  $dayOff = dayOff($tanggalAwal, $tanggalAkhir);
  $set = $hariAkhir-$dayOff;
  $sql = $db->prepare(
    'SELECT (data.totalPoin-((?-data.totalAbsen)*10)+(?*10))/(?-data.hariLibur) as poinKotor, data.idPegawai, data.namaPegawai, data.idCabang, data.namaCabang, data.hariLibur FROM 
        (
        SELECT SUM(poin) as totalPoin, balistars_absensi.idPegawai, count(idAbsensi) as totalAbsen, namaPegawai, balistars_pegawai.idCabang, balistars_cabang.namaCabang,
        CASE
            '.$parameterSql.'
            ELSE 5678
        END AS hariLibur
        FROM balistars_pegawai 
        inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang 
        inner join balistars_absensi on balistars_absensi.idPegawai=balistars_pegawai.idPegawai 
        where  
        idJabatan!=? and 
        idJabatan!=? and 
        idJabatan!=? and 
        idJabatan!=? and
        (balistars_absensi.tanggalDatang BETWEEN ? and ?) group by idPegawai
        ) as data 
        order by poinKotor DESC, namaPegawai ASC'
  );
  $execute = array($hariAkhir,$dayOff,$set);
  $execute2= array(1,3,9,11,$tanggalAwal,$tanggalAkhir);
  $execute = array_merge($execute, $banyakHariLibur);
  $execute = array_merge($execute, $execute2);
  $sql->execute($execute);

  $data=$sql->fetchAll();
  foreach ($data as $row) {
    $poinBersih = round($row['poinKotor']);
    $n++;
    if($poinBersih>=9){
      $predikat='A';
      $color='success'; 
    }
    else if($poinBersih>=8){
      $predikat='B';
      $color='info';
    }
    else if($poinBersih>=6){
      $predikat='C';
      $color='warning';
    }
    else {
      $predikat='D';
      $color='danger';
    }
    ?>
    <tr>
      <td><?=$n?></td>
      <td>
        <button class="btn btn-<?=$color?>" style="width: 100%; text-align: left; color: white;">
          <?=$row['namaCabang']?>
        </button>
      </td>
      <td><?=$row['namaPegawai']?></td>
      <td><?=$poinBersih?></td>
      <td>Kelas <?=$predikat?></td>
      <td style="color: red;">
        <?php  
        if($predikat=='D'){
          echo "Warning";
        }
        ?>
      </td>
    </tr>
  <?php
  }
  ?>