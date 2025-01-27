<!DOCTYPE html>
<html>
<head>
    <title>Eagle UI - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/img/logo.ico" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<body data-bs-theme="dark">
    <div class="vh-100">
      <header class="navbar bg-body-tertiary border-bottom">
        <div class="container-fluid">
          <div class="d-flex align-items-center">  
            <a class="navbar-brand text-white" href="#">
              <img class="me-2" src="assets/img/logo.png" width="50" alt="logo">
              <b>EAGLE </b>UI
            </a>
            <div class="navbar-nav ms-lg-5 px-4 text-uppercase">
              <a class="nav-link text-white" href="staff.php">Dashboard</a>
            </div>
            <div class="navbar-nav px-4 text-uppercase">
              <a class="nav-link text-white" href="logout.php">Logout</a>
            </div>
          </div>
          <div class="d-flex align-items-center">
              <b><?php echo getPlayerName($_SESSION['uuid']); ?></b>
              <img class="rounded ms-3" src="<?php echo getPlayerImageUrl($_SESSION['uuid']); ?>" alt="Avatar" width="50" height="50">
          </div>
        </div>
      </header>