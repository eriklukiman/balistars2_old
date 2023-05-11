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
    const status = $("#status").val() ?? "";

    $.ajax({
        url: "datadaftarpettycash.php",
        type: "POST",
        data: {
            rentang,
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

function prosesPenyetujuan(btn, idPettyCash, hasil) {
    const opsiTitle = {
        Disetujui: "Apakah Anda Yakin Ingin Menyetujui Pengajuan Ini ?",
        Reject: "Apakah Anda Yakin Ingin Melakukan Reject Pada Pengajuan Ini ?",
    };

    if (opsiTitle[hasil] === undefined) {
        swal({
            title: "Jenis Penyetujuan Tidak Valid",
            icon: "warning",
        });

        btn.removeAttr("disabled");
        return;
    }

    btn.attr("disabled", "disabled");
    swal({
        title: opsiTitle[hasil],
        text: "Setelah dibatalkan, proses tidak dapat diulangi!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            const keterangan = $("#keteranganPenyetujuan").val();
            $.ajax({
                url: "prosespenyetujuan.php",
                type: "POST",
                data: {
                    idPettyCash: idPettyCash,
                    hasil: hasil,
                    keterangan,
                },
                dataType: "json",
                success: function (data) {
                    const { status, pesan } = data;

                    notifikasi(status, pesan);

                    $("#boxFormPettyCash").empty();
                    dataDaftarPettyCash();
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
