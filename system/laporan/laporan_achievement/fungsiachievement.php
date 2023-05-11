<?php 
function fungsiPenyesuaian($db,$tanggalAwal,$tanggalAkhir,$idCabang,$jenisPenyesuaian) 
  {
    if($idCabang==0){
      $sqlPenyesuaian1=$db->prepare('SELECT SUM(nominal) as totalUP 
        FROM balistars_penyesuaian 
        where jenisPenyesuaian=? 
        and (tanggalPenyesuaian between ? and ?) 
        and status=? 
        and statusPenyesuaian=?');
      $sqlPenyesuaian1->execute([
        $jenisPenyesuaian,
        $tanggalAwal,$tanggalAkhir,
        "Naik",
        "Aktif"]);

      $sqlPenyesuaian2=$db->prepare('SELECT SUM(nominal) as totalDown 
        FROM balistars_penyesuaian 
        where jenisPenyesuaian=? 
        and (tanggalPenyesuaian between ? and ?) 
        and status=? 
        and statusPenyesuaian=?');
      $sqlPenyesuaian2->execute([
        $jenisPenyesuaian,
        $tanggalAwal,$tanggalAkhir,
        "Turun",
        "Aktif"]);

    }
    else{
      $sqlPenyesuaian1=$db->prepare('SELECT SUM(nominal) as totalUP 
        FROM balistars_penyesuaian where jenisPenyesuaian=? 
        and (tanggalPenyesuaian between ? and ?) 
        and status=? 
        and idCabang=? 
        and statusPenyesuaian=?');
      $sqlPenyesuaian1->execute([
        $jenisPenyesuaian,
        $tanggalAwal,$tanggalAkhir,
        "Naik",
        $idCabang,
        "Aktif"]);

      $sqlPenyesuaian2=$db->prepare('SELECT SUM(nominal) as totalDown 
        FROM balistars_penyesuaian where jenisPenyesuaian=? 
        and (tanggalPenyesuaian between ? and ?) 
        and status=? 
        and idCabang=? 
        and statusPenyesuaian=?');
      $sqlPenyesuaian2->execute([
        $jenisPenyesuaian,
        $tanggalAwal,$tanggalAkhir,
        "Turun",
        $idCabang,
        "Aktif"]);

    } 

    $dataPenyesuaian1=$sqlPenyesuaian1->fetch();
    $dataPenyesuaian2=$sqlPenyesuaian2->fetch();
    return $dataPenyesuaian1['totalUP']-$dataPenyesuaian2['totalDown'];
  }

function bintang($db,$tanggal,$waktuAwal,$waktuAkhir){
  $sqlBintang=$db->prepare('SELECT idCabang, tanggalPenjualan, timeStamp, sum(grandTotal) as acv 
    from balistars_penjualan 
    where tanggalPenjualan=? 
    and timeStamp BETWEEN ? and ? 
    and statusPenjualan=?
    group by idCabang 
    ORDER by acv DESC limit 1');
  $sqlBintang->execute([
    $tanggal,
    $waktuAwal,$waktuAkhir,
    'Aktif']);
  $dataBintang=$sqlBintang->fetch();

  return $dataBintang;
}

 ?>