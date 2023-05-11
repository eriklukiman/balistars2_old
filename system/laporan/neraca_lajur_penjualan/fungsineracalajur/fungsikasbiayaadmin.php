<?php  
function kasBiayaAdmin($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'6360', 'Biaya ADM Bank',
		// '6360', $tanggalAwal,$tanggalAkhir, '6360',
		// '6360', $tanggalAwal,$tanggalAkhir, '6360',
		// '6360', $tanggalAwal,$tanggalAkhir, '6360',
		'6360', $tanggalAwal,$tanggalAkhir, 0,

		$tanggalAwal,$tanggalAkhir,
	);
	$sql 	 = $db->prepare("
		SELECT ? as kodeACC, ? as keterangan, 0 as saldoAwal ,SUM(debet) as debet, SUM(kredit) as kredit, SUM(memorial) as memorial
		FROM
		(
		  -- (
		  --   SELECT SUM(nilai) as debet, 0 as kredit, ? as kodeACC 
		  --   FROM balistars_biaya 
		  --   INNER JOIN balistars_biaya_detail 
		  --   ON balistars_biaya.noNota=balistars_biaya_detail.noNota 
		  --   WHERE balistars_biaya_detail.statusCancel='oke'  
		  --   and (tanggalBiaya BETWEEN ? AND ?) 
		  --   and kodeAkunting = ? 
		  --   and statusBiaya='Aktif'
	   --  )
	    -- UNION ALL
	    -- (
		   --  SELECT SUM(nilai) as debet, 0 as kredit, ? as kodeACC 
		   --  from balistars_pengeluaran_lain 
		   --  where (tanggalPengeluaranLain between ? AND ?) 
		   --  and statusFinal='Final'  and kodeAkunting = ? 
		   --  and statusPengeluaranLain='Aktif'
	    -- )
	   --  UNION ALL
	   --  (
		  --   SELECT sum((grandTotal-nilaiPPN)) as debet, 0 as kredit, ? as kodeACC
				-- FROM  balistars_pembelian_mesin
				-- WHERE (tanggalPembelian BETWEEN ? AND ?) 
				-- and kodeAkunting = ? 
				-- and statusPembelianMesin='Aktif'
	   --  )
	    -- UNION ALL
	    	(
		    SELECT sum(biayaAdmin) as debet, 0 as kredit, ? as kodeACC
		    FROM balistars_piutang 
				INNER JOIN balistars_penjualan 
				on balistars_piutang.noNota=balistars_penjualan.noNota 
				where (tanggalPembayaran between ? and ?) 
				AND biayaAdmin != ? 
				AND statusPenjualan='Aktif'
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