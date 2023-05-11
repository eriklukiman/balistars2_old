<?php  
function kasHutang($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2015-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal); 

	$execute = array(
		'Hutang A1', '2111',

		$tanggalAwalSaldo,$tanggalAkhirSaldo, 'A1',
		$tanggalAwalSaldo,$tanggalAkhirSaldo, 'A1',
		$tanggalAwalSaldo,$tanggalAkhirSaldo, 'A1',
		$tanggalAwalSaldo,$tanggalAkhirSaldo, 'A1',

		$tanggalAwal,$tanggalAkhir, 'A1',
		$tanggalAwal,$tanggalAkhir, 'A1',
		$tanggalAwal,$tanggalAkhir, 'A1', 'DP', 'Aktif', 
		$tanggalAwal,$tanggalAkhir, 'A1', 'DP', 'Aktif', 

		$tanggalAwal,$tanggalAkhir, '2111',
	);
	$sql 	 = $db->prepare('
		SELECT debet, kredit, saldoAwal, ? AS keterangan, ? AS kodeACC, memorial, 0 AS laba
		FROM 
		(
			SELECT SUM(debet-kredit) AS saldoAwal 
			FROM
	    (
        (
        	SELECT SUM(grandTotal) as kredit, 0 as debet, tipePembelian AS tipe 
        	FROM balistars_pembelian 
        	where (tanggalPembelian between ? and ?) 
        	and idSupplier>0 
        	and tipePembelian =? 
        	and status="Aktif"
        )
        UNION ALL
        (
        	SELECT 0 as kredit, SUM(balistars_hutang.jumlahPembayaran) as debet, tipePembelian AS tipe 
        	FROM balistars_hutang 
        	inner join balistars_pembelian 
        	on balistars_hutang.noNota=balistars_pembelian.noNota 
        	where (balistars_hutang.tanggalCair between ? and ?) 
        	and balistars_pembelian.tipePembelian =? 
        	and statusHutang="Aktif"
        )
        UNION ALL
        (
        	SELECT SUM(grandTotal) as kredit, 0 as debet, tipePembelian as tipe 
        	FROM balistars_pembelian_mesin 
        	where (tanggalPembelian BETWEEN ? and ?) 
        	and tipePembelian =? 
        	and statusPembelianMesin="Aktif"
        )
        UNION ALL
        (
        	SELECT 0 as kredit, SUM(jumlahPembayaran) as debet, tipePembelian 
        	FROM balistars_hutang_mesin 
        	inner join balistars_pembelian_mesin 
        	on balistars_pembelian_mesin.noNota=balistars_hutang_mesin.noNota 
        	where (balistars_hutang_mesin.tanggalCair BETWEEN ? and ?) 
        	and tipePembelian = ? 
        	and statusPembelianMesin="Aktif"
        )
	    )
	    AS data1
		)
		AS dataAkumulasi
		JOIN
		(
			SELECT SUM(debet) AS debet, SUM(kredit) AS kredit 
			FROM
			(
        (
        	SELECT SUM(grandTotal) as kredit, 0 as debet, tipePembelian AS tipe 
        	FROM  balistars_pembelian 
        	where (tanggalPembelian between ? and ?) 
        	and idSupplier>0 
        	and tipePembelian=? 
        	and status="Aktif"
        )
        UNION ALL
        (
        	SELECT 0 as kredit, SUM(balistars_hutang.jumlahPembayaran) as debet, tipePembelian AS tipe 
        	FROM balistars_hutang 
        	inner join balistars_pembelian 
        	on balistars_hutang.noNota=balistars_pembelian.noNota 
        	where (balistars_hutang.tanggalCair between ? and ?) 
        	and balistars_pembelian.tipePembelian =? 
        	and statusHutang="Aktif"
        )      
        UNION all
        (
        	SELECT 0 as kredit, SUM(dp) as debet, tipePembelian as tipe FROM balistars_dpgiro
        	WHERE (tanggalCairDp BETWEEN ? AND ?) 
        	AND tipePembelian = ?
        	AND jenisGiro=?
        	AND statusDpGiro=?
        )
        UNION all
        (
        	SELECT SUM(dp) as kredit, 0 as debet, tipePembelian as tipe FROM balistars_dpgiro 
        	WHERE (tglPelunasan BETWEEN ? AND ?) 
        	AND tipePembelian = ?
        	AND jenisGiro=?
        	AND statusDpGiro=?
        )
		  )
			AS data2
		)
		AS dataMain
		JOIN 
		(
	    SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
	    FROM balistars_memorial 
	    WHERE (tanggalMemorial BETWEEN ? AND ?) 
	    AND statusMemorial="Aktif"
	    AND kodeNeracaLajur=?
		)
		AS dataMemorial
	');
	$sql->execute($execute);
	$data = $sql->fetchAll();
	foreach ($data AS $row) {
		$total	  = tampilTable(
					($row['kodeACC']),
					($row['keterangan']),
					($row['saldoAwal']),
					($row['debet']),
					($row['kredit']),
					($row['saldoAwal']+$row['debet']-$row['kredit']),
					($row['memorial']),
					($row['laba']),
					($row['saldoAwal']+$row['debet']-$row['kredit']+$row['memorial']),
					$total);
	}
	return $total;
}
?>