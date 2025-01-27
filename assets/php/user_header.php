<header class="navbar bg-body-tertiary border-bottom">
  <div class="container-fluid">
    <a class="navbar-brand text-white" href="#">
      <img class="me-2" src="assets/img/logo.png" width="50" alt="logo">
      <?php echo $SERVERNAME?>
    </a>
    <div class="d-flex align-items-center">
        <b><?php echo getPlayerName($_SESSION['uuid']); ?></b>
        <img class="rounded ms-3" src="<?php echo getPlayerImageUrl($_SESSION['uuid']); ?>" alt="Avatar" width="50" height="50">
    </div>
  </div>
</header>