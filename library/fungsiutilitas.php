<?php
function selected($id1, $id2)
{
    $selected = '';
    if ($id1 == $id2) {
        $selected = 'selected';
    }
    return $selected;
}

function checked($id1, $id2)
{
    $checked = '';
    if ($id1 == $id2) {
        $checked = 'checked';
    }
    return $checked;
}

function radioBox($radioBoxName, $arrayValue, $valueUpdate)
{
    for ($i = 0; $i < count($arrayValue); $i++) {
        $checked = checked($arrayValue[$i], $valueUpdate);
?>
        <label class="fancy-radio custom-color-green">
            <input name="<?= $radioBoxName ?>" value="<?= $arrayValue[$i] ?>" type="radio" <?= $checked ?>>
            <span><i></i><?= $arrayValue[$i] ?></span>
        </label>
        <br>
<?php
    }
}

function executeQueryUpdateForm($sql, $db, $var)
{
    $sqlUpdate = $db->prepare($sql);
    $sqlUpdate->execute([$var]);
    $dataUpdate = $sqlUpdate->fetch();

    return $dataUpdate;
}

function poinPengajuan(string $tahapan, int $minutes)
{
    switch ($tahapan) {
        case 'Kontrol Area':

            // < 180
            if ($minutes < 180) {
                return 100;
            }
            // 181 - 360
            else if ($minutes >= 180 && $minutes <= 360) {
                return 90;
            }
            // 361 - 540
            else if ($minutes >= 361 && $minutes <= 540) {
                return 80;
            }
            // 541 - 720
            else if ($minutes >= 541 && $minutes <= 720) {
                return 70;
            }
            // > 720
            else {
                return 60;
            }

            break;
        case 'Headoffice':

            // < 240
            if ($minutes < 240) {
                return 100;
            }
            // 240 - 480
            else if ($minutes >= 240 && $minutes <= 480) {
                return 90;
            }
            // 481 - 720
            else if ($minutes >= 481 && $minutes <= 720) {
                return 80;
            }
            // 721 - 960
            else if ($minutes >= 721 && $minutes <= 960) {
                return 70;
            }
            // > 960
            else {
                return 60;
            }

            break;
        case 'Payment':

            // < 480
            if ($minutes < 480) {
                return 100;
            }
            // 480 - 960
            else if ($minutes >= 480 && $minutes <= 960) {
                return 90;
            }
            // 961 - 1200
            else if ($minutes >= 961 && $minutes <= 1200) {
                return 80;
            }
            // 1201 - 1680
            else if ($minutes >= 1201 && $minutes <= 1680) {
                return 70;
            }
            // > 1681
            else {
                return 60;
            }

            break;

        default:
            return 0;
            break;
    }
}

function statusAveragePoin(int $average)
{
    if ($average <= 60) {
        return 'danger';
    } else if ($average > 60 && $average <= 80) {
        return 'warning';
    } else {
        return 'success';
    }
}


?>