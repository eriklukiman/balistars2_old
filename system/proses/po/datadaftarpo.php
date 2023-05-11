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
  'po'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);
$tanggal = explode(' - ', $rentang);
$tanggalSatu = konversiTanggal($tanggal[0]);
$tanggalDua = konversiTanggal($tanggal[1]); 

$sqlPo=$db->prepare('
  SELECT * FROM balistars_po 
  inner join balistars_cabang_advertising 
  on balistars_po.idCabangAdvertising=balistars_cabang_advertising.idCabang 
  where (tanggalPo between ? and ?) 
  and statusPo=? 
  order by tanggalPo ASC, balistars_po.timeStamp ASC');
$sqlPo->execute([$tanggalSatu,$tanggalDua,'Aktif']);
$dataPO=$sqlPo->fetchAll();

$n = 1;
//$totalBiaya=0;
foreach($dataPO as $row){
  if($row['idCustomer']==0){
    $konsumen='umum';
  }
  else{
     $konsumen='pelanggan';
  }
  $sqlPoDetail=$db->prepare('SELECT *  FROM balistars_po_detail where noPo=? and statusPoDetail=?');
  $sqlPoDetail->execute([$row['noPo'],'Aktif']);
  $dataPoDetail=$sqlPoDetail->fetchAll();

  if(!$dataPoDetail){
    $rowspan=1;
  }
  else{
    $rowspan=count($dataPoDetail);
  }
  //$n++;
  ?>

  <tr>
    <td style="vertical-align: top" rowspan="<?=$rowspan?>"><?=$n?></td>
    <td style="vertical-align: top" rowspan="<?=$rowspan?>" >
      <a href="../form_po?noPo=<?=$row['noPo']?>&flag=update&konsumen=<?=$konsumen?>&rentang=<?=$rentang?>" 
        class="btn btn-warning btn-sm" 
        style="color: white">
        <i class="fa fa-edit"></i>
      </a> 
      <button type    = "button"
              title   = "Tambah" 
              class   = "btn btn-danger tombolTambahNoNota" 
              onclick = "tambahNoNota('<?=$row['noPo']?>')" >
        <i class="fa fa-plus"> </i>
      </button>
    </td>
    <td style="vertical-align: top" rowspan="<?=$rowspan?>"><?=$row['noPo']?></td>
    <td style="vertical-align: top" rowspan="<?=$rowspan?>"><?=$row['noNota']?></td>
    <td style="vertical-align: top" rowspan="<?=$rowspan?>"><?=ubahTanggalIndo($row['tanggalPo'])?></td>
    <td style="vertical-align: top" rowspan="<?=$rowspan?>"><?=$row['namaCustomer']?></td>
    <td style="vertical-align: top" rowspan="<?=$rowspan?>"><?=$row['noTelpCustomer']?></td>
    <td style="vertical-align: top" rowspan="<?=$rowspan?>"><?=$row['namaCabang']?></td>
    <td style="vertical-align: top" rowspan="<?=$rowspan?>"><?=$row['status']?></td>
    <td style="vertical-align: top" rowspan="<?=$rowspan?>"><?=ubahTanggalIndo($row['tanggalSelesai'])?></td>
    
    <?php 
    $cek=1;
    if(!$dataPoDetail){
     ?>
     <td style="vertical-align: top"></td>
     <td style="vertical-align: top"></td>
     <td style="vertical-align: top"></td>
     <td style="vertical-align: top"></td>
     <td style="vertical-align: top"></td>
     <td style="vertical-align: top"></td>
     <td style="vertical-align: top"></td>
    </tr>
     <?php 
   }
    else{
      $sqlTotal=$db->prepare('SELECT sum(nilai) as grandTotal FROM balistars_po_detail where noPo=? and statusPoDetail=?');
      $sqlTotal->execute([$row['noPo'],'Aktif']);
      $dataTotal=$sqlTotal->fetchAll();
      
      foreach($dataPoDetail as $item){
        if($cek==1){
          ?>
          <td style="vertical-align: top"><?=wordwrap($item['namaBahan'],25,'<br>')?></td>
          <td style="vertical-align: top"><?=$item['ukuran']?></td>
          <td style="vertical-align: top"><?=$item['finishing']?></td>
          <td style="vertical-align: top"><?=$item['qty']?></td>
          <td style="vertical-align: top"><?=ubahtoRp($item['hargaSatuan'])?></td>
          <td style="vertical-align: top"><?=ubahToRp($item['nilai'])?></td>
          <?php
            $sqlTotal=$db->prepare('SELECT SUM(nilai) as grandTotal FROM balistars_po_detail where noPo=? and statusPoDetail=?');
            $sqlTotal->execute([$row['noPo'],'Aktif']);
            $dataTotal=$sqlTotal->fetch();
           ?>
          <td style="vertical-align: top" style="vertical-align: middle; text-align: center;" rowspan="<?=$rowspan?>">
            <?=ubahToRp($dataTotal['grandTotal'])?>
          </td>
        </tr>
          <?php
        }
        else{
          ?>
          <tr>
             <td style="vertical-align: top"><?=wordwrap($item['namaBahan'],25,'<br>')?></td>
             <td style="vertical-align: top"><?=$item['ukuran']?></td>
             <td style="vertical-align: top"><?=$item['finishing']?></td>
             <td style="vertical-align: top"><?=ubahToRp($item['qty'])?></td>
             <td style="vertical-align: top"><?=ubahToRp($item['hargaSatuan'])?></td>
             <td style="vertical-align: top"><?=ubahToRp($item['nilai'])?></td>
          </tr>
          <?php
        }
        $cek++;
      }
    }
 ?>
    
    <?php 
    $n++;
    }
     ?>
  
    
                          
