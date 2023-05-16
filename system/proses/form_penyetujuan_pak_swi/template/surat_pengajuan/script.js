$(function () {
    const idPengajuan = $("#idPengajuan").val();

    getFormListKronologi(idPengajuan);
    getFormListTransaksi(idPengajuan);
    getFormNoSurat(idPengajuan);

    $(document).on("keypress", "input", function (e) {
        const keyCode = e.keyCode || e.which;

        const parameter = {
            flag: "input",
            row: $(this).data("row"),
            id: $(this).data("id") ?? "",
            data: $(this).val(),
            kolom: $(this).data("col"),
            idPengajuan: $("#idPengajuan").val(),
        };

        if (keyCode === 13) {
            prosesSaveData($(this), parameter);
        }
    });
});

function prosesSaveData(inputEl, parameter) {
    inputEl.attr("disabled", "disabled");

    if (typeof parameter === "object") {
        const validasi = parameter.data !== "";

        if (validasi === true) {
            $.ajax({
                url: "proses.php",
                type: "post",
                data: parameter,
                dataType: "json",
                success: function (data) {
                    const { status, pesan, execFunc } = data;

                    if (status) {
                        window[execFunc](parameter.idPengajuan);
                    }

                    notifikasi(status, pesan);
                    inputEl.removeAttr("disabled");
                },
            });
        } else {
            inputEl.addClass("is-invalid");

            notifikasi(false, "Proses Gagal, Inputan Belum Terisi Data");
            inputEl.removeAttr("disabled");
        }
    } else {
        console.error(`"parameter" isn't an object`);
    }
}

function getFormNoSurat(idPengajuan, id = "") {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });

    const isPreview = params.preview !== null;

    $.ajax({
        url: "formnosurat.php",
        type: "POST",
        data: {
            idPengajuan,
            isPreview,
            id,
        },
        success: function (data) {
            $(".nomor").empty().html(data);
        },
    });
}

function getFormListKronologi(idPengajuan, id = "") {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    const isPreview = params.preview !== null;

    $.ajax({
        url: "formlistkronologi.php",
        type: "POST",
        data: {
            idPengajuan,
            isPreview,
            id,
        },
        success: function (data) {
            $(".list-kronologi").empty().html(data);
        },
    });
}

function getFormListTransaksi(idPengajuan, id = "") {
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });

    const isPreview = params.preview !== null;
    $.ajax({
        url: "formlisttransaksi.php",
        type: "POST",
        data: {
            idPengajuan,
            isPreview,
            id,
        },
        success: function (data) {
            $(".list-transaksi").empty().html(data);
        },
    });
}

function deleteList(btn, kolom, idPengajuan, id) {
    btn.attr("disabled", "disabled");
    swal({
        title: "Apakah Anda Yakin ?",
        text: "Setelah Dihapus, proses tidak dapat diulangi!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: "proses.php",
                type: "POST",
                data: {
                    flag: "delete",
                    id,
                    kolom,
                },
                dataType: "json",
                success: function (data) {
                    const { status, pesan, execFunc } = data;

                    if (status) {
                        window[execFunc](idPengajuan);
                    }
                    btn.removeAttr("disabled");
                    notifikasi(status, pesan);
                },
            });
        } else {
            swal({
                title: "Proses Dibatalkan !",
                icon: "warning",
            });
            btn.removeAttr("disabled");
        }
    });
}

function notifikasi(status, pesan) {
    if (status == true) {
        toastr.success(pesan);
    } else if (status == false) {
        toastr.error(pesan);
    }
}
