<?php

function getGrandTotal($diskon, $total, $DP, $PPN)
{
    $diskon = floatval($diskon);
    $total = intval($total);
    $DP = intval($DP);
    $PPN = floatval($PPN);

    $setelahDiskon = ((100 - $diskon) / 100) * $total;
    $setelahPPN = ((100 + $PPN) / 100) * $setelahDiskon;
    $grandTotal = $setelahPPN - $DP;

    return $grandTotal;
}

function getGrandTotalNoDP($diskon, $total, $PPN)
{
    $diskon = floatval($diskon);
    $total = intval($total);
    $PPN = floatval($PPN);

    $setelahDiskon = ((100 - $diskon) / 100) * $total;
    $grandTotal = ((100 + $PPN) / 100) * $setelahDiskon;

    return $grandTotal;
}
