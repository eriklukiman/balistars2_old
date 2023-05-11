<?php  
function kasPenjualanJL($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$kode = explode('-', $kodeACC);
	$kodeCabang = $kode[1];

	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$execute = array(
		
		$tanggalAwal 	 ,$tanggalAkhir, $kodeCabang,

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
		  SELECT noNota as keterangan,(grandTotal-nilaiPPN) as kredit, 0 as debet, tanggalPenjualan as tanggal, timeStamp 
		  FROM balistars_penjualan 
		  where (tanggalPenjualan between ? and ?) 
		  and statusFinalNota='final' 
		  and tipePenjualan= 'A2' 
		  and idCabang = ? 
		  and statusPenjualan='Aktif'
		        
		)
		as data
		order by timeStamp ASC
	";
	$dataAwal['saldo'] = 0;
	saldoAwal($dataAwal, $total);

	$sql = $db->prepare($sqlProperty);
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