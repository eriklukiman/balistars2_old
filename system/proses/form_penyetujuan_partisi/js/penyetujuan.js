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
    const filterCustomer = $("#filterCustomer").val() ?? "";
    const status = $("#status").val() ?? "";

    $.ajax({
        url: "datadaftarpartisi.php",
        type: "POST",
        data: {
            rentang,
            filterCustomer,
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

function prosesPenyetujuan(btn, idPartisi, hasil) {
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
            $.ajax({
                url: "prosespenyetujuan.php",
                type: "POST",
                data: {
                    idPartisi: idPartisi,
                    hasil: hasil,
                },
                dataType: "json",
                success: function (data) {
                    const { status, pesan } = data;

                    notifikasi(status, pesan);

                    $("#boxFormPartisi").empty();
                    dataDaftarPartisi();
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
