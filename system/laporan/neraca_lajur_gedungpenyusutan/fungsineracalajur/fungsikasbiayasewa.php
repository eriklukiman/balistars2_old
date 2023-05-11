<?php  
function kasBiayaSewa($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'6340', 'Biaya Sewa',
		'6340', $tanggalAwal,$tanggalAkhir,

		$tanggalAwal,$tanggalAkhir,
	);
	$sql 	 = $db->prepare("
		SELECT ? as kodeACC, ? as keterangan, 0 as saldoAwal ,SUM(debet) as debet, SUM(kredit) as kredit, SUM(memorial) as memorial
		FROM
		(
	    (
		    SELECT sum(nilaiPenyusutan) as debet, 0 as kredit, ? as kodeACC 
		    from balistars_gedung_penyusutan 
		    inner join balistars_gedung 
		    on balistars_gedung_penyusutan.idGedung=balistars_gedung.idGedung 
		    where (tanggalPenyusutan between ? AND ?) 
		    AND statusGedungPenyusutan='Aktif'
		  )
		) as dataMain
		LEFT JOIN
		(
		  SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
		  FROM balistars_memorial 
	    WHERE (tanggalMemorial BETWEEN ? AND ?) 
	    AND statusMemorial='Aktif'
	    GROUP BY kodeNeracaLajur
		) AS dataMemorial
		ON dataMain.kodeACC=dataMemorial.kodeACC;
	");
	$sql->execute($execute);

	$data = $sql->fetchAll();
	foreach ($data as $row) {
		$row['keterangan'] = strtoupper($row['keterangan']);
		$total	  = tampilTable(
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