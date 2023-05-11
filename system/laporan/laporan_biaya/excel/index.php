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
  'laporan_biaya'
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

?>
<!DOCTYPE html>
<html>
<head>
	<title>Export Laporan Biaya</title>
<?php

extract($_REQUEST);

$rentang=explode(' - ',$rentang);
$tanggalAwal=konversiTanggal($rentang[0]);
$tanggalAkhir=konversiTanggal($rentang[1]);

$dataCabang=executeQueryUpdateForm('SELECT * FROM balistars_cabang where idCabang=?',$db,$idCabang);
if($dataCabang){
  $namaCabang=$dataCabang['namaCabang'];
}
else{
  $namaCabang="Semua Cabang";
}


  ?>
  <!-- Bootstrap 3.3.6 -->
</head>
<?php   

$filename = 'Laporan Biaya '.$namaCabang.' '.ubahTanggalIndo($tanggalAwal).'-'.ubahTanggalIndo($tanggalAkhir);
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=".$filename.".xls");
?>
<style type="text/css">
	body{
		font-family: sans-serif;
	}
	table{
		margin: 20px auto;
		border-collapse: collapse;
	}
	table td{
		border: 1px solid #3c3c3c;
		padding: 3px 8px;
 
	}
	a{
		background: blue;
		color: #fff;
		padding: 8px 10px;
		text-decoration: none;
		border-radius: 2px;
	}
</style>
<body>
 <table border="1">
  <tr>
    <th colspan="9" style="font-size: 17px; text-align: center;">Laporan Biaya <?=$namaCabang?> <br> <?=ubahTanggalIndo($tanggalAwal)?> - <?=ubahTanggalIndo($tanggalAkhir)?></th>
  </tr>
  <tr>
    <th rowspan="2" style="width: 5%;">No</th>
    <th rowspan="2">Tanggal Biaya </th>
    <th rowspan="2">No Nota </th>
    <th colspan="4" style="text-align: center;">Detail </th>
    <th rowspan="2">Grand Total </th>
    <?php 
    if($dataCekMenu['tipeA2']==1){
      ?>
    <th rowspan="2">A1/A2 <i class="fa fa-sort-alpha-up"></i></th>
    <?php
    } 
      ?>
  </tr>
  <tr>
    <th> Keterangan </th>
    <th>QTY </th>
    <th>Harga </th>
    <th>Subtotal </th>
  </tr>
  <tbody>
    <?php
      if($tipe=='Semua'){
          $parameter1 = ' AND balistars_biaya.tipeBiaya != ?';
        } else{
          $parameter1 = ' AND balistars_biaya.tipeBiaya = ?';
        }

        if($idCabang=='0'){
          $parameter2 = ' AND balistars_biaya.idCabang != ?';
        } else{
          $parameter2 = ' AND balistars_biaya.idCabang = ?';
        }

        $sqlBiaya  = $db->prepare('
          SELECT * FROM balistars_biaya 
          inner join balistars_cabang 
          on balistars_biaya.idCabang = balistars_cabang.idCabang 
          WHERE (balistars_biaya.tanggalBiaya between ? and ?)' . $parameter1 . $parameter2. 
          'and statusBiaya=? order by tanggalBiaya');
        $sqlBiaya->execute([
          $tanggalAwal,
          $tanggalAkhir,
          $tipe,
          $idCabang,
          'Aktif']);
        $dataBiaya = $sqlBiaya->fetchAll();
        $n = 1;
        $totalBiaya=0;
        foreach($dataBiaya as $row){
          $sqlDetail=$db->prepare('SELECT * FROM balistars_biaya_detail where noNota=? and statusCancel=? order by idBiayaDetail');
          $sqlDetail->execute([$row['noNota'],"oke"]);
          $hasilDetail=$sqlDetail->fetchAll();

          if(!$hasilDetail){
            $rowspan=1;
          }
          else{
            $rowspan=count($hasilDetail);
          }
        ?>
      <tr>
        <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=$n?></td>
        <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap(ubahTanggalIndo($row['tanggalBiaya']),50,'<br>')?></td>
        <td style="vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap($row['noNotaBiaya'],50,'<br>')?></td>
       <?php 
       $cek=1;
       if(!$hasilDetail){
        ?>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="text-align: right; vertical-align: top;"><?=wordwrap(ubahToRp($row['grandTotal']),50,'<br>')?></td>
        <?php 
        if($dataCekMenu['tipeA2']==1){
          ?>
          <td style="text-align: center; vertical-align: top;"><?=wordwrap($row['tipeBiaya'],50,'<br>')?></td>
        <?php
        } ?>
      </tr>
        <?php
       } 
       else{
        foreach($hasilDetail as $item){
          if($cek==1){
            ?>
            <td><?=$item['keterangan']?></td>
            <td><?=wordwrap(ubahToRp($item['qty']),50,'<br>')?></td>
            <td><?=wordwrap(ubahToRp($item['hargaSatuan']),50,'<br>')?></td>
            <td><?=wordwrap(ubahToRp($item['nilai']),50,'<br>')?></td>
            <td style="text-align: right; vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap(ubahToRp($row['grandTotal']),50,'<br>')?></td>
            <?php 
            if($dataCekMenu['tipeA2']==1){
              ?>
              <td style="text-align: center; vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap($row['tipeBiaya'],50,'<br>')?></td>
            <?php
            } ?>
          </tr>
              <?php
            }
            else{
              ?>
              <tr>
                <td><?=$item['keterangan']?></td>
                <td><?=wordwrap(ubahToRp($item['qty']),50,'<br>')?></td>
                <td><?=wordwrap(ubahToRp($item['hargaSatuan']),50,'<br>')?></td>
                <td><?=wordwrap(ubahToRp($item['nilai']),50,'<br>')?></td>
              </tr>
            <?php
            }
            $cek++;
          }
         }
        $totalBiaya=$totalBiaya+$row['grandTotal'];
        $n++;
      }
      ?>
      <tr>
        <td colspan="6"></td>
        <td style="text-align: right;">Total Biaya : </td>
        <td style="text-align: right;"><?=ubahToRp($totalBiaya)?></td>
        <td></td>
      </tr>
  </tbody>
</table>
</body>
</html>