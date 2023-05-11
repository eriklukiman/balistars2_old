<?php  
function kasPenjualan($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'Penjualan ',' ','42','0',
		'A1','A2',
		'A1', $tanggalAwal,$tanggalAkhir,'final',
		$tanggalAwal,$tanggalAkhir,'42','0',0
	);
	$sql 	 = $db->prepare('
		SELECT  CONCAT( ? , balistars_cabang.namaCabang, ?,dataTipe.tipe) AS keterangan, balistars_cabang.idCabang, CONCAT( ? ,balistars_cabang.idCabang, ? ) AS kodeACC,  saldoAwal, debet, kredit, dataTipe.tipe, memorial 
		FROM balistars_cabang
		JOIN
    (
      (SELECT ? as tipe)
      UNION
      (SELECT ? as tipe)
    )
    as dataTipe
		LEFT JOIN
		(
			SELECT 0 as saldoAwal, 0 as debet,
			CASE 
		    WHEN tipePenjualan = ? 
		    THEN SUM((grandTotal-nilaiPPN))
		    ELSE (SUM(grandTotal))
			END AS kredit, idCabang, tipePenjualan AS tipe 
			FROM balistars_penjualan 
			where (tanggalPenjualan between ? and ?) 
			and statusFinalNota=? 
			and statusPenjualan="Aktif"
			GROUP BY idCabang, tipePenjualan
		)
		as dataMain
		ON (dataMain.idCabang=balistars_cabang.idCabang 
			AND dataTipe.tipe=dataMain.tipe)
		LEFT JOIN
		(
	    SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
	    FROM balistars_memorial 
	    WHERE (tanggalMemorial BETWEEN ? AND ?) 
	    AND statusMemorial="Aktif"
	    GROUP BY kodeNeracaLajur
		)
		AS dataMemorial
		ON CONCAT( ? ,balistars_cabang.idCabang, ? )=dataMemorial.kodeACC
		where balistars_cabang.idCabang!=? 
		AND balistars_cabang.statusCabang="Aktif"
	');
	$sql->execute($execute);
	$data = $sql->fetchAll();
	var_dump($sql->errorInfo());
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