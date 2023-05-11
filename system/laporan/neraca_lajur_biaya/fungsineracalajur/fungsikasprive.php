<?php  
function kasPrive($total,$db,$tanggalAwal,$tanggalAkhir,$tipe)
{
	$tanggakPecah 	   = explode('-', $tanggalAwal);
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		$tanggalAwal,$tanggalAkhir,'oke', '3140',
		$tanggalAwal,$tanggalAkhir, '3140'
	);
	$sql 	 = $db->prepare('
		SELECT *, balistars_kode_akunting.kodeAkunting as kodeACC 
		FROM balistars_kode_akunting
		LEFT JOIN
		(
			SELECT 0 as saldoAwal ,SUM(debet) as debet, SUM(kredit) as kredit, kodeAkunting
			FROM
			(
		    (
		      SELECT SUM(grandTotal) as debet, 0 as kredit, kodeAkunting 
		      from balistars_biaya 
		      inner join balistars_biaya_detail 
		      on balistars_biaya.noNota=balistars_biaya_detail.noNota 
		      where (tanggalBiaya between ? and ?) 
		      and balistars_biaya_detail.statusCancel=? 
		      and kodeAkunting=? 
		      and statusBiaya="Aktif"
		    )
			)
			as data1
			GROUP BY kodeAkunting
		)
		as dataMain
		ON dataMain.kodeAkunting=balistars_kode_akunting.kodeAkunting
		LEFT JOIN 
		(
			SELECT SUM(nilaiMemorial) AS memorial, kodeNeracaLajur AS kodeACC 
			FROM balistars_memorial 
			WHERE (tanggalMemorial BETWEEN ? and ?) 
			and statusMemorial="Aktif"
			GROUP BY kodeNeracaLajur
		)
		AS dataMemorial
		ON balistars_kode_akunting.kodeAkunting=dataMemorial.kodeACC
		WHERE balistars_kode_akunting.kodeAkunting = ?
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