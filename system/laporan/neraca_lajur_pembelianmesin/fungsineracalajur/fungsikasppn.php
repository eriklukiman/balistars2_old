<?php  
function kasPPN($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'2145', 'PPN',
		// $tanggalAwal,$tanggalAkhir,
	    $tanggalAwal,$tanggalAkhir,
	    // $tanggalAwal,$tanggalAkhir,

		$tanggalAwal,$tanggalAkhir,
	);
	$sql = $db->prepare('
		SELECT SUM(debet) as debet, SUM(kredit) as kredit, ?  as kodeACC, ? as keterangan, 0 as saldoAwal, memorial 
		FROM
		(
	    
	    -- (
	    -- 	SELECT 0 as kredit, SUM(nilaiPPN) as debet 
	    -- 	FROM balistars_pembelian 
	    -- 	WHERE (tanggalPembelian BETWEEN ? and ?) 
	    -- 	AND status="Aktif"
	    -- )
	    
      -- UNION ALL
      (
      	SELECT 0 as kredit, SUM(nilaiPPN) as debet 
      	FROM balistars_pembelian_mesin 
      	WHERE (tanggalPembelian BETWEEN ? and ?)
      	and kodeAkunting NOT in ("1313","1314","1316","1161","1323","1324","1326","3140", "1119,2","6380","6398","6340")
      	AND statusPembelianMesin="Aktif"
      )
     --  UNION ALL
	    -- (
	    -- 	SELECT SUM(ppn) as kredit, 0 as debet 
	    -- 	FROM balistars_penjualan_mesin 
	    -- 	WHERE (tanggalPenjualan BETWEEN ? and ?)
	    -- 	AND statusPenjualanMesin="Aktif"
	    -- )
		)
		as dataMain
		LEFT JOIN 
		(
	    SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
	    FROM balistars_memorial 
	    WHERE (tanggalMemorial BETWEEN ? and ?) 
	    AND statusMemorial="Aktif"
	    GROUP BY kodeNeracaLajur
		)
		AS dataMemorial
		ON kodeACC = dataMemorial.kodeACC
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
					($row['saldoAwal']+$row['debet']-$row['kredit']),
					($row['memorial']),
					(0),
					($row['saldoAwal']+$row['debet']-$row['kredit']+$row['memorial']),
					$total);
	}
	return $total;
}
?>