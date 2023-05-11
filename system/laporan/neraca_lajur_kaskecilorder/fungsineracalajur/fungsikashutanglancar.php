<?php  
function kasHutangLancar($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);

	//echo '"'.$tanggalAwal.'" AND "'.$tanggalAkhir.'"<br>';
	//echo '"'.$tanggalAwalSaldo.'" AND "'.$tanggalAkhirSaldo.'"<br>';
	
	$execute = array(
		'Hutang Lancar Lain - Lain','2135',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Final', 30, 22, 23, 24, 25, 33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Final', 30, 22, 23, 24, 25, 33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'final', 30, 22, 23, 24, 25, 33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'0', 30, 22, 23, 24, 25, 33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'final', 30, 22, 23, 24, 25, 33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo, 30, 22, 23, 24, 25, 33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'final', 30, 22, 23, 24, 25, 33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'approved',0, 30, 22, 23, 24, 25, 33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Lunas', 30, 22, 23, 24, 25, 33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Giro','Cair', 30, 22, 23, 24, 25, 33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'final', 30, 22, 23, 24, 25, 33,  37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Giro','Cair',  30, 22, 23, 24, 25, 33, 37,

		
		// $tanggalAwal,$tanggalAkhir,'final', 30, 22, 23, 24, 25, 33, 37,		
		// $tanggalAwal,$tanggalAkhir, 30, 22, 23, 24, 25, 33, 37,
		// $tanggalAwal,$tanggalAkhir,'final', 30, 22, 23, 24, 25, 33, 37,
		$tanggalAwal,$tanggalAkhir,'approved',0, 30, 22, 23, 24, 25, 33, 37,
		// $tanggalAwal,$tanggalAkhir,'Lunas', 30, 22, 23, 24, 25, 33, 37,
		// $tanggalAwal,$tanggalAkhir,'Giro','Cair', 30, 22, 23, 24, 25, 33, 37,
		
		// $tanggalAwal,$tanggalAkhir,'Giro','Cair', 30, 22, 23, 24, 25, 33, 37,

		$tanggalAwal,$tanggalAkhir,
		'2135',
	);
	$sql 	 = $db->prepare('
		SELECT debet, kredit, saldoAwal, ? AS keterangan, ? AS kodeACC, memorial, 0 AS laba
		FROM 
		(
			SELECT SUM(debet-kredit) AS saldoAwal 
			FROM
	    (
        (
          SELECT SUM(jumlahSetor) AS debet, 0 AS kredit, idBank 
          FROM balistars_setor_penjualan_cash 
          WHERE (tanggalSetor BETWEEN ? AND ?) 
          AND statusFinal=? 
          AND idBank in (?,?,?,?,?,?,?)
          AND statusSetor="Aktif"
        )
        UNION ALL
        (
          SELECT SUM(jumlahSetor) AS debet, 0 AS kredit, idBank 
          FROM balistars_kas_kecil_setor 
          WHERE (tanggalSetor BETWEEN ? AND ?) 
          AND statusFinal=? 
          AND idBank in (?,?,?,?,?,?,?)
          AND statusKasKecilSetor="Aktif"
        )
        UNION ALL
        (
          SELECT SUM(nilaiTransfer) AS debet, 0 AS kredit, idBankTujuan AS idBank 
          FROM balistars_bank_transfer 
          WHERE (tanggalTransfer BETWEEN ? AND ?) 
          AND statusTransfer=? 
          AND idBankTujuan in (?,?,?,?,?,?,?)
        )
        UNION ALL
        (
          SELECT (SUM(jumlahPembayaran)-SUM(biayaAdmin)-SUM(PPH)) AS debet, 0 AS kredit, balistars_piutang.bankTujuanTransfer AS idBank 
          FROM balistars_piutang
          inner join balistars_penjualan 
          on balistars_penjualan.noNota=balistars_piutang.noNota 
          WHERE (balistars_piutang.tanggalPembayaran BETWEEN ? AND ?) 
          AND balistars_piutang.bankTujuanTransfer!=? 
          AND balistars_piutang.bankTujuanTransfer in (?,?,?,?,?,?,?) 
          AND statusPenjualan="Aktif"
        )
        UNION ALL
        (
          SELECT SUM(nilai) AS debet, 0 AS kredit, idBank 
          FROM balistars_pemasukan_lain 
          WHERE (tanggalPemasukanLain BETWEEN ? AND ?) 
          AND statusFinal=? 
          AND idBank in (?,?,?,?,?,?,?)
          AND statusPemasukanLain="Aktif"
        )
        UNION ALL
        (
          SELECT SUM(dpp+ppn) AS debet, 0 AS kredit, idBank 
          FROM balistars_penjualan_mesin 
          WHERE (tanggalPenjualan BETWEEN ? AND ?) 
          AND idBank in (?,?,?,?,?,?,?) 
          AND statusPenjualanMesin="Aktif"
        )
        UNION ALL
        (
          SELECT 0 AS debet, SUM(nilaiTransfer) AS kredit, idBankAsal AS idBank 
          FROM balistars_bank_transfer
          WHERE (tanggalTransfer BETWEEN ? AND ?) 
          AND statusTransfer=? 
          AND idBankAsal in (?,?,?,?,?,?,?)
        )
        UNION ALL
        (
          SELECT 0 AS debet, SUM(nilaiApproved) AS kredit, bankAsalTransfer AS idBank 
          FROM balistars_kas_kecil_order 
          WHERE (tanggalOrder BETWEEN ? AND ?) 
          AND statusApproval=? 
          AND bankAsalTransfer!=?
          AND bankAsalTransfer in (?,?,?,?,?,?,?) 
          AND statusKasKecilOrder="Aktif"
        )
        UNION ALL
        (
          SELECT 0 AS debet, SUM(jumlahPembayaran) AS kredit, bankAsalTransfer AS idBank 
          FROM  balistars_hutang 
          inner join balistars_pembelian 
          on balistars_pembelian.noNota=balistars_hutang.noNota 
          WHERE  balistars_pembelian.idSupplier!=0 
          AND (tanggalCair BETWEEN ? AND ?) 
          AND balistars_pembelian.statusPembelian=? 
          AND bankAsalTransfer in (?,?,?,?,?,?,?) 
          AND statusHutang="Aktif"
        )
        UNION ALL
        (
          SELECT 0 AS debet, SUM(jumlahPembayaran) AS kredit, bankAsalTransfer AS idBank 
          FROM balistars_hutang_mesin 
          WHERE (tanggalPembayaran BETWEEN ? AND ?) 
          AND jenisPembayaran=? 
          AND statusCair=? 
          AND bankAsalTransfer in (?,?,?,?,?,?,?)
        )
        UNION ALL
        (
          SELECT 0 AS debet, SUM(nilai) AS kredit, idBank 
          FROM balistars_pengeluaran_lain 
          WHERE (tanggalPengeluaranLain BETWEEN ? AND ?) 
          AND statusFinal=?
          AND idBank in (?,?,?,?,?,?,?) 
          AND statusPengeluaranlain="Aktif"
        )
        UNION ALL
        (
          SELECT 0 AS debet, SUM(jumlahPembayaran) AS kredit, bankAsalTransfer AS idBank 
          FROM balistars_hutang_gedung_pembayaran 
          WHERE (tanggalPembayaran BETWEEN ? AND ?) 
          AND jenisPembayaran=? 
          AND statusCair=? 
          AND bankAsalTransfer in (?,?,?,?,?,?,?) 
          AND statusPembayaranHutangGedung="Aktif"
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
				
				-- (
				--  	SELECT SUM(nilaiTransfer) AS debet, 0 AS kredit, idBankTujuan AS idBank 
				--  	FROM balistars_bank_transfer 
				--  	WHERE (tanggalTransfer BETWEEN ? AND ?) 
				--  	AND statusTransfer=? 
				--  	AND idBankTujuan in (?,?,?,?,?,?,?)
				-- )
				-- UNION ALL
				-- (
			 --    SELECT SUM(dpp+ppn) AS debet, 0 AS kredit, idBank 
			 --    FROM balistars_penjualan_mesin 
			 --    WHERE (tanggalPenjualan BETWEEN ? AND ?) 
			 --    AND idBank in (?,?,?,?,?,?,?) 
			 --    AND statusPenjualanMesin="Aktif"
				-- )
				-- UNION ALL
				-- (
				-- 	SELECT 0 AS debet, SUM(nilaiTransfer) AS kredit, idBankAsal AS idBank 
				-- 	FROM balistars_bank_transfer
				--   WHERE (tanggalTransfer BETWEEN ? AND ?) 
				--   AND statusTransfer=? 
				--   AND idBankAsal in (?,?,?,?,?,?,?)
				-- )
				-- UNION ALL
				(
			    SELECT 0 AS debet, SUM(nilaiApproved) AS kredit, bankAsalTransfer AS idBank 
			    FROM balistars_kas_kecil_order 
			    WHERE (tanggalOrder BETWEEN ? AND ?) 
			    AND statusApproval=? 
			    AND bankAsalTransfer!=? 
			    AND bankAsalTransfer in (?,?,?,?,?,?,?)
			    AND statusKasKecilOrder="Aktif"
				)
				-- UNION ALL
				-- (
			 --    SELECT 0 AS debet, SUM(jumlahPembayaran) AS kredit, bankAsalTransfer AS idBank 
			 --    FROM  balistars_hutang 
			 --    inner join balistars_pembelian 
			 --    on balistars_pembelian.noNota=balistars_hutang.noNota 
			 --    WHERE  balistars_pembelian.idSupplier!=0 
			 --    AND (tanggalCair BETWEEN ? AND ?) 
			 --    AND balistars_pembelian.statusPembelian=? 
			 --    AND bankAsalTransfer in (?,?,?,?,?,?,?)
			 --    AND statusHutang="Aktif"
				-- )
				-- UNION ALL
				-- (
			 --    SELECT 0 AS debet, SUM(jumlahPembayaran) AS kredit, bankAsalTransfer AS idBank 
			 --    FROM balistars_hutang_mesin 
			 --    WHERE (tanggalPembayaran BETWEEN ? AND ?) 
			 --    AND jenisPembayaran=? 
			 --    AND statusCair=? 
			 --    AND bankAsalTransfer in (?,?,?,?,?,?,?)
				-- )
				-- UNION ALL
				-- (
				-- 	SELECT 0 AS debet, SUM(jumlahPembayaran) AS kredit, bankAsalTransfer AS idBank 
				-- 	FROM balistars_hutang_gedung_pembayaran 
				--   WHERE (tanggalPembayaran BETWEEN ? AND ?) 
				--   AND jenisPembayaran=? 
				--   AND statusCair=? 
				--   AND bankAsalTransfer in (?,?,?,?,?,?,?) 
				--   AND statusPembayaranHutangGedung="Aktif"
				-- )
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