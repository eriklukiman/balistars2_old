<?php 
function nilaiRupiah($sql, $tanggalAwal, $tanggalAkhir, $idBank, $idCabang, $statusFinal, $db)
{
	$sqlData=$db->prepare($sql);
	$sqlData->execute([
		$tanggalAwal, 
		$tanggalAkhir, 
		$idBank, 
		$idCabang, 
		$statusFinal]);
	$dataNilai=$sqlData->fetch();
	return $dataNilai;
}

function debetBank($tanggalAwal, $tanggalAkhir, $idBank, $idCabang, $db)
{
	$sqlTransfer=$db->prepare('SELECT SUM(jumlahPembayaran) as totalTransfer, SUM(biayaAdmin) as totalAdmin, SUM(PPH) as totalPPH  
		FROM balistars_piutang 
		inner join balistars_penjualan 
		on balistars_piutang.noNota=balistars_penjualan.noNota 
		where balistars_piutang.bankTujuanTransfer=? 
		and balistars_piutang.jenisPembayaran=? 
		and balistars_penjualan.idCabang=?
		and statusPenjualan=? 
		and (balistars_piutang.tanggalPembayaran between ? and ?)');
	$sqlTransfer->execute([
		$idBank,
		"Transfer",
		$idCabang,
		"Aktif", 
		$tanggalAwal,
		$tanggalAkhir]);  

	$sqlSetor=$db->prepare('SELECT SUM(jumlahSetor) as totalSetor 
		FROM balistars_setor_penjualan_cash  
		where idCabang=? 
		and (tanggalSetor between ? and ?) 
		and idBank=?
		and statusSetor=?');
	$sqlSetor->execute([
		$idCabang,
		$tanggalAwal,$tanggalAkhir,
		$idBank,
		"Aktif"]);

	$dataTransfer=$sqlTransfer->fetch();    
	$dataSetor=$sqlSetor->fetch();

	$setor=$dataSetor['totalSetor']+$dataTransfer['totalTransfer']-$dataTransfer['totalAdmin']-$dataTransfer['totalPPH'];
	return $setor;
}

function fungsiPenyesuaian($db,$tanggalAwal,$tanggalAkhir,$idCabang,$jenisPenyesuaian)
{
	if($idCabang==0){
	  $sqlPenyesuaian1=$db->prepare('SELECT SUM(nominal) as totalUP 
	  	FROM balistars_penyesuaian 
	  	where jenisPenyesuaian=? 
	  	and (tanggalPenyesuaian between ? and ?) 
	  	and status=? 
	  	and statusPenyesuaian=?');
	  $sqlPenyesuaian1->execute([
	  	$jenisPenyesuaian,
	  	$tanggalAwal,$tanggalAkhir,
	  	"Naik",
	  	"Aktif"]);

	  $sqlPenyesuaian2=$db->prepare('SELECT SUM(nominal) as totalDown 
	  	FROM balistars_penyesuaian 
	  	where jenisPenyesuaian=? 
	  	and (tanggalPenyesuaian between ? and ?) 
	  	and status=? 
	  	and statusPenyesuaian=?');
	  $sqlPenyesuaian2->execute([
	  	$jenisPenyesuaian,
	  	$tanggalAwal,$tanggalAkhir,
	  	"Turun",
	  	"Aktif"]);

	}
	else{
	  $sqlPenyesuaian1=$db->prepare('SELECT SUM(nominal) as totalUP 
	  	FROM balistars_penyesuaian 
	  	where jenisPenyesuaian=? 
	  	and (tanggalPenyesuaian between ? and ?) 
	  	and status=? 
	  	and idCabang=? 
	  	and statusPenyesuaian=?');
	  $sqlPenyesuaian1->execute([
	  	$jenisPenyesuaian,
	  	$tanggalAwal,$tanggalAkhir,
	  	"Naik",
	  	$idCabang, 
	  	"Aktif"]);

	  $sqlPenyesuaian2=$db->prepare('SELECT SUM(nominal) as totalDown 
	  	FROM balistars_penyesuaian 
	  	where jenisPenyesuaian=? 
	  	and (tanggalPenyesuaian between ? and ?) 
	  	and status=? 
	  	and idCabang=? 
	  	and statusPenyesuaian=?');
	  $sqlPenyesuaian2->execute([
	  	$jenisPenyesuaian,
	  	$tanggalAwal,$tanggalAkhir,
	  	"Turun",
	  	$idCabang,
	  	"Aktif"]);

	}
	$dataPenyesuaian1=$sqlPenyesuaian1->fetch();
	$dataPenyesuaian2=$sqlPenyesuaian2->fetch();
	return $dataPenyesuaian1['totalUP']-$dataPenyesuaian2['totalDown'];
}

function fungsiPenyesuaianPembelian($db,$tanggalAwal,$tanggalAkhir,$idCabang,$jenisPenyesuaian,$tipePembayaran)
{
	if($idCabang==0){
	  $sqlPenyesuaian1=$db->prepare('SELECT SUM(nominal) as totalUP 
	  	FROM balistars_penyesuaian 
	  	where jenisPenyesuaian=? 
	  	and (tanggalPenyesuaian between ? and ?) 
	  	and tipePembayaran=? 
	  	and status=? 
	  	and statusPenyesuaian=?');
	  $sqlPenyesuaian1->execute([
	  	$jenisPenyesuaian,
	  	$tanggalAwal,$tanggalAkhir,
	  	$tipePembayaran,
	  	"Naik",
	  	"Aktif"]);

	  $sqlPenyesuaian2=$db->prepare('SELECT SUM(nominal) as totalDown 
	  	FROM balistars_penyesuaian 
	  	where jenisPenyesuaian=? 
	  	and (tanggalPenyesuaian between ? and ?) 
	  	and tipePembayaran =? 
	  	and status=? 
	  	and statusPenyesuaian=?');
	  $sqlPenyesuaian2->execute([
	  	$jenisPenyesuaian,
	  	$tanggalAwal,$tanggalAkhir,
	  	$tipePembayaran,
	  	"Turun",
	  	"Aktif"]);

	}
	else{
	  $sqlPenyesuaian1=$db->prepare('SELECT SUM(nominal) as totalUP 
	  	FROM balistars_penyesuaian 
	  	where jenisPenyesuaian=? 
	  	and (tanggalPenyesuaian between ? and ?) 
	  	and tipePembayaran =? 
	  	and status=? 
	  	and idCabang=? 
	  	and statusPenyesuaian=?');
	  $sqlPenyesuaian1->execute([
	  	$jenisPenyesuaian,
	  	$tanggalAwal,$tanggalAkhir,
	  	$tipePembayaran,
	  	"Naik",
	  	$idCabang,
	   	"Aktif"]);

	  $sqlPenyesuaian2=$db->prepare('SELECT SUM(nominal) as totalDown 
	  	FROM balistars_penyesuaian 
	  	where jenisPenyesuaian=? 
	  	and (tanggalPenyesuaian between ? and ?) 
	  	and tipePembayaran =? 
	  	and status=? 
	  	and idCabang=? 
	  	and statusPenyesuaian=?');
	  $sqlPenyesuaian2->execute([
	  	$jenisPenyesuaian,
	  	$tanggalAwal,$tanggalAkhir,
	  	$tipePembayaran,
	  	"Turun",
	  	$idCabang,
	  	"Aktif"]);
	}

	$dataPenyesuaian1=$sqlPenyesuaian1->fetch();
	$dataPenyesuaian2=$sqlPenyesuaian2->fetch();
	return $dataPenyesuaian1['totalUP']-$dataPenyesuaian2['totalDown'];
}
 ?>