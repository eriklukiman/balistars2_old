<?php  
function kasPendapatan($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$kode = explode('-', $kodeACC);
	$kodePendapatan = $kode[1];
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		

	
		$tanggalAwal 	 ,$tanggalAkhir, $kodePendapatan,
	);
	$parameter = '0 as saldo';
	//$parameter = 'SUM(debet-kredit) as saldo';
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
		  SELECT keterangan as keterangan, 0 as debet, nilai as kredit, tanggalPemasukanLain as tanggal, timeStamp 
		  from balistars_pemasukan_lain 
		  where (tanggalPemasukanLain between ? and ?) 
		  and idKodePemasukan= ? 
		  and statusFinal='final' 
		  and statusPemasukanLain='Aktif'	   
		)
		as data
		order by timeStamp ASC
	";
	$dataAwal['saldo'] = 0;
	saldoAwal($dataAwal, $total);

	$sql = $db->prepare($sqlProperty);
	$sql->execute($execute);
	$data = $sql->fetchAll();
	// var_dump($sql->errorInfo());
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