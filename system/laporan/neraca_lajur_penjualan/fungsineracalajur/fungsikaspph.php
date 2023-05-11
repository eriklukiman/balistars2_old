<?php  
function kasPPH($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		'2142', 'PPH',
		$tanggalAwal,$tanggalAkhir,
		$tanggalAwal,$tanggalAkhir,
	);
	$sql = $db->prepare('
		SELECT *, dataMain.kodeACC as kodeACC 
		FROM
		(
			(
				SELECT  ? as kodeACC, ? as keterangan, 0 as saldoAwal, 0 as kredit, SUM(PPH) as debet 
				FROM balistars_piutang 
				inner join balistars_penjualan 
				on balistars_piutang.noNota=balistars_penjualan.noNota 
				where (tanggalPembayaran between ? and ?)
				and statusPenjualan="Aktif"
			)
			as dataMain
			LEFT JOIN 
			(
		    SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
		    FROM balistars_memorial 
		    WHERE (tanggalMemorial BETWEEN ? AND ?) 
		    AND statusMemorial="Aktif"
		    GROUP BY kodeNeracaLajur
			)
			AS dataMemorial
			ON dataMain.kodeACC = dataMemorial.kodeACC
		)
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