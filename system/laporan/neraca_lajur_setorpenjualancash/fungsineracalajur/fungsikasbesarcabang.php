<?php  
function kasBesarCabang($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);

	
	$execute = array(
		'Kas Besar ','111',',1', 
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'0',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'0',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'0',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Final',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,

		
		$tanggalAwal,$tanggalAkhir,

		$tanggalAwal,$tanggalAkhir,'111',',1',
		9
	);
	$sql 	 = $db->prepare('
		SELECT saldoAwal, debet, kredit, CONCAT( ? , namaCabang) AS keterangan, CONCAT( ? ,balistars_cabang.idCabang, ? ) AS kodeACC, memorial, 0 as laba
			FROM 
      	balistars_cabang
    	LEFT JOIN
		(
		    SELECT SUM(data1.debetAwal-data1.kreditAwal) AS saldoAwal, data1.idCabang 
		    FROM
	    	(
		        (
		            SELECT SUM(jumlahPembayaran) AS debetAwal, 0 AS kreditAwal, idCabang 
		            FROM balistars_piutang 
		            INNER JOIN balistars_penjualan 
		            ON balistars_penjualan.noNota=balistars_piutang.noNota
		            WHERE (balistars_piutang.tanggalPembayaran BETWEEN ? AND ?) 
		            AND balistars_piutang.bankTujuanTransfer!=? 
		            AND statusPenjualan="Aktif"
		            GROUP BY balistars_penjualan.idCabang
		        )
		        UNION all
		        (
		        	SELECT  0 AS debetAwal, SUM(jumlahPembayaran) AS kreditAwal, idCabang 
		        	FROM balistars_piutang 
		        	INNER JOIN balistars_penjualan 
				    ON balistars_penjualan.noNota = balistars_piutang.noNota
				    WHERE (balistars_piutang.tanggalPembayaran BETWEEN ? AND ?) 
				    AND balistars_piutang.bankTujuanTransfer!= ? 
				    AND statusPenjualan="Aktif"
				    GROUP BY balistars_penjualan.idCabang
		        )
		        UNION all
		        (
					SELECT SUM(jumlahPembayaran) as debetAwal, SUM(PPH+biayaAdmin) as kreditAwal, idCabang 
					FROM  balistars_piutang 
					INNER JOIN balistars_penjualan 
					ON balistars_penjualan.noNota=balistars_piutang.noNota
				    WHERE (balistars_piutang.tanggalPembayaran BETWEEN ? AND ?) 
				    AND balistars_piutang.bankTujuanTransfer=? 
				    AND statusPenjualan="Aktif"
				    GROUP BY idCabang		        
				)
		        UNION all
		        (
		            SELECT SUM(nilai) AS debetAwal, 0 AS kreditAwal, idCabang 
		            FROM balistars_cabang_cash
		            where (tanggalCabangCash BETWEEN ? AND ?) 
		            AND statusFinal=? 
		            GROUP BY idCabang
		        )
		        UNION all
		        (
		            SELECT 0 AS debetAwal, SUM(jumlahSetor) AS kreditAwal, idCabang 
		            FROM balistars_setor_penjualan_cash 
		            WHERE (tanggalSetor BETWEEN ? AND ?) 
		            AND statusSetor="Aktif"
		            GROUP BY idCabang
		        )
		    )
		    AS data1
		    GROUP BY data1.idCabang
			)
			AS dataAkumulasi
      ON balistars_cabang.idCabang=dataAkumulasi.idCabang
			LEFT JOIN
			(
		    SELECT SUM(data2.debet) AS debet, SUM(data2.kredit) AS kredit, data2.idCabang AS idCabang FROM
		    (
		
		        (
		        -- kredit setor penjualan cash
		            SELECT 0 AS debet, SUM(jumlahSetor) AS kredit, idCabang 
		            FROM balistars_setor_penjualan_cash 
		            WHERE (tanggalSetor BETWEEN ? AND ?) 
		            AND statusFinal="Final" 
		            AND statusSetor="Aktif"
		            GROUP BY idCabang
		        )
		    )
		    AS data2
		    GROUP BY data2.idCabang
		)
		AS dataMain
		ON balistars_cabang.idCabang=dataMain.idCabang
		LEFT JOIN 
		(
			SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
			FROM balistars_memorial 
			WHERE (tanggalMemorial BETWEEN ? AND ?) 
			AND statusMemorial="Aktif" 
			GROUP BY kodeNeracaLajur
		)
		AS dataMemorial
		ON (CONCAT( ? ,balistars_cabang.idCabang, ?))=dataMemorial.kodeACC
       	WHERE balistars_cabang.idCabang!=?
       	AND balistars_cabang.statusCabang="Aktif"
	');
	$sql->execute($execute);
	$data = $sql->fetchAll();
	//var_dump($sql->errorInfo());
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
					($row['laba']),
					($row['saldoAwal']+$row['debet']-$row['kredit']+$row['memorial']),
					$total);
	}
	return $total;
}
?>