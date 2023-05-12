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

    $(document).on("keyup", "#omset, #biaya", getProfitRatio);
});

function dataDaftarAdditional() {
    const rentang = $("#rentang").val() ?? "";
    const filterCustomer = $("#filterCustomer").val() ?? "";
    const status = $("#status").val() ?? "";

    $.ajax({
        url: "datadaftaradditional.php",
        type: "POST",
        data: {
            rentang,
            filterCustomer,
            status,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataDaftarAdditional").empty().html(data);
        },
    });
}

function getFormAdditional(idAdditional) {
    $.ajax({
        url: "formadditional.php",
        type: "post",
        data: {
            idAdditional,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxFormAdditional").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function showProgressAdditional(idAdditional) {
    $("#modalProgressAdditional").modal("show");
    $.ajax({
        url: "progressadditional.php",
        type: "post",
        data: {
            idAdditional,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxProgressAdditional").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function cancelAdditional(btn, idAdditional) {
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
                url: "prosesadditional.php",
                type: "POST",
                data: {
                    idAdditional: idAdditional,
                    flag: "cancel",
                },
                dataType: "json",
                success: function (data) {
                    const { status, pesan } = data;

                    notifikasi(status, pesan);
                    dataDaftarAdditional();
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

function pengajuanUlang(btn, idAdditional) {
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
                url: "prosesadditional.php",
                type: "POST",
                data: {
                    idAdditional: idAdditional,
                    flag: "pengajuanUlang",
                },
                dataType: "json",
                success: function (data) {
                    const { status, pesan } = data;

                    notifikasi(status, pesan);

                    getFormAdditional();
                    dataDaftarAdditional();
                },
            });
        } else {
            swal({
                title: "Proses dibatalkan !",
                icon: "warning",
            });
            btn.removeAttr("disabled");
            getFormAdditional();
        }
    });
}

function prosesAdditional(btn, statusPengajuanUlang = false) {
    btn.attr("disabled", "disabled");

    const formAdditional = document.getElementById("formAdditional");
    const dataFormAdditional = new FormData(formAdditional);

    const formBuktiLampiran = document.getElementById("formBuktiLampiran");
    const dataFormBuktiLampiran = new FormData(formBuktiLampiran);

    const dataForm = new FormData();

    [dataFormAdditional, dataFormBuktiLampiran].forEach((FDwrapper) => {
        FDwrapper.forEach((value, key) => {
            dataForm.append(key, value);
        });
    });

    const validasi = formValidation(dataForm);
    if (validasi === true) {
        $.ajax({
            url: "prosesadditional.php",
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
                        getFormAdditional();
                        dataDaftarAdditional();
                    }
                }
            },
        });
    } else {
        notifikasi(false, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
        btn.removeAttr("disabled");
    }
}

function getProfitRatio() {
    let omset = $("#omset").val();
    let biaya = $("#biaya").val();

    if (omset !== "" && biaya !== "") {
        omset = ubahToInt(omset);
        biaya = ubahToInt(biaya);

        const profit = omset - biaya;
        const ratio = Number((profit / omset) * 100).toFixed(2);

        $("#profit").val(ubahToRupiah(profit));
        $("#ratio").val(ratio);
    }
}

function notifikasi(status, pesan) {
    if (status == true) {
        toastr.success(pesan);
    } else if (status == false) {
        toastr.error(pesan);
    }
}
