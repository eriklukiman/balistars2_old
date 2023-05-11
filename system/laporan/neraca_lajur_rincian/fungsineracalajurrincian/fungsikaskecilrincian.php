<?php  
function kasKecil($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	$kode = explode('-', $kodeACC);
	$kodebank = $kode[1];

	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$executeAwal = array(
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		$tanggalAwalSaldo 	 ,$tanggalAkhirSaldo, $kodebank,
		
	);
	$execute = array(
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		$tanggalAwal 	 ,$tanggalAkhir, $kodebank,
		
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
        SELECT keterangan as keterangan, 0 as kredit, nilaiApproved as debet, tanggalOrder as tanggal, timeStamp 
        FROM balistars_kas_kecil_order 
        WHERE (tanggalOrder between ? and ?) 
        and statusApproval='approved' 
        and idCabang = ? 
        and statusKasKecilOrder='Aktif'
      )
      UNION ALL
      (
        SELECT keterangan as keterangan,0 as kredit, nilai as debet, tanggalCabangCashKecil as tanggal, timeStamp 
        FROM balistars_cabang_cash_kecil 
        WHERE (tanggalCabangCashKecil between ? and ?) 
        and idCabang = ?
      )
      UNION ALL
      (
        SELECT noNota as keterangan, grandTotal as kredit, 0 as debet, tanggalBiaya as tanggal, timeStamp 
        FROM balistars_biaya
        WHERE (tanggalBiaya between ? and ?) 
        and idCabang = ?
        AND statusBiaya='Aktif'
      )
      UNION ALL
      (
        SELECT noNota as keterangan, grandTotal as kredit,  0 as debet, tanggalPembelian as tanggal, timeStamp 
        FROM balistars_pembelian 
        WHERE (tanggalPembelian between ? and ?) 
        and idSupplier= '0' 
        and idCabang = ? 
        and status='Aktif'
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, jumlahSetor as kredit, 0 as debet,  tanggalSetor as tanggal, timeStamp 
        FROM balistars_kas_kecil_setor
        WHERE (tanggalSetor between ? and ?) 
        and idCabang = ? 
        and statusKasKecilSetor='Aktif'
      )
      UNION ALL
      (
        SELECT balistars_pembelian_mesin.noNota as keterangan, grandTotal as kredit,0 as debet, balistars_pembelian_mesin.tanggalPembelian as tanggal, balistars_pembelian_mesin.timeStamp 
        FROM balistars_pembelian_mesin 
        INNER JOIN balistars_hutang_mesin 
        ON balistars_pembelian_mesin.noNota=balistars_hutang_mesin.noNota 
        WHERE (balistars_pembelian_mesin.tanggalPembelian between ? and ?) 
        and balistars_hutang_mesin.bankAsalTransfer= '0' 
        and balistars_hutang_mesin.jenisPembayaran= 'Cash' 
        and idCabang = ? 
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
	var_dump($sql->errorInfo());
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