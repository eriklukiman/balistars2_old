<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
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
  'form_kasir'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

if($flag=='cancel'){
    $sql=$db->prepare('UPDATE balistars_penjualan_detail set statusCancel=? WHERE noNota=?');
    $hasil = $sql->execute(['cancel',$noNota]);
    if($hasil){
      $sql=$db->prepare('UPDATE balistars_penjualan set statusPenjualan=?, idUserEdit=? WHERE noNota=?');
      $hasil = $sql->execute(['Non Aktif',$idUserAsli,$noNota]);
    }
}
else{
  if($jenisPPN == 'Non PPN'){
    $persenPPN=0;
  }

  $nilaiPPN=ubahToInt($nilaiPPN);
  $grandTotal=ubahToInt($grandTotal);
  $jumlahPembayaranAwal=ubahToInt($jumlahPembayaranAwal);
  $tanggalPenjualan=konversiTanggal($tanggalPenjualan);

  if($jumlahPembayaranAwal>=$grandTotal){
    $jumlahPembayaranAwal=$grandTotal; 
  }

  if($tipePenjualan=='A2'){
    $statusFakturPajak='Tanpa Faktur';
    $jenisPPN='Non PPN';
  }
  if($konsumen=='pelanggan'){
    $sql=$db->prepare('SELECT * FROM balistars_customer where idCustomer=?');
    $sql->execute([$idCustomer]);
    $row=$sql->fetch();
    $namaCustomer=$row['namaCustomer'];
    $noTelpCustomer=$row['noTelpCustomer']; 
  }

  if($flag=='update'){
    $sql=$db->prepare('UPDATE balistars_penjualan set 
      statusFakturPajak=?,
      idCabang=?,                                                 
      tipePenjualan=?,
      tanggalPenjualan=?,
      idCustomer=?,
      namaCustomer=?,
      noTelpCustomer=?,
      jenisPembayaran=?,
      bankTujuanTransfer=?,
      jenisPPN=?,
      persenPPN=?,
      lamaSelesai=?,
      statusPembayaran=?,
      statusInput=?,
      idDesigner=?,
      idUserEdit=?
      where noNota=?');
    $hasil=$sql->execute([
      $statusFakturPajak,
      $idCabang,
      $tipePenjualan,
      $tanggalPenjualan,
      $idCustomer,
      $namaCustomer,
      $noTelpCustomer,
      $jenisPembayaran,
      $bankTujuanTransfer,
      $jenisPPN,
      $persenPPN,
      $lamaSelesai,
      'Lunas',
      $statusInput,
      $idDesigner,
      $idUserAsli,
      $noNota]);
  }
  else{
    $sql=$db->prepare('INSERT INTO balistars_penjualan set 
      idCabang=?,                                         
      noNota=?,
      statusFakturPajak=?,
      tipePenjualan=?,
      tanggalPenjualan=?,
      idCustomer=?,
      namaCustomer=?,
      noTelpCustomer=?,
      jenisPembayaran=?,
      bankTujuanTransfer=?,
      jenisPPN=?,
      persenPPN=?,
      lamaSelesai=?,
      nilaiPPN = ?,
      jumlahPembayaranAwal=?,
      grandTotal=?,
      statusPembayaran=?,
      statusInput=?,
      idDesigner=?,
      idUser=?');
    $hasil=$sql->execute([
      $idCabang,
      $noNota,
      $statusFakturPajak,
      $tipePenjualan,
      $tanggalPenjualan,
      $idCustomer,
      $namaCustomer,
      $noTelpCustomer,
      $jenisPembayaran,
      $bankTujuanTransfer,
      $jenisPPN,
      $persenPPN,
      $lamaSelesai,
      $nilaiPPN,
      $jumlahPembayaranAwal,
      $grandTotal,
      $statusPembayaran,
      $statusInput,
      $idDesigner,
      $idUserAsli]);

    $sqlUpdatePenjualanDetail=$db->prepare('UPDATE balistars_penjualan_detail set statusFinal=?,
      idUser=?
      where noNota=?');
    $hasilUpdatePenjualanDetail=$sqlUpdatePenjualanDetail->execute([
      'final',
      $idUserAsli,
      $noNota]);
    //var_dump($sql->errorInfo());
    if($hasil){
      if($tipePenjualan=='A1'){
        updateNoNotaA1($db);
      }
      elseif($tipePenjualan=='A2'){
        updateNoNotaA2($db);
      }  
    } 
  }
}

$data = array('notifikasi' => 2);
//  echo "
// $idCabang,
//       $noNota,
//       $statusFakturPajak,
//       $tipePenjualan,
//       $tanggalPenjualan,
//       $idCustomer,
//       $namaCustomer,
//       $noTelpCustomer,
//       $jenisPembayaran,
//       $bankTujuanTransfer,
//       $jenisPPN,
//       $persenPPN,
//       $lamaSelesai,
//       $nilaiPPN,
//       $jumlahPembayaranAwal,
//       $grandTotal,
//       $statusPembayaran,
//       $statusInput,
//       $idDesigner,
//       $idUserAsli
//  ";
if($hasil){
  $data = array('notifikasi' => 1, 'tipePenjualan' => $tipePenjualan, 'konsumen' => $konsumen);
}
echo json_encode($data);
?>