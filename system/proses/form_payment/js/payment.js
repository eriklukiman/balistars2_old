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

function dataDaftarPayment() {
    const rentang = $("#rentang").val() ?? "";
    const jenisPengajuan = $("#jenisPengajuan").val() ?? "";
    const status = $("#status").val() ?? "";

    const listURL = {
        Additional: "datadaftaradditional.php",
        Partisi: "datadaftarpartisi.php",
        Pengembalian: "datadaftarpengembalian.php",
        "Petty Cash": "datadaftarpettycash.php",
    };

    if (listURL[jenisPengajuan] === undefined) {
        notifikasi(false, "Jenis Pengajuan Tidak Terdaftar");
        return;
    }

    $.ajax({
        url: listURL[jenisPengajuan],
        type: "POST",
        data: {
            rentang,
            jenisPengajuan,
            status,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataDaftarPayment").empty().html(data);
        },
    });
}

function showFormPengajuan(jenisPengajuan, idPengajuan) {
    const listURL = {
        Additional: "formadditional.php",
        Partisi: "formpartisi.php",
        Pengembalian: "formpengembalian.php",
        "Petty Cash": "formpettycash.php",
    };

    if (listURL[jenisPengajuan] === undefined) {
        notifikasi(false, "Jenis Pengajuan Tidak Terdaftar");
        return;
    }

    $.ajax({
        url: listURL[jenisPengajuan],
        type: "post",
        data: {
            idPengajuan,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxViewPengajuan").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function getFormPayment(jenisPengajuan, idPengajuan) {
    $("#modalFormPayment").modal("show");
    $.ajax({
        url: "formpayment.php",
        type: "post",
        data: {
            idPengajuan,
            jenisPengajuan,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxFormPayment").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function prosesPayment(btn, idPengajuan, jenisPengajuan) {
    btn.attr("disabled", "disabled");

    const tglPayment = $("#tglPayment").val();
    const keterangan = $("#keterangan").val();

    $.ajax({
        url: "prosespayment.php",
        type: "POST",
        data: {
            idPengajuan: idPengajuan,
            jenisPengajuan: jenisPengajuan,
            keterangan: keterangan,
            tanggal: tglPayment,
        },
        dataType: "json",
        success: function (data) {
            const { status, pesan } = data;

            notifikasi(status, pesan);

            $("#modalFormPayment").modal("hide");
            $("#boxFormPayment").empty();

            dataDaftarPayment();
        },
    });
}

function notifikasi(status, pesan) {
    if (status == true) {
        toastr.success(pesan);
    } else if (status == false) {
        toastr.error(pesan);
    }
}
