<?php  
function kasPemutihanPiutang($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$kode = explode('-', $kodeACC);
	$kodeA = $kode[1];
	$executeAwal = array(
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodeA,
	);
		$execute = array(
		$tanggalAwal 	 ,$tanggalAkhir, $kodeA,
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
			SELECT balistars_penjualan_detail.namaBahan as keterangan, 0 as kredit, balistars_pemutihan_piutang.sisaPiutang as debet, tanggalPenjualan as tanggal, balistars_pemutihan_piutang.timeStamp 
			FROM balistars_pemutihan_piutang 
			INNER JOIN balistars_penjualan 
			ON balistars_pemutihan_piutang.noNota=balistars_penjualan.noNota
			INNER JOIN balistars_penjualan_detail 
			ON balistars_pemutihan_piutang.noNota=balistars_penjualan_detail.noNota
			WHERE (tanggalPenjualan BETWEEN ? AND ?) 
			AND tipePenjualan = ? 
			AND statusPemutihan='Aktif'
			group by balistars_penjualan.noNota
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