<?php
include_once '../../../library/konfigurasiurl.php';
include_once $BASE_URL_PHP . '/library/konfigurasidatabase.php';
include_once $BASE_URL_PHP . '/library/fungsienkripsidekripsi.php';
include_once $BASE_URL_PHP . '/library/konfigurasikuncirahasia.php';
include_once $BASE_URL_PHP . '/library/fungsiutilitas.php';
include_once $BASE_URL_PHP . '/library/fungsitanggal.php';
include_once $BASE_URL_PHP . '/library/fungsirupiah.php';

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
  'form_input_neraca'
]);
$dataCekMenu = $sqlCekMenu->fetch();

//KICK SAAT ID USER TIDAK ADA PADA DATABASE
if (!$dataCekUser || !$dataCekMenu) {
  header('location:' . $BASE_URL_HTML . '/?flagNotif=gagal');
}

extract($_REQUEST);

$tanggalRentang      = explode(' - ', $rentang);
$tanggalAwal  = konversiTanggal($tanggalRentang[0]);
$tanggalAkhir = konversiTanggal($tanggalRentang[1]);

$tanggalAwalSekali="2019-01-01";
$tanggalKemarin=waktuKemarin($tanggalAwal);

$saldo=0;  
$sqlSaldoAwal=$db->prepare('
  SELECT * FROM balistars_input_neraca 
  where tipeBiaya=? 
  and (tanggalInputNeraca between ? and ?) 
  and statusInputNeraca=?
  order by tanggalInputNeraca ASC, jenisInput DESC');
$sqlSaldoAwal->execute([
  $tipeBiaya,
  $tanggalAwal,
  $tanggalAkhir,
  'Aktif']);
$dataSaldoAwal=$sqlSaldoAwal->fetchAll();

$n = 1;
foreach($dataSaldoAwal as $row){
  if($row['jenisInput']=='Debet' || $row['jenisInput']=='Saldo Awal'){
        $debet=$row['nilaiInputNeraca'];
        $kredit=0;
      }
      else{
        $kredit=$row['nilaiInputNeraca'];
        $debet=0;
      }
      $saldo+=($debet-$kredit);
  ?>
  <tr>
    <?php
    $disabled1  = '';
    $disabled2  = '';
    if($dataCekMenu['tipeEdit']=='0'){
       $disabled1 = 'style = "display: none;"';
    }
    if($dataCekMenu['tipeDelete']=='0'){
       $disabled2 = 'style = "display: none;"';
    }
     ?>
    <td><?=$n?></td>
    <td>
      <button type               = "button" 
              title              = "Edit"
              class              = "btn btn-warning tombolEditInputNeraca" 
              style              = "color: white;"
              onclick = "editInputNeraca('<?=$row['idInputNeraca']?>')" <?=$disabled1?> >
        <i class="fa fa-edit"></i>
      </button>
      <button type    = "button"
              title   = "Hapus" 
              class   = "btn btn-danger" 
              onclick = "cancelInputNeraca('<?=$row['idInputNeraca']?>')" <?=$disabled2?> 
              >
        <i class="fa fa-trash"></i>
      </button>
    </td>
    <td><?=wordwrap(ubahTanggalIndo($row['tanggalInputNeraca']),50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($debet),50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($kredit),50,'<br>')?></td>
    <td>Rp <?=wordwrap(ubahToRp($saldo),50,'<br>')?></td>
  </tr>
  <?php
  $n++;
}
?>