<?php 

function debetKasPiutang($idCabang,$tanggal,$saldo,$db,$jenis,$tipe){
  if($idCabang=='0'){
    $parameter1 = ' and idCabang !=?';
  } else {
    $parameter1 = ' and idCabang =?';
  }
  
  if($tipe=='Semua'){
    $parameter3 = ' and tipePenjualan !=?';

  } else {
    $parameter3 = ' and tipePenjualan =?';
  }


  $sqlDebet1=$db->prepare('SELECT *, grandTotal as debet 
    from balistars_penjualan 
    where tanggalPenjualan=? 
    and jumlahPembayaranAwal!=grandTotal 
    and statusFinalNota=? 
    and statusPenjualan = ?'
    .$parameter1
    .$parameter3);
  $sqlDebet1->execute([
    $tanggal,
    "final",
    'Aktif',
    $idCabang,
    $tipe]);  
  $dataDebet1=$sqlDebet1->fetchAll();

  foreach($dataDebet1 as $row){
    $saldo[0]+=$row['debet'];
    $saldo[1]+=$row['debet'];
    $saldo[3]++;
    ?>
    <tr>
      <td><?=$saldo[3]?></td>
      <td><?=tanggalTerbilang($tanggal)?></td>
      <td><?=$row['noNota']?></td>
      <td>
        <?php  
        if($row['idCustomer']=="0"){
          echo $row['namaCustomer'];
        }
        else{
          $sqlKonsumen=$db->prepare('SELECT namaCustomer from balistars_customer where idCustomer=?');
          $sqlKonsumen->execute([$row['idCustomer']]);
          $dataKonsumen=$sqlKonsumen->fetch();
          echo $dataKonsumen['namaCustomer'];
        }
        ?>
      </td>
        <?php 
        $sqlPenjualanDetail=$db->prepare('SELECT * 
          from balistars_penjualan_detail 
          where noNota=?
          and statusCancel=?');
        $sqlPenjualanDetail->execute([$row['noNota'],'ok']);
        $dataPenjualanDetail=$sqlPenjualanDetail->fetchAll();
        foreach ($dataPenjualanDetail as $cek) {
          $keterangan .= $cek['namaBahan']." / ".$cek['ukuran']." ,";
        }
        ?>
      <td><?=wordwrap($keterangan,50,'<br>')?></td>
      <td><?=ubahToRp($row['debet'])?></td>
      <td>-</td>
      <td><?=ubahToRp($saldo[0])?></td>
    </tr>

  <?php
  }
  return $saldo;
}

function kreditKasPiutang($idCabang,$tanggal,$saldo,$db,$jenis,$tipe){
  if($idCabang=='0'){
    $parameter2 = ' and balistars_penjualan.idCabang !=?';
  } else {
    $parameter2 = ' and balistars_penjualan.idCabang =?';
  }
  
  if($tipe=='Semua'){
    $parameter4 = ' and balistars_penjualan.tipePenjualan !=?';

  } else {
    $parameter4 = ' and balistars_penjualan.tipePenjualan =?';
  }

  $sqlKredit1=$db->prepare('SELECT *, balistars_piutang.jumlahPembayaran as kredit 
    from balistars_piutang 
    inner join balistars_penjualan 
    on balistars_penjualan.noNota=balistars_piutang.noNota 
    where balistars_piutang.tanggalPembayaran=? 
    and balistars_piutang.jumlahPembayaran!=balistars_piutang.grandTotal 
    and balistars_penjualan.statusFinalNota=? 
    and balistars_piutang.jumlahPembayaran>? 
    and balistars_penjualan.statusPenjualan=?'
    .$parameter2
    .$parameter4);
  $sqlKredit1->execute([
    $tanggal,
    "final",
    0,
    'Aktif',
    $idCabang,
    $tipe]);
  $dataKredit1=$sqlKredit1->fetchAll();

  foreach($dataKredit1 as $row){
    $saldo[0]-=$row['kredit'];
    $saldo[2]+=$row['kredit'];
    $saldo[3]++;
    ?>
    <tr>
      <td><?=$saldo[3]?></td>
      <td><?=tanggalTerbilang($tanggal)?></td>
      <td><?=$row['noNota']?></td>
      <td>
        <?php  
        if($row['idCustomer']=="0"){
          echo $row['namaCustomer'];
        }
        else{
          $sqlKonsumen=$db->prepare('SELECT namaCustomer from balistars_customer where idCustomer=?');
          $sqlKonsumen->execute([$row['idCustomer']]);
          $dataKonsumen=$sqlKonsumen->fetch();
          echo $dataKonsumen['namaCustomer'];
        }
        ?>
      </td>
      <td>Pembayaran Piutang</td>
      <td>-</td>
      <td><?=ubahToRp($row['kredit'])?></td>
      <td><?=ubahToRp($saldo[0])?></td>
    </tr>
    <?php
  }
  return $saldo;
}
    
?>

