<?php  
function kasKecilCabang($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'Kas Kecil ','111',',2',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'approved',
		$tanggalAwalSaldo,$tanggalAkhirSaldo,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,0,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'0','Cash',

		
		// $tanggalAwal,$tanggalAkhir,
		$tanggalAwal,$tanggalAkhir,
		// $tanggalAwal,$tanggalAkhir,0,

		$tanggalAwal,$tanggalAkhir,'111',',2',
		99

	);
	$sql 	 = $db->prepare('
		SELECT saldoAwal, debet, kredit, CONCAT( ? , namaCabang) AS keterangan, CONCAT( ? ,balistars_cabang.idCabang, ? ) AS kodeACC, memorial, 0 as laba 
		FROM balistars_cabang
		LEFT JOIN
		(
			SELECT SUM(data1.debet-data1.kredit) as saldoAwal, idCabang 
			FROM
	    (
        (
          SELECT SUM(nilaiApproved) as debet, 0 as kredit, idCabang 
          FROM balistars_kas_kecil_order 
          WHERE (tanggalOrder between ? and ?) 
          and statusApproval=? 
          and statusKasKecilOrder="Aktif"
          GROUP BY idCabang
        )
        UNION All
        (
          SELECT SUM(nilai) as debet, 0 as kredit, idCabang 
          from balistars_cabang_cash_kecil 
          where (tanggalCabangCashKecil between ? and ?) 
          GROUP BY idCabang
        )
        UNION All
        (
          SELECT 0 as debet, SUM(grandTotal) as kredit, idCabang 
          from balistars_biaya
          where (tanggalBiaya between ? and ?) 
          And statusBiaya="Aktif"
          GROUP BY idCabang
        )
        UNION All
        (
          SELECT 0 as debet, SUM(grandTotal) as kredit, idCabang 
          from balistars_pembelian 
          where (tanggalPembelian between ? and ?) 
          and idSupplier=? 
          and status="Aktif"
          GROUP BY idCabang
        )
        UNION All
        (
          SELECT 0 as debet, SUM(jumlahSetor) as kredit, idCabang 
          from balistars_kas_kecil_setor
          where (tanggalSetor between ? and ?) 
          and statusKasKecilSetor="Aktif"
          GROUP BY idCabang
        )
        UNION All
        (
          SELECT 0 as debet, SUM(grandTotal) as kredit, idCabang 
          from balistars_pembelian_mesin 
          inner join balistars_hutang_mesin 
          on balistars_pembelian_mesin.noNota=balistars_hutang_mesin.noNota 
          where (balistars_pembelian_mesin.tanggalPembelian between ? and ?) 
          and balistars_hutang_mesin.bankAsalTransfer=? 
          and balistars_hutang_mesin.jenisPembayaran=? 
          and statusPembelianMesin="Aktif"
          GROUP BY idCabang
        )
	    )
	    AS data1
	    GROUP BY idCabang
		)
		AS dataAkumulasi
		ON balistars_cabang.idCabang=dataAkumulasi.idCabang
		LEFT JOIN
		(
	    SELECT SUM(data2.debet) as debet, SUM(data2.kredit) as kredit, idCabang FROM
	    (
        
        -- (
        --   SELECT SUM(nilai) as debet, 0 as kredit, idCabang 
        --   from balistars_cabang_cash_kecil 
        --   where (tanggalCabangCashKecil between ? and ?) 
        --   GROUP BY idCabang
        -- )
        -- UNION All
        (
          SELECT 0 as debet, SUM(nilai) as kredit, idCabang 
          from balistars_biaya 
          inner join balistars_biaya_detail
          on balistars_biaya.noNota=balistars_biaya_detail.noNota
          where (tanggalBiaya between ? and ?) 
          and balistars_biaya_detail.statusCancel="oke" 
          and statusBiaya="Aktif"
          and kodeAkunting NOT in ("1313","1314","1316","1161","1323","1324","1326", "1119,2","6380","6398")
          GROUP BY idCabang
        )
        -- UNION All
        -- (
        --   SELECT 0 as debet, SUM(grandTotal) as kredit, idCabang 
        --   from balistars_pembelian 
        --   where (tanggalPembelian between ? and ?) 
        --   and idSupplier=? 
        --   and status="Aktif"
        --   GROUP BY idCabang
        -- )
	    )
	    AS data2
	    GROUP BY idCabang
		)
		AS dataMain
		ON balistars_cabang.idCabang=dataMain.idCabang		
		LEFT JOIN 
		(
			SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
			FROM balistars_memorial 
			WHERE (tanggalMemorial BETWEEN ? AND ?) 
			and statusMemorial="Aktif"
			GROUP BY kodeNeracaLajur
		)
		AS dataMemorial
		ON (CONCAT( ? ,balistars_cabang.idCabang, ?))=dataMemorial.kodeACC
		WHERE balistars_cabang.idCabang!=?
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
					($row['saldoAwal']+$row['debet']-$row['kredit']),
					($row['memorial']),
					($row['laba']),
					($row['saldoAwal']+$row['debet']-$row['kredit']+$row['memorial']),
					$total);
	}
	return $total;
}
?>