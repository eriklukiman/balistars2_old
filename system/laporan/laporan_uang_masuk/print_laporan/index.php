<?php
include_once '../../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';

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
  'laporan_uang_masuk'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$sqlInformasi    = $db->query('SELECT * FROM balistars_information');
$dataInformasi = $sqlInformasi->fetch();
$logo            = $BASE_URL_HTML.'/assets/images/'.$dataInformasi['logo'];

//informasi user login
$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

extract($_REQUEST);

//$rentang1=$rentang;
$tanggal = explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]); 
$selisihTanggal=selisihTanggal($tanggalAwal,$tanggalAkhir);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="css/custom.css">
  <title>Print Kas Kecil</title>
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/toastr/toastr.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/select2/select2.css"> 
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/datepicker/datepicker.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/main2.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/color_skins.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/loader.css">
  <?php
  icon($logo);
  ?>
</head>
<?php  
$dataCabang=executeQueryUpdateForm('SELECT * FROM balistars_cabang where idCabang=?',$db,$idCabang);
if($dataCabang){
  $namaCabang=$dataCabang['namaCabang'];
}
else{
  $namaCabang="Semua Cabang";
}
?>
<body onload="doPrint()">
<div class="container">
 <table class="table table-bordered table-hover">
    <thead>
      <th colspan="9" style="font-size: 17px; text-align: center;">Laporan Uang Masuk <?=$namaCabang?> <br> <?=ubahTanggalIndo($tanggalAwal)?> - <?=ubahTanggalIndo($tanggalAkhir)?></th>
    </thead>
     <thead>
      <tr>
        <th>Tanggal </th>
        <th>Cash </th>
        <th>Transfer</th>
        <th>Jumlah </th>
        <th>Setoran Bank</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      if($idCabang==0){
        $parameter1=' and balistars_penjualan.idCabang !=?';
        $parameter2=' and idCabang !=?';
      } else {
        $parameter1=' and balistars_penjualan.idCabang =?';
        $parameter2=' and idCabang =?';
      }

      $totalCash=0;
      $totalTransfer=0;
      $totalJumlah=0;
      $totalSetor=0; 

      for ($i=0; $i <= $selisihTanggal ; $i++) { 
        $sqlCash=$db->prepare('SELECT SUM(jumlahPembayaran) as totalCash 
          FROM balistars_piutang 
          inner join balistars_penjualan 
          on balistars_piutang.noNota=balistars_penjualan.noNota 
          where balistars_piutang.bankTujuanTransfer=? 
          and balistars_piutang.jenisPembayaran=? 
          and (balistars_piutang.tanggalPembayaran=?) 
          and statusPenjualan=?'
          .$parameter1);
        $sqlCash->execute([
          0,
          "Cash",
          $tanggalAwal,
          'Aktif',
          $idCabang]);

        $sqlTransfer=$db->prepare('SELECT SUM(jumlahPembayaran) as totalTransfer, SUM(biayaAdmin) as totalAdmin, SUM(PPH) as totalPPH  
          FROM balistars_piutang 
          inner join balistars_penjualan 
          on balistars_piutang.noNota=balistars_penjualan.noNota 
          where balistars_piutang.bankTujuanTransfer!=? 
          and balistars_piutang.jenisPembayaran=? 
          and balistars_penjualan.statusPenjualan=? 
          and (balistars_piutang.tanggalPembayaran=?)'
          .$parameter1);
        $sqlTransfer->execute([
          0,
          "Transfer",
          'Aktif',
          $tanggalAwal,
          $idCabang]);  

        $sqlPPN=$db->prepare('SELECT SUM(jumlahPembayaran) as totalPPN 
          FROM balistars_piutang 
          inner join balistars_penjualan 
          on balistars_piutang.noNota=balistars_penjualan.noNota 
          where balistars_piutang.bankTujuanTransfer=? 
          and balistars_piutang.jenisPembayaran=? 
          and balistars_penjualan.statusPenjualan=? 
          and (balistars_piutang.tanggalPembayaran=?)'
          .$parameter1);
        $sqlPPN->execute([
          "-",
          "PPN",
          'Aktif',
          $tanggalAwal,
          $idCabang]);

        $sqlSetor=$db->prepare('SELECT SUM(jumlahSetor) as totalSetor 
          FROM balistars_setor_penjualan_cash 
          where tanggalSetor=? 
          and statusSetor=?'
          .$parameter2);
        $sqlSetor->execute([
          $tanggalAwal,
          'Aktif',
          $idCabang]);
       
        $dataCash=$sqlCash->fetch();
        $dataTransfer=$sqlTransfer->fetch();
        $dataPPN=$sqlPPN->fetch();       
        $dataSetor=$sqlSetor->fetch();

        $jumlah=$dataCash['totalCash']+$dataTransfer['totalTransfer']+$dataPPN['totalPPN'];
       ?>
      <tr>
        <td><?=wordwrap(ubahTanggalIndo($tanggalAwal),50,'<br>')?></td>
        <td>Rp <?=wordwrap(ubahToRp($dataCash['totalCash']),50,'<br>')?></td>
        <td>Rp <?=wordwrap(ubahToRp($dataTransfer['totalTransfer']),50,'<br>')?></td>
        <td>Rp <?=wordwrap(ubahToRp($jumlah),50,'<br>')?></td>
        <td>Rp <?=wordwrap(ubahToRp($dataSetor['totalSetor']+$dataTransfer['totalTransfer']-$dataTransfer['totalAdmin']-$dataTransfer['totalPPH']),50,'<br>')?></td>
      </tr>
        <?php
        $totalJumlah+=$jumlah;
        $totalTransfer+=($dataTransfer['totalTransfer']+$dataPPN['totalPPN']);
        $totalCash+=$dataCash['totalCash'];
        $totalSetor+=$dataSetor['totalSetor']+$dataTransfer['totalTransfer']-$dataTransfer['totalAdmin']-$dataTransfer['totalPPH'];
        $tanggalAwal=waktuBesok($tanggalAwal);

      }
       ?>
      <tr>
        <td>TOTAL</td>
        <td>Rp <?=ubahToRp($totalCash)?></td>
        <td>Rp <?=ubahToRp($totalTransfer)?></td>
        <td>Rp <?=ubahToRp($totalJumlah)?></td>
        <td>Rp <?=ubahToRp($totalSetor)?></td>
      </tr>
    </tbody>
  </table>
</div>
  <script>
  function doPrint() {
    window.print();
    window.onafterprint=function(event){
      window.close();
    };            
  }
  </script>
</body>
</html>