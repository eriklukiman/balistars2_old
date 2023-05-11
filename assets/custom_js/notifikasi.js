function notifikasi(obj) {

	const flag = obj.flagNotif;
	let pesan = obj.pesan || null;

	if(flag == 'sukses'){
		if(!pesan){
			pesan = 'Data Processing is Successful!';
		}

		toastr.success(pesan);
		$(".modal.show.utama").modal('hide');
	} 
	else if(flag == 'gagal'){
		if(!pesan){
			pesan = 'Data Processing is Failed!';
		}

		toastr.error(pesan);
	}
}
