<?php  
function kasKendaraan($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$tanggalAwalSaldo  = '2015-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);

	$executeAwal = array(
		$tanggalAwalSaldo   ,$tanggalAkhirSaldo, $kodeACC,
		$tanggalAwalSaldo   ,$tanggalAkhirSaldo, $kodeACC,
	);
	$execute = array(
		$tanggalAwal 	 ,$tanggalAkhir, $kodeACC,
		$tanggalAwal 	 ,$tanggalAkhir, $kodeACC,
		
	);

	//$parameter = '0 as saldo';
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
				SELECT balistars_pembelian_mesin_detail.noNota as keterangan, 0 as kredit, nilai AS debet, tanggalPembelian as tanggal, balistars_pembelian_mesin_detail.timeStamp
		    FROM balistars_pembelian_mesin_detail 
		    inner join balistars_pembelian_mesin 
		    on balistars_pembelian_mesin_detail.noNota=balistars_pembelian_mesin.noNota 
		    where (tanggalPembelian BETWEEN ? and ?) 
		    and kodeAkunting = ? 
		    and jenisPPN != 'Include' 
		    and statusPembelianMesin='Aktif'
	    )
	    UNION ALL
	    (
	    	SELECT balistars_pembelian_mesin_detail.noNota as keterangan, 0 as kredit, ((100/110)*hargaSatuan*qty) AS debet, tanggalPembelian as tanggal, balistars_pembelian_mesin_detail.timeStamp 
	    	FROM balistars_pembelian_mesin_detail 
	    	inner join balistars_pembelian_mesin 
	    	on balistars_pembelian_mesin_detail.noNota=balistars_pembelian_mesin.noNota 
	    	where (tanggalPembelian BETWEEN ? and ?) 
	    	and kodeAkunting = ? 
	    	and jenisPPN = 'Include' 
	    	and statusPembelianMesin='Aktif'
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