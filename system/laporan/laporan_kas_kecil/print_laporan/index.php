<?php
include_once '../../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/system/fungsinavigasi.php';
include_once '../fungsidebetkredit.php';

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
  'laporan_kas_kecil'
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
$rentang=explode(' - ',$rentang);
$tanggalAwal=konversiTanggal($rentang[0]);
$tanggalAkhir=konversiTanggal($rentang[1]);
$tanggalAwalSekali="2019-01-01"; 
$sekarang = $tanggalAwal;
$tanggalKemarin=waktuKemarin($tanggalAwal);
$selisih=selisihTanggal($tanggalAwal, $tanggalAkhir);

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
      <th colspan="9" style="font-size: 17px; text-align: center;">Laporan Kas Kecil <?=$namaCabang?> <br> <?=ubahTanggalIndo($tanggalAwal)?> - <?=ubahTanggalIndo($tanggalAkhir)?></th>
    </thead>
     <thead>
      <tr>
        <th>Tanggal </th>
        <th>Jurnal </th>
        <th>Referensi</th>
        <th>Keterangan </th>
        <th>Debet</th>
        <th>Kredit</th>
        <th>Saldo</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      if($idCabang==0){
        $parameter1 = ' and idCabang !=?';
        $parameter2 = ' and balistars_pembelian_mesin.idCabang !=?';
      } else {
        $parameter1 = ' and idCabang =?';
        $parameter2 = ' and balistars_pembelian_mesin.idCabang !=?';
      }

      //Menghitung Saldo Awal
      $sqlDebetAwal1  =$db->prepare('SELECT SUM(nilaiApproved) as debetAwal 
        from balistars_kas_kecil_order 
        where (tanggalOrder between ? and ?) 
        and statusApproval=? 
        and statusKasKecilOrder=?'
        .$parameter1);
      $sqlDebetAwal1->execute([
        $tanggalAwalSekali,
        $tanggalKemarin,
        'approved',
        'Aktif',
        $idCabang]);
      $dataDebetAwal1=$sqlDebetAwal1->fetch();

      $sqlDebetAwal2  =$db->prepare('SELECT SUM(nilai) as debetAwal 
        from balistars_cabang_cash_kecil 
        where (tanggalCabangCashKecil between ? and ?)
        and statusFinal=?'
        .$parameter1);
      $sqlDebetAwal2->execute([
        $tanggalAwalSekali,
        $tanggalKemarin,
        'Final',
        $idCabang]);
      $dataDebetAwal2=$sqlDebetAwal2->fetch();

      $sqlKreditAwal1 =$db->prepare('SELECT SUM(grandTotal) as kreditAwal 
        from balistars_biaya 
        where (tanggalBiaya between ? and ?) 
        and statusBiaya=?'
        .$parameter1);
      $sqlKreditAwal1->execute([
        $tanggalAwalSekali,
        $tanggalKemarin,
        'Aktif',
        $idCabang]);
      $dataKreditAwal1=$sqlKreditAwal1->fetch();

      $sqlKreditAwal2 =$db->prepare('SELECT SUM(grandTotal) as kreditAwal 
        from balistars_pembelian 
        where (tanggalPembelian between ? and ?) 
        and idSupplier=? 
        and status=?'
        .$parameter1);
      $sqlKreditAwal2->execute([
        $tanggalAwalSekali,
        $tanggalKemarin,
        0,
        'Aktif',
        $idCabang]);
      $dataKreditAwal2=$sqlKreditAwal2->fetch();

      $sqlKreditAwal3=$db->prepare('SELECT SUM(jumlahSetor) as kreditAwal 
        from balistars_kas_kecil_setor 
        where (tanggalSetor between ? and ?) 
        and statusKasKecilSetor=?'
        .$parameter1);
      $sqlKreditAwal3->execute([
        $tanggalAwalSekali,
        $tanggalKemarin,
        'Aktif',
        $idCabang]);
      $dataKreditAwal3=$sqlKreditAwal3->fetch();

      $sqlKreditAwal4 =$db->prepare('SELECT SUM(grandTotal) as kreditAwal 
        from balistars_pembelian_mesin 
        inner join balistars_hutang_mesin 
        on balistars_pembelian_mesin.noNota=balistars_hutang_mesin.noNota 
        where (balistars_pembelian_mesin.tanggalPembelian between ? and ?) 
        and balistars_hutang_mesin.bankAsalTransfer=? 
        and balistars_hutang_mesin.jenisPembayaran=? 
        and statusPembelianMesin=?'
        .$parameter2);
      $sqlKreditAwal4->execute([
        $tanggalAwalSekali,
        $tanggalKemarin,
        0,
        'Cash',
        'Aktif',
        $idCabang]);
      $dataKreditAwal4=$sqlKreditAwal4->fetch();

      $saldo[0]=$dataDebetAwal1['debetAwal']
                +$dataDebetAwal2['debetAwal']
                -$dataKreditAwal1['kreditAwal']
                -$dataKreditAwal2['kreditAwal']
                -$dataKreditAwal3['kreditAwal']
                -$dataKreditAwal4['kreditAwal'];
      $saldo[1]=0;
      $saldo[2]=0;
      ?>
      <tr>
        <td>-</td>
        <td>-</td>
        <td>-</td>
        <td>Saldo Awal</td>
        <td><strong>-</strong></td>
        <td><strong>-</strong></td>
        <td><strong><?=ubahToRp($saldo[0])?></strong></td>
      </tr>
      <?php 
      for($i=0; $i<=$selisih; $i++){
        $saldo=debetKasKecil($idCabang,$sekarang,$saldo,$db);
        $saldo=kreditKasKecil($idCabang,$sekarang,$saldo,$db);
        $sekarang=waktuBesok($sekarang);
      }
       ?>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><strong>Total Debet</strong></td>
        <td><strong>Total Kredit</strong></td>
        <td><strong>Saldo Akhir</strong></td>
      </tr>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><strong><?=ubahToRp($saldo[1])?></strong></td>
        <td><strong><?=ubahToRp($saldo[2])?></strong></td>
        <td><strong><?=ubahToRp($saldo[0])?></strong></td>
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