document.addEventListener("keypress", function (event) {
    if (event.keyCode === 13 || event.which === 13) {
        prosesLogin();
    }
});

function prosesLogin() {
    let username = $("#username").val();
    let password = $("#password").val();

    let arrayValue = [username, password];
    let arrayLabel = ["#labelUsername", "#labelPassword"];
    let arrayField = ["#username", "#password"];

    let hasilValidasi = validasiFormKosong(arrayValue, arrayLabel, arrayField);
    if (hasilValidasi) {
        let formLogin = document.getElementById("formLogin");
        let dataForm = new FormData(formLogin);

        $.ajax({
            url: "library/proseslogin.php",
            type: "post",
            enctype: "multipart/form-data",
            processData: false,
            contentType: false,
            data: dataForm,

            beforeSend: function () {
                $(".overlay").show();
            },

            success: function (data, status) {
                let dataJSON = JSON.parse(data);
                notifikasi(dataJSON);
                if (dataJSON.flagNotif == "sukses") {
                    location.href = dataJSON.lokasi;
                }

                $(".overlay").hide();
            },
        });
    }
}

function prosesLoginUserAktif() {
    let formLogin = document.getElementById("formLogin");
    let dataForm = new FormData(formLogin);
    //console.log(dataForm);
    $.ajax({
        url: "library/proseslogin.php",
        type: "post",
        enctype: "multipart/form-data",
        processData: false,
        contentType: false,
        data: dataForm,

        beforeSend: function () {
            $(".overlay").show();
        },

        success: function (data, status) {
            let dataJSON = JSON.parse(data);
            if (dataJSON.flagNotif == "sukses") {
                location.href = dataJSON.lokasi;
            }

            $(".overlay").hide();
        },
    });
}

window.onload = function () {
    let flagNotif = $("#flagNotif").val();
    let data = {
        flagNotif: flagNotif,
        pesan: "Maaf, anda belum login!",
    };
    notifikasi(data);

    prosesLoginUserAktif();
};
