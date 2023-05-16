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

function dataDaftarPettyCash() {
    const rentang = $("#rentang").val() ?? "";
    const filterCustomer = $("#filterCustomer").val() ?? "";
    const status = $("#status").val() ?? "";

    $.ajax({
        url: "datadaftarpettycash.php",
        type: "POST",
        data: {
            rentang,
            filterCustomer,
            status,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataDaftarPettyCash").empty().html(data);
        },
    });
}

function getFormPettyCash(idPettyCash) {
    $.ajax({
        url: "formpettycash.php",
        type: "post",
        data: {
            idPettyCash,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxFormPettyCash").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function showProgressPettyCash(idPettyCash) {
    $("#modalProgressPettyCash").modal("show");
    $.ajax({
        url: "progresspettycash.php",
        type: "post",
        data: {
            idPettyCash,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxProgressPettyCash").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function cancelPettyCash(btn, idPettyCash) {
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
                url: "prosespettycash.php",
                type: "POST",
                data: {
                    idPettyCash: idPettyCash,
                    flag: "cancel",
                },
                dataType: "json",
                success: function (data) {
                    const { status, pesan } = data;

                    notifikasi(status, pesan);
                    dataDaftarPettyCash();
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

function pengajuanUlang(btn, idPettyCash) {
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
                url: "prosespettycash.php",
                type: "POST",
                data: {
                    idPettyCash: idPettyCash,
                    flag: "pengajuanUlang",
                },
                dataType: "json",
                success: function (data) {
                    const { status, pesan } = data;

                    notifikasi(status, pesan);

                    getFormPettyCash();
                    dataDaftarPettyCash();
                },
            });
        } else {
            swal({
                title: "Proses dibatalkan !",
                icon: "warning",
            });
            btn.removeAttr("disabled");
            getFormPettyCash();
        }
    });
}

function prosesPettyCash(btn, statusPengajuanUlang = false) {
    btn.attr("disabled", "disabled");

    const formPettyCash = document.getElementById("formPettyCash");
    const dataFormPettyCash = new FormData(formPettyCash);

    const formBuktiLampiran = document.getElementById("formBuktiLampiran");
    const dataFormBuktiLampiran = new FormData(formBuktiLampiran);

    const dataForm = new FormData();

    [dataFormPettyCash, dataFormBuktiLampiran].forEach((FDwrapper) => {
        FDwrapper.forEach((value, key) => {
            dataForm.append(key, value);
        });
    });

    const validasi = formValidation(dataForm, ["noPO"]);
    if (validasi === true) {
        $.ajax({
            url: "prosespettycash.php",
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
                        getFormPettyCash();
                        dataDaftarPettyCash();
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
