<?php  
function kasHpp($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = bulanKemarin($tanggalAwal);
	$tanggalAkhirSaldo = bulanKemarin($tanggalAkhir);
	$execute = array(
		'HPP ','511',
		$tanggalAwal,$tanggalAkhir,
		$tanggalAwal,$tanggalAkhir,
		'511'
	);
	$sql 	 = $db->prepare('
		SELECT 
		CONCAT( ? ,balistars_cabang.namaCabang) as keterangan, 
		CONCAT( ? ,balistars_cabang.idCabang) as kodeACC,
		balistars_cabang.idCabang, debet, kredit, memorial, 0 as saldoAwal
		FROM balistars_cabang 
		LEFT JOIN
		(
	    SELECT SUM(debet) as debet, SUM(kredit) as kredit, idCabang 
	    FROM
	    (
	      SELECT 0 as kredit, SUM(0-nilaiPersediaan) as debet, idCabang 
	      FROM balistars_persediaan_global 
	      where (tanggalPersediaan between ? and ?) 
	      and nilaiPersediaan<0 
	      and statusPersediaan="Aktif"
	      GROUP BY idCabang
	    )
	    as data2
	    GROUP BY data2.idCabang
		) as dataMain
		ON dataMain.idCabang=balistars_cabang.idCabang
		LEFT JOIN 
		(
		  SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
		  FROM balistars_memorial 
	    WHERE (tanggalMemorial BETWEEN ? AND ?) 
	    AND statusMemorial="Aktif"
	    GROUP BY kodeNeracaLajur
		)
		AS dataMemorial
		ON (CONCAT( ? ,balistars_cabang.idCabang))=dataMemorial.kodeACC 
		where balistars_cabang.statusCabang="Aktif"
	');
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
					($row['debet']-$row['kredit']),
					($row['memorial']),
					($row['saldoAwal']+$row['debet']-$row['kredit']+$row['memorial']),
					(0),
					$total);
	}
	return $total;
}

?>