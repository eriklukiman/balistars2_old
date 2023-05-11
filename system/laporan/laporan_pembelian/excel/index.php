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
  'laporan_pembelian'
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
	<title>Export Laporan Pembelian</title>
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

$filename = 'Laporan Pembelian '.$namaCabang.' '.ubahTanggalIndo($tanggalAwal).'-'.ubahTanggalIndo($tanggalAkhir);
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
    <th colspan="12" style="font-size: 17px; text-align: center;">Laporan Pembelian <?=$namaCabang?> <br> <?=ubahTanggalIndo($tanggalAwal)?> - <?=ubahTanggalIndo($tanggalAkhir)?></th>
  </tr>
  <tr>
    <th rowspan="2" style="width: 5%;">No</th>
    <th rowspan="2">Tanggal </th>
    <th rowspan="2">No Nota </th>
    <th rowspan="2">No Nota Vendor</th>
    <th rowspan="2">Supplier </th>
    <th colspan="4" style="text-align: center;">Detail </th>
    <th rowspan="2">Grand Total </th>
    <th rowspan="2">DPP/PPN </th>
    <!-- <?php 
    if($dataCekMenu['tipeA2']==1){
      ?> -->
    <th rowspan="2">A1/A2 <i class="fa fa-sort-alpha-up"></i></th>
    <!-- <?php
    } 
      ?> -->
  </tr>
  <tr>
    <th> Nama Barang </th>
    <th>QTY </th>
    <th>Harga </th>
    <th>Subtotal </th>
  </tr>
  <tbody>
    <?php 
        if($jenisPembelian=='Kredit'){
          if($idCabang==0){
            $parameter1 =' and balistars_pembelian.idCabang !=?';
          } else{
            $parameter1 =' and balistars_pembelian.idCabang =?';
          }
          if($idSupplier==0){
            $parameter2 =' and balistars_pembelian.idSupplier !=?';
          } else{
            $parameter2 =' and balistars_pembelian.idSupplier =?';
          }
          if($tipe=='Semua'){
            $parameter3 =' and balistars_pembelian.tipePembelian !=?';
          } else{
            $parameter3 =' and balistars_pembelian.tipePembelian =?';
          }

          $sql=$db->prepare('SELECT * FROM balistars_pembelian 
            inner join balistars_cabang 
            on balistars_pembelian.idCabang=balistars_cabang.idCabang 
            where (balistars_pembelian.tanggalPembelian between ? and ?)'
            . $parameter1 
            . $parameter2
            . $parameter3 . 
            'and status = ? order by  balistars_pembelian.tanggalPembelian');
          $sql->execute([
            $tanggalAwal,$tanggalAkhir,
            $idCabang,
            $idSupplier,
            $tipe,
            'Aktif']);
          $hasil=$sql->fetchAll();
        }
        elseif($jenisPembelian=='Cash'){
          if($idCabang==0){
            $parameter1 =' and balistars_pembelian.idCabang !=?';
          } else{
            $parameter1 =' and balistars_pembelian.idCabang =?';
          }
          if($tipe=='Semua'){
            $parameter2 =' and balistars_pembelian.tipePembelian !=?';
          } else{
            $parameter2 =' and balistars_pembelian.tipePembelian =?';
          }

          $sql=$db->prepare('SELECT * FROM balistars_pembelian 
            inner join balistars_cabang 
            on balistars_pembelian.idCabang=balistars_cabang.idCabang 
            where (balistars_pembelian.tanggalPembelian between ? and ?)
            and balistars_pembelian.idSupplier =?'
            . $parameter1 
            . $parameter2 .
            'and status = ? order by  balistars_pembelian.tanggalPembelian');
          $sql->execute([
            $tanggalAwal,$tanggalAkhir,
            0,
            $idCabang,
            $tipe,
            'Aktif']);
          $hasil=$sql->fetchAll();
        }
        else{
          if($idSupplier==0){
            if($idCabang==0){
            $parameter1 =' and balistars_pembelian.idCabang !=?';
            } else{
              $parameter1 =' and balistars_pembelian.idCabang =?';
            }
            if($tipe=='Semua'){
              $parameter2 =' and balistars_pembelian.tipePembelian !=?';
            } else{
              $parameter2 =' and balistars_pembelian.tipePembelian =?';
            }

            $sql=$db->prepare('SELECT * FROM balistars_pembelian 
              inner join balistars_cabang 
              on balistars_pembelian.idCabang=balistars_cabang.idCabang 
              where (balistars_pembelian.tanggalPembelian between ? and ?)'
              . $parameter1 
              . $parameter2 .
              'and status = ? order by  balistars_pembelian.tanggalPembelian');
            $sql->execute([
              $tanggalAwal,$tanggalAkhir,
              $idCabang,
              $tipe,
              'Aktif']);
            $hasil=$sql->fetchAll();
          } else{
            if($idCabang==0){
              $parameter1 =' and balistars_pembelian.idCabang !=?';
            } else{
              $parameter1 =' and balistars_pembelian.idCabang =?';
            }
            if($tipe=='Semua'){
              $parameter2 =' and balistars_pembelian.tipePembelian !=?';
            } else{
              $parameter2 =' and balistars_pembelian.tipePembelian =?';
            }

            $sql=$db->prepare('SELECT * FROM balistars_pembelian 
              inner join balistars_cabang 
              on balistars_pembelian.idCabang=balistars_cabang.idCabang 
              where (balistars_pembelian.tanggalPembelian between ? and ?)
              and balistars_pembelian.idSupplier =?'
              . $parameter1 
              . $parameter2 .
              'and status = ? order by  balistars_pembelian.tanggalPembelian');
            $sql->execute([
              $tanggalAwal,$tanggalAkhir,
              $idSupplier,
              $idCabang,
              $tipe,
              'Aktif']);
            $hasil=$sql->fetchAll();
          }
        }

        $n = 1;
        $grandTotal=0;
        $ppn=0;
        foreach($hasil as $row){
          $grandTotal=$grandTotal+$row['grandTotal'];
          $ppn=$ppn+$row['nilaiPPN'];

          $sqlDetail=$db->prepare('SELECT * FROM balistars_pembelian_detail where noNota=? and statusCancel=? order by idPembelianDetail');
          $sqlDetail->execute([$row['noNota'],'oke']);
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
        <td rowspan="<?=$rowspan?>" style="vertical-align: top;"><?=ubahTanggalIndo($row['tanggalPembelian'])?></td>
        <td rowspan="<?=$rowspan?>" style="vertical-align: top;"><?=wordwrap($row['noNota'],50,'<br>')?></td>
        <td rowspan="<?=$rowspan?>" style="vertical-align: top;"><?=wordwrap($row['noNotaVendor'],50,'<br>')?></td>
        <td rowspan="<?=$rowspan?>" style="vertical-align: top;"><?=$row['namaSupplier']?></td>
        <?php 
        $cek=1;
        if(!$hasilDetail){
         ?>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td rowspan="<?=$rowspan?>" style="vertical-align: top;"><?=$row['grandTotal']?></td>
          <td rowspan="<?=$rowspan?>" style="vertical-align: top;"><?=ubahToRp($row['grandTotal']-$row['nilaiPPN']).' / '.ubahToRp($row['nilaiPPN'])?></td>
           <?php 
              if($dataCekMenu['tipeA2']==1){
                ?>
                <td style="text-align: center; vertical-align: top;"><?=wordwrap($row['tipePembelian'],50,'<br>')?></td>
              <?php
              } ?>
        </tr>
           <?php 
          }
          else{
            foreach($hasilDetail as $item){
              if($cek==1){
            ?> 
                <td><?=wordwrap($item['jenisOrder'].'/'.$item['namaBarang'],30,'<br>')?></td>
                <td><?=$item['qty']?></td>
                <td><?=ubahtoRp($item['hargaSatuan'])?></td>
                <td><?=ubahToRp($item['nilai'])?></td>
                <td rowspan="<?=$rowspan?>" style="vertical-align: top;"><?=ubahToRp($row['grandTotal'])?></td>
                <td rowspan="<?=$rowspan?>" style="vertical-align: top;"><?=ubahToRp($row['grandTotal']-$row['nilaiPPN']).' / '.ubahToRp($row['nilaiPPN'])?></td> 
                 <?php 
              if($dataCekMenu['tipeA2']==1){
                ?>
                <td style="text-align: center; vertical-align: top;" rowspan="<?=$rowspan?>"><?=wordwrap($row['tipePembelian'],50,'<br>')?></td>
              <?php
              } ?>
              </tr>
            <?php 
              }
              else{
                ?>
                <tr>
                  <td><?=wordwrap($item['jenisOrder'].'/'.$item['namaBarang'],30,'<br>')?></td>
                  <td><?=$item['qty']?></td>
                  <td><?=ubahtoRp($item['hargaSatuan'])?></td>
                  <td><?=ubahToRp($item['nilai'])?></td>
                </tr>
                <?php
              }
              $cek++;
            }
             ?>
        <?php     
          }
        $n++;
      }
      ?>  
      <tr>
        <td colspan="9" style="text-align: right;"> Total</td>
        <td style="text-align: bold;"><?=ubahtoRp($grandTotal)?></td>
        <td style="text-align: bold;"><?=ubahtoRp($grandTotal-$ppn)?>/<?=ubahtoRp($ppn)?></td>
        <td></td>
      </tr> 
  </tbody>
</table>
</body>
</html>