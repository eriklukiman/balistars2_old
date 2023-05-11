<?php
include_once 'fungsisession.php';

extract($_REQUEST);
cekLogin($username,$password,$idUser);
?>