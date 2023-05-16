$(function () {
    $(document).on("change", ".input-link", function (e) {
        const id = $(this).attr("id");
        const value = $(this).val();
        const anchorElement = $('a[data-id="' + id + '"]');

        if (/https\:\/\/[\w.]+.com\//g.test(value)) {
            anchorElement.removeClass("btn-secondary").addClass("btn-danger");
            anchorElement.attr("href", value);
            $(this).removeClass("border-warning");
        } else {
            anchorElement.removeClass("btn-danger").addClass("btn-secondary");
            anchorElement.attr("href", "#");
            $(this).addClass("border-warning");
        }
    });
});

function dataDaftarPengembalian() {
    const rentang = $("#rentang").val() ?? "";
    const filterCustomer = $("#filterCustomer").val() ?? "";
    const status = $("#status").val() ?? "";

    $.ajax({
        url: "datadaftarpengembalian.php",
        type: "POST",
        data: {
            rentang,
            filterCustomer,
            status,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataDaftarPengembalian").empty().html(data);
        },
    });
}

function getFormPengembalian(idPengembalian) {
    $.ajax({
        url: "formpengembalian.php",
        type: "post",
        data: {
            idPengembalian,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxFormPengembalian").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function showProgressPengembalian(idPengembalian) {
    $("#modalProgressPengembalian").modal("show");
    $.ajax({
        url: "progresspengembalian.php",
        type: "post",
        data: {
            idPengembalian,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxProgressPengembalian").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function pengajuanUlang(btn, idPengembalian) {
    btn.attr("disabled", "disabled");
    swal({
        title: "Apakah Anda Yakin Ingin Melakukan Pengajuan Ulang ?",
        text: "Setelah dibatalkan, proses tidak dapat diulangi!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: "prosespengembalian.php",
                type: "POST",
                data: {
                    idPengembalian: idPengembalian,
                    flag: "pengajuanUlang",
                },
                dataType: "json",
                success: function (data) {
                    const { status, pesan } = data;

                    notifikasi(status, pesan);

                    getFormPengembalian();
                    dataDaftarPengembalian();
                },
            });
        } else {
            swal({
                title: "Proses dibatalkan !",
                icon: "warning",
            });
            btn.removeAttr("disabled");
            getFormPengembalian();
        }
    });
}

function cancelPengembalian(btn, idPengembalian) {
    btn.attr("disabled", "disabled");
    swal({
        title: "Apakah Anda Yakin Ingin Membatalkan Pengajuan Ini?",
        text: "Setelah dibatalkan, proses tidak dapat diulangi!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: "prosespengembalian.php",
                type: "POST",
                data: {
                    idPengembalian: idPengembalian,
                    flag: "cancel",
                },
                dataType: "json",
                success: function (data) {
                    const { status, pesan } = data;

                    notifikasi(status, pesan);
                    dataDaftarPengembalian();
                },
            });
        } else {
            swal({
                title: "Proses nonaktif item dibatalkan !",
                icon: "warning",
            });
            btn.removeAttr("disabled");
        }
    });
}

function prosesPengembalian(btn, statusPengajuanUlang = false) {
    btn.attr("disabled", "disabled");

    const formPengembalian = document.getElementById("formPengembalian");
    const dataFormPengembalian = new FormData(formPengembalian);

    const formBuktiLampiran = document.getElementById("formBuktiLampiran");
    const dataFormBuktiLampiran = new FormData(formBuktiLampiran);

    const dataForm = new FormData();

    [dataFormPengembalian, dataFormBuktiLampiran].forEach((FDwrapper) => {
        FDwrapper.forEach((value, key) => {
            dataForm.append(key, value);
        });
    });

    const validasi = formValidation(dataForm, [
        "linkSuratPengajuan",
        "linkSuratPernyataanCustomer",
        "linkNotaPenjualan",
        "linkBuktiTransfer",
        "linkBuktiPotongPPH",
        "linkBuktiPotongPPN",
        "linkRincianPenjualanExcel",
        "linkBuktiChatCustomer",
    ]);

    if (validasi === true) {
        $.ajax({
            url: "prosespengembalian.php",
            type: "post",
            enctype: "multipart/form-data",
            processData: false,
            contentType: false,
            data: dataForm,
            dataType: "json",
            beforeSend: function () {
                if (dataForm.get("flag") === "tambah") {
                    $(".overlay").show();
                }
            },
            success: function (data) {
                $(".overlay").hide();
                const { status, pesan } = data;

                notifikasi(status, pesan);

                btn.removeAttr("disabled");

                if (status === true) {
                    if (statusPengajuanUlang === false) {
                        getFormPengembalian();
                        dataDaftarPengembalian();
                    }
                }
            },
        });
    } else {
        notifikasi(false, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
        btn.removeAttr("disabled");
    }
}

function notifikasi(status, pesan) {
    if (status == true) {
        toastr.success(pesan);
    } else if (status == false) {
        toastr.error(pesan);
    }
}
