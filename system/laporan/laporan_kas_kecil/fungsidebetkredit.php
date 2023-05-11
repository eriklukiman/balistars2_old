<?php 

function debetKasKecil($idCabang,$tanggal,$saldo,$db){

if($idCabang==0){
  $parameter1 = ' and idCabang !=?';
} else {
  $parameter1 = ' and idCabang =?';
}


$sqlDebet1  =$db->prepare('SELECT nilaiApproved, keteranganApproval 
  from balistars_kas_kecil_order 
  where tanggalOrder = ? 
  and statusApproval=? 
  and statusKasKecilOrder=?'
  .$parameter1);
$sqlDebet1->execute([
  $tanggal,
  'approved',
  'Aktif',
  $idCabang]);
$dataDebet1=$sqlDebet1->fetchAll();

$sqlDebet2  =$db->prepare('SELECT nilai, keterangan 
  from balistars_cabang_cash_kecil 
  where tanggalCabangCashKecil = ? 
  and statusFinal=?'
  .$parameter1);
$sqlDebet2->execute([
  $tanggal,
  'Final',
  $idCabang]);
$dataDebet2=$sqlDebet2->fetchAll();

  foreach($dataDebet1 as $row){
    $saldo[0]+=$row['nilaiApproved'];
    $saldo[1]+=$row['nilaiApproved'];
    ?>
    <tr>
      <td><?=ubahTanggalIndo($tanggal)?></td>
      <td>Transfer Pety Cash</td>
      <td>-</td>
      <td><?=$row['keteranganApproval']?></td>
      <td><?=ubahToRp($row['nilaiApproved'])?></td>
      <td>-</td>
      <td><?=ubahToRp($saldo[0])?></td>
    </tr>
    <?php
  }

  foreach($dataDebet2 as $row){
    $saldo[0]+=$row['nilai'];
    $saldo[1]+=$row['nilai'];
    ?>
    <tr>
      <td><?=ubahTanggalIndo($tanggal)?></td>
      <td>Saldo Awal Pety Cash</td>
      <td>-</td>
      <td><?=$row['keterangan']?></td>
      <td><?=ubahToRp($row['nilai'])?></td>
      <td>-</td>
      <td><?=ubahToRp($saldo[0])?></td>
    </tr>
    <?php
  }
  return $saldo;
}

function kreditKasKecil($idCabang,$tanggal,$saldo,$db){

	if($idCabang==0){
	  $parameter1 = ' and idCabang !=?';
	  $parameter2 = ' and balistars_pembelian_mesin.idCabang !=?';
	} else {
	  $parameter1 = ' and idCabang =?';
	  $parameter2 = ' and balistars_pembelian_mesin.idCabang !=?';
	}

	$sqlKredit1 =$db->prepare('SELECT noNota, kodeAkunting, grandTotal 
	  from balistars_biaya 
	  where tanggalBiaya =?  
	  and statusBiaya=?'
	  .$parameter1);
	$sqlKredit1->execute([
	  $tanggal,
	  'Aktif',
	  $idCabang]);
	$dataKredit1=$sqlKredit1->fetchAll();

	$sqlKredit2 =$db->prepare('SELECT noNota, namaSupplier as keterangan, grandTotal  
	  from balistars_pembelian 
	  where tanggalPembelian =?  
	  and idSupplier=? 
	  and status=?'
	  .$parameter1);
	$sqlKredit2->execute([
	  $tanggal,
	  0,
	  'Aktif',
	  $idCabang]);
	$dataKredit2=$sqlKredit2->fetchAll();

	$sqlKredit3=$db->prepare('SELECT idSetor as noNota, keterangan, jumlahSetor as grandTotal  
	  from balistars_kas_kecil_setor 
	  where tanggalSetor =? 
	  and statusKasKecilSetor=?'
	  .$parameter1);
	$sqlKredit3->execute([
	  $tanggal,
	  'Aktif',
	  $idCabang]);
	$dataKredit3=$sqlKredit3->fetchAll();

	$sqlKredit4 =$db->prepare('SELECT balistars_pembelian_mesin.noNota as noNota, grandTotal, namaSupplier as keterangan 
	  from balistars_pembelian_mesin 
	  inner join balistars_hutang_mesin 
	  on balistars_pembelian_mesin.noNota=balistars_hutang_mesin.noNota 
	  where balistars_pembelian_mesin.tanggalPembelian =? 
	  and balistars_hutang_mesin.bankAsalTransfer=? 
	  and balistars_hutang_mesin.jenisPembayaran=? 
	  and statusPembelianMesin=?'
	  .$parameter2);
	$sqlKredit4->execute([
	  $tanggal,
	  0,
	  'Cash',
	  'Aktif',
	  $idCabang]);
	$dataKredit4=$sqlKredit4->fetchAll();


  foreach($dataKredit1 as $row){
    $keterangan='';
    $n=0;
    $sqlBiayaDetail=$db->prepare('SELECT keterangan from balistars_biaya_detail where noNota=?');
    $sqlBiayaDetail->execute([$row['noNota']??'']);
    $dataBiayaDetail=$sqlBiayaDetail->fetchAll();
    foreach ($dataBiayaDetail as $dataBiayaDetail) {
     if($n==0){
        $keterangan=$keterangan." ".$dataBiayaDetail['keterangan'];
     }
     else{
        $keterangan=$keterangan.", ".$dataBiayaDetail['keterangan'];
     }
     $n++;
    }
    $sqlKodeAkunting=$db->prepare('SELECT keterangan from balistars_kode_akunting where kodeAkunting=?');
    $sqlKodeAkunting->execute([$row['kodeAkunting']]);
    $dataKodeAkunting=$sqlKodeAkunting->fetch();

    $saldo[0]-=$row['grandTotal'];
    $saldo[2]+=$row['grandTotal'];
    ?>
    <tr>
    <td><?=ubahTanggalIndo($tanggal)?></td>
    <td><?=$row['noNota']??''?></td>
    <td><?=wordwrap($row['kodeAkunting']??''." (".$dataKodeAkunting['keterangan'].")",50,'<br>')?></td>
    <td><?=wordwrap($keterangan,50,'<br>')?></td>
    <td>-</td>
    <td><?=ubahToRp($row['grandTotal']??'')?></td>
    <td><?=ubahToRp($saldo[0])?></td>
    </tr>
    <?php
  }

  $saldo=executeKasKecil($dataKredit2,$saldo,$tanggal,$db);
  $saldo=executeKasKecil($dataKredit3,$saldo,$tanggal,$db);
  $saldo=executeKasKecil($dataKredit4,$saldo,$tanggal,$db);
  return $saldo;
}

function executeKasKecil($data, $saldo, $tanggal,$db){
  foreach($data as $row){
    $keterangan='';
    if(isset($row['keterangan'])){
      $keterangan=$keterangan." ".$row['keterangan'];
    }
    $saldo[0]-=$row['grandTotal'];
    $saldo[2]+=$row['grandTotal'];
    ?>
    <tr>
      <td><?=ubahTanggalIndo($tanggal)?></td>
      <td><?=$row['noNota']?></td>
      <td><?=wordwrap($keterangan,50,'<br>')?></td>
      <td></td>
      <td>-</td>
      <td><?=ubahToRp($row['grandTotal'])?></td>
      <td><?=ubahToRp($saldo[0])?></td>
    </tr>
    <?php
  }
  return $saldo;
}
 ?>