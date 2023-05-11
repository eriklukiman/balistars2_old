<?php  
function kasBiaya($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		$tanggalAwal,$tanggalAkhir,

		$tanggalAwal,$tanggalAkhir,
		'1313','1314','1316','1161','1323','1324','1326','3140', '1119,2','6380',
		'6398','6340','6360'
		// '3140', '6340','6360'
	);
	$sql 	 = $db->prepare("
		SELECT *, balistars_kode_akunting.kodeAkunting as kodeACC 
		FROM balistars_kode_akunting
		LEFT JOIN
		(
			SELECT 0 as saldoAwal ,SUM(debet) as debet, SUM(kredit) as kredit, kodeAkunting
			FROM
			(
		    (
		     	SELECT SUM(nilai) as debet, 0 as kredit, kodeAkunting 
		     	FROM balistars_biaya 
		     	INNER JOIN balistars_biaya_detail 
		     	ON balistars_biaya.noNota=balistars_biaya_detail.noNota 
		      WHERE balistars_biaya_detail.statusCancel='oke'  
		      and (tanggalBiaya BETWEEN ? AND ?) 
		      and statusBiaya='Aktif'
		      GROUP BY kodeAkunting
		    )
			)
			as data1
			GROUP BY kodeAkunting
		)
		as dataMain
		ON balistars_kode_akunting.kodeAkunting=dataMain.kodeAkunting
		LEFT JOIN 
		(
	    SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
	    FROM balistars_memorial 
	    WHERE (tanggalMemorial BETWEEN ? and ?) 
	    AND statusMemorial='Aktif'
	    GROUP BY kodeNeracaLajur
		)
		AS dataMemorial
		ON balistars_kode_akunting.kodeAkunting=dataMemorial.kodeACC
		WHERE balistars_kode_akunting.kodeAkunting 
		NOT IN (?,?,?,?,?,?,?,?,?,?,
						?,?,?) 
		-- NOT IN (?,?,?) 
		AND statusKodeAkunting='Aktif'
		ORDER BY balistars_kode_akunting.kodeAkunting ASC
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