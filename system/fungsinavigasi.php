<?php
//phpinfo();
require_once($BASE_URL_PHP . '/../library/consts.php');

function icon($logo)
{
?>
  <link rel="shortcut icon" type="image/x-icon" href="<?= $logo ?>">
<?php
}

function navBarTop($logo, $BASE_URL_HTML)
{
?>
  <nav class="navbar navbar-fixed-top" style="background-color: green; color: white;">
    <div class="container-fluid">
      <div class="navbar-btn">
        <button type="button" class="btn-toggle-offcanvas"><i class="lnr lnr-menu fa fa-bars"></i></button>
      </div>

      <div class="navbar-brand" >
        <a href="#">
          <img src="<?= $logo ?>" alt="Clinic Logo" style="width: 10%;">
          <span style="color: white;">Balistars System</span>
        </a>
      </div>

      <div class="navbar-right">
        <div id="navbar-menu">
          <ul class="nav navbar-nav">
            <li>
              <a href="<?= $BASE_URL_HTML ?>/library/proseslogout.php" class="icon-menu">
                <i class="icon-login" style="color: white;"></i>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
<?php
}

function navBarSide($data, $db, $idUser, $BASE_URL_HTML)
{
?>
  <div id="left-sidebar" class="sidebar">
    <div class="sidebar-scroll">
      <div class="user-account">
        <img src="<?= $BASE_URL_HTML ?>/assets/images/man.png" class="rounded-circle user-photo" alt="User Profile Picture">
        <div class="dropdown">
          <span>Selamat Datang,</span><br>
          <strong style="color: #17a3b8"><?= $data['namaPegawai']?> <?=$idUser?></strong>
        </div>
      </div>
      <!-- Nav tabs -->
      <ul class="nav nav-tabs">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="page-blank.html#menu"> Menu</a></li>
      </ul>

      <!-- Tab panes -->
      <div class="tab-content p-l-0 p-r-0">
        <div class="tab-pane active" id="menu">
          <nav id="left-sidebar-nav" class="sidebar-nav">
            <ul id="main-menu" class="metismenu">
              <?php
              $sqlMenu = $db->prepare('
             SELECT balistars_user_detail.idMenu, namaMenu, icon FROM balistars_user_detail
             LEFT JOIN balistars_menu ON balistars_menu.idMenu=balistars_user_detail.idMenu
             WHERE balistars_user_detail.idUser=? AND balistars_menu.statusMenu = ? GROUP BY balistars_menu.idMenu ORDER BY balistars_menu.indexMenu');
              $sqlMenu->execute([$idUser,'Aktif']);
              //var_dump($dataMenu);
              while ($row = $sqlMenu->fetch(PDO::FETCH_ASSOC)) {
              ?>
                <li>
                  <a href="#" class="has-arrow">
                    <i class="<?= $row['icon'] ?>"></i> <span><?= $row['namaMenu'] ?></span>
                  </a>
                  <ul>
                    <?php
                    $sqlMenuSub = $db->prepare('
                   SELECT * FROM balistars_user_detail
                    LEFT JOIN balistars_menu_sub ON balistars_menu_sub.idMenuSub=balistars_user_detail.idMenuSub inner join balistars_menu on balistars_menu.idMenu=balistars_menu_sub.idMenu
                    WHERE balistars_user_detail.idUser=? and balistars_user_detail.idMenu=? AND balistars_menu_sub.statusMenuSub = ? ORDER BY balistars_menu_sub.indexMenuSub');
                    $sqlMenuSub->execute([$idUser, $row['idMenu'],'Aktif']);
                    while ($key = $sqlMenuSub->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                      <li><a href="<?= $BASE_URL_HTML ?>/system/<?= $key['namaKelompok'] ?>/<?= $key['namaFolder'] ?>"><?= $key['namaMenuSub'] ?></a></li>
                    <?php
                    }
                    ?>
                  </ul>
                </li>
              <?php
              }
              ?>

            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
<?php
}

function footer()
{
?>
  <div class="float-right d-none d-sm-block">
    <b>Version</b> 1.0.0
  </div>
  <strong>Copyright &copy; 2019 <a href="#" style="color: #17a3b8">TempatKita Software</a>.</strong> All rights
  reserved.
<?php
}

?>