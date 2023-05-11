<?php  
function kasPendapatanLain($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
  $sql=$db->prepare('
    SELECT SUM(debet) as debet, SUM(kredit) as kredit, ? as kodeACC, ? as keterangan, 0 as saldoAwal, 0 as memorial 
    FROM
    ( 
      (
        SELECT 0 as kredit, SUM(nilai) as debet , "7120" as kodeACC
        FROM balistars_pengeluaran_lain 
        WHERE (tanggalPengeluaranLain BETWEEN ? and ?) 
        and statusFinal=? 
        and kodeAkunting=? 
        and statusPengeluaranLain="Aktif"
      )
      UNION ALL
      (
        SELECT SUM(nilai) as kredit, 0 as debet, "7120" as kodeACC 
        FROM balistars_pemasukan_lain 
        WHERE (tanggalPemasukanLain BETWEEN ? and ?) 
        and statusFinal=? 
        and idKodePemasukan=?
      )
    ) as dataMain
    LEFT JOIN 
    ( 
      SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC2 
      FROM balistars_memorial 
      WHERE (tanggalMemorial BETWEEN ? and ?) 
      AND statusMemorial="Aktif"
      GROUP BY kodeNeracaLajur 
    ) AS dataMemorial 
    ON dataMain.kodeACC=dataMemorial.kodeACC2
  ');

  $sql->execute([
    '7120',
    'Pendapatan Lainnya',
    $tanggalAwal,$tanggalAkhir,'final',0,
    $tanggalAwal,$tanggalAkhir,'final',1,
    $tanggalAwal,$tanggalAkhir,
  ]);

  $data = $sql->fetchAll();
  foreach ($data as $row) {
    $row['keterangan'] = strtoupper($row['keterangan']);
    $total    = tampilTable(
          ($row['kodeACC']),
          ($row['keterangan']),
          ($row['saldoAwal']),
          ($row['debet']),
          ($row['kredit']),
          ($row['saldoAwal']+$row['debet']-$row['kredit']),
          ($row['memorial']),
          ($row['saldoAwal']+$row['debet']-$row['kredit']+$row['memorial']),
          (0),
          $total);
  }
  return $total;
}
?>