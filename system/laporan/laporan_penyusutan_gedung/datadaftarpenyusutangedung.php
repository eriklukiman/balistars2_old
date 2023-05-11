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
  'laporan_penyusutan_gedung'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggalAwal=$tahun.'-01-01';
$tanggalAkhir=$tahun.'-12-31';
$grandNilaiAwal=0;
$grandBayar=0;
$grandPemakaian=0;
$grandNilaiAkhir=0;

$n=0;
$sqlGedung=$db->prepare('SELECT * 
  FROM balistars_gedung 
  LEFT JOIN 
  (SELECT MAX(tanggalAkhir) as tanggalAkhir, idGedung 
    FROM balistars_hutang_gedung 
    GROUP BY idGedung) as dataTutup 
  ON balistars_gedung.idGedung=dataTutup.idGedung
  where statusGedung=? ');
$sqlGedung->execute(["Aktif"]);
$dataGedung=$sqlGedung->fetchAll();

foreach ($dataGedung as $row) {
  $pemakaian          = 0;
  $pemakaianBaru      = 0;
  $pemakaianAkumulasi = 0;

  $sqlBayar=$db->prepare('SELECT SUM(nilaiSewa) as totalBayar 
    FROM balistars_hutang_gedung 
    where (tanggalSewa between ? and ?) 
    and idGedung=? 
    and statusHutangGedung=?');
  $sqlBayar->execute([
    $tanggalAwal,$tanggalAkhir,
    $row['idGedung'],
    "Aktif"]);
  $dataBayar=$sqlBayar->fetch();

  $sqlPemakaian = $db->prepare('SELECT SUM(nilaiPenyusutan) as totalPemakaian 
    FROM balistars_gedung_penyusutan 
    where (tanggalPenyusutan between ? and ?) 
    and idGedung=? 
    and statusGedungPenyusutan=?');
  $sqlPemakaian->execute([
    $tanggalAwal,$tanggalAkhir,
    $row['idGedung'],
    "Aktif"]);
  $dataPemakaian=$sqlPemakaian->fetch();

  $sqlBayarSebelum=$db->prepare('SELECT SUM(nilaiSewa) as totalBayarSebelum 
    FROM balistars_hutang_gedung 
    where tanggalSewa < ? 
    and idGedung=? 
    and statusHutangGedung=?');
  $sqlBayarSebelum->execute([
    $tanggalAwal,
    $row['idGedung'],
    "Aktif"]);
  $dataBayarSebelum=$sqlBayarSebelum->fetch();

  $sqlPemakaianSebelum = $db->prepare('SELECT SUM(nilaiPenyusutan) as totalPemakaianSebelum 
    FROM balistars_gedung_penyusutan 
    where tanggalPenyusutan < ? 
    and idGedung=? 
    and statusGedungPenyusutan=?');
  $sqlPemakaianSebelum->execute([
    $tanggalAwal,
    $row['idGedung'],
    "Aktif"]);
  $dataPemakaianSebelum=$sqlPemakaianSebelum->fetch();

  $nilaiAwal  = $dataBayarSebelum['totalBayarSebelum']
                -$dataPemakaianSebelum['totalPemakaianSebelum'];
  $nilaiBayar = $dataBayar['totalBayar']; 
  $pemakaian  =$dataPemakaian['totalPemakaian'];
  $nilaiAkhir = $nilaiAwal+$nilaiBayar-$pemakaian;
  $n++;
?>
  <tr>
    <td><?=$n?></td>
    <td><?=$row['namaGedung']?></td>
    <td><?=ubahToRp($nilaiAwal)?></td>
    <td><?=ubahToRp($nilaiBayar)?></td>
    <td><?=ubahToRp($pemakaian)?></td>
    <td><?=ubahToRp($nilaiAkhir)?></td>
    <td><?=ubahTanggalIndo($row['tanggalAkhir'])?></td>
  </tr> 
<?php
  $grandNilaiAwal+=$nilaiAwal;
  $grandBayar+=$nilaiBayar;
  $grandPemakaian+=$pemakaian;
  $grandNilaiAkhir+=$nilaiAkhir;
}
?>
  <tr>
    <td colspan="2"><b>Grand Total</b></td>
    <td><b><?=ubahToRp($grandNilaiAwal)?></b></td>
    <td><b><?=ubahToRp($grandBayar)?></b></td>
    <td><b><?=ubahToRp($grandPemakaian)?></b></td>
    <td colspan="2"><b><?=ubahToRp($grandNilaiAkhir)?></b></td>
  </tr>
