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

function dataDaftarPartisi() {
    const rentang = $("#rentang").val() ?? "";
    const status = $("#status").val() ?? "";

    $.ajax({
        url: "datadaftarpartisi.php",
        type: "POST",
        data: {
            rentang,
            status,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataDaftarPartisi").empty().html(data);
        },
    });
}

function getFormPartisi(idPartisi) {
    $.ajax({
        url: "formpartisi.php",
        type: "post",
        data: {
            idPartisi,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxFormPartisi").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function showProgressPartisi(idPartisi) {
    $("#modalProgressPartisi").modal("show");
    $.ajax({
        url: "progresspartisi.php",
        type: "post",
        data: {
            idPartisi,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxProgressPartisi").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function cancelPartisi(btn, idPartisi) {
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
                url: "prosespartisi.php",
                type: "POST",
                data: {
                    idPartisi: idPartisi,
                    flag: "cancel",
                },
                dataType: "json",
                success: function (data) {
                    const { status, pesan } = data;

                    notifikasi(status, pesan);
                    dataDaftarPartisi();
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

function pengajuanUlang(btn, idPartisi) {
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
                url: "prosespartisi.php",
                type: "POST",
                data: {
                    idPartisi: idPartisi,
                    flag: "pengajuanUlang",
                },
                dataType: "json",
                success: function (data) {
                    const { status, pesan } = data;

                    notifikasi(status, pesan);
                    getFormPartisi();
                    dataDaftarPartisi();
                },
            });
        } else {
            swal({
                title: "Proses dibatalkan !",
                icon: "warning",
            });
            btn.removeAttr("disabled");
            getFormPartisi();
        }
    });
}

function prosesPartisi(btn, statusPengajuanUlang = false) {
    btn.attr("disabled", "disabled");

    const formPartisi = document.getElementById("formPartisi");
    const dataFormPartisi = new FormData(formPartisi);

    const formBuktiLampiran = document.getElementById("formBuktiLampiran");
    const dataFormBuktiLampiran = new FormData(formBuktiLampiran);

    const dataForm = new FormData();

    [dataFormPartisi, dataFormBuktiLampiran].forEach((FDwrapper) => {
        FDwrapper.forEach((value, key) => {
            dataForm.append(key, value);
        });
    });

    const validasi = formValidation(dataForm);
    if (validasi === true) {
        $.ajax({
            url: "prosespartisi.php",
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
                        getFormPartisi();
                        dataDaftarPartisi();
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
