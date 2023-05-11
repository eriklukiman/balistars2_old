<?php  
function generateNoNotaPenyusutan($db,$idCabang)
{
	$sql=$db->prepare('SELECT * from balistars_nomor where jenis =? order by tanggal DESC limit 1');
	$sql->execute(['Penyusutan']);
	$data=$sql->fetch();
	$noNota ='PN'.$idCabang.'-'.date("Ym").'-'.str_pad($data['nomorUrut'], 5, '0', STR_PAD_LEFT);
	return $noNota;
}

function updateNoNotapenyusutan($db)
{
	$digit = 5;
	$sql=$db->prepare('SELECT * from balistars_nomor where 
		jenis = ? 
		order by tanggal DESC limit 1');
	$sql->execute(['Penyusutan']);
	$data=$sql->fetch();
	if (10 ** ($digit) === intval($data['nomorUrut']) + 1) {
		$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut= 1,
		tanggal= ?  
		where jenis = ? 
		order by tanggal LIMIT 1');
		$sql->execute([
			date('Y-m-d'),
			'Penyusutan']);
	} else {
		$sql=$db->prepare('UPDATE balistars_nomor SET 
		nomorUrut= nomorUrut+1,
		tanggal= ?  
		where jenis = ? 
		order by tanggal LIMIT 1');
		$sql->execute([
			date('Y-m-d'),
			'Penyusutan']);
	}
}


?>