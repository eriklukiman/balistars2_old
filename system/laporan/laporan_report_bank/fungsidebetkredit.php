<?php 

function debetKasBesar($idBank,$tanggal,$saldo,$db){
  $sqlDebet1=$db->prepare('SELECT jumlahSetor as grandTotal, idCabang 
    from balistars_setor_penjualan_cash 
    where (tanggalSetor=?) 
    and idBank=? 
    and statusFinal=? 
    and statusSetor=?');
  $sqlDebet1->execute([
    $tanggal,
    $idBank,
    "Final",
    "Aktif"]);
  $dataDebet1=$sqlDebet1->fetchAll();

  $sqlDebet2=$db->prepare('SELECT *, jumlahSetor as grandTotal 
    from balistars_kas_kecil_setor 
    where (tanggalSetor=?) 
    and idBank=? 
    and statusFinal=? 
    and statusKasKecilSetor=?');
  $sqlDebet2->execute([
    $tanggal,
    $idBank, 
    "Final", 
    "Aktif"]);
  $dataDebet2=$sqlDebet2->fetchAll();

  $sqlDebet3=$db->prepare('SELECT nilaiTransfer as grandTotal, idBankAsal, keterangan 
    from balistars_bank_transfer 
    where (tanggalTransfer=?) 
    and idBankTujuan=? 
    and statusTransfer=?');
  $sqlDebet3->execute([
    $tanggal,
    $idBank,
    "final"]);
  $dataDebet3=$sqlDebet3->fetchAll();

  $sqlDebet5=$db->prepare('SELECT nilai as grandTotal, keterangan 
    from balistars_pemasukan_lain 
    where (tanggalPemasukanLain=?) 
    and idBank=? 
    and statusFinal=? 
    and statusPemasukanLain=?');
  $sqlDebet5->execute([
    $tanggal,
    $idBank,
    "final",
    "Aktif"]);
  $dataDebet5=$sqlDebet5->fetchAll();

  $sqlDebet6=$db->prepare('SELECT (dpp+ppn) as grandTotal 
    from balistars_penjualan_mesin 
    where tanggalPenjualan=? 
    and idBank=? 
    and statusPenjualanMesin=?');
  $sqlDebet6->execute([
    $tanggal,
    $idBank,
    "Aktif"]);
  $dataDebet6=$sqlDebet6->fetchAll();

  $saldo=executeKasBesarDebet($dataDebet1,$saldo,$tanggal,"Setor Penjualan Cash Dari",$db);
  $saldo=executeKasBesarDebet($dataDebet2,$saldo,$tanggal,"Setor Kas Kecil Dari",$db);
  $saldo=executeKasBesarDebet($dataDebet6,$saldo,$tanggal,"Penjualan Mesin",$db);

  foreach($dataDebet3 as $row){
    $sqlBankAsal=$db->prepare('SELECT namaBank 
      from balistars_bank 
      where idBank=?');
    $sqlBankAsal->execute([$row['idBankAsal']]);
    $dataBankAsal=$sqlBankAsal->fetch();

    $saldo[0]+=$row['grandTotal'];
    $saldo[1]+=$row['grandTotal'];
    ?>
    <tr>
      <td><?=ubahTanggalIndo($tanggal)?></td>
      <td><?='Transfer Dari Bank '.$dataBankAsal['namaBank'].' ('.$row['keterangan'].')'?></td>
      <td><?=ubahToRp($row['grandTotal'])?></td>
      <td>-</td>
      <td><?=ubahToRp($saldo[0])?></td>
    </tr>
    <?php
  }

  $sqlDebet4=$db->prepare('SELECT (jumlahPembayaran) as debet, (PPH+biayaAdmin) as kredit, noNota 
    from balistars_piutang 
    where bankTujuanTransfer=? 
    and tanggalPembayaran=?');
  $sqlDebet4->execute([
    $idBank,
    $tanggal]);
  $dataDebet4=$sqlDebet4->fetchAll();

  foreach($dataDebet4 as $row){
    $saldo[0]+=($row['debet']-$row['kredit']);
    $saldo[1]+=($row['debet']-$row['kredit']);

    $sqlCustomer=$db->prepare('SELECT namaCustomer, idCustomer 
      FROM balistars_penjualan where noNota=?');
    $sqlCustomer->execute([$row['noNota']]);
    $dataCustomer=$sqlCustomer->fetch();
    $namaCustomer='';

    if($dataCustomer['idCustomer']=="0" || $dataCustomer['idCustomer']==0){ 
      $namaCustomer=$dataCustomer['namaCustomer'];
    }
    else{
      $sqlPelangan=$db->prepare('SELECT namaCustomer 
        FROM balistars_customer 
        where idCustomer=?');
      $sqlPelangan->execute([$dataCustomer['idCustomer']]);
      $dataPelangan=$sqlPelangan->fetch();
      $namaCustomer=$dataPelangan['namaCustomer'];
    }
    ?>   
    <tr>
      <td><?=ubahTanggalIndo($tanggal)?></td>
      <td>Pembayaran Piutang <?=$row['noNota']?> <?=$namaCustomer?></td>
      <td><?=ubahToRp($row['debet']-$row['kredit'])?></td>
      <td>-</td>
      <td><?=ubahToRp($saldo[0])?></td>
    </tr>
    <?php
  }

  foreach($dataDebet5 as $row){
    $saldo[0]+=$row['grandTotal'];
    $saldo[1]+=$row['grandTotal'];
    ?>
    <tr>
      <td><?=ubahTanggalIndo($tanggal)?></td>
      <td><?="Pemasukan Lain-Lain (".$row['keterangan'].")"?></td>
      <td><?=ubahToRp($row['grandTotal'])?></td>
      <td>-</td>
      <td><?=ubahToRp($saldo[0])?></td>
    </tr>
    <?php
  }

  return $saldo;
}

function kreditKasBesar($idBank,$tanggal,$saldo,$db){
 $sqlKredit1=$db->prepare('SELECT nilaiTransfer as grandTotal, idBankTujuan, keterangan 
  from balistars_bank_transfer 
  where idBankAsal=? 
  and statusTransfer=? 
  and tanggalTransfer=?');
  $sqlKredit1->execute([
    $idBank,
    "final",
    $tanggal]);
  $dataKredit1=$sqlKredit1->fetchAll();
   
  $sqlKredit2=$db->prepare('SELECT nilaiApproved as grandTotal 
    from balistars_kas_kecil_order 
    where bankAsalTransfer=? 
    and statusApproval=? 
    and statusKasKecilOrder=?
    and tanggalOrder=?');
  $sqlKredit2->execute([
    $idBank,
    "approved",
    "Aktif",
    $tanggal]);
  $dataKredit2=$sqlKredit2->fetchAll();

  // $sqlKredit3=$db->prepare('SELECT SUM(jumlahPembayaran) as grandTotal, idSupplier 
  //   from  balistars_hutang 
  //   inner join balistars_pembelian 
  //   on balistars_pembelian.noNota=balistars_hutang.noNota 
  //   where bankAsalTransfer=? 
  //   and balistars_pembelian.idSupplier!=? 
  //   and tanggalCair=? 
  //   and statusHutang=?
  //   and balistars_pembelian.statusPembelian=? 
  //   GROUP BY balistars_pembelian.idSupplier');
  // $sqlKredit3->execute([
  //   $idBank,
  //   0,
  //   $tanggal,
  //   "Aktif",
  //   "Lunas"]);
  // $dataKredit3=$sqlKredit3->fetchAll();

  $sqlKredit3=$db->prepare('SELECT dp as grandTotal, idSupplier 
    from  balistars_dpgiro 
    where idBank=? 
    and tanggalCairDp=? 
    and jenisGiro=? 
    and statusDpGiro=? ');
  $sqlKredit3->execute([
    $idBank,
    $tanggal,
    'Pelunasan',
    'Aktif']);
  $dataKredit3=$sqlKredit3->fetchAll();

  $sqlKredit4=$db->prepare('SELECT jumlahPembayaran as grandTotal 
    from  balistars_hutang_mesin 
    where bankAsalTransfer=? 
    and jenisPembayaran=? 
    and tanggalPembayaran=? 
    and statusCair=?');
  $sqlKredit4->execute([
    $idBank,
    "Giro",
    $tanggal,
    "Cair"]);
  $dataKredit4=$sqlKredit4->fetchAll();

  // $sqlKredit5=$db->prepare('SELECT nilai as grandTotal from balistars_biaya_cabang where idBank=? and tanggalBiaya=?');
  // $sqlKredit5->execute([$idBank,$tanggal]);
  // $dataKredit5=$sqlKredit5->fetchAll();

  $sqlKredit6=$db->prepare('SELECT nilai as grandTotal, kodeAkunting, keterangan 
    from balistars_pengeluaran_lain 
    where (tanggalPengeluaranLain=?) 
    and idBank=? 
    and statusFinal=? 
    and statusPengeluaranLain=?');
  $sqlKredit6->execute([
    $tanggal,
    $idBank,
    "final",
    "Aktif"]);
  $dataKredit6=$sqlKredit6->fetchAll();

  // $sqlKredit7=$db->prepare('SELECT *, nilaiDisetujui as grandTotal from balistars_advertising_rab where (tanggalPengajuan=?) and idBankTransfer=? and statusRAB=?');
  // $sqlKredit7->execute([$tanggal,$idBank,"Disetujui"]);
  // $dataKredit7=$sqlKredit7->fetchAll();

  $sqlKredit8=$db->prepare('SELECT jumlahPembayaran as grandTotal 
    from  balistars_hutang_gedung_pembayaran 
    where bankAsalTransfer=? 
    and jenisPembayaran=? 
    and tanggalPembayaran=? 
    and statusCair=? 
    and statusPembayaranHutangGedung=?');
  $sqlKredit8->execute([
    $idBank,
    "Giro",
    $tanggal,
    "Cair",
    "Aktif"]);
  $dataKredit8=$sqlKredit8->fetchAll();

  $sqlKredit9=$db->prepare('SELECT dp as grandTotal, idSupplier 
    from  balistars_dpgiro 
    where idBank=? 
    and tanggalCairDp=?
    and jenisGiro=? 
    and statusDpGiro=?');
  $sqlKredit9->execute([
    $idBank,
    $tanggal,
    'Dp',
    'Aktif']);
  $dataKredit9=$sqlKredit9->fetchAll();
  
  $saldo=executeKasBesarKredit($dataKredit2,$saldo,$tanggal,"Order Kas Kecil",$db);
  $saldo=executeKasBesarKredit($dataKredit3,$saldo,$tanggal,"Hutang",$db);
  $saldo=executeKasBesarKredit($dataKredit4,$saldo,$tanggal,"Hutang Mesin",$db);
  // $saldo=executeKasBesarKredit($dataKredit5,$saldo,$tanggal,"Biaya Cabang",$db);
  // $saldo=executeKasBesarKredit($dataKredit7,$saldo,$tanggal,"RAB Advertising Disetujui",$db);
  $saldo=executeKasBesarKredit($dataKredit8,$saldo,$tanggal,"Hutang Gedung",$db);
  $saldo=executeKasBesarKredit($dataKredit9,$saldo,$tanggal,"DP Pembelian",$db);

   foreach($dataKredit1 as $row){
    $saldo[0]-=$row['grandTotal'];
    $saldo[2]+=$row['grandTotal'];
     $sqlBankAsal=$db->prepare('SELECT namaBank 
      from balistars_bank 
      where idBank=?');
    $sqlBankAsal->execute([$row['idBankTujuan']]);
    $dataBankAsal=$sqlBankAsal->fetch();
    ?>
    <tr>
      <td><?=ubahTanggalIndo($tanggal)?></td>
       <td><?='Transfer Ke Bank '.$dataBankAsal['namaBank'].' ('.$row['keterangan'].')'?></td>
      <td>-</td>
      <td><?=ubahToRp($row['grandTotal'])?></td>
      <td><?=ubahToRp($saldo[0])?></td>
    </tr>
    <?php
  }


  foreach($dataKredit6 as $row){
    $saldo[0]-=$row['grandTotal'];
    $saldo[2]+=$row['grandTotal'];
    if($row['kodeAkunting']==0){
      $keterangan='Pengeluaran Lain-Lain';
    }
    else{
        $sqlKodeAkunting=$db->prepare('SELECT keterangan 
          from balistars_kode_akunting 
          where kodeAkunting=?');
        $sqlKodeAkunting->execute([$row['kodeAkunting']]);
        $dataKodeAkunting=$sqlKodeAkunting->fetch();
        $keterangan=$dataKodeAkunting['keterangan'];
    }
    ?>
    <tr>
      <td><?=ubahTanggalIndo($tanggal)?></td>
      <td><?=$keterangan.' ('.$row['keterangan'].')'?></td>
      <td>-</td>
      <td><?=ubahToRp($row['grandTotal'])?></td>
      <td><?=ubahToRp($saldo[0])?></td>
    </tr>
    <?php
  }
  return $saldo;
}

function executeKasBesarDebet($data, $saldo, $tanggal, $keterangan,$db){
  foreach($data as $row){
    if(isset($row['idCabang'])){
      $sqlNamaCabang=$db->prepare('SELECT namaCabang from balistars_cabang where idCabang=?');
      $sqlNamaCabang->execute([$row['idCabang']]);
      $dataNamaCabang=$sqlNamaCabang->fetch();
      $namaCabang=$dataNamaCabang['namaCabang'];
      if($row['idCabang']==0){
        $sqlNamaCabangAdvertising=$db->prepare('SELECT namaCabang from balistars_cabang_advertising where idCabang=?');
        $sqlNamaCabangAdvertising->execute([$row['idCabangAdvertising']]);
        $dataNamaCabangAdvertising=$sqlNamaCabangAdvertising->fetch();
        $namaCabang=$dataNamaCabangAdvertising['namaCabang'];
      }
    }
    else{
      $namaCabang='';
    }
    $saldo[0]+=$row['grandTotal'];
    $saldo[1]+=$row['grandTotal'];
    ?>
    <tr>
      <td><?=ubahTanggalIndo($tanggal)?></td>
      <td><?=$keterangan.' '.$namaCabang?></td>
      <td><?=ubahToRp($row['grandTotal'])?></td>
      <td>-</td>
      <td><?=ubahToRp($saldo[0])?></td>
    </tr>
    <?php
  }
  return $saldo;
}
function executeKasBesarKredit($data, $saldo, $tanggal, $keterangan,$db){
  foreach($data as $row){
    $keteranganPrint=$keterangan;
    if(isset($row['idCabangAdvertising'])){
      $sqlNamaCabangAdvertising=$db->prepare('SELECT namaCabang from balistars_cabang_advertising where idCabang=?');
      $sqlNamaCabangAdvertising->execute([$row['idCabangAdvertising']]);
      $dataNamaCabangAdvertising=$sqlNamaCabangAdvertising->fetch();
      $keteranganPrint=$keterangan." ".$dataNamaCabangAdvertising['namaCabang'];
    }
    if(isset($row['idSupplier'])){
      $sqlSupplier=$db->prepare('SELECT namaSupplier from balistars_supplier where idSupplier=?');
      $sqlSupplier->execute([$row['idSupplier']]);
      $dataSupplier=$sqlSupplier->fetch();
      $keteranganPrint=$keterangan." ".$dataSupplier['namaSupplier'];
    }
    $saldo[0]-=$row['grandTotal'];
    $saldo[2]+=$row['grandTotal'];
    ?>
    <tr>
      <td><?=ubahTanggalIndo($tanggal)?></td>
      <td><?=$keteranganPrint?></td>
      <td>-</td>
      <td><?=ubahToRp($row['grandTotal'])?></td>
      <td><?=ubahToRp($saldo[0])?></td>
    </tr>
    <?php
  }
  return $saldo;
}
?>

