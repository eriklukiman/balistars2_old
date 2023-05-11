<?php
include_once '../../../library/konfigurasiurl.php';
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
  'laporan_pembelian'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggal = explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]); 

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
    left join balistars_user 
    on balistars_pembelian.idUser=balistars_user.idUser
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
    left join balistars_user 
    on balistars_pembelian.idUser=balistars_user.idUser
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
      left join balistars_user 
      on balistars_pembelian.idUser=balistars_user.idUser
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
      left join balistars_user 
      on balistars_pembelian.idUser=balistars_user.idUser
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
  //$n++;
  ?>
  <tr>
    <td rowspan="<?=$rowspan?>" style="vertical-align: top;"><?=$n?></td>
    <td style="vertical-align: top;" rowspan="<?=$rowspan?>" >
      <?php 
      $sqlJnsPembayaran=$db->prepare('SELECT jenisPembayaran FROM balistars_hutang where noNota=? ');
      $sqlJnsPembayaran->execute([$row['noNota']]);
      $hasilJns=$sqlJnsPembayaran->fetch();

      if($row['statusPembelian']=='Belum Lunas'){
        if($dataCekMenu['tipeEdit']=='1'){
           ?>
          <button type               = "button" 
                  title              = "Edit"
                  class              = "btn btn-warning tombolEditPembelian" 
                  style              = "color: white;"
                  onclick = "editPembelian('<?=$row['noNota']?>')">
            <i class="fa fa-edit"></i>
          </button>
          <?php 
        }
        if($dataCekMenu['tipeDelete']=='1'){
           ?>
          <button type    = "button"
                  title   = "Hapus" 
                  class   = "btn btn-danger" 
                  style="color: white;"
                  onclick = "cancelPembelian('<?=$row['noNota']?>')" >
            <i class="fa fa-trash"></i>
          </button>
          <?php 
        }     
      } 
      elseif($row['statusPembelian']=='Lunas' && $hasilJns['jenisPembayaran']!='Giro'){
        if($dataCekMenu['tipeEdit']=='1'){
           ?>
          <button type               = "button" 
                  title              = "Edit"
                  class              = "btn btn-secondary tombolEditPembelian" 
                  style              = "color: white;"
                  onclick = "editPembelian('<?=$row['noNota']?>')">
            <i class="fa fa-edit"></i>
          </button>
          <?php 
        }
        if($dataCekMenu['tipeDelete']=='1'){
           ?>
          <button type    = "button"
                  title   = "Hapus" 
                  class   = "btn btn-danger" 
                  style="color: white;"
                  onclick = "cancelPembelian('<?=$row['noNota']?>')" >
            <i class="fa fa-trash"></i>
          </button>
          <?php 
        }
      }

     ?>
      </button>
    </td>
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
    <td rowspan="<?=$rowspan?>" style="vertical-align: top;"><?=$row['userName']?></td>
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
          <td rowspan="<?=$rowspan?>" style="vertical-align: top;"><?=$row['userName']?></td>
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
  <td colspan="10" style="text-align: right;"> Total</td>
  <td style="text-align: bold;"><?=ubahtoRp($grandTotal)?></td>
  <td style="text-align: bold;"><?=ubahtoRp($grandTotal-$ppn)?>/<?=ubahtoRp($ppn)?></td>
</tr> 
