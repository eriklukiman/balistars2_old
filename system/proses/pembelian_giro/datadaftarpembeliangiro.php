<?php
include_once '../../../library/konfigurasiurl.php';
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
  'pembelian_giro'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggalAwal=$tahun.'-'.$bulan.'-01';
$tanggalAkhir=$tahun.'-'.$bulan.'-31';

$sql=$db->prepare('
  SELECT distinct idSupplier 
  FROM balistars_pembelian 
  where (balistars_pembelian.tanggalpembelian between ? and ?) 
  and balistars_pembelian.idSupplier!=? 
  and balistars_pembelian.tipePembelian=? 
  and balistars_pembelian.status=?  
  order by balistars_pembelian.tanggalpembelian');
$sql->execute([
  $tanggalAwal,
  $tanggalAkhir,
  0,
  $tipe,
  'Aktif']);
$hasil=$sql->fetchAll();

$n = 1;
foreach($hasil as $data){  
  $sqlDp=$db->prepare('SELECT 
    SUM(dp) as totalDp 
    FROM balistars_dpgiro 
    where (periode between ? and ?) 
    and idSupplier=? 
    and tipePembelian=? 
    and statusDpGiro=?
    and jenisGiro=?');
  $sqlDp->execute([
    $tanggalAwal,$tanggalAkhir,
    $data['idSupplier'],
    $tipe,
    'Aktif',
    'DP']);
  $dataDp=$sqlDp->fetch();

  $sqlSupplier=$db->prepare('
    SELECT * FROM balistars_pembelian 
    where idSupplier=? 
    and tipePembelian=? 
    and (balistars_pembelian.tanggalpembelian between ? and ?)
    and status=?');
  $sqlSupplier->execute([
    $data['idSupplier'],
    $tipe,
    $tanggalAwal,
    $tanggalAkhir,
    'Aktif']);
  $dataSupplier=$sqlSupplier->fetchAll();

  $totalPembelian=0;
  $dataNoNota='';
  $cek=0;
  $i=0;
  $tanggalUpdate='';

  foreach ($dataSupplier as $row) {
    $noNotaKirim=$row['noNota'];
    if($row['statusPembelian']=="Belum Lunas"){
      $cek=$cek+1;
    }
    $namaSupplier=$row['namaSupplier'];
    $totalPembelian=$totalPembelian+$row['grandTotal'];
    if($i==0){
      $sqlUpdate=$db->prepare('
        SELECT * FROM balistars_hutang 
        where noNota=?');
      $sqlUpdate->execute([$row['noNota']]);
      $dataUpdate=$sqlUpdate->fetch();

      $bankAsalTransfer=$dataUpdate['bankAsalTransfer'];
      $tanggalUpdate=$dataUpdate['tanggalCair'];
      $noGiro=$dataUpdate['noGiro'];
      $dataNoNota=$row['noNota'];
    }
    else{
      $dataNoNota=$dataNoNota.",".$row['noNota'];
    }
    //var_dump($dataNoNota);
    $i++;
  }

  if(is_null($tanggalUpdate)){
    $tanggalCair='';
  }
  else if($tanggalUpdate=="" || $tanggalUpdate=="0" || $tanggalUpdate=="0000-00-00"){
    $tanggalCair='';
  }
  else if($tanggalUpdate){
    $tanggalCair=$tanggalUpdate;
  }
  else{
    $tanggalCair='';
  }

  if($cek==0){
    $disabled="disabled";
  }
  else{
    $disabled='';
  }

  $sqlIdDPGiro = $db->prepare('SELECT idDpGiro 
    FROM balistars_dpgiro 
    WHERE noGiro = ? 
    AND jenisGiro IS ?');
  $sqlIdDPGiro->execute([$noGiro, NULL]);
  $idDpGiro = $sqlIdDPGiro->fetch()['idDpGiro'];

  $sisaPembelian = $totalPembelian-$dataDp['totalDp'];
  ?>
  <tr>
    <td><?=$n?></td>
    <td>
       <?php 
      $display1 = '';
      $display2 = 'display : none;';
      
      if($noGiro!=''){
       $display2 = '';
      }
      if($noGiro!='' && $cek==0){
       $display1 = 'display : none;';
       $display2 = 'display : none;';
      }
       ?>

      <button type    = "button"
              title   = "DP" 
              class   = "btn btn-success" 
              onclick = "tambahDpGiro('<?=$data['idSupplier']?>',
                                      '<?=$namaSupplier?>',
                                      '<?=$totalPembelian?>',
                                      '<?=$tipe?>',
                                      '<?=$tanggalAwal?>',
                                      '<?=$disabled?>')">
        <i class="">Dp</i>
      </button>
      <button type    = "button"
              title   = "Pelunasan" 
              class   = "btn btn-warning" 
              style              = "<?=$display1?>"
              onclick = "tambahPembelianGiro('<?=$noNotaKirim?>',
                                            '<?=$data['idSupplier']?>',
                                            '<?=$tipe?>',
                                            '<?=$tanggalAwal?>',
                                            '<?=$sisaPembelian?>',
                                            '<?=$dataNoNota?>')">
        <i class="fa fa-plus-circle"></i>
      </button>
      <button type               = "button" 
              title              = "final"
              class              = "btn btn-primary" 
              style              = "color: white; <?=$display2?>"
              onclick = "finalPembelianGiro('<?=$dataNoNota?>','<?=$idDpGiro?>')">
        <i class="fa fa-check"></i>
      </button>
      <?php
      if($cek===0){
       ?>
       <button type               = "button" 
              title              = "buka"
              class              = "btn btn-danger" 
              style              = "color: white;"
              onclick = "cancelFinalisasi('<?=$noGiro?>')">
        <i class="fa fa-times-circle"></i>
      </button>
       <?php 
      }
        ?>
    </td>
    <td><?=wordwrap($namaSupplier,50,'<br>')?> </td>
    <td>Rp <?=wordwrap(ubahToRp($sisaPembelian),50,'<br>')?></td>
    <td><?=wordwrap(ubahTanggalIndo($tanggalCair),50,'<br>')?></td>
    <td>
      <?php 
      $sql = $db->prepare('SELECT * FROM balistars_bank where idBank =?');
      $sql->execute([$bankAsalTransfer]);
      $dataBank=$sql->fetch();
      echo $dataBank['namaBank'];
      ?>
    </td>
    <td><?=wordwrap($noGiro,50,'<br>')?></td>   
  </tr>
  <?php
  $n++;
}
?>