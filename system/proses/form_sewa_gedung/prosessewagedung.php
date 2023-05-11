<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
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
  'form_sewa_gedung'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);
if($flag == 'cancel'){
  $sql = $db->prepare('
    UPDATE balistars_hutang_gedung 
    set statusHutangGedung = ?, 
    idUserEdit=? 
    where idHutangGedung = ?');
  $hasil = $sql->execute([
    'Non Aktif',
    $idUserAsli, 
    $idHutangGedung]);

  $sql1=$db->prepare('
    DELETE FROM balistars_gedung_penyusutan 
    where noNota=?');
  $hasil1=$sql1->execute([$noNota]);
}
else{ 
  $nilaiSewa=ubahToInt($nilaiSewa);
  $tanggalSewa=konversiTanggal($tanggalSewa);
  $tanggalPenyusutan=konversiTanggal($tanggalPenyusutan);
  $tanggalAkhirPecah=explode('-', $tanggalPenyusutan);
  $tanggalAkhir=($tanggalAkhirPecah[0]+$penyusutan)."-".$tanggalAkhirPecah[1]."-".$tanggalAkhirPecah[2];

  if($flag == 'update'){
    $sql=$db->prepare('UPDATE balistars_hutang_gedung set 
      idGedung=?,
      noNota=?,
      tanggalSewa=?,
      tanggalPenyusutan=?,
      tanggalAkhir=?,
      nilaiSewa=?,
      penyusutan=?,
      notaSewa=?,
      idUserEdit=?
      where idHutangGedung=?');
    $hasil=$sql->execute([
      $idGedung,
      $noNota,
      $tanggalSewa,
      $tanggalPenyusutan,
      $tanggalAkhir,
      $nilaiSewa,
      $penyusutan,
      $notaSewa,
      $idUserAsli,
      $idHutangGedung]);

    $sql1=$db->prepare('DELETE FROM balistars_gedung_penyusutan 
      where noNota=?');
    $hasil1=$sql1->execute([$noNota]);

    $interval=$penyusutan*12;
    $nilaiPenyusutan=$nilaiSewa/($penyusutan*12);
    for($i=1; $i<=$interval; $i++ ){
      $tanggal=$tanggalPenyusutan;
      $sql2=$db->prepare('INSERT INTO balistars_gedung_penyusutan set 
        idGedung=?,
        noNota=?,
        tanggalPenyusutan=?,
        nilaiPenyusutan=?,
        idUser=?');
      $hasil2=$sql2->execute([
        $idGedung,
        $noNota,
        $tanggal,
        $nilaiPenyusutan,
        $idUserAsli]);

      $tanggalPenyusutan=bulanBesok($tanggal);
    }
    if($hasil2){
      updateNoNotaPenyusutan($db);
    }
  }
  else{
    $sql = $db->prepare('INSERT INTO balistars_hutang_gedung set 
      idGedung=?,
      noNota=?,
      tanggalSewa=?,
      tanggalPenyusutan=?,
      tanggalAkhir=?,
      nilaiSewa=?,
      penyusutan=?,
      notaSewa=?,
      idUser=?');
    $hasil=$sql->execute([
      $idGedung,
      $noNota,
      $tanggalSewa,
      $tanggalPenyusutan,
      $tanggalAkhir,
      $nilaiSewa,
      $penyusutan,
      $notaSewa,
      $idUserAsli]);

    $interval=$penyusutan*12;
    $nilaiPenyusutan=$nilaiSewa/($penyusutan*12);
    for($i=1; $i<=$interval; $i++ ){
      $tanggal=$tanggalPenyusutan;
      $sql1=$db->prepare('INSERT INTO balistars_gedung_penyusutan set 
        idGedung=?,
        noNota=?,
        tanggalPenyusutan=?,
        nilaiPenyusutan=?,
        idUser=?');
      $hasil1=$sql1->execute([
        $idGedung,
        $noNota,
        $tanggal,
        $nilaiPenyusutan,
        $idUserAsli]);
      $tanggalPenyusutan=bulanBesok($tanggal);
    }
    if($hasil1){
      updateNoNotaPenyusutan($db);
    }
  }
}

$data = array('flag' => $flag, 'notifikasi' => 2);
if($hasil){
  $data = array('flag' => $flag, 'notifikasi' => 1);
}
echo json_encode($data);

?>