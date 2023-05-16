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

function dataDaftarPoin() {
    const rentang = $("#rentang").val() ?? "";

    $.ajax({
        url: "datadaftarpoin.php",
        type: "POST",
        data: {
            rentang,
        },
        beforeSend: function () {},
        success: function (data) {
            $("#dataDaftarPoin").empty().html(data);
        },
    });
}

function getFormPoin(idPoin) {
    $.ajax({
        url: "formpoin.php",
        type: "post",
        data: {
            idPoin,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxFormPoin").empty().html(data);
            $("select.select2").select2();
        },
    });
}

function showProgressPoin(idPoin) {
    $("#modalProgressPoin").modal("show");
    $.ajax({
        url: "progresspoin.php",
        type: "post",
        data: {
            idPoin,
        },
        beforeSend: function () {},
        success: function (data, status) {
            $("#boxProgressPoin").empty().html(data);
            $("select.select2").select2();
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
