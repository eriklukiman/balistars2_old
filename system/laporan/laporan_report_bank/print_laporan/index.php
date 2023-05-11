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
  'laporan_report_bank'
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
  <title>Print Report Bank</title>
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
$dataBank=executeQueryUpdateForm('SELECT * FROM balistars_bank where idBank=?',$db,$idBank);
?>
<body onload="doPrint()">
<div class="container">
 <table class="table table-bordered table-hover">
    <thead>
      <th colspan="9" style="font-size: 17px; text-align: center;">Laporan Kas Bank <?=$dataBank['namaBank']?> <br> <?=ubahTanggalIndo($tanggalAwal)?> - <?=ubahTanggalIndo($tanggalAkhir)?></th>
    </thead>
     <thead>
      <tr>
        <th>Tanggal </th>
        <th>Keterangan </th>
        <th>Debet</th>
        <th>Kredit</th>
        <th>Saldo</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      // **SQL DEBET**
      $sqlDebetAwal1=$db->prepare('SELECT SUM(jumlahSetor) as debetAwal 
        from balistars_setor_penjualan_cash 
        where (tanggalSetor between ? and ?) 
        and idBank=? 
        and statusFinal=? 
        and statusSetor=?');
      $sqlDebetAwal1->execute([
        $tanggalAwalSekali, $tanggalKemarin, 
        $idBank, 
        "Final",
        "Aktif"]);
      $dataDebetAwal1=$sqlDebetAwal1->fetch();

      $sqlDebetAwal2=$db->prepare('SELECT SUM(jumlahSetor) as debetAwal 
        from balistars_kas_kecil_setor 
        where (tanggalSetor between ? and ?) 
        and idBank=? 
        and statusFinal=? 
        and statusKasKecilSetor=?');
      $sqlDebetAwal2->execute([
        $tanggalAwalSekali, $tanggalKemarin, 
        $idBank, 
        "Final", 
        "Aktif"]);
      $dataDebetAwal2=$sqlDebetAwal2->fetch();

      $sqlDebetAwal3=$db->prepare('SELECT SUM(nilaiTransfer) as debetAwal 
        from balistars_bank_transfer 
        where (tanggalTransfer between ? and ?) 
        and idBankTujuan=? 
        and statusTransfer=?');
      $sqlDebetAwal3->execute([
        $tanggalAwalSekali, $tanggalKemarin, 
        $idBank, 
        "final"]);
      $dataDebetAwal3=$sqlDebetAwal3->fetch();

      $sqlDebetAwal4=$db->prepare('SELECT (SUM(jumlahPembayaran)-SUM(biayaAdmin)-SUM(PPH)) as debetAwal 
        from balistars_piutang 
        inner join balistars_penjualan 
        on balistars_penjualan.noNota=balistars_piutang.noNota 
        where (balistars_piutang.tanggalPembayaran between ? and ?) 
        and balistars_piutang.bankTujuanTransfer=? 
        and statusPenjualan=?');
      $sqlDebetAwal4->execute([
        $tanggalAwalSekali,$tanggalKemarin,
        $idBank,
        "Aktif"]);
      $dataDebetAwal4=$sqlDebetAwal4->fetch();

      $sqlDebetAwal5=$db->prepare('SELECT SUM(nilai) as debetAwal 
        from balistars_pemasukan_lain 
        where (tanggalPemasukanLain between ? and ?) 
        and idBank=? 
        and statusFinal=? 
        and statusPemasukanLain=?');
      $sqlDebetAwal5->execute([
        $tanggalAwalSekali, $tanggalKemarin, 
        $idBank, 
        "final", 
        "Aktif"]);
      $dataDebetAwal5=$sqlDebetAwal5->fetch();


      $sqlDebetAwal6=$db->prepare('SELECT SUM(dpp+ppn) as debetAwal 
        from balistars_penjualan_mesin 
        where (tanggalPenjualan between ? and ?) 
        and idBank=? 
        and statusPenjualanMesin=?');
      $sqlDebetAwal6->execute([
        $tanggalAwalSekali,$tanggalKemarin,
        $idBank,
        "Aktif"]);
      $dataDebetAwal6=$sqlDebetAwal6->fetch();


      // **SQL KREDIT**
      $sqlKreditAwal1=$db->prepare('SELECT SUM(nilaiTransfer) as kreditAwal 
        from balistars_bank_transfer 
        where (tanggalTransfer between ? and ?) 
        and idBankAsal=? 
        and statusTransfer=?');
      $sqlKreditAwal1->execute([
        $tanggalAwalSekali, $tanggalKemarin, 
        $idBank, 
        "final"]);
      $dataKreditAwal1=$sqlKreditAwal1->fetch();

      $sqlKreditAwal2=$db->prepare('SELECT SUM(nilaiApproved) as kreditAwal 
        from balistars_kas_kecil_order 
        where (tanggalOrder between ? and ?) 
        and bankAsalTransfer=? 
        and statusApproval=? 
        and statusKasKecilOrder=?');
      $sqlKreditAwal2->execute([
        $tanggalAwalSekali, $tanggalKemarin, 
        $idBank, 
        "approved", 
        "Aktif"]);
      $dataKreditAwal2=$sqlKreditAwal2->fetch();

      // $sqlKreditAwal3=$db->prepare('SELECT SUM(jumlahPembayaran) as kreditAwal 
      //   from  balistars_hutang 
      //   inner join balistars_pembelian 
      //   on balistars_pembelian.noNota=balistars_hutang.noNota 
      //   where bankAsalTransfer=? 
      //   and balistars_pembelian.idSupplier!=? 
      //   and (tanggalCair between ? and ?) 
      //   and balistars_pembelian.statusPembelian=? 
      //   and statusHutang=?');
      // $sqlKreditAwal3->execute([
      //   $idBank,
      //   0,
      //   $tanggalAwalSekali,$tanggalKemarin,
      //   "Lunas",
      //   "Aktif"]);
      // $dataKreditAwal3=$sqlKreditAwal3->fetch();

      $sqlKreditAwal3 = $db->prepare('SELECT SUM(dp) as kreditAwal 
        from  balistars_dpgiro 
        where idBank=? 
        and (tanggalCairDp between ? and ?) 
        and jenisGiro=? ');
      $sqlKreditAwal3->execute([
        $idBank, 
        $tanggalAwalSekali, $tanggalKemarin, 
        'Pelunasan']);
      $dataKreditAwal3 = $sqlKreditAwal3->fetch();

      $sqlKreditAwal4=$db->prepare('SELECT SUM(jumlahPembayaran) as kreditAwal 
        from balistars_hutang_mesin 
        where (tanggalPembayaran between ? and ?) 
        and bankAsalTransfer=? 
        and jenisPembayaran=? 
        and statusCair=?');
      $sqlKreditAwal4->execute([
        $tanggalAwalSekali,$tanggalKemarin,
        $idBank,
        "Giro",
        "Cair"]);
      $dataKreditAwal4=$sqlKreditAwal4->fetch();

      // $sqlKreditAwal5=$db->prepare('SELECT SUM(nilai) as kreditAwal 
      //   from balistars_biaya_cabang 
      //   where (tanggalBiaya between ? and ?) 
      //   and idBank=?');
      // $sqlKreditAwal5->execute([$tanggalAwalSekali,$tanggalKemarin,$idBank]);
      // $dataKreditAwal5=$sqlKreditAwal5->fetch();

      $sqlKreditAwal6=$db->prepare('SELECT SUM(nilai) as kreditAwal 
        from balistars_pengeluaran_lain 
        where (tanggalPengeluaranLain between ? and ?) 
        and idBank=? 
        and statusFinal=? 
        and statusPengeluaranLain=?');
      $sqlKreditAwal6->execute([
        $tanggalAwalSekali,$tanggalKemarin,
        $idBank,
        "final",
        "Aktif"]);
      $dataKreditAwal6=$sqlKreditAwal6->fetch();

      // $sqlKreditAwal7=$db->prepare('SELECT SUM(nilaiDisetujui) as kreditAwal 
      //   from balistars_advertising_rab where (tanggalPengajuan between ? and ?) and idBankTransfer=? and statusRAB=?');
      // $sqlKreditAwal7->execute([$tanggalAwalSekali,$tanggalKemarin,$idBank,"Disetujui"]);
      // $dataKreditAwal7=$sqlKreditAwal7->fetch();

      $sqlKreditAwal8=$db->prepare('SELECT SUM(jumlahPembayaran) as kreditAwal 
        from balistars_hutang_gedung_pembayaran 
        where (tanggalPembayaran between ? and ?) 
        and bankAsalTransfer=? 
        and jenisPembayaran=? 
        and statusCair=? 
        and statusPembayaranHutangGedung=?');
      $sqlKreditAwal8->execute([
        $tanggalAwalSekali,$tanggalKemarin,
        $idBank,
        "Giro",
        "Cair",
        "Aktif"]);
      $dataKreditAwal8=$sqlKreditAwal8->fetch();

      $sqlKreditAwal9 = $db->prepare('SELECT SUM(dp) as kreditAwal 
        from  balistars_dpgiro 
        where idBank=? 
        and (tanggalCairDp between ? and ?) 
        and jenisGiro=? ');
      $sqlKreditAwal9->execute([
        $idBank, 
        $tanggalAwalSekali, 
        $tanggalKemarin, 
        'DP']);
      $dataKreditAwal9 = $sqlKreditAwal9->fetch();

      $saldo[0]=$dataDebetAwal1['debetAwal']
                +$dataDebetAwal2['debetAwal']
                +$dataDebetAwal3['debetAwal']
                +$dataDebetAwal4['debetAwal']
                +$dataDebetAwal5['debetAwal']
                +$dataDebetAwal6['debetAwal']
                -$dataKreditAwal1['kreditAwal']
                -$dataKreditAwal2['kreditAwal']
                -$dataKreditAwal3['kreditAwal']
                -$dataKreditAwal4['kreditAwal']
                -$dataKreditAwal6['kreditAwal']
                -$dataKreditAwal8['kreditAwal']
                - $dataKreditAwal9['kreditAwal'];
      $saldo[1]=0;
      $saldo[2]=0;
      ?>
      <tr>
        <td colspan="2">Saldo Awal</td>
        <td><strong>-</strong></td>
        <td><strong>-</strong></td>
        <td><strong><?=ubahToRp($saldo[0])?></strong></td>
      </tr>
      <?php
      for($i=0; $i<=$selisih; $i++){
        $saldo=debetKasBesar($idBank,$sekarang,$saldo,$db);
        $saldo=kreditKasBesar($idBank,$sekarang,$saldo,$db);
        $sekarang=waktuBesok($sekarang);
      }
       ?>
      <tr>
        <td></td>
        <td></td>
        <td><strong>Total Debet</strong></td>
        <td><strong>Total Kredit</strong></td>
        <td><strong>Saldo Akhir</strong></td>
      </tr>
      <tr>
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