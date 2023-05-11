<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
include_once 'fungsinomor.php';

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
  'form_pembelian'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}
$flag='';
extract($_REQUEST);

if($flag=='cancel'){
    $sql=$db->prepare('UPDATE balistars_pembelian_detail set statusCancel=? WHERE noNota=?');
    $hasil = $sql->execute(['cancel',$noNota]);
    if($hasil){
      $sql=$db->prepare('UPDATE balistars_pembelian set statusBiaya=?, idUserEdit=? WHERE noNota=?');
      $hasil = $sql->execute(['Non Aktif',$idUser,$noNota]);
    }
}
else{
  $statusPembelian='Lunas';
  $tanggalPembelian=konversiTanggal($tanggalPembelian);
  $jumlahPembayaran=ubahToInt($jumlahPembayaran);
  $nilaiPPN=ubahToInt($nilaiPPN);
  $persenPPN=ubahToInt($persenPPN);
  $grandTotal=ubahToInt($grandTotal);
  $sisaHutang=$grandTotal-$jumlahPembayaran;

  if($persenPPN=='Non PPN'){
    $persenPPN=0;
  }

  if($jenisPembayaran=='Giro'){
    $sqlSupplier=$db->prepare('SELECT * FROM balistars_supplier 
        WHERE idSupplier=?');
    $sqlSupplier->execute([$idSupplier]);
    $dataSupplier=$sqlSupplier->fetch();

    $namaSupplier=$dataSupplier['namaSupplier'];
    $noTelpSupplier=$dataSupplier['noTelpSupplier'];
    $statusPembelian='Belum Lunas';
  }
  if($flag=='update'){
    $sql=$db->prepare('UPDATE balistars_pembelian set 
      idCabang=?,                                                   
      noNotaVendor=?,
      tanggalPembelian=?,
      idSupplier=?,
      namaSupplier=?,
      noTelpSupplier=?,
      tipePembelian=?,
      jenisPPN=?,
      jatuhTempo=?,
      statusPembelian=?,
      idUserEdit=?
      where noNota=?');
    $hasil=$sql->execute([
      $idCabang,
      $noNotaVendor,
      $tanggalPembelian,
      $idSupplier,
      $namaSupplier,
      $noTelpSupplier,
      $tipePembelian,
      $jenisPPN,
      $jatuhTempo,
      'Lunas',
      $idUserAsli,
      $noNota]);
  }
  else{
    $sql=$db->prepare('INSERT INTO balistars_pembelian set 
      idCabang=?,
      noNota=?,
      noNotaVendor=?,
      tanggalPembelian=?,
      idSupplier=?,
      namaSupplier=?,
      noTelpSupplier=?,
      tipePembelian=?,
      jenisPPN=?,
      persenPPN=?,
      nilaiPPN=?,
      grandTotal=?,
      jatuhTempo=?,
      statusPembelian=?,
      idUser=?');
    $hasil=$sql->execute([
      $idCabang,
      $noNota,
      $noNotaVendor,
      $tanggalPembelian,
      $idSupplier,
      $namaSupplier,
      $noTelpSupplier,
      $tipePembelian,
      $jenisPPN,
      $persenPPN,
      $nilaiPPN,
      $grandTotal,
      $jatuhTempo,
      $statusPembelian,
      $idUserAsli]);

    $sqlInsertHutang=$db->prepare('INSERT INTO balistars_hutang set 
      noNota=?,
      tanggalPembelian=?,
      tanggalPembayaran=?,
      grandTotal=?,
      jumlahPembayaran=?,
      sisaHutang=?,
      jenisPembayaran=?,
      bankAsalTransfer=?,
      idUser=?');
    $hasilInsertHutang=$sqlInsertHutang->execute([
      $noNota,
      $tanggalPembelian,
      $tanggalPembelian,
      $grandTotal,
      $jumlahPembayaran,
      $sisaHutang,
      $jenisPembayaran,
      $bankAsalTransfer,
      $idUserAsli]);

    if($jenisPembayaran=='Cash'){
    //jika pembelian cash, mengurangi kas kecil
      $sqlKasKecil=$db->prepare('SELECT * FROM balistars_kas_kecil 
        WHERE idCabang=? order by timeStamp DESC LIMIT 1');
      $sqlKasKecil->execute([$idCabang]);
      $dataKasKecil=$sqlKasKecil->fetch();
      $saldo=$dataKasKecil['saldo']-$jumlahPembayaran;

      $sqlInsertKasKecil=$db->prepare('INSERT INTO balistars_kas_kecil set 
        idCabang=?,
        tanggalTransaksi=?,                               
        debet=?,
        kredit=?,
        saldo=?,
        idUser=?');
      $hasilKasKecil=$sqlInsertKasKecil->execute([
        $idCabang,
        $tanggalPembelian,
        0,
        $jumlahPembayaran,
        $saldo,
        $idUserAsli]);
    }
// var_dump($sql->errorInfo());
// var_dump($sqlInsertHutang->errorInfo());
    if($hasil && $hasilInsertHutang){
      if($tipePembelian=='A1'){
        updateNoBeliA1($db);
      }
      elseif($tipePembelian=='A2'){
        updateNoBeliA2($db);
      }
    }
  }
}

$data = array('notifikasi' => 2,'tipePembelian' => $tipePembelian, 'konsumen' => $konsumen);

if($hasil){
  $data = array('notifikasi' => 1, 'tipePembelian' => $tipePembelian, 'konsumen' => $konsumen);
}
echo json_encode($data);

?>