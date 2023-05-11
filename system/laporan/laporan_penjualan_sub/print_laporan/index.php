<?php
include_once '../../../../library/konfigurasiurl.php';
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
  'laporan_penjualan_sub'
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="css/custom.css">
  <title>laporan penjualan Sub</title>
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/toastr/toastr.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/select2/select2.css"> 
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/vendor/datepicker/datepicker.min.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/main2.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/color_skins.css">
  <link rel="stylesheet" href="<?=$BASE_URL_HTML?>/assets/css/loader.css">
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
      <th colspan="15" style="font-size: 17px; text-align: center;">Laporan Penjualan Sub <?=$namaCabang?> <br> <?=ubahTanggalIndo($tanggalAwal)?> - <?=ubahTanggalIndo($tanggalAkhir)?></th>
    </thead>
     <thead>
      <tr>
        <th style="width: 5%;">No</th>
        <th>Tanggal Penjualan </th>
        <th>No Nota </th>
        <th>Nama Customer </th>
        <th>Nama Projek </th>
        <th>Harga</th>
        <th>Tanggal Pembayaran</th>
        <th>Biaya</th>
        <th>Nama Supplier</th>
        <th>Keterangan</th>
        <th>Profit</th>
        <th>Rasio</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        if($idCabang==0){
          $sql=$db->prepare('SELECT SUM(nilaiPembayaran) as jumlahPembayaran, idPenjualanDetail 
            FROM balistars_biaya_sub 
            where (tanggalPembayaran between ? and ?) 
            and statusBiayaSub=? 
            group by idPenjualanDetail');
          $sql->execute([
            $tanggalAwal,$tanggalAkhir,
            'Aktif']);
        }
        else{
          $sql=$db->prepare('SELECT SUM(nilaiPembayaran) as jumlahPembayaran, idPenjualanDetail 
            FROM balistars_biaya_sub 
            where (tanggalPembayaran between ? and ?) 
            and idCabang=? 
            and statusBiayaSub=? 
            group by idPenjualanDetail');
          $sql->execute([
            $tanggalAwal,$tanggalAkhir,
            $idCabang,
            'Aktif']);
        }
        $hasil=$sql->fetchAll();

        $totalNilaiPenjualan=0; 
        $totalNilaiPembayaran=0;
        $totalProfit=0;
        $n=1;
        foreach($hasil as $data){
          $sqlCustomer=$db->prepare('SELECT * FROM balistars_penjualan_detail 
            inner join balistars_penjualan 
            on balistars_penjualan.noNota=balistars_penjualan_detail.noNota 
            where balistars_penjualan_detail.idPenjualanDetail=?');
          $sqlCustomer->execute([$data['idPenjualanDetail']]);
          $dataCustomer=$sqlCustomer->fetch();

          if($dataCustomer['statusCancel']!='ok'){
            $dataCustomer['nilai']=0;
          }

          $subTotal=0;  
          $sqlSub=$db->prepare('SELECT * FROM balistars_biaya_sub where idPenjualanDetail=? and (tanggalPembayaran between ? and ?)');
          $sqlSub->execute([$data['idPenjualanDetail'],$tanggalAwal,$tanggalAkhir]);
          $dataSub=$sqlSub->fetchAll();

          if(!$dataSub){
            $rowspan=1;
          }
          else{
            $rowspan=count($dataSub);
          }

          ?>
        <tr>
          <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=$n?></td>
          <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap(ubahTanggalIndo($dataCustomer['tanggalPenjualan']),50,'<br>')?></td>
          <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap($dataCustomer['noNota'],50,'<br>')?></td>
          <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap($dataCustomer['namaCustomer'],50,'<br>')?></td>
          <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap($dataCustomer['namaBahan'],50,'<br>')?></td>
          <td style="vertical-align: top;" rowspan="<?=$rowspan?>">Rp <?=wordwrap(ubahToRp($dataCustomer['nilai']),50,'<br>')?></td>
         <?php 
         $cek=1;
         if(!$dataSub){
          ?>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td style="text-align: right; vertical-align: top;">Rp <?=wordwrap(ubahToRp(0),50,'<br>')?></td>
          <td style="text-align: center; vertical-align: top;"><?=wordwrap(0,50,'<br>')?></td>

        </tr>
          <?php
         } 
         else{
          foreach($dataSub as $item){
            if($cek==1){
              ?>
              <td><?=wordwrap(ubahTanggalIndo($item['tanggalPembayaran']),25,'<br>')?></td>
              <td>Rp <?=wordwrap(ubahToRp($item['nilaiPembayaran']),50,'<br>')?></td>
              <td><?=wordwrap($item['namaSupplier'],50,'<br>')?></td>
              <td><?=wordwrap($item['keterangan'],50,'<br>')?></td>

              <td style="text-align: right; vertical-align: top;" rowspan="<?=$rowspan?>">Rp <?=wordwrap(ubahToRp($dataCustomer['nilai']-$data['jumlahPembayaran']),50,'<br>')?></td>
              <td style="text-align: right; vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap(ubahToRp(($dataCustomer['nilai']-$data['jumlahPembayaran'])/$dataCustomer['nilai']*100),50,'<br>')?>%</td>
            </tr>
              <?php
            }
            else{
              ?>
              <tr>
                <td><?=wordwrap(ubahTanggalIndo($item['tanggalPembayaran']),25,'<br>')?></td>
                <td>Rp <?=wordwrap(ubahToRp($item['nilaiPembayaran']),50,'<br>')?></td>
                <td><?=wordwrap($item['namaSupplier'],50,'<br>')?></td>
                <td><?=wordwrap($item['keterangan'],50,'<br>')?></td>
              </tr>
            <?php
            }
            $cek++;
            $subTotal+=$item['nilaiPembayaran'];
          }
         }
        $totalNilaiPenjualan=$totalNilaiPenjualan+$dataCustomer['nilai'];
        $totalNilaiPembayaran=$totalNilaiPembayaran+$subTotal;
        $totalProfit=$totalProfit+$dataCustomer['nilai']-$subTotal;
        $n++;
      }
      ?>
      <tr>
        <td colspan="5" style="text-align: center;">Grand Total</td>
        <td>Rp <?=ubahToRp($totalNilaiPenjualan)?></td>
        <td></td>
        <td colspan="3">Rp <?=ubahToRp($totalNilaiPembayaran)?></td>
        <td style="font-weight: bold;">Rp <?=ubahToRp($totalProfit)?></td>
        <?php 
        if($totalNilaiPenjualan==0){
          ?>
          <td>0</td>
          <?php
        } else{
          ?>
        <td><?=round($totalProfit/$totalNilaiPenjualan*100)?>%</td>
        <?php 
        } ?>
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