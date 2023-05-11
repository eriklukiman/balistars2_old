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
  'laporan_piutang_history'
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


if($tipe=='Semua'){
  $parameter1 = ' AND balistars_penjualan.tipePenjualan != ?';
} else{
  $parameter1 = ' AND balistars_penjualan.tipePenjualan = ?';
}

if($idCabang=='0'){
  $parameter2 = ' AND balistars_penjualan.idCabang != ?';
} else{
  $parameter2 = ' AND balistars_penjualan.idCabang = ?';
}

$sqlAwal=$db->prepare('SELECT MIN(sisaPiutang) as sisaPiutang, balistars_penjualan.grandTotal as grandTotal 
  FROM balistars_piutang 
  inner join balistars_penjualan 
  on balistars_piutang.noNota=balistars_penjualan.noNota 
  WHERE balistars_penjualan.statusFinalNota=?
  and balistars_penjualan.statusPenjualan=? 
  and balistars_penjualan.tanggalPenjualan<?' 
  .$parameter2 
  .$parameter1 
  .'GROUP BY balistars_penjualan.noNota');
$sqlAwal->execute([
  "final",
  'Aktif',
  $tanggalAwal,
  $idCabang,
  $tipe]);

$totalPenjualan=0;
$totalPiutang=0;

$dataAwal=$sqlAwal->fetchAll();
foreach ($dataAwal as $row) {
  $totalPiutang+=$row['sisaPiutang'];
  $totalPenjualan+=$row['grandTotal'];
}
$totalPembayaran=$totalPenjualan-$totalPiutang;
  ?>
  <tr>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td>Piutang Awal</td>
    <td><?=ubahToRp($totalPenjualan)?></td>
    <td><?=ubahToRp($totalPembayaran)?></td>
    <td><?=ubahToRp($totalPiutang)?></td>
    <td></td>
    <td></td>
  </tr>
<?php 
$sql=$db->prepare('SELECT *, MAX(sisaPiutang) as firstPiutang 
  FROM balistars_penjualan 
  inner join balistars_piutang 
  on balistars_penjualan.noNota=balistars_piutang.noNota 
  where balistars_penjualan.statusFinalNota=? 
  and balistars_penjualan.statusPenjualan=?
  and (balistars_penjualan.tanggalPenjualan between ? and ?)' 
  .$parameter1 
  .$parameter2
  .'group by balistars_piutang.noNota 
  ORDER BY balistars_penjualan.tanggalPenjualan');
$sql->execute([
  'final',
  'Aktif',
  $tanggalAwal,$tanggalAkhir,
  $tipe,
  $idCabang]);
$hasil=$sql->fetchAll();

foreach($hasil as $data){
  $umurPiutang=selisihTanggal(date('Y-m-d'),$data['tanggalPenjualan']);

  $sqlSub=$db->prepare('SELECT * FROM balistars_piutang 
    WHERE idPiutang 
    NOT IN (SELECT MIN(idPiutang) 
      FROM balistars_piutang 
      where noNota=?) 
    AND noNota=? 
    ORDER BY idPiutang ASC');
  $sqlSub->execute([
    $data['noNota'],
    $data['noNota']]);
  $dataSub=$sqlSub->fetchAll();

  $countRow=count($dataSub)+1;
  $n=1;
  $style='';
  if($countRow<=$n){
    $totalPiutang+=$data['firstPiutang'];
    $style='background: rgb(242, 242, 242);';
  }
  if($data['firstPiutang']>0){
    $dataBank=executeQueryUpdateForm('SELECT namaBank FROM balistars_bank where idBank=?',$db,$data['bankTujuanTransfer']);
    if($dataBank){
      $namaBank=$dataBank['namaBank'];
    }
    else if($data['bankTujuanTransfer']=='-'){
      $namaBank="PPN Bayar Dinas";
    }
    else{
      $namaBank="Cash";
    }
  ?>
    <tr>
      <td rowspan="<?=$countRow?>"><?=$data['noNota']?></td>
      <td rowspan="<?=$countRow?>"><?=$data['namaCustomer']?></td>
      <td rowspan="<?=$countRow?>">
        <?php  
        if($data['idCustomer']==0){
          echo $data['noTelpCustomer'];
        }
        else{
          $sqlCustomer=$db->prepare('SELECT * FROM balistars_customer where idCustomer=?');
          $sqlCustomer->execute([$data['idCustomer']]);
          $dataCustomer=$sqlCustomer->fetch();
          echo $dataCustomer['noTelpCustomer'];
        }
        ?>
      </td>
      <td rowspan="<?=$countRow?>"><?=ubahTanggalIndo($data['tanggalPenjualan'])?></td>
          <td rowspan="<?=$countRow?>"><?=$umurPiutang?> hari</td>
      <td rowspan="<?=$countRow?>"><?=ubahToRp($data['grandTotal'])?></td>
      <td style="<?=$style?>"><?=ubahToRp($data['jumlahPembayaran'])?></td>
      <td style="<?=$style?>"><?=ubahToRp($data['firstPiutang'])?></td>
      <td>
        <button type="button" class="btn btn-warning btn-sm" onclick="editPiutangHistory('<?=$data['idPiutang']?>')"><i class="fa fa-edit"></i>
        </button>
      </td>
      <?php 
      if($dataCekMenu['tipeA2']==1){
        ?>
        <td rowspan="<?=$countRow?>"><?=wordwrap($data['tipePenjualan'],50,'<br>')?></td>
      <?php
      } ?>
    </tr>
  <?php
  $totalPembayaran+=$data['jumlahPembayaran'];
  $totalPenjualan+=$data['grandTotal'];
  }
  foreach ($dataSub as $row) {
    $namaBank='';
    $n++;
    if($n==$countRow && $countRow>1){
      $totalPiutang+=$row['sisaPiutang'];
      $style='background: rgb(242, 242, 242);';
    }
    $dataBank=executeQueryUpdateForm('SELECT namaBank FROM balistars_bank where idBank=?',$db,$row['bankTujuanTransfer']);
    if($dataBank){
      $namaBank=$dataBank['namaBank'];
    }
    else if($row['bankTujuanTransfer']=='-'){
      $namaBank="PPN Bayar Dinas";
    }
    else{
      $namaBank="Cash";
    }
    ?>
    <tr style="<?=$style?>">
      <td><?=ubahToRp($row['jumlahPembayaran'])?></td>
      <td><?=ubahToRp($row['sisaPiutang'])?></td>
      <td>
        <button type="button" class="btn btn-warning btn-sm" onclick="editPiutangHistory('<?=$row['idPiutang']?>')"><i class="fa fa-edit"></i>
        </button>
      </td>
    </tr> 
  <?php
  $totalPembayaran+=$row['jumlahPembayaran'];
  }
}
?>
<tr>
  <td></td>
  <td></td>
  <td></td>
  <td colspan="2" style="font-weight: bold;">Total Penjualan / Total Pembayaran / Piutang</td>
  <td style="font-weight: bold;"><?=ubahToRp($totalPenjualan)?></td>
  <td style="font-weight: bold;"><?=ubahToRp($totalPembayaran)?></td>
  <td style="font-weight: bold;"><?=ubahToRp($totalPiutang)?></td>
  <td></td>
</tr>
