<?php
// Fichier : pages/vehicules.php

session_start();

// 🔒 Accès sécurisé : réservé aux administrateurs
if (empty($_SESSION['id']) || $_SESSION['role'] !== "Admin") {
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
  <title>Garage – Gestion des véhicules</title>

  <?php include('inclusion_haut.php'); ?>
</head>

<body id="page-top">
  <?php include('alert_vehicule.php'); ?>

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
          <h1 class="h3 mb-4 text-primary">Gestion des véhicules</h1>

          <div class="text-left mb-3">
            <button
              class="btn btn-primary open-Ajouter_Vehicule"
              data-toggle="modal"
              data-backdrop="false"
              data-target="#ajouter_vehicule"
            >
              Ajouter un véhicule <i class="fa fa-plus"></i>
            </button>
          </div>

          <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Liste des véhicules</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table
                  class="table table-bordered"
                  id="dataTableVehicule"
                  width="100%"
                  cellspacing="0"
                >
                  <thead>
                    <tr>
                      <th class="text-center">N°</th>
                      <th class="text-center">Client</th>
                      <th class="text-center">Immatriculation</th>
                      <th class="text-center">Marque</th>
                      <th class="text-center">Modèle</th>
                      <th class="text-center">Type moteur</th>
                      <th class="text-center">Année</th>
                      <th class="text-center">Kilométrage</th>
                      <th class="text-center">Couleur</th>
                      <th class="text-center">Transmission</th>
                      <th class="text-center">Statut</th>
                      <th class="text-center">Détails</th>
                      <th class="text-center">Modifier</th>
                      <th class="text-center">Supprimer</th>
                    </tr>
                  </thead>
                  <tfoot>
                    <tr>
                      <th class="text-center">N°</th>
                      <th class="text-center">Client</th>
                      <th class="text-center">Immatriculation</th>
                      <th class="text-center">Marque</th>
                      <th class="text-center">Modèle</th>
                      <th class="text-center">Type moteur</th>
                      <th class="text-center">Année</th>
                      <th class="text-center">Kilométrage</th>
                      <th class="text-center">Couleur</th>
                      <th class="text-center">Transmission</th>
                      <th class="text-center">Statut</th>
                      <th class="text-center">Détails</th>
                      <th class="text-center">Modifier</th>
                      <th class="text-center">Supprimer</th>
                    </tr>
                  </tfoot>
                  <tbody>
                    <!-- Remplissage dynamique via DataTables -->
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

  <!-- Scripts JS -->
  <?php include('inclusion_bas.php'); ?>
  <script src="DataTables/data_table_vehicule.js?version=1.3"></script>

  <!-- Modals -->
  <?php include('modals/modal_vehicule.php'); ?>
  <?php include('modals/modal_vehicule_details.php'); ?>
  <?php include('modals/modal_deconnexion.php'); ?>

</body>
</html>
