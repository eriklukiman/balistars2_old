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
  'form_kasir'
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

$namaPegawai = explode(' ', $dataLogin['namaPegawai']);

  $sqlPenjualan=$db->prepare('SELECT *, balistars_penjualan.idUser as idUserAsli, balistars_penjualan.idCabang as idCabangAsli FROM balistars_penjualan left join balistars_pegawai on balistars_penjualan.idDesigner=balistars_pegawai.idPegawai where noNota=?');
  $sqlPenjualan->execute([$noNota]);
  $dataPenjualan=$sqlPenjualan->fetch();

  $dataPiutang=executeQueryUpdateForm('SELECT * FROM balistars_piutang where noNota=? order by idPiutang',$db,$noNota);
  $dataCabang=executeQueryUpdateForm('SELECT * FROM balistars_cabang where idCabang=?',$db,$dataPenjualan['idCabangAsli']);
  $dataKasir=executeQueryUpdateForm('SELECT * FROM balistars_pegawai inner join balistars_jabatan on balistars_pegawai.idJabatan=balistars_jabatan.idJabatan inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_pegawai.idPegawai=?',$db,$dataLogin['idPegawai']);
  $dataUser=executeQueryUpdateForm('SELECT * FROM balistars_user where idUser=?',$db,$dataPenjualan['idUserAsli']);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="css/custom.css">
        <title>Receipt</title>
    </head>
    <body onload="doPrint()">
  <?php
  $queryBarang = $db->prepare("SELECT * FROM balistars_penjualan_detail WHERE noNota=? and statusCancel=?");
  $queryBarang->execute([$noNota, 'ok']);
  $dataBarang = $queryBarang->fetchAll();

  $penutup = "Terimakasih dan Selamat jalan";
  $tanggalSekarang = date("Y-m-d H:i:s");
  $namaStore = "Bali Stars Promosindo";
  $alamatStore  = $dataCabang['alamatCabang'];
  $noTelp  = $dataCabang['noTelpCabang'];
  $kota = $dataCabang['kota'];
  $provinsiStore ="";
  ?>
  <div class="container">
    <div class="row">
      <div class="col-xs-12">
        <!--<img src="../images/logo.png" style="width: 20%">-->
        <?php 
        if($dataPenjualan['tipePenjualan']=='A1'){
         ?>
         <div >
            <?=$namaStore?> <br>
            <?=$alamatStore?> <br>
            Tlp. <?=$noTelp?>
          </div>
         <?php 
       }
          ?>
              
          <?php
          $stylePage="top: 0px;";
          $styleButtom="top: 0px;";
        
        ?>
        <div style="position: relative; <?=$stylePage?> width: 400px;" >
          Kepada Yth : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$kota?> <?=tanggalTerbilang($dataPenjualan['tanggalPenjualan'])?><br>
          <?=$dataPenjualan['namaCustomer']?>/<?=$dataPenjualan['noTelpCustomer']?><br>
          * Nota Penjualan * No. <?=$noNota?><br>
          * Designer * <?=$dataPenjualan['namaPegawai']?>
          <div style="position: relative; top: 0px;">_________________________________________</div>
        </div>
        <table style="position: relative; <?=$stylePage?>">
          <thead>
            <th style="text-align: left;">No / Nama Barang / Ukuran</th>
            <th>Nilai (Rp)</th>
          </thead>
          <tbody>
            <?php
            $totalGeneral=0;
            $n=1;
            foreach($dataBarang as $row){
              ?>
              <tr>
                <td><?=$n?>. <?=$row['namaBahan']?>
                <?php
                if($row['jenisOrder']=="Indoor" || $row['jenisOrder']=="Outdoor" || $row['jenisOrder']=="UV"){
                  ?>
                  <?=$row['ukuran']?>
                  <?php
                }
                ?>
                  <br>
                  &nbsp;&nbsp;&nbsp;<?=$row['qty']?> X <?=number_format($row['hargaSatuan'])?>
                </td>
                <td style="vertical-align:bottom; text-align: right;"><?=number_format($row['nilai'])?></td>
              </tr>
              <?php
              $n++;
            }
            ?>
            <tr>
              <td colspan="2">_________________________________________</td>
            </tr>
           
            <?php
              if($dataPenjualan['jenisPPN']=="Exclude"){
              ?>
              <tr>
                        <td style="vertical-align:bottom; text-align: right;">DPP :</td>
                        <td style="vertical-align:bottom; text-align: right;"><?=ubahToRp($dataPenjualan['grandTotal']-$dataPenjualan['nilaiPPN'])?></td>
                      </tr>
                      <tr>
                        <td style="vertical-align:bottom; text-align: right;">PPN :</td>
                        <td style="vertical-align:bottom; text-align: right;"><?=ubahToRp($dataPenjualan['nilaiPPN'])?></td>
                      </tr>
                      <?php  
                      }
                      ?>
                      <tr>
                        <td style="vertical-align:bottom; text-align: right;">Nilai Total :</td>
                        <td style="vertical-align:bottom; text-align: right;"><?=ubahToRp($dataPenjualan['grandTotal'])?></td>
              
            <tr>
              <td style="vertical-align:bottom; text-align: right;">DP1 Tanggal <?=$dataPenjualan['tanggalPenjualan']?> :</td>
              <td style="vertical-align:bottom; text-align: right;"><?=ubahToRp($dataPenjualan['jumlahPembayaranAwal'])?></td>
            </tr>
            <tr>
              <?php 
              $sisaPembayaran = $dataPenjualan['grandTotal']-$dataPenjualan['jumlahPembayaranAwal'];
              if($sisaPembayaran<0){
                $sisaPembayaran = 0;
              }
               ?>
              
              <td style="vertical-align:bottom; text-align: right;">Sisa Pembayaran :</td>
              <td style="vertical-align:bottom; text-align: right;"><?=ubahToRp($sisaPembayaran)?></td>
            </tr>
          </tbody>
        </table>
        <table style="position: relative; <?=$styleButtom?>">
          <tbody>
            <tr>
              <td style="width: 400px;">
                <br>
                Penerima:________
              </td>
              <td style="width: 800px;">
                <br>
                Admin: <?=$dataUser['userName']?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <script>
  function doPrint() {
    window.print();
    window.onafterprint=function(event){
      window.close();
    };            
  }
  </script>
 <!--  <script>
    function doPrint(url) {
      window.print();
      window.onafterprint=function(event){
         window.location.href=url;
      };            
    }
  </script> -->
</body>
</html>