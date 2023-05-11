$(function () {
    $(document).on("click", ".row-input", function (e) {
        e.stopPropagation();

        const tableRow = $(this);

        const idUserDetail = tableRow.data("id");
        const inputCheckbox = document.querySelector("input[type=checkbox]#menuSub_" + idUserDetail);

        inputCheckbox.checked = !inputCheckbox.checked;
        $("input[type=checkbox]#menuSub_" + idUserDetail).trigger("change");
    });

    $(document).on("keypress", "input[type=checkbox]", function (key) {
        const iterator = parseInt($(this).data("iterator"));
        const keyPress = key.which ? key.which : key.keyCode;
        if (keyPress === 13) {
            this.checked = !this.checked;
            $(this).trigger("change");
        }
    });

    $(document).on('click', "input[type=checkbox]", function(e){
        e.preventDefault();
    });
});

function dataDaftarMasterDataUser(parameterOrder) {
    $.ajax({
        url: "datadaftarmasterdatauser.php",
        type: "post",
        data: {
            parameterOrder: parameterOrder,
        },
        beforeSend: function () {
            $(".overlay").show();
        },
        success: function (data, status) {
            $("#dataDaftarMasterDataUser").empty().append(data);
            $("#parameterOrder").val(parameterOrder);
            $(".overlay").hide();
        },
    });
}

function tambahUser() {
    $("#modalFormMasterDataUser").modal("show");

    $.ajax({
        url: "dataformmasterdatauser.php",
        type: "post",
        data: {
            parameterOrder: $("#parameterOrder").val(),
            flag: "",
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataFormMasterDataUser").html(data);
            $("select.select2").select2();
        },
    });
}

function editUser(id) {
    $("#modalFormMasterDataUser").modal("show");

    $.ajax({
        url: "dataformmasterdatauser.php",
        type: "post",
        data: {
            parameterOrder: $("#parameterOrder").val(),
            idUserAccount: id,
            flag: "update",
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataFormMasterDataUser").html(data);
            $("select.select2").select2();
        },
    });
}

function prosesTipeEdit(id) {
    event.stopPropagation();
    var idUserAccount = $('#idUserAccount').val();
    var idPegawai = $('#idPegawai').val();
    $.ajax({
        url: "prosestipe.php",
        type: "post",
        data: {
            idUserDetail: id,
            idUserAccount: idUserAccount,
            flag: "edit",
        },
        beforeSend: function () {},
        success: function (data, status) {
            let dataJSON = JSON.parse(data);
            notifikasi(dataJSON.notifikasi);
            getFormDetailUser(idPegawai, idUserAccount);
        },
    });
}

function prosesTipeDelete(id) {
    event.stopPropagation();
    var idUserAccount = $('#idUserAccount').val();
    var idPegawai = $('#idPegawai').val();
    $.ajax({
        url: "prosestipe.php",
        type: "post",
        data: {
            idUserDetail: id,
            idUserAccount: idUserAccount,
            flag: "delete",
        },
        beforeSend: function () {},
        success: function (data, status) {
            console.log(data);
            let dataJSON = JSON.parse(data);
            notifikasi(dataJSON.notifikasi);
            getFormDetailUser(idPegawai, idUserAccount);
        },
    });
}

function prosesTipeA2(id) {
    event.stopPropagation();
    var idUserAccount = $('#idUserAccount').val();
    var idPegawai = $('#idPegawai').val();
    $.ajax({
      url:'prosestipe.php',
      type:'post',
      data:{
        idUserDetail   : id,
        idUserAccount   : idUserAccount,
        flag            : 'a2'
      },
      beforeSend:function(){
      },
      success:function(data,status){
          let dataJSON=JSON.parse(data);
          notifikasi(dataJSON.notifikasi);
          getFormDetailUser(idPegawai, idUserAccount);
      }
    });
}

function prosesMasterDataUser() {
    let userName = $("#userName").val();
    let password = $("#password").val();

    let arrayValue = [userName, password];
    let arrayLabel = ["#labelUserName", "#labelPassword"];
    let arrayField = ["#userName", "#password"];

    let hasilValidasi = validasiFormKosong(arrayValue, arrayLabel, arrayField);
    if (hasilValidasi) {
        let formMasterDataUser = document.getElementById("formMasterDataUser");
        let dataForm = new FormData(formMasterDataUser);

        $.ajax({
            url: "prosesmasterdatauser.php",
            type: "post",
            enctype: "multipart/form-data",
            processData: false,
            contentType: false,
            data: dataForm,

            beforeSend: function () {},

            success: function (data, status) {
                //console.log(data);
                let dataJSON = JSON.parse(data);
                dataDaftarMasterDataUser(dataJSON.parameterOrder);
                notifikasi(dataJSON.notifikasi);
                resetForm(dataJSON.notifikasi);
                if (dataJSON.flag == "update") {
                    $("#modalFormMasterDataUser").modal("hide");
                }
            },
        });
    }
}

function cancelUser(id) {
    swal({
        title: "Apakah anda yakin ingin membatalkan data user account ini?",
        text: "Setelah dibatalkan, proses tidak dapat diulangi!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            let parameterOrder = $("#parameterOrder").val();
            let flag = "cancel";

            $.ajax({
                url: "prosesmasterdatauser.php",
                type: "post",
                data: {
                    idUserAccount: id,
                    parameterOrder: parameterOrder,
                    flag: flag,
                },

                success: function (data, status) {
                    let dataJSON = JSON.parse(data);
                    dataDaftarMasterDataUser(dataJSON.parameterOrder);
                    notifikasi(dataJSON.notifikasi);
                },
            });
        } else {
            swal({
                title: "Proses pembatalan user account dibatalkan!",
                icon: "warning",
            });
        }
    });
}

function resetForm(sukses) {
    if (sukses == 1) {
        $("input[type=text]").val("");
        $("input[type=password]").val("");
        $("#flag").val("");
        $("#idPegawaiUser option:first")
            .prop("selected", true)
            .trigger("change");
    }
}

function getFormDetailUser(idPegawai, idUserAccount) {
    $("#modalFormMenuUser").modal("show");
    $.ajax({
        url: "dataformmenuuser.php",
        type: "post",
        data: {
            idPegawai: idPegawai,
            idUserAccount: idUserAccount,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataFormMenuUser").empty().html(data);
            $("select.select2").select2();

        },
    });
}



// $(function () {
//     $(document).on("click", ".tombolMenuUser", function (e) {
//         e.preventDefault();
//         $("#modalFormMenuUser").modal("show");

//         $.ajax({
//             url: "dataformmenuuser.php",
//             type: "post",
//             data: {
//                 idUserAccount: $(this).attr("data-idUserAccount"),
//                 idPegawai: $(this).attr("data-idPegawai"),
//             },
//             beforeSend: function () {},
//             success: function (data, status) {
//                 $("#dataFormMenuUser").html(data);
//                 $("select.select2").select2();
//             },
//         });
//     });
// });

function prosesMenuUser(flag,idMenu, idMenuSub, idUserDetail){
    
    var idUserAccount = $("#idUserAccount").val();
    var idPegawai = $("#idPegawai").val();
    $.ajax({
        url: "prosesmenuuser.php",
        type: "post",
        data: {
            flag : flag,
            idMenu: idMenu,
            idMenuSub: idMenuSub,
            idPegawai: idPegawai,
            idUserAccount: idUserAccount,
            idUserDetail : idUserDetail
        },
        beforeSend: function () {},
        success: function (data, status) {
            //console.log(data);
            let dataJSON = JSON.parse(data);
            notifikasi(dataJSON.notifikasi);
            getFormDetailUser(idPegawai, idUserAccount);
        },
    });
}

function notifikasi(sukses) {
    if (sukses == 1) {
        toastr.success("Proses Data User Berhasil");
    } else if (sukses == 2) {
        toastr.error("Proses Data User Gagal");
    }
}
