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

const config = {
    Additional: {
        form: "formadditional.php",
        daftar: "datadaftaradditional.php",
        progress: "progressadditional.php",
    },
    Partisi: {
        form: "formpartisi.php",
        daftar: "datadaftarpartisi.php",
        progress: "progresspartisi.php",
    },
    Pengembalian: {
        form: "formpengembalian.php",
        daftar: "datadaftarpengembalian.php",
        progress: "progresspengembalian.php",
    },
};

function dataDaftarPenyetujuan() {
    const rentang = $("#rentang").val() ?? "";
    const jenisPengajuan = $("#jenisPengajuan").val() ?? "";
    const status = $("#status").val() ?? "";

    const ajaxURL = config[jenisPengajuan]["daftar"];

    if (ajaxURL === undefined) {
        notifikasi(false, "Jenis Pengajuan Tidak Terdaftar");
        return;
    }

    $.ajax({
        url: ajaxURL,
        type: "POST",
        data: {
            rentang,
            jenisPengajuan,
            status,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#dataDaftarPenyetujuan").empty().html(data);
        },
    });
}

function getFormPenyetujuan(jenisPengajuan, idPengajuan) {
    const ajaxURL = config[jenisPengajuan]["form"];

    if (ajaxURL === undefined) {
        notifikasi(false, "Jenis Pengajuan Tidak Terdaftar");
        return;
    }

    $.ajax({
        url: ajaxURL,
        type: "post",
        data: {
            idPengajuan,
            jenisPengajuan,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxFormPenyetujuan").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function showProgressPenyetujuan(jenisPengajuan, idPengajuan) {
    const ajaxURL = config[jenisPengajuan]["progress"];

    if (ajaxURL === undefined) {
        notifikasi(false, "Jenis Pengajuan Tidak Terdaftar");
        return;
    }

    $("#modalProgressPenyetujuan").modal("show");
    $.ajax({
        url: ajaxURL,
        type: "post",
        data: {
            idPengajuan,
            jenisPengajuan,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxProgressPenyetujuan").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function prosesPenyetujuan(btn, jenisPengajuan, idPengajuan, hasil) {
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
                    idPengajuan,
                    jenisPengajuan,
                    hasil,
                    keterangan,
                },
                dataType: "json",
                beforeSend: function () {
                    $(".overlay").show();
                },
                success: function (data) {
                    $(".overlay").hide();
                    const { status, pesan } = data;

                    notifikasi(status, pesan);

                    $("#boxFormPenyetujuan").empty();
                    dataDaftarPenyetujuan();
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
