<?php
<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
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
  'form_pembelian'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggal = explode(' - ', $rentang);
$tanggalAwal = $tanggal[0];
$tanggalAkhir = $tanggal[1]; 

$sqlMenu = $db->prepare('SELECT * FROM balistars_user_detail inner join balistars_menu_sub ON balistars_menu_sub.idMenuSub=balistars_user_detail.idMenuSub WHERE idPegawai=? and namaMenuSub=?');
$sqlMenu->execute([$idPegawaiUser,'Biaya']);
$dataMenu=$sqlMenu->fetch();

// $sqlPegawai= $db->prepare('SELECT * from balistars_pegawai where idPegawai=? and statusPegawai=?');
// $sqlPegawai->execute([$idPegawaiUser,'Aktif']);
// $data = $sqlPegawai->fetch();

$sqlPembelian=$db->prepare('SELECT * FROM balistars_pembelian inner join balistars_cabang on balistars_pembelian.idCabang=balistars_cabang.idCabang where (balistars_pembelian.tanggalPembelian between ? and ?) and status=? and balistars_pembelian.tipePembelian=? order by  balistars_pembelian.tanggalPembelian');
$sqlPembelian->execute([$tanggalAwal,$tanggalAkhir,'Aktif','A1']);
$dataPembelian=$sqlPembelian->fetchAll();

$n = 1;
$grandTotal=0;
$ppn=0;
foreach($dataPembelian as $row){
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
    <td rowspan="<?=$rowspan?>"><?=$n?></td>
    <td style="vertical-align: middle;" rowspan="<?=$rowspan?>" >
      <a href="../<?=$link?>?noNota=<?=$row['noNota']?>&flag=update&rentang=<?=$rentang?>" 
        class="btn btn-warning btn-sm" 
        style="color: white">
        <i class="fa fa-edit"></i>
      </a> 
      <button type    = "button"
              title   = "Tambah" 
              class   = "btn btn-danger tombolTambahNoNota" 
              onclick = "tambahPembelianSupplierA1('<?=$row['noNota']?>')" >
        <i class="fa fa-plus"> </i>
      </button>
    </td>
    <td rowspan="<?=$rowspan?>"><?=ubahTanggalIndo($row['tanggalPembelian'])?></td>
    <td rowspan="<?=$rowspan?>"><?=wordwrap($row['noNota'],50,'<br>')?></td>
    <td rowspan="<?=$rowspan?>"><?=wordwrap($row['noNotaVendor'],50,'<br>')?></td>
    <td rowspan="<?=$rowspan?>"><?=$row['namaSupplier']?></td>
    <?php 
    $cek=1;
    if(!$hasilDetail){
     ?>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td rowspan="<?=$rowspan?>"><?=$row['grandTotal']?></td>
    <td rowspan="<?=$rowspan?>"><?=ubahToRp($row['grandTotal']-$row['nilaiPPN']).' / '.ubahToRp($row['nilaiPPN'])?></td>
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
          <td rowspan="<?=$rowspan?>"><?=ubahToRp($row['grandTotal'])?></td>
          <td rowspan="<?=$rowspan?>"><?=ubahToRp($row['grandTotal']-$row['nilaiPPN']).' / '.ubahToRp($row['nilaiPPN'])?></td> 
        </tr>
      <?php 
        }
        else{
          ?>
          <tr>
            <td><?=$item['jenisOrder']?>/<?=$item['namaBarang']?></td>
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
