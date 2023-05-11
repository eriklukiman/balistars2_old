<?php  
function kasPiutangA2($db,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$executeAwal = array(
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo,
	);
		$execute = array(
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir,
		$tanggalAwal 	 ,$tanggalAkhir,
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
				SELECT noNota as keterangan, jumlahPembayaranAwal as kredit, 0 as debet, tanggalPenjualan as tanggal, timeStamp 
				FROM balistars_penjualan 
				WHERE (tanggalPenjualan BETWEEN ? and ? ) 
				AND tipePenjualan='A2' 
				AND statusPenjualan='Aktif'
			)
			UNION ALL
			(
				SELECT balistars_piutang.noNota as keterangan, jumlahPembayaran as kredit, 0 as debet, tanggalPembayaran as tanggal, balistars_piutang.timeStamp as timeStamp 
				FROM balistars_piutang 
				INNER JOIN balistars_penjualan 
				on balistars_piutang.noNota=balistars_penjualan.noNota 
				WHERE idPiutang NOT IN 
					(SELECT MIN(idPiutang) as idPiutangMin 
					FROM `balistars_piutang` GROUP BY noNota) 
				AND (tanggalPembayaran BETWEEN  ? and ?) 
				AND tipePenjualan='A2' 
				AND statusPenjualan='Aktif'
			)
			UNION ALL
			(
				SELECT noNota as keterangan, 0 as kredit, grandTotal as debet, tanggalPenjualan as tanggal, timeStamp 
				FROM balistars_penjualan 
				WHERE (tanggalPenjualan BETWEEN ? AND ?) 
				AND tipePenjualan='A2' 
				AND statusPenjualan='Aktif'
			)
			UNION ALL
			(
				SELECT CONCAT('Pemutihan ',balistars_pemutihan_piutang.noNota) as keterangan, balistars_pemutihan_piutang.sisaPiutang as kredit, 0 as debet, tanggalPenjualan as tanggal, balistars_pemutihan_piutang.timeStamp 
				FROM balistars_pemutihan_piutang 
				INNER JOIN balistars_penjualan 
				on balistars_pemutihan_piutang.noNota=balistars_penjualan.noNota
				WHERE (tanggalPenjualan BETWEEN ? AND ?) 
				AND tipePenjualan = 'A2' 
				AND statusPemutihan='Aktif'
				group by balistars_penjualan.noNota)   
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