<?php  
function kasBesar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$kode = explode('-', $kodeACC);
	$kodebesar = $kode[1];
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$executeAwal = array(
		'2000-01-01' 	 ,'2000-01-01', $kodebesar, 'Aktif',
		'2000-01-01' 	 ,'2000-01-01', $kodebesar, 'Aktif',
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebesar, 'Aktif',
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebesar, 
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebesar, 'Aktif',
		
	);
	$execute = array(
		$tanggalAwal 	 ,$tanggalAkhir, $kodebesar, 'Aktif',
		$tanggalAwal 	 ,$tanggalAkhir, $kodebesar, 'Aktif',
		$tanggalAwal 	 ,$tanggalAkhir, $kodebesar, 'Aktif',
		$tanggalAwal 	 ,$tanggalAkhir, $kodebesar,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebesar, 'Aktif',
		
	);
	$parameter = 'SUM(debet-kredit) as saldo';
	$sqlProperty = 
	"
		SELECT 
			keterangan, 
			kredit, 
			debet,
			tanggal,
			timeStamp,
			".$parameter."
		FROM
		(
			(
				SELECT balistars_piutang.noNota as keterangan, jumlahPembayaran AS debet, 0 AS kredit, balistars_piutang.tanggalPembayaran as tanggal, balistars_piutang.timeStamp as timeStamp 
				FROM balistars_piutang 
				INNER JOIN balistars_penjualan 
				ON balistars_penjualan.noNota = balistars_piutang.noNota
				WHERE (balistars_piutang.tanggalPembayaran BETWEEN ? AND ?) 
				AND balistars_piutang.bankTujuanTransfer!='0' 
				AND balistars_penjualan.idCabang = ?
				AND statusPenjualan=?
			)
			UNION ALL
			(
				SELECT 'Transfer Bank' as keterangan, 0 AS debet, jumlahPembayaran AS kredit, balistars_piutang.tanggalPembayaran as tanggal, balistars_piutang.timeStamp as timeStamp
				FROM balistars_piutang 
				INNER JOIN balistars_penjualan 
				ON balistars_penjualan.noNota = balistars_piutang.noNota
			  WHERE (balistars_piutang.tanggalPembayaran BETWEEN ? AND ?) 
			  AND balistars_piutang.bankTujuanTransfer!='0' 
			  AND balistars_penjualan.idCabang = ? 
			  AND statusPenjualan=?
			)
			UNION ALL
			(
		    SELECT balistars_piutang.noNota as keterangan, (jumlahPembayaran) as debet, (PPH+biayaAdmin) kredit, balistars_piutang.tanggalPembayaran as tanggal, balistars_piutang.timeStamp as timeStamp
		    FROM balistars_piutang 
		    INNER JOIN balistars_penjualan 
		    ON balistars_penjualan.noNota=balistars_piutang.noNota 
		    WHERE (balistars_piutang.tanggalPembayaran BETWEEN ? AND ?) 
		    AND balistars_piutang.bankTujuanTransfer='0' 
		    AND idCabang = ? 
		    AND statusPenjualan=?
 			)
  		UNION ALL
  		(
      	SELECT keterangan as keterangan, nilai AS debet, 0 AS kredit, tanggalCabangCash as tanggal, timeStamp 
      	FROM balistars_cabang_cash 
      	where (tanggalCabangCash BETWEEN ? AND ?) 
      	AND statusFinal='Final' 
      	AND idCabang = ?
  		)
  		UNION ALL
  		(
		    SELECT 'Setor Penjualan Cash' as keterangan, 0 AS debet, jumlahSetor AS kredit, tanggalSetor as tanggal, timeStamp 
		    FROM balistars_setor_penjualan_cash 
		    WHERE (tanggalSetor BETWEEN ? AND ?) 
		    AND idCabang = ? 
		    AND statusSetor=?
  		)	    
		)
		as data
		order by timeStamp ASC
	";
	$sqlAwal = $sqlProperty;
	$sqlAwal = $db->prepare($sqlAwal);
	$sqlAwal->execute($executeAwal);
	$dataAwal = $sqlAwal->fetch();

	$total = saldoAwal($dataAwal, $total);

	$sqlProperty = str_replace($parameter,'0 as saldo',$sqlProperty);
	$sql = $sqlProperty;
	$sql = $db->prepare($sql);
	$sql->execute($execute);
	$data = $sql->fetchAll();
	//var_dump($sql->errorInfo());
	foreach ($data as $row) {
		$total	  = tampilTable(
					($row['keterangan']),
					($row['tanggal']),
					($row['debet']),
					($row['kredit']),
					($row['saldo']),
					$total);
	}
	return $total;
}
?>