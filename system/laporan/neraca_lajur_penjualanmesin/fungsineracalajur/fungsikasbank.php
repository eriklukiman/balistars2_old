<?php  
function kasBank($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);

	//echo '"'.$tanggalAwal.'" AND "'.$tanggalAkhir.'"<br>';
	//echo '"'.$tanggalAwalSaldo.'" AND "'.$tanggalAkhirSaldo.'"<br>';
	
	$execute = array(
		'112',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Final',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Final',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'final',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'0',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'final',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'final',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'approved',0,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Giro','Cair',  
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'final',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Giro','Cair', 
		$tanggalAwalSaldo,$tanggalAkhirSaldo, 'DP','Aktif', 
		$tanggalAwalSaldo,$tanggalAkhirSaldo, 'Pelunasan', 'Aktif', 
		
		$tanggalAwal,$tanggalAkhir,
		// $tanggalAwal,$tanggalAkhir,'Giro','Cair',  
		// $tanggalAwal,$tanggalAkhir,'Giro','Cair',
		// $tanggalAwal,$tanggalAkhir, 'DP', 'Aktif', 
		// $tanggalAwal,$tanggalAkhir, 'Pelunasan', 'Aktif', 

		$tanggalAwal,$tanggalAkhir,
		'112',
		30,22,23,24,25,
		33,37
	);
	$sql 	 = $db->prepare('
		SELECT debet, kredit, saldoAwal, namaBank AS keterangan, 
		CONCAT( ? ,balistars_bank.idBank) AS kodeACC, memorial, 0 AS laba, balistars_bank.idBank 
		FROM 
		balistars_bank 
		LEFT JOIN
		(
			SELECT SUM(debet-kredit) AS saldoAwal, idBank FROM
	    (
        (
           SELECT SUM(jumlahSetor) AS debet, 0 AS kredit, idBank 
           FROM balistars_setor_penjualan_cash 
           WHERE (tanggalSetor BETWEEN ? AND ?) 
           AND statusFinal=? 
           AND statusSetor="Aktif"
           GROUP BY idBank
        )
        UNION all
        (
          SELECT SUM(jumlahSetor) AS debet, 0 AS kredit, idBank 
          FROM balistars_kas_kecil_setor 
          WHERE (tanggalSetor BETWEEN ? AND ?) 
          AND statusFinal=? 
          AND statusKasKecilSetor="Aktif" 
          GROUP BY idBank
        )
        UNION all
        (
          SELECT SUM(nilaiTransfer) AS debet, 0 AS kredit, idBankTujuan AS idBank 
          FROM balistars_bank_transfer 
          WHERE (tanggalTransfer BETWEEN ? AND ?) 
          AND statusTransfer=? 
          GROUP BY idBankTujuan
        )
        UNION all
        (
          SELECT (SUM(jumlahPembayaran)-SUM(biayaAdmin)-SUM(PPH)) AS debet, 0 AS kredit, balistars_piutang.bankTujuanTransfer AS idBank 
          FROM balistars_piutang
          inner join balistars_penjualan 
          on balistars_penjualan.noNota=balistars_piutang.noNota 
          WHERE (balistars_piutang.tanggalPembayaran BETWEEN ? AND ?) 
          AND balistars_piutang.bankTujuanTransfer!=? 
          AND statusPenjualan="Aktif"
          GROUP BY balistars_piutang.bankTujuanTransfer
        )
        UNION all
        (
          SELECT SUM(nilai) AS debet, 0 AS kredit, idBank 
          FROM balistars_pemasukan_lain 
          WHERE (tanggalPemasukanLain BETWEEN ? AND ?) 
          AND statusFinal=? 
          AND statusPemasukanLain="Aktif"
          GROUP BY idBank
        )
        UNION all
        (
          SELECT SUM(dpp+ppn) AS debet, 0 AS kredit, idBank 
          FROM balistars_penjualan_mesin 
          WHERE (tanggalPenjualan BETWEEN ? AND ?) 
          AND statusPenjualanMesin="Aktif"
          GROUP BY idBank
        )
        UNION all
        (
          SELECT 0 AS debet, SUM(nilaiTransfer) AS kredit, idBankAsal AS idBank 
          FROM balistars_bank_transfer
          WHERE (tanggalTransfer BETWEEN ? AND ?) 
          AND statusTransfer=? 
          GROUP BY idBankAsal
        )
        UNION all
        (
          SELECT 0 AS debet, SUM(nilaiApproved) AS kredit, bankAsalTransfer AS idBank 
          FROM balistars_kas_kecil_order 
          WHERE (tanggalOrder BETWEEN ? AND ?) 
          AND statusApproval=? 
          AND bankAsalTransfer!=? 
          AND statusKasKecilOrder="Aktif"
          GROUP BY bankAsalTransfer
        )
        UNION all
        (
          SELECT 0 AS debet, SUM(jumlahPembayaran) AS kredit, bankAsalTransfer AS idBank 
          FROM balistars_hutang_mesin 
          WHERE (tanggalPembayaran BETWEEN ? AND ?) 
          AND jenisPembayaran=? 
          AND statusCair=? 
          GROUP BY bankAsalTransfer
        )
        UNION all
        (
          SELECT 0 AS debet, SUM(nilai) AS kredit, idBank 
          FROM balistars_pengeluaran_lain 
          WHERE (tanggalPengeluaranLain BETWEEN ? AND ?) 
          AND statusFinal=? 
          AND statusPengeluaranlain="Aktif"
          GROUP BY idBank
        )
        UNION all
        (
          SELECT 0 AS debet, SUM(jumlahPembayaran) AS kredit, bankAsalTransfer AS idBank 
          FROM balistars_hutang_gedung_pembayaran 
          WHERE (tanggalPembayaran BETWEEN ? AND ?) 
          AND jenisPembayaran=? 
          AND statusCair=? 
          AND statusPembayaranHutangGedung="Aktif"
          GROUP BY bankAsalTransfer
        )
        UNION all
        (
        	SELECT 0 as debet, SUM(dp) as kredit, idBank FROM balistars_dpgiro
        	WHERE (tanggalCairDp BETWEEN ? AND ?) 
        	AND jenisGiro=?
        	AND statusDpGiro=?
        	GROUP BY idBank
        )
        UNION all
        (
        	SELECT 0 as debet, SUM(dp) as kredit, idBank FROM balistars_dpgiro
        	WHERE (tanggalCairDp BETWEEN ? AND ?) 
        	AND jenisGiro=?
        	AND statusDpGiro=?
        	GROUP BY idBank
        )
	    )
	    AS data1
	    GROUP BY idBank
		)
		AS dataAkumulasi
		ON balistars_bank.idBank=dataAkumulasi.idBank
		LEFT JOIN
		(
			SELECT SUM(debet) AS debet, SUM(kredit) AS kredit, idBank 
			FROM
			(
				
				(
				  SELECT SUM(dpp+ppn) AS debet, 0 AS kredit, idBank 
				  FROM balistars_penjualan_mesin 
				  WHERE (tanggalPenjualan BETWEEN ? AND ?) 
				  AND statusPenjualanMesin="Aktif"
				  GROUP BY idBank
				)
				-- UNION all
				-- (
			 --    SELECT 0 AS debet, SUM(jumlahPembayaran) AS kredit, bankAsalTransfer AS idBank 
			 --    FROM balistars_hutang_mesin 
			 --    WHERE (tanggalPembayaran BETWEEN ? AND ?) 
			 --    AND jenisPembayaran=? 
			 --    AND statusCair=? 
			 --    GROUP BY bankAsalTransfer
				-- )
				-- UNION all
				-- (
				-- 	SELECT 0 AS debet, SUM(jumlahPembayaran) AS kredit, bankAsalTransfer AS idBank 
				-- 	FROM balistars_hutang_gedung_pembayaran 
			 --    WHERE (tanggalPembayaran BETWEEN ? AND ?) 
			 --    AND jenisPembayaran=? 
			 --    AND statusCair=? 
			 --    AND statusPembayaranHutangGedung="Aktif"
			 --    GROUP BY bankAsalTransfer
				-- )
		  --       UNION all
		  --       (
		  --       	SELECT 0 as debet, SUM(dp) as kredit, idBank FROM balistars_dpgiro
		  --       	WHERE (tanggalCairDp BETWEEN ? AND ?) 
		  --       	AND jenisGiro=?
		  --       	AND statusDpGiro=?
		  --       	GROUP BY idBank
		  --       )
		  --       UNION all
		  --       (
		  --       	SELECT 0 as debet, SUM(dp) as kredit, idBank FROM balistars_dpgiro
		  --       	WHERE (tanggalCairDp BETWEEN ? AND ?) 
		  --       	AND jenisGiro=?
		  --       	AND statusDpGiro=?
		  --       	GROUP BY idBank
		  --       )
			)
			AS data2
			GROUP BY idBank
		)
		AS dataMain
		ON balistars_bank.idBank=dataMain.idBank
		LEFT JOIN 
		(
		    SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
		    FROM balistars_memorial 
		    WHERE (tanggalMemorial BETWEEN ? AND ?) 
		    AND statusMemorial="Aktif"
		    GROUP BY kodeNeracaLajur
		)
		AS dataMemorial
		ON (CONCAT( ? ,balistars_bank.idBank))=dataMemorial.kodeACC
		WHERE balistars_bank.idBank!=? 
		and balistars_bank.idBank!=? 
		and balistars_bank.idBank!=? 
		and balistars_bank.idBank!=? 
		and balistars_bank.idBank!=? 
		and balistars_bank.idBank!=? 
		and balistars_bank.idBank!=?
    ORDER BY 
    FIELD(balistars_bank.idBank,
    	27,15,19,8,9,
    	10,11,12,13,20,
    	21,26,14,22,23,
    	24,25)

	');
	$sql->execute($execute);
	$data = $sql->fetchAll();
	//var_dump($sql->errorInfo());
	foreach ($data AS $row) {
		if($row['idBank']==27){
			$row['keterangan']='KAS PAK SUI';
		}
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