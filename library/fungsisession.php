<?php
 session_start();
 
function cekLogin($userName, $password, $idUser){
  include_once 'konfigurasiurl.php';
  include_once 'konfigurasidatabase.php';
  include_once 'konfigurasikuncirahasia.php';
  include_once 'fungsienkripsidekripsi.php';

  $arrayNotifikasi = [];
  if($idUser == ''){
    $sql  = $db->prepare("SELECT * from balistars_user 
      where userName=?
      AND jenisUser = ?
      and statusUser=?");
    $sql->execute([
      $userName,
      'Baru',
      'Aktif']);
    $data = $sql->fetch();

    $auth = false;
    if (!empty($password))
      $auth = password_verify($password, $data['password']);
    
    if($auth!=1){
      //username atau/ dan password salah
      $arrayNotifikasi = array('flagNotif' => 'gagal', 'pesan' => 'Maaf, username atau/dan password anda salah!');
    }
    else{
      //berhasil login
      $idUserTerenkripsi = enkripsi($data['idUser'],$kunciRahasia);
      $lokasi            = $BASE_URL_HTML.'/system/';
      $tokenCSRF         = bin2hex(random_bytes(32));

      if($auth == 1){
        $_SESSION['idUser']    = $idUserTerenkripsi;
        $_SESSION['tokenCSRF'] = $tokenCSRF;
        $arrayNotifikasi       = array('flagNotif' => 'sukses', 'lokasi' => $lokasi);
      }
    }
  }
  else{
    //sudah pernah login

    $idUserTerdekripsi = dekripsi($idUser,$kunciRahasia);

    $sql  = $db->prepare("SELECT * from balistars_user 
      where idUser=? 
      AND jenisUser = ?
      and statusUser=?");
    $sql->execute([
      $idUserTerdekripsi,
      'Baru',
      'Aktif']);
    $data = $sql->fetch();
    
    if($data){
      $tokenCSRF             = bin2hex(random_bytes(32));
      $_SESSION['idUser']    = $idUser;
      $_SESSION['tokenCSRF'] = $tokenCSRF;
      $lokasi                = $BASE_URL_HTML.'/system/';
      $arrayNotifikasi       = array('flagNotif' => 'sukses', 'lokasi' => $lokasi);
    }

  }

  echo json_encode($arrayNotifikasi);
}

function prosesLogOut(){
  include_once 'konfigurasiurl.php';

  session_start();
  session_destroy();

  header('location:'.$BASE_URL_HTML);
}
?>