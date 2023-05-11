<?php  
function hutangLancar($db,$kodeACC,$tanggalAwal,$tanggalAkhir,$total)
{
	
	$tanggalAwalSaldo  = '2019-01-01';
	$tanggalAkhirSaldo = waktuKemarin($tanggalAwal);
	$executeAwal = array(
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Final', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Final', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'final', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'0', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'final', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo, 30, 22, 23, 24, 25,33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'final', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'approved',0, 30, 22, 23, 24, 25,33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Lunas', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Giro','Cair', 30, 22, 23, 24, 25,33, 37,  
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'final', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwalSaldo,$tanggalAkhirSaldo,'Giro','Cair',  30, 22, 23, 24, 25,33, 37, 
		
	);

	$execute = array(
		$tanggalAwal,$tanggalAkhir,'Final', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwal,$tanggalAkhir,'Final', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwal,$tanggalAkhir,'final', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwal,$tanggalAkhir,'0', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwal,$tanggalAkhir,'final', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwal,$tanggalAkhir, 30, 22, 23, 24, 25,33, 37,
		$tanggalAwal,$tanggalAkhir,'final', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwal,$tanggalAkhir,'approved',0, 30, 22, 23, 24, 25,33, 37,
		$tanggalAwal,$tanggalAkhir,'Lunas', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwal,$tanggalAkhir,'Giro','Cair', 30, 22, 23, 24, 25,33, 37,  
		$tanggalAwal,$tanggalAkhir,'final', 30, 22, 23, 24, 25,33, 37,
		$tanggalAwal,$tanggalAkhir,'Giro','Cair', 30, 22, 23, 24, 25,33, 37,
		
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
        SELECT 'Setor Penjualan Cash' as keterangan, 0 AS kredit, jumlahSetor AS debet, tanggalSetor as tanggal, timeStamp 
        FROM balistars_setor_penjualan_cash 
        WHERE (tanggalSetor BETWEEN ? AND ?) 
        AND statusFinal= ? 
        AND idBank in (?,?,?,?,?,?,?) 
        AND statusSetor='Aktif'
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, 0 AS kredit, jumlahSetor AS debet, tanggalSetor as tanggal, timeStamp 
        FROM balistars_kas_kecil_setor 
        WHERE (tanggalSetor BETWEEN ? AND ?) 
        AND statusFinal=? 
        AND idBank in (?,?,?,?,?,?,?) 
        AND statusKasKecilSetor='Aktif'
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, 0 AS kredit, nilaiTransfer AS debet, tanggalTransfer as tanggal, timeStamp 
        FROM balistars_bank_transfer 
        WHERE (tanggalTransfer BETWEEN ? AND ?) 
        AND statusTransfer=? 
        AND idBankTujuan in (?,?,?,?,?,?,?)
      )
      UNION ALL
      (
        SELECT balistars_piutang.noNota as keterangan, 0 AS kredit, (jumlahPembayaran-biayaAdmin-PPH) AS debet, balistars_piutang.tanggalPembayaran as tanggal,  balistars_piutang.timeStamp as timeStamp 
        FROM balistars_piutang 
        inner join balistars_penjualan 
        on balistars_penjualan.noNota=balistars_piutang.noNota 
        WHERE (balistars_piutang.tanggalPembayaran BETWEEN ? AND ?) 
        AND balistars_piutang.bankTujuanTransfer!=? 
        AND balistars_piutang.bankTujuanTransfer in (?,?,?,?,?,?,?) 
        AND statusPenjualan='Aktif'
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, 0 AS kredit, nilai AS debet, tanggalPemasukanLain as tanggal, timeStamp 
        FROM balistars_pemasukan_lain 
        WHERE (tanggalPemasukanLain BETWEEN ? AND ?) 
        AND statusFinal=? 
        AND idBank in (?,?,?,?,?,?,?) 
        AND statusPemasukanLain='Aktif'
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, 0 AS kredit, (dpp+ppn) AS debet, tanggalPenjualan as tanggal, timeStamp 
        FROM balistars_penjualan_mesin 
        WHERE (tanggalPenjualan BETWEEN ? AND ?) 
        AND idBank in (?,?,?,?,?,?,?) 
        AND statusPenjualanMesin='Aktif'
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, nilaiTransfer AS kredit, 0 AS debet, tanggalTransfer as tanggal, timeStamp 
        FROM balistars_bank_transfer
        WHERE (tanggalTransfer BETWEEN ? AND ?) 
        AND statusTransfer=? 
        AND idBankAsal in (?,?,?,?,?,?,?)
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, nilaiApproved AS kredit, 0 AS debet, tanggalOrder as tanggal, timeStamp 
        FROM balistars_kas_kecil_order 
        WHERE (tanggalOrder BETWEEN ? AND ?) 
        AND statusApproval=? 
        AND bankAsalTransfer!=?
        AND bankAsalTransfer in (?,?,?,?,?,?,?) 
        AND statusKasKecilOrder='Aktif'
      )
      UNION ALL
      (
        SELECT balistars_hutang.noNota as keterangan, jumlahPembayaran AS kredit, 0 AS debet, tanggalCair as tanggal, balistars_hutang.timeStamp 
        FROM  balistars_hutang 
        inner join balistars_pembelian 
        on balistars_pembelian.noNota=balistars_hutang.noNota 
        WHERE  balistars_pembelian.idSupplier!=0 
        AND (tanggalCair BETWEEN ? AND ?) 
        AND balistars_pembelian.statusPembelian=? 
        AND bankAsalTransfer in (?,?,?,?,?,?,?) 
        AND statusHutang='Aktif'
      )
      UNION ALL
      (
        SELECT noNota as keterangan, jumlahPembayaran AS kredit, 0 AS debet, tanggalPembayaran as tanggal, timeStamp 
        FROM balistars_hutang_mesin 
        WHERE (tanggalPembayaran BETWEEN ? AND ?) 
        AND jenisPembayaran=? 
        AND statusCair=? 
        AND bankAsalTransfer in (?,?,?,?,?,?,?)
      )
      UNION ALL
      (
        SELECT keterangan as keterangan, nilai AS kredit, 0 AS debet, tanggalPengeluaranLain as tanggal, timeStamp 
        FROM balistars_pengeluaran_lain 
        WHERE (tanggalPengeluaranLain BETWEEN ? AND ?) 
        AND statusFinal=?
        AND idBank in (?,?,?,?,?,?,?) 
        AND statusPengeluaranLain='Aktif'
      )
      UNION ALL
      (
        SELECT 'Pembayaran Gedung' as keterangan, jumlahPembayaran AS kredit, 0 AS debet, tanggalPembayaran as tanggal, timeStamp 
        FROM balistars_hutang_gedung_pembayaran 
        WHERE (tanggalPembayaran BETWEEN ? AND ?) 
        AND jenisPembayaran=? 
        AND statusCair=? 
        AND bankAsalTransfer in (?,?,?,?,?,?,?) 
        AND statusPembayaranHutangGedung='Aktif'
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