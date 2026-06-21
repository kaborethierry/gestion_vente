<?php
// pages/historique.php

session_start();

// Seul un Admin peut accéder à cette page
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Garage – Historique des actions</title>

  <?php include('inclusion_haut.php'); ?>
</head>

<body id="page-top">
  <?php include('alert_utilisateur.php'); ?>

  <!-- Page Wrapper -->
  <div id="wrapper">

    <?php include('menu_admin.php'); ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <?php include('entete.php'); ?>

        <!-- Begin Page Content -->
        <div class="container-fluid">

          <!-- Page Heading -->
          <h1 class="h3 mb-4 text-gray-800">Historique des actions</h1>

          <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
              <h6 class="m-0 font-weight-bold text-primary">Liste des actions enregistrées</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table
                  class="table table-bordered"
                  id="datatable"
                  width="100%"
                  cellspacing="0"
                >
                  <thead>
                    <tr>
                      <th class="text-center">N°</th>
                      <th class="text-center">Adresse IP</th>
                      <th class="text-center">Date & Heure</th>
                      <th class="text-center">Utilisateur</th>
                      <th class="text-center">Action</th>
                      <th class="text-center">Table</th>
                      <th class="text-center">ID concerné</th>
                      <th class="text-center">Ancienne valeur</th>
                      <th class="text-center">Nouvelle valeur</th>
                    </tr>
                  </thead>
                  <tfoot>
                    <tr>
                      <th class="text-center">N°</th>
                      <th class="text-center">Adresse IP</th>
                      <th class="text-center">Date & Heure</th>
                      <th class="text-center">Utilisateur</th>
                      <th class="text-center">Action</th>
                      <th class="text-center">Table</th>
                      <th class="text-center">ID concerné</th>
                      <th class="text-center">Ancienne valeur</th>
                      <th class="text-center">Nouvelle valeur</th>
                    </tr>
                  </tfoot>
                  <tbody>
                    <!-- Contenu généré dynamiquement via DataTables -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->

      <!-- Footer -->
      <?php include('footer.php'); ?>
      <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Inclusion des scripts JS -->
  <?php include('inclusion_bas.php'); ?>
  <script src="DataTables/data_table_historique.js?v=20250813_1"></script>

  <!-- Modals éventuels -->
  <?php include('modals/modal_deconnexion.php'); ?>

</body>
</html>
