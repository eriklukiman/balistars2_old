<?php  
function kasMesin($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2015-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'1313','Mesin dan perlengkapan',

		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Include', '1313',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Include', '1313',
		'1313',$tanggalAwalSaldo,$tanggalAkhirSaldo,

		// $tanggalAwal,$tanggalAkhir,'Include', '1313',
		// $tanggalAwal,$tanggalAkhir,'Include', '1313',
		'1313',$tanggalAwal,$tanggalAkhir,

		$tanggalAwal,$tanggalAkhir,
	);
	$sql 	 = $db->prepare('
		SELECT *, dataKode.kodeACC as kodeACC 
		FROM
		( 
		  SELECT ? as kodeACC, ? as keterangan 

		)
		as dataKode
		LEFT JOIN
		(
		  SELECT SUM(data1.debetAwal-data1.kreditAwal) as saldoAwal, kodeACC 
	    FROM 
	    (
	      (
					SELECT  0 as kreditAwal, SUM(nilai) AS debetAwal, kodeAkunting as kodeACC 
			     FROM balistars_pembelian_mesin_detail 
			     inner join balistars_pembelian_mesin 
			     on balistars_pembelian_mesin_detail.noNota=balistars_pembelian_mesin.noNota 
			     where (tanggalPembelian BETWEEN ? and ?)  
			     and jenisPPN != ? 
			     and kodeAkunting = ? 
			     and statusPembelianMesin="Aktif"
    		)
    		UNION ALL
		    (
		    	SELECT  0 as kreditAwal, SUM((100/110)*hargaSatuan*qty) AS debetAwal, kodeAkunting as kodeACC
		    	FROM balistars_pembelian_mesin_detail 
		    	inner join balistars_pembelian_mesin 
		    	on balistars_pembelian_mesin_detail.noNota=balistars_pembelian_mesin.noNota 
		    	where (tanggalPembelian BETWEEN ? and ?) 
		    	and jenisPPN = ? 
		    	and kodeAkunting = ? 
		    	and statusPembelianMesin="Aktif"
		    )
		    UNION ALL
		    (
			    SELECT  SUM(dpp) as kreditAwal, 0 as debetAwal, ? as kodeACC 
			    FROM balistars_penjualan_mesin 
			    where (tanggalPenjualan BETWEEN ? and ?) 
			    AND statusPenjualanMesin="Aktif"
		    )
		  ) 
	    as data1 
	    GROUP BY kodeACC
		)
		as dataAkumulasi
		ON dataKode.kodeACC=dataAkumulasi.kodeACC
		LEFT JOIN
		( 
	    SELECT SUM(debet) as debet, SUM(kredit) as kredit, kodeACC 
	    FROM 
	    ( 
		   --  (
					-- SELECT  0 as kredit, SUM(nilai) AS debet, kodeAkunting as kodeACC 
			  --    FROM balistars_pembelian_mesin_detail 
			  --    inner join balistars_pembelian_mesin 
			  --    on balistars_pembelian_mesin_detail.noNota=balistars_pembelian_mesin.noNota 
			  --    where (tanggalPembelian BETWEEN ? and ?) 
			  --    and jenisPPN != ? 
			  --    and kodeAkunting = ? 
			  --    and statusPembelianMesin="Aktif"
    	-- 	)
    	-- 	UNION ALL
		   --  (
		   --  	SELECT  0 as kredit, SUM((100/110)*hargaSatuan*qty) AS debet, kodeAkunting as kodeACC
		   --  	FROM balistars_pembelian_mesin_detail 
		   --  	inner join balistars_pembelian_mesin 
		   --  	on balistars_pembelian_mesin_detail.noNota=balistars_pembelian_mesin.noNota 
		   --  	where (tanggalPembelian BETWEEN ? and ?) 
		   --  	and jenisPPN = ? 
		   --  	and kodeAkunting = ? 
		   --  	and statusPembelianMesin="Aktif"
		   --  )
		   --   UNION ALL
		    (
		     	SELECT  SUM(dpp) as kredit, 0 as debet, ? as kodeACC 
		     	FROM balistars_penjualan_mesin 
		     	where (tanggalPenjualan BETWEEN ? and ?) 
		     	AND statusPenjualanMesin="Aktif"
		    )
	    ) 
	    as data2 GROUP BY kodeACC 
		) 
		as dataMain
		ON dataKode.kodeACC=dataMain.kodeACC
		LEFT JOIN 
		( 
			SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
			FROM balistars_memorial 
		 	WHERE (tanggalMemorial BETWEEN ? AND ?) 
		 	AND statusMemorial="Aktif"
		 	GROUP BY kodeNeracaLajur 
		) AS dataMemorial 
		ON dataKode.kodeACC=dataMemorial.kodeACC
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