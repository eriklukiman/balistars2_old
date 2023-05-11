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
  'pembayaran_piutang'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

$sqlLogin  = $db->prepare('SELECT * FROM balistars_pegawai inner join balistars_user on balistars_pegawai.idPegawai=balistars_user.idPegawai inner join balistars_cabang on balistars_pegawai.idCabang=balistars_cabang.idCabang where balistars_user.idUser = ?');
$sqlLogin->execute([$idUserAsli]);
$dataLogin = $sqlLogin->fetch();

extract($_REQUEST);

$tanggal = explode(' - ', $rentang);
$tanggalAwal = konversiTanggal($tanggal[0]);
$tanggalAkhir = konversiTanggal($tanggal[1]); 

$sqlAwal=$db->prepare('SELECT MIN(sisaPiutang) as sisaPiutang, balistars_penjualan.grandTotal as grandTotal 
  FROM balistars_piutang 
  inner join balistars_penjualan 
  on balistars_piutang.noNota=balistars_penjualan.noNota 
  WHERE balistars_penjualan.statusFinalNota=? 
  and balistars_penjualan.tanggalPenjualan<? 
  and balistars_penjualan.idCabang=? 
  and balistars_penjualan.statusPenjualan=? 
  and balistars_penjualan.noNota 
  NOT IN (SELECT noNota FROM balistars_pemutihan_piutang) 
  GROUP BY balistars_penjualan.noNota
  order by balistars_penjualan.tanggalPenjualan');
$sqlAwal->execute([
  "final",
  $tanggalAwal,
  $dataLogin['idCabang'],
  'Aktif']);
$dataAwal=$sqlAwal->fetchAll();

$totalPiutang=0;
$totalPenjualan=0;
foreach ($dataAwal as $row) {
  $totalPiutang+=$row['sisaPiutang'];
  $totalPenjualan+=$row['grandTotal'];
}

?>

<tr>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td>Piutang Awal</td>
  <td><?=ubahToRp($totalPenjualan)?></td>
  <td><?=ubahToRp($totalPiutang)?></td>
  <td></td>
  <td></td>
</tr>

<?php
$sql=$db->prepare('SELECT *, balistars_penjualan.tanggalPenjualan as tanggal, MIN(sisaPiutang) as sisaPiutang2 
  FROM balistars_penjualan 
  inner join balistars_piutang 
  on balistars_penjualan.noNota=balistars_piutang.noNota 
  where balistars_penjualan.idCabang=? 
  and (balistars_penjualan.tanggalPenjualan between ? and ?) 
  and balistars_penjualan.statusFinalNota=?
  and balistars_penjualan.statusPenjualan=? 
  and balistars_penjualan.noNota 
  NOT IN (SELECT noNota FROM balistars_pemutihan_piutang) 
  group by balistars_piutang.noNota
  order by balistars_penjualan.tanggalPenjualan');
$sql->execute([
  $dataLogin['idCabang'],
  $tanggalAwal,
  $tanggalAkhir,
  "final",
  'Aktif']);
$hasil=$sql->fetchAll();

$n = 1;
foreach($hasil as $data){
  $totalPenjualan+=$data['grandTotal'];
  $totalPiutang+=$data['sisaPiutang2'];
  ?>
  <tr>
    <?php
    $disabled1  = '';
    $disabled2  = '';
    if($dataCekMenu['tipeEdit']=='0'){
       $disabled1 = 'display : none;';
    }
    if($dataCekMenu['tipeDelete']=='0'){
       $disabled2 = 'display : none;';
    }
     ?>
    <td><?=$n?> </td>
    <td>      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-success" 
              style="<?=$disabled2?>"
              onclick = "printPembayaranPiutang('<?=$data['noNota']?>')" >
        <i class="fa fa-print"></i>
      </button>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-primary tombolBayarPembayaranPiutang" 
              style              = "color: white;<?=$disabled1?>"
              onclick = "bayarPembayaranPiutang('<?=$data['noNota']?>','<?=$data['idPiutang']?>')">
        <i class="fa fa-calculator"></i>
      </button>
    </td>
    <td><?=wordwrap(ubahTanggalIndo($data['tanggal']),50,'<br>')?></td>
    <td><?=wordwrap($data['noNota'],50,'<br>')?></td>
    <td><?=wordwrap($data['namaCustomer'],50,'<br>')?></td>
    <td>
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
      <?php
      $sqlKeterangan=$db->prepare('SELECT * FROM balistars_penjualan_detail where noNota=?');
      $sqlKeterangan->execute([$data['noNota']]);
      $dataKeterangan=$sqlKeterangan->fetchAll();
      foreach ($dataKeterangan as $cek) {
        $keterangan .= $cek['namaBahan']." / ".$cek['ukuran']." ,";
       } 
      ?>
    <td><?=wordwrap($keterangan,50,'<br>')?></td>
    <td><?=wordwrap(ubahToRp($data['grandTotal']),50,'<br>')?></td>
    <td><?=wordwrap(ubahToRp($data['sisaPiutang2']),50,'<br>')?></td>
    <td><?=wordwrap(ubahToRp($totalPiutang),50,'<br>')?></td>
    <?php 
    if($dataCekMenu['tipeA2']==1){
      ?>
      <td><?=wordwrap($data['tipePenjualan'],50,'<br>')?></td>
    <?php
    } ?>
  </tr>
  <?php
    $n++;
  }
  ?>
  <tr>
    <td colspan="6" style="text-align: right;"></td>
    <td>Grand Total</td>
    <td><?=ubahToRp($totalPenjualan)?></td>
    <td><?=ubahToRp($totalPiutang)?></td>
    <td></td>
    <td></td>
    <td></td>
  </tr>
