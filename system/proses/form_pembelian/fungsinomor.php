<?php  
function generateNoBeliA1($db,$idCabang)
{
	$sql=$db->prepare('SELECT * from balistars_nomor where jenis =? order by tanggal DESC limit 1');
	$sql->execute(['BeliA1']);
	$data=$sql->fetch();
	$noNota ='PB'.$idCabang.'-'.date("Ym").'-'.str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
	return $noNota;
}

function updateNoBeliA1($db)
{
	$digit = 5;
	$sql=$db->prepare('SELECT * from balistars_nomor where 
		jenis = ? 
		order by tanggal DESC limit 1');
	$sql->execute(['BeliA1']);
	$data=$sql->fetch();
	if (10 ** ($digit) === intval($data['nomorUrut']) + 1) {
		$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut= 1,
		tanggal= ?  
		where jenis = ? 
		order by tanggal LIMIT 1');
		$sql->execute([
			date('Y-m-d'),
			'BeliA1']);
	} else {
		$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut= nomorUrut+1,
		tanggal= ?  
		where jenis = ? 
		order by tanggal LIMIT 1');
		$sql->execute([
			date('Y-m-d'),
			'BeliA1']);
	}
}

function generateNoBeliA2($db,$idCabang)
{
	$sql=$db->prepare('SELECT * from balistars_nomor where jenis = ? order by tanggal DESC limit 1');
	$sql->execute(['BeliA2']);
	$data=$sql->fetch();
	$noNota ='BL'.$idCabang.'-'.date("Ym").'-'.str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
	return $noNota;
}

function updateNoBeliA2($db)
{
	$digit = 5;
	$sql=$db->prepare('SELECT * from balistars_nomor where 
		jenis = ? 
		order by tanggal DESC limit 1');
	$sql->execute(['BeliA2']);
	$data=$sql->fetch();
	if (10 ** ($digit) === intval($data['nomorUrut']) + 1) {
		$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut= 1,
		tanggal= ?  
		where jenis = ? 
		order by tanggal LIMIT 1');
		$sql->execute([
			date('Y-m-d'),
			'BeliA2']);
	} else {
		$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut= nomorUrut+1,
		tanggal= ?  
		where jenis = ? 
		order by tanggal LIMIT 1');
		$sql->execute([
			date('Y-m-d'),
			'BeliA2']);
	}
	
}
?>