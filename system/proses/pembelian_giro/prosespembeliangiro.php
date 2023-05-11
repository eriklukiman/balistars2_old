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
  'pembelian_giro'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if ($flag == 'cancel') {

  $sqlPelunasanGiro = $db->prepare('SELECT * from balistars_dpgiro 
      WHERE noGiro = ?');
  $sqlPelunasanGiro->execute([$noGiro]);
  $dataPelunasan=$sqlPelunasanGiro->fetch();

  $sql=$db->prepare('UPDATE balistars_dpgiro set 
    tglPelunasan = ?
    where jenisGiro = ?
    and periode=? 
    and tipePembelian=? ');
  $hasil=$sql->execute([
    NULL,
    "DP",
    $dataPelunasan['periode'],
    $dataPelunasan['tipePembelian']]);

  $sql1 = $db->prepare('DELETE FROM balistars_dpgiro WHERE noGiro = ? AND jenisGiro = ?');
  $status = $sql1->execute([$noGiro, 'Pelunasan']);

  if ($status == true) {
    $sql2 = $db->prepare('UPDATE balistars_pembelian 
        INNER JOIN balistars_hutang 
        ON balistars_pembelian.noNota = balistars_hutang.noNota
        SET
        balistars_pembelian.statusPembelian = ?,
        balistars_hutang.sisaHutang = balistars_hutang.jumlahPembayaran,
        balistars_hutang.jumlahPembayaran = ?,
        balistars_hutang.bankAsalTransfer = ? ,
        balistars_hutang.tanggalCair = ?,
        balistars_hutang.noGiro = ?
        WHERE balistars_hutang.noGiro = ?'
    );
    $hasil = $sql2->execute([
      'Belum Lunas', 
      0, 
      0, 
      NULL, 
      NULL, 
      $noGiro]);
  } 
}
else {
  $dataNoNota= explode(',' , $dataNoNota);
  foreach ($dataNoNota as $index => $value) {
    $dataNoNota[$index] = "'" . $value . "'";
  }

  $joinNoNota = '(' . join(',', $dataNoNota) . ')';
  //var_dump($dataNoNota);
  if($flag=="finalisasi"){
    $sql=$db->prepare('UPDATE balistars_pembelian set 
      statusPembelian=? 
      where noNota in '.$joinNoNota);
    $hasil=$sql->execute(["Lunas"]);

    $sqlBayar=$db->prepare('UPDATE balistars_hutang set 
      jumlahPembayaran=grandTotal, 
      sisaHutang=? 
      where noNota in '.$joinNoNota);
    $sqlBayar->execute([0]);

    $sqlDPGiro = $db->prepare('UPDATE balistars_dpgiro set 
      jenisGiro = ? 
      WHERE idDpGiro = ?');
    $sqlDPGiro->execute(['Pelunasan', $idDpGiro]);

    $sqlPelunasanGiro = $db->prepare('SELECT * from balistars_dpgiro 
      WHERE idDpGiro = ?');
    $sqlPelunasanGiro->execute([$idDpGiro]);
    $dataPelunasan=$sqlPelunasanGiro->fetch();

    $sql=$db->prepare('UPDATE balistars_dpgiro set 
      tglPelunasan=? 
      where jenisGiro = ?
      and periode=? 
      and tipePembelian=? ');
    $hasil=$sql->execute([
      $dataPelunasan['tanggalCairDp'],
      "DP",
      $dataPelunasan['periode'],
      $dataPelunasan['tipePembelian']]);
  }
  else{
    $tanggalCair=konversiTanggal($tanggalCair);
    $sql1=$db->prepare('UPDATE balistars_hutang set 
      noGiro=?, 
      bankAsalTransfer=?, 
      tanggalCair=? 
      where noNota in '.$joinNoNota);
    $hasil1=$sql1->execute([
      $noGiro,
      $bankAsalTransfer,
      $tanggalCair]);

    $sql = $db->prepare('INSERT INTO balistars_dpgiro set 
        idSupplier=?,
        tanggalCairDp=?,
        dp=?,
        idBank=?,
        noGiro=?,
        tipePembelian=?,
        jenisGiro=?,
        periode=?, 
        idUser=? ');
    $hasil = $sql->execute([
      $idSupplier,
      $tanggalCair,
      $sisaPembelian,
      $bankAsalTransfer,
      $noGiro,
      $tipePembelian,
      NULL,
      $tanggalAwal,
      $idUserAsli
    ]);


  }
  //var_dump($sql1->errorInfo());
} 

$data = array('status' => false,'notifikasi' => 'Proses Data Gagal');
if($hasil){
  $data = array('status' => true, 'notifikasi' => 'Proses Data Berhasil');
}
echo json_encode($data);

?>