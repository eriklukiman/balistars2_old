function rupiah(id) {
    const value = $(id).val();

    // console.log(id);
    let unformatValue = String(value).replace(/[.]/g, "");
    let type = "";

    if (/(^-)|(^0-)/g.test(unformatValue)) {
        type = "negative";
    } else {
        type = "positive";
    }

    const filteredValue = unformatValue.replace(/[\D]/g, "");
    let formattedValue = new Intl.NumberFormat("de-DE").format(filteredValue);

    if (type == "negative") {
        formattedValue = "-" + formattedValue;
    }

    $(id).val(formattedValue);
}

function formatRupiahDesimal(id) {
    const value = $(id).val();

    let nominal = "0",
        decimal = "0";

    let isDecimal;

    if (/[,]/.test(value)) {
        [nominal, decimal] = String(value).split(",");
        isDecimal = true;
    } else {
        nominal = value;
        isDecimal = false;
    }

    // BAGIAN NOMINAL

    let unformatNominal = String(nominal).replace(/[.]/g, "");
    let type = "";

    if (/(^-)|(^0-)/g.test(unformatNominal)) {
        type = "negative";
    } else {
        type = "positive";
    }

    const filteredNominal = unformatNominal.replace(/[\D]/g, "");

    // BAGIAN DESIMAL
    let output;
    if (isDecimal) {
        let filteredDecimal = decimal.replace(/[\D]/g, "");

        if (filteredDecimal !== "0") {
            filteredDecimal = filteredDecimal.replace(/0+$/g, "");
        }

        if (filteredDecimal === "") {
            output = new Intl.NumberFormat("de-DE").format(filteredNominal) + ",";
        } else {
            output = new Intl.NumberFormat("de-DE").format(filteredNominal) + "," + filteredDecimal;
        }
    } else {
        output = new Intl.NumberFormat("de-DE").format(filteredNominal);
    }

    // CONCAT NOMINAL & DESIMAL

    if (type == "negative") {
        output = "-" + output;
    }

    $(id).val(output);
}

function ubahToInt(value) {
    value = value || "0";
    value = accounting.unformat(value, ",");
    value = parseInt(value);

    return value;
}

function formatToFloat(value) {
    value = value || "0";
    value = accounting.unformat(value, ",");
    value = parseFloat(value);

    return value;
}

function ubahToRp(value) {
    // console.log(value, /[,]/g.test(value));
    if (/[\.]/g.test(value)) {
        const [nominal, decimal] = String(value).split(".");
        return accounting.formatNumber(value, { precision: decimal.length, thousand: ".", decimal: "," });
    } else {
        return accounting.formatNumber(value, { thousand: ".", decimal: "," });
    }
}
