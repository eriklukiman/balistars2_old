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
  'hutang_mesin'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$flag='';
extract($_REQUEST);

if($flag=='cancel'){
    $sql=$db->prepare('UPDATE balistars_pembelian_mesin_detail set statusCancel=? WHERE noNota=?');
    $hasil = $sql->execute(['cancel',$noNota]);
    if($hasil){
      $sql=$db->prepare('UPDATE balistars_pembelian_mesin set statusPembelianMesin=?, idUserEdit=? WHERE noNota=?');
      $hasil = $sql->execute(['Non Aktif',$idUserAsli,$noNota]);

      $sql1=$db->prepare('DELETE FROM balistars_mesin_penyusutan where noNota=?');
      $hasil1=$sql1->execute([$noNota]);
    }
}
else{

  if($jenisPPN=='Non PPN'){
    $persenPPN=0;
  }

  $jumlahPembayaran=0;
  $bankAsalTransfer=0;
  $tanggalPembelian=konversiTanggal($tanggalPembelian);
  $nilaiPPN=ubahToInt($nilaiPPN);
  $grandTotal=ubahToInt($grandTotal);

  if($flag=='update'){
    $sql=$db->prepare('UPDATE balistars_pembelian_mesin set 
      tipePembelian=?,
      noNotaVendor=?,
      lamaPenyusutan=?,
      jenisPPN=?,
      persenPPN=?,
      nilaiPPN=?,
      kodeAkunting=?,
      idCabang=?,
      tanggalPembelian=?,
      namaSupplier=?,
      grandTotal=?,
      idUserEdit=?
    where noNota=?');
    $hasil=$sql->execute([
      $tipePembelian,
      $noNotaVendor,
      $lamaPenyusutan,
      $jenisPPN,
      $persenPPN,
      $nilaiPPN,
      $kodeAkunting,
      $idCabang,
      $tanggalPembelian,
      $namaSupplier,
      $grandTotal,
      $idUserAsli,
      $noNota]);

    $sql1=$db->prepare('DELETE FROM balistars_mesin_penyusutan where noNota=?');
    $hasil1=$sql1->execute([$noNota]);

    $sqlMesin=$db->prepare('SELECT * 
    FROM balistars_pembelian_mesin_detail 
    inner join balistars_pembelian_mesin 
    on balistars_pembelian_mesin_detail.noNota=balistars_pembelian_mesin.noNota 
    where balistars_pembelian_mesin_detail.noNota=?');
    $sqlMesin->execute([$noNota]);
    $dataMesin = $sqlMesin->fetchAll();

    foreach($dataMesin as $data){
      $interval=$data['lamaPenyusutan']*12;
      $kodeAkunting=$data['kodeAkunting'];
      $idPembelianDetail=$data['idPembelianDetail'];
      $tanggalPembelian=$data['tanggalPembelian'];
      if($data['jenisPPN']=='Include'){
        $nilaiBeli=100/(100+$persenPPN)*$data['nilai'];
      }
      else{
        $nilaiBeli=$data['nilai'];
      }
      if($interval===0){
        break;
      }
      $nilaiPenyusutan=$nilaiBeli/$interval;
      for($i=1; $i<=$interval; $i++ ){
        $tanggal=$tanggalPembelian;
        $sql2=$db->prepare('INSERT INTO balistars_mesin_penyusutan set 
          idPembelianDetail=?,
          noNota=?,
          kodeAkunting=?,
          tanggalPenyusutan=?,
          nilaiPenyusutan=?,
          idUser=?');
        $hasil2=$sql2->execute([$idPembelianDetail,$noNota,$kodeAkunting,$tanggal,$nilaiPenyusutan,$idUserAsli]);
        $tanggalPembelian=bulanBesok($tanggal);
      }
    }
  }

}

$data = array('notifikasi' => 2, 'tipePembelian' => $tipePembelian);

if($hasil){
  $data = array('notifikasi' => 1, 'tipePembelian' => $tipePembelian);
}
echo json_encode($data);

?>