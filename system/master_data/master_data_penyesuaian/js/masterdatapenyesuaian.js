$("#rentang").daterangepicker({
	locale: {
		format: "DD-MM-YYYY",
	},
});

function dataDaftarMasterDataPenyesuaian() {
	$.ajax({
		url: "datadaftarmasterdatapenyesuaian.php",
		type: "post",
		data: {
			rentang: $("#rentang").val(),
		},
		beforeSend: function () {
			$(".overlay").show();
		},
		success: function (data, status) {
			$("#dataDaftarMasterDataPenyesuaian").empty().append(data);
			$(".overlay").hide();
		},
	});
}

function getFormPenyesuaian(id = "", jenisPenyesuaian = "Pembelian") {
	$("#modalFormMasterDataPenyesuaian").modal("show");

	$.ajax({
		url: "dataformmasterdatapenyesuaian.php",
		type: "post",
		data: {
			idPenyesuaian: id,
			jenisPenyesuaian: jenisPenyesuaian,
		},
		success: function (data, status) {
			$("#dataFormMasterDataPenyesuaian").html(data);
			$("select.select2").select2();

			selectTipeBayar(id);
		},
	});
}

function selectTipeBayar(id = "") {
	const jenisPenyesuaian = $("#jenisPenyesuaian").val() ?? "";
	$.ajax({
		url: "selecttipebayar.php",
		type: "POST",
		data: {
			idPenyesuaian: id,
			jenisPenyesuaian: jenisPenyesuaian,
		},
		success: function (data, status) {
			$("#boxTipePembayaran").empty().html(data);
			$("select.select2").select2();
		},
	});
}

function prosesMasterDataPenyesuaian() {
	let formMasterDataPenyesuaian = document.getElementById("formMasterDataPenyesuaian");
	let dataForm = new FormData(formMasterDataPenyesuaian);

	const validasi = formValidation(dataForm);
	if (validasi === true) {
		$.ajax({
			url: "prosesmasterdatapenyesuaian.php",
			type: "post",
			enctype: "multipart/form-data",
			processData: false,
			contentType: false,
			data: dataForm,

			beforeSend: function () {},

			success: function (data, status) {
				//console.log(data);
				let dataJSON = JSON.parse(data);
				dataDaftarMasterDataPenyesuaian();

				notifikasi(dataJSON.notifikasi);
				resetForm(dataJSON.notifikasi);

				getFormPenyesuaian();

				if (dataJSON.flag == "update") {
					$("#modalFormMasterDataPenyesuaian").modal("hide");
				}
			},
		});
	} else {
		notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
	}
}

function cancelPenyesuaian(id) {
	swal({
		title: "Apakah anda yakin ingin membatalkan data Penyesuaian ini?",
		text: "Setelah dibatalkan, proses tidak dapat diulangi!",
		icon: "warning",
		buttons: true,
		dangerMode: true,
	}).then((willDelete) => {
		if (willDelete) {
			let flag = "cancel";

			$.ajax({
				url: "prosesmasterdatapenyesuaian.php",
				type: "post",
				data: {
					idPenyesuaian: id,
					flag: flag,
				},

				success: function (data, status) {
					//console.log(data);
					let dataJSON = JSON.parse(data);
					dataDaftarMasterDataPenyesuaian();
					notifikasi(dataJSON.notifikasi);
				},
			});
		} else {
			swal({
				title: "Proses pembatalan Penyesuaian dibatalkan!",
				icon: "warning",
			});
		}
	});
}

function resetForm(sukses) {
	if (sukses == 1) {
		$("input[type=text]").val("");
		$("#flag").val("");
		$(".select2").val(null).trigger("change");
		$("#keterangan").val("");
	}
}

function notifikasi(sukses) {
	if (sukses == 1) {
		toastr.success("Proses Data Penyesuaian Berhasil");
	} else if (sukses == 2) {
		toastr.error("Proses Data Penyesuaian Gagal");
	}
}
