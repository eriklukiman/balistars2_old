<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';

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
  'form_persediaan_global'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggalRentang      = explode(' - ', $rentang);
$tanggalAwal  = konversiTanggal($tanggalRentang[0]);
$tanggalAkhir = konversiTanggal($tanggalRentang[1]);
$tanggalAwalSekali="2019-01-01";
$tanggalKemarin=waktuKemarin($tanggalAwal);

// **Persediaan Awal Untuk Menghitung Saldo Awal**
$sqlPersediaanAwal=$db->prepare('
    SELECT SUM(debet-kredit) as saldoAwal, idCabang 
    FROM
    (
        (
            SELECT SUM(grandTotal-nilaiPPN) as debet, 0 as kredit, idCabang 
            FROM balistars_pembelian 
            where (tanggalPembelian between ? and ?) 
            and  idCabang=?
            and status=?
        )
        UNION
        (
            SELECT SUM(nilaiPersediaan) as debet, 0 as kredit, idCabang 
            FROM balistars_persediaan_global 
            where (tanggalPersediaan between ? and ?) 
            and nilaiPersediaan>=0 
            and  idCabang=?
            and statusPersediaan =?
        )
        UNION
        (
            SELECT 0 as debet, SUM(0-nilaiPersediaan) as kredit, idCabang 
            FROM balistars_persediaan_global 
            where (tanggalPersediaan between ? and ?) 
            and nilaiPersediaan<0 
            and  idCabang=?
            and statusPersediaan=?
        )
          )
    as data
      ');
    $sqlPersediaanAwal->execute([
      $tanggalAwalSekali,$tanggalKemarin,$idCabang,'Aktif',
      $tanggalAwalSekali,$tanggalKemarin,$idCabang,'Aktif',
      $tanggalAwalSekali,$tanggalKemarin,$idCabang, 'Aktif',
    ]);
    $dataPersediaanAwal=$sqlPersediaanAwal->fetch();

    $saldo=$dataPersediaanAwal['saldoAwal'];


// ** Persediaan Untuk Menghitung Debet Kredit Persediaan **
$sqlPersediaan=$db->prepare('
 SELECT * FROM
  (
      (
      SELECT noNota, (grandTotal-nilaiPPN) as debet, 0 as kredit, idCabang, timeStamp, tanggalPembelian as tanggal, 0 as idPersediaan 
      FROM balistars_pembelian 
      where (tanggalPembelian between ? and ?) 
      and  idCabang=?
      and status=?
      )
      UNION
      (
      SELECT idPersediaan as noNota, (nilaiPersediaan) as debet, 0 as kredit, idCabang, timeStamp, tanggalPersediaan as tanggal, idPersediaan 
      FROM balistars_persediaan_global 
      where (tanggalPersediaan between ? and ?) 
      and nilaiPersediaan>=0 
      and idCabang=?
      and statusPersediaan=?
      )
      UNION
      (
      SELECT idPersediaan as noNota, 0 as debet, (0-nilaiPersediaan) as kredit, idCabang, timeStamp, tanggalPersediaan as tanggal, idPersediaan 
      FROM balistars_persediaan_global 
      where (tanggalPersediaan between ? and ?) 
      and nilaiPersediaan<0 
      and  idCabang=?
      and statusPersediaan=?
      )
  )
  as data 
  ORDER BY tanggal, timestamp

');
$sqlPersediaan->execute([
  $tanggalAwal,$tanggalAkhir,$idCabang, 'Aktif',
  $tanggalAwal,$tanggalAkhir,$idCabang, 'Aktif',
  $tanggalAwal,$tanggalAkhir,$idCabang, 'Aktif',
]);
//var_dump($sqlPersediaan->errorInfo());
$dataPersediaan=$sqlPersediaan->fetchAll();

?>
  <tr>
    <td></td>
    <td></td>
    <td><b>Saldo Awal</b></td>
    <td></td>
    <td></td>
    <td>Rp <?=ubahToRp($saldo)?></td>
  </tr>
<?php
$totalDebet=0;
$totalKredit=0;
$n = 1;
foreach($dataPersediaan as $row){
  $debet = $row['debet'];
  $kredit = $row['kredit'];
  $totalDebet+=$debet;
  $totalKredit+=$kredit;
  $saldo+=($debet-$kredit);
  ?>
  <tr>
    <?php
    $disabled1  = '';
    $disabled2  = '';
    if($dataCekMenu['tipeEdit']=='0'){
       $disabled1 = 'style = "display: none;"';
    }
    if($dataCekMenu['tipeDelete']=='0'){
       $disabled2 = 'style = "display: none;"';
    }
     ?>
    <td><?=$n?></td>
    <td>
      <?php 
      if($row['idPersediaan']>0){
        ?>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditPersediaan" 
              style              = "color: white;"
              onclick = "editPersediaanGlobal('<?=$row['idPersediaan']?>')" <?=$disabled1?> >
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cancelPersediaanGlobal('<?=$row['idPersediaan']?>')" <?=$disabled2?> 
              >
        <i class="fa fa-trash"></i>
      </button>
      <?php
      } ?>
    </td>
    <td ><?=wordwrap(ubahTanggalIndo($row['tanggal']),50,'<br>')?></td>
    <td >Rp <?=wordwrap(ubahToRp($debet),50,'<br>')?></td>
    <td >Rp <?=wordwrap(ubahToRp($kredit),50,'<br>')?></td>
    <td >Rp <?=wordwrap(ubahToRp($saldo),50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>
<tr>
  <td></td>
  <td></td>
  <td><b>Grand Total</b></td>
  <td>Rp <?=ubahToRp($totalDebet)?></td>
  <td>Rp <?=ubahToRp($totalKredit)?></td>
  <td>Rp <?=ubahToRp($saldo)?></td>
  <td colspan="1"></td>
</tr>