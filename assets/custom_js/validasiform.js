$(function () {
    $(document).on("keyup change", ".is-invalid", function () {
        const element = $(this);
        const attrName = element.attr("name");
        const elementID = element.attr("id");

        if (element.val() !== "") {
            $(`small[data-role="form_validation"][data-key=${attrName}]`).remove();
            // $("input[name=" + attrName + "].is-invalid ~ small").remove();
            element.removeClass("is-invalid");
            $(`#s2id_${elementID}`).css("border-color", "#ced4da");
        }
    });
});

function validasiFormKosong(arrayValue, arrayLabel, arrayField) {
    let hasilValidasi = true;
    for (let i = 0; i < arrayValue.length; i++) {
        $(arrayField[i]).removeClass("borderMerah");
        $(arrayLabel[i]).hide();

        if (arrayValue[i] == "") {
            $(arrayField[i]).addClass("borderMerah");
            $(arrayLabel[i]).show();
        }

        if (arrayValue.includes("")) {
            hasilValidasi = false;
        }
    }
    return hasilValidasi;
}

function formValidation(formData, keys = []) {
    let keyKosong = [];

    const FORM_ID = formData.has("__FORM_ID__") ? "#" + formData.get("__FORM_ID__") : "";
    const FORM_ID_FOR_PROP = formData.has("__FORM_ID__") ? formData.get("__FORM_ID__") : "";
    // MERESET KONDISI INPUT DENGAN MENGHILANGKAN SEGALA CLASS VALIDASI
    for (const entries of formData.entries()) {
        let [key, value] = entries;
        if (key === "__FORM_ID__") continue;

        key = /\w\[\]/g.test(key) ? String(key).replace(/[\[\]]/g, "") : key;

        $(`small[data-role="form_validation"][data-key=${key}]`).remove();
    }

    $(
        `${FORM_ID} input.is-invalid:not(input[type=hidden]),input.is-invalid:not(input[type=hidden])[form="${FORM_ID_FOR_PROP}"],${FORM_ID} select.form-control.is-invalid,select.form-control.is-invalid[form="${FORM_ID_FOR_PROP}"],${FORM_ID} textarea.form-control.is-invalid,textarea.form-control.is-invalid[form="${FORM_ID_FOR_PROP}"]`
    ).removeClass("is-invalid");

    for (const data of formData.entries()) {
        let [key, value] = data;

        // VALIDASI TIDAK DILAKUKAN UNTUK KEY '__FORM_ID__'
        if (key === "__FORM_ID__") continue;

        let querySelector;
        let isMultiple = false;

        if (/\w\[\]/g.test(key)) {
            key = String(key).replace(/[\[\]]/g, "");
            querySelector = `${FORM_ID} select[name*=${key}][multiple=multiple], select[name*=${key}][multiple=multiple][form="${FORM_ID_FOR_PROP}"]`;

            isMultiple = true;
        } else {
            // SELECTOR UNTUK MENGECEK AGAR DATA YANG DIDAPATKAN ADALAH SELECT, TEXTAREA DAN INPUT SELAIN YANG BERTIPE HIDDEN
            querySelector = `${FORM_ID} input[name=${key}]:not(input[type=hidden]),input[form="${FORM_ID_FOR_PROP}"][name=${key}]:not(input[type=hidden]),${FORM_ID} select[name=${key}],select[name=${key}][form="${FORM_ID_FOR_PROP}"],${FORM_ID} textarea[name=${key}],textarea[name=${key}][form="${FORM_ID_FOR_PROP}"]`;
        }

        // PERULANGAN AKAN DI LANJUTKAN APABILA NAMA KEY ADA DI DALAM PENGECUALIAN
        if (keys.includes(key)) continue;

        // UNTUK SELECT2 MULTIPLE
        if (isMultiple) {
            // PERULANGAN AKAN DI LANJUTKAN APABILA ELEMENT DITEMUKAN DAN MEMILIKI NILAI
            if ($(querySelector).length > 0 && $(querySelector + " :selected").length > 0) continue;
        }
        // UNTUK INPUT SINGLE
        else {
            // PERULANGAN AKAN DI LANJUTKAN APABILA ELEMENT DITEMUKAN DAN MEMILIKI NILAI
            if ($(querySelector).length === 0) continue;
            // PERULANGAN AKAN DI LANJUTKAN APABILA MEMILIKI NILAI
            if (value !== "") continue;
        }

        keyKosong.push(key);

        // PERULANGAN AKAN DI LANJUTKAN APABILA ELEMENT TELAH DIVALIDASI
        if ($(querySelector).hasClass("is-invalid")) continue;

        $(querySelector).addClass("is-invalid");

        // UNTUK ELEMENT YANG MEMILIKI PARENT 'div.input-group'
        if ($(querySelector).parent().is("div.input-group")) {
            const parent = $(querySelector).parent();

            // UNTUK ELEMENT YANG MEMILIKI PARENT DENGAN CLASS YANG MENGANDUNG KATA 'col'
            if (parent.parent().is('[class*="col"]')) {
                const grandParent = parent.parent();

                grandParent.append(
                    '<small class="text-danger" data-role="form_validation" data-key="' +
                        key +
                        '"><i class="fas fa-exclamation-circle pr-2"></i>Data Belum Terisi</small>'
                );
            }
        }
        // UNTUK ELEMENT YANG MEMILIKI CLASS YANG TIDAK MENGANDUNG KATA 'col'
        else if ($(querySelector).is('[class*="col"]') === false) {
            $(
                '<small class="text-danger" data-role="form_validation" data-key="' +
                    key +
                    '"><i class="fas fa-exclamation-circle pr-2"></i>Data Belum Terisi</small>'
            ).insertAfter(querySelector);
        }

        const elementID = $(querySelector).prop("id");
        $(`#s2id_${elementID}`).css("border-color", "#dc3545");
    }

    const status = keyKosong.length === 0 ? true : false;

    return status;
}
