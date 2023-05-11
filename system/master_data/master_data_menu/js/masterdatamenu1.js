function dataDaftarMasterDataMenu(parameterOrder) {
    $.ajax({
        url: "datadaftarmasterdatamenu.php",
        type: "post",
        data: {
            parameterOrder: parameterOrder,
        },
        beforeSend: function () {
            $(".overlay").show();
        },
        success: function (data, status) {
            $("#dataDaftarMasterDataMenu").empty().append(data);
            $("#parameterOrder").val(parameterOrder);
            $(".overlay").hide();
        },
    });
}

function tambahMenu() {
    $("#modalFormMasterDataMenu").modal("show");

    $.ajax({
        url: "dataformmasterdatamenu.php",
        type: "post",
        data: {
            parameterOrder: $("#parameterOrder").val(),
            flag: "",
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataFormMasterDataMenu").html(data);
            $("select.select2").select2();
        },
    });
}

function tambahMenuSub(id) {
    console.log(id);
    $("#modalFormMasterDataMenuSub").modal("show");

    $.ajax({
        url: "dataformmasterdatamenusub.php",
        type: "post",
        data: {
            parameterOrder: $("#parameterOrder").val(),
            idMenu: id,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataFormMasterDataMenuSub").html(data);
            $("select.select2").select2();
        },
    });
}

function editMenu(id) {
    $("#modalFormMasterDataMenu").modal("show");

    $.ajax({
        url: "dataformmasterdatamenu.php",
        type: "post",
        data: {
            parameterOrder: $("#parameterOrder").val(),
            idMenu: id,
            flag: "update",
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataFormMasterDataMenu").html(data);
            $("select.select2").select2();
        },
    });
}

function prosesMasterDataMenu() {
    let formMasterDataMenu = document.getElementById("formMasterDataMenu");
    let dataForm = new FormData(formMasterDataMenu);

    const validasi = formValidation(dataForm, [
        "flag",
        "parameterOrder",
        "idMenu",
    ]);

    if (validasi === true) {
        $.ajax({
            url: "prosesmasterdatamenu.php",
            type: "post",
            enctype: "multipart/form-data",
            processData: false,
            contentType: false,
            data: dataForm,

            beforeSend: function () {},

            success: function (data, status) {
                //console.log(data);
                let dataJSON = JSON.parse(data);
                dataDaftarMasterDataMenu(dataJSON.parameterOrder);
                if (dataJSON.notifikasi == 1) {
                    notifikasi(
                        dataJSON.notifikasi,
                        "Proses Data Menu Telah Berhasil"
                    );
                } else {
                    notifikasi(dataJSON.notifikasi, "Proses Data Menu Gagal");
                }
                tambahMenu();
                //resetForm(dataJSON.notifikasi);
                if (dataJSON.flag == "update") {
                    $("#modalFormMasterDataMenu").modal("hide");
                }
            },
        });
    } else {
        notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
    }
}

function prosesMasterDataMenuSub() {
    let formMasterDataMenuSub = document.getElementById(
        "formMasterDataMenuSub"
    );
    let dataForm = new FormData(formMasterDataMenuSub);

    const validasi = formValidation(dataForm, [
        "flag",
        "parameterOrder",
        "idMenuSub",
    ]);

    if (validasi === true) {
        $.ajax({
            url: "prosesmasterdatamenusub.php",
            type: "post",
            enctype: "multipart/form-data",
            processData: false,
            contentType: false,
            data: dataForm,

            beforeSend: function () {},

            success: function (data, status) {
                console.log(data);
                let dataJSON = JSON.parse(data);
                if (dataJSON.notifikasi == 1) {
                    notifikasi(
                        dataJSON.notifikasi,
                        "Proses Data Menu Sub Telah Berhasil"
                    );
                } else {
                    notifikasi(
                        dataJSON.notifikasi,
                        "Proses Data Menu Sub Gagal"
                    );
                }
                tambahMenuSub(dataJSON.idMenu);
            },
        });
    } else {
        notifikasi(2, "Proses Gagal, Form Belum Terisi Dengan Lengkap");
    }
}

function cancelMenu(id) {
    swal({
        title: "Apakah anda yakin ingin membatalkan data Menu ini?",
        text: "Setelah dibatalkan, proses tidak dapat diulangi!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            let parameterOrder = $("#parameterOrder").val();
            let flag = "cancel";

            $.ajax({
                url: "prosesmasterdatamenu.php",
                type: "post",
                data: {
                    idMenu: id,
                    parameterOrder: parameterOrder,
                    flag: flag,
                },

                success: function (data, status) {
                    let dataJSON = JSON.parse(data);
                    dataDaftarMasterDataMenu(dataJSON.parameterOrder);
                    if (dataJSON.notifikasi == 1) {
                        notifikasi(
                            dataJSON.notifikasi,
                            "Proses Non Aktif Data Menu Telah Berhasil"
                        );
                    } else {
                        notifikasi(
                            dataJSON.notifikasi,
                            "Proses Non Aktif Data Menu Gagal"
                        );
                    }
                },
            });
        } else {
            swal({
                title: "Proses pembatalan Menu dibatalkan!",
                icon: "warning",
            });
        }
    });
}

function deleteMenuSub(id1, id2) {
    swal({
        title: "Apakah anda yakin ingin membatalkan data Menu ini?",
        text: "Setelah dibatalkan, proses tidak dapat diulangi!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            let parameterOrder = $("#parameterOrder").val();
            let flag = "cancel";

            $.ajax({
                url: "prosesmasterdatamenusub.php",
                type: "post",
                data: {
                    idMenuSub: id1,
                    idMenu: id2,
                    parameterOrder: parameterOrder,
                    flag: flag,
                },

                success: function (data, status) {
                    //console.log(data);
                    let dataJSON = JSON.parse(data);
                    tambahMenuSub(dataJSON.idMenu);
                    if (dataJSON.notifikasi == 1) {
                        notifikasi(
                            dataJSON.notifikasi,
                            "Proses Non Aktif Data Menu Sub Telah Berhasil"
                        );
                    } else {
                        notifikasi(
                            dataJSON.notifikasi,
                            "Proses Non Aktif Data Menu Sub Gagal"
                        );
                    }
                },
            });
        } else {
            swal({
                title: "Proses pembatalan Menu dibatalkan!",
                icon: "warning",
            });
        }
    });
}

function editMenuSub(idMenuSub, idMenu) {
    $("#modalFormMasterDataMenuSub").modal("show");

    $.ajax({
        url: "dataformmasterdatamenusub.php",
        type: "post",
        data: {
            parameterOrder: $("#parameterOrder").val(),
            idMenuSub: idMenuSub,
            idMenu: idMenu,
            flag: "update",
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataFormMasterDataMenuSub").html(data);
            $("select.select2").select2();
        },
    });
}

function resetForm(sukses) {
    if (sukses == 1) {
        $("input[type=text]").val("");
        $("#flag").val("");
        $(".select2").val(null).trigger("change");
    }
}

function notifikasi(sukses, pesan) {
    if (sukses == 1) {
        toastr.success(pesan);
    } else if (sukses == 2) {
        toastr.error(pesan);
    }
}
