<?php
// pages/parametres.php

session_start();

// Seul un Admin peut accéder à cette page
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== "Admin") {
    session_unset();
    session_destroy();
    header('Location: ../index.php?erreur=3');
    exit;
}

// Chargement initial côté serveur (lecture des paramètres existants)
$tva            = '18.00';
$remise         = '0.00';
$societe        = ['nom' => '', 'logo' => null, 'pied_page' => ''];
$rolesExistants = ['Admin','Mécanicien','Gestionnaire','Caissier']; // d'après la colonne role de `utilisateurs`
$categories     = [];

try {
    require_once __DIR__ . '/../api/modules/connect_db_pdo.php';

    if ($bdd instanceof PDO) {
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Lecture param TVA/remise depuis table parametres (si elle existe)
        // sinon valeurs par défaut ci-dessus
        try {
            $stmt = $bdd->query("SELECT cle_param, valeur FROM parametres WHERE supprimer = 'Non'");
            $params = $stmt->fetchAll();
            foreach ($params as $p) {
                if ($p['cle_param'] === 'tva')    { $tva = (string)$p['valeur']; }
                if ($p['cle_param'] === 'remise') { $remise = (string)$p['valeur']; }
            }
        } catch (Throwable $ignored) {
            // Si la table n'existe pas encore, on continue avec les défauts
        }

        // Lecture infos société (premier enregistrement non supprimé)
        try {
            $stmt = $bdd->query("SELECT nom, logo, pied_page FROM societe WHERE supprimer = 'Non' ORDER BY id_societe ASC LIMIT 1");
            if ($row = $stmt->fetch()) {
                $societe['nom']       = $row['nom'] ?? '';
                $societe['logo']      = $row['logo'] ?? null;
                $societe['pied_page'] = $row['pied_page'] ?? '';
            }
        } catch (Throwable $ignored) {}

        // Lecture catégories de pièces
        try {
            $stmt = $bdd->query("
                SELECT id_categorie, libelle, description
                FROM categories_pieces
                WHERE supprimer = 'Non'
                ORDER BY libelle ASC
            ");
            $categories = $stmt->fetchAll();
        } catch (Throwable $ignored) {}
    }
} catch (Throwable $e) {
    // error_log('[parametres] ' . $e->getMessage());
}

// Helpers d'affichage
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Garage – Paramètres & Configuration</title>

  <?php include('inclusion_haut.php'); ?>
</head>

<body id="page-top">

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
          <h1 class="h3 mb-4 text-gray-800">Paramètres & Configuration</h1>

          <!-- Alertes (feedback) -->
          <?php if (!empty($_SESSION['param_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <?= e($_SESSION['param_success']); unset($_SESSION['param_success']); ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          <?php endif; ?>
          <?php if (!empty($_SESSION['param_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?= e($_SESSION['param_error']); unset($_SESSION['param_error']); ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          <?php endif; ?>

          <!-- Sections -->
          <div class="row">

            <!-- Configuration TVA / Remise -->
            <div class="col-lg-6">
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Configuration TVA & Remise</h6>
                </div>
                <div class="card-body">
                  <form action="../api/modules/maj_tva_remise.php" method="post">
                    <div class="form-group">
                      <label for="tva">TVA (%)</label>
                      <input type="text" class="form-control" id="tva" name="tva" value="<?= e($tva) ?>" placeholder="Ex: 18.00" required>
                    </div>
                    <div class="form-group">
                      <label for="remise">Remise par défaut (%)</label>
                      <input type="text" class="form-control" id="remise" name="remise" value="<?= e($remise) ?>" placeholder="Ex: 0.00" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Paramètres société -->
            <div class="col-lg-6">
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Paramètres société</h6>
                </div>
                <div class="card-body">
                  <form action="../api/modules/maj_societe.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                      <label for="nom_societe">Nom de la société</label>
                      <input type="text" class="form-control" id="nom_societe" name="nom_societe" value="<?= e($societe['nom']) ?>" required>
                    </div>
                    <div class="form-group">
                      <label for="logo">Logo (PNG/JPG, max 2 Mo)</label>
                      <input type="file" class="form-control-file" id="logo" name="logo" accept=".png,.jpg,.jpeg">
                      <?php if (!empty($societe['logo'])): ?>
                        <small class="form-text text-muted">Logo actuel: <?= e($societe['logo']) ?></small>
                      <?php endif; ?>
                    </div>
                    <div class="form-group">
                      <label for="pied_page">Pied de page des documents</label>
                      <textarea class="form-control" id="pied_page" name="pied_page" rows="3"><?= e($societe['pied_page']) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                  </form>
                </div>
              </div>
            </div>

          </div><!-- /.row -->

          <div class="row">

            <!-- Rôles & Permissions (basé sur la colonne role de `utilisateurs`) -->
            <div class="col-lg-6">
              <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                  <h6 class="m-0 font-weight-bold text-primary">Rôles & Permissions</h6>
                  <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal_role_nouveau">Nouveau rôle</button>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                      <thead class="thead-light">
                        <tr>
                          <th>Rôle</th>
                          <th>Utilisateurs</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        // Comptage des utilisateurs par rôle existant
                        $rolesCount = [];
                        try {
                            $rs = $bdd->query("
                                SELECT role, COUNT(*) AS nb
                                FROM utilisateurs
                                WHERE supprimer = 'Non'
                                GROUP BY role
                            ")->fetchAll();
                            foreach ($rs as $r) {
                                $rolesCount[$r['role']] = (int)$r['nb'];
                            }
                        } catch (Throwable $ignored) {}

                        foreach ($rolesExistants as $role):
                          $nb = $rolesCount[$role] ?? 0;
                        ?>
                          <tr>
                            <td><?= e($role) ?></td>
                            <td class="text-right"><?= $nb ?></td>
                            <td class="text-center">
                              <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal_role_edit" data-role="<?= e($role) ?>">Modifier</button>
                              <?php if ($role !== 'Admin'): ?>
                                <form action="../api/modules/supprimer_role.php" method="post" style="display:inline-block" onsubmit="return confirm('Supprimer ce rôle ?');">
                                  <input type="hidden" name="role" value="<?= e($role) ?>">
                                  <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                  <small class="text-muted d-block mt-2">
                    Remarque: le système actuel utilise la colonne <code>role</code> de la table <code>utilisateurs</code>.
                    Pour une granularité fine, on peut introduire des tables <code>roles</code> / <code>permissions</code>.
                  </small>
                </div>
              </div>
            </div>

            <!-- Options de rappel (Email / SMS) -->
            <div class="col-lg-6">
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Options de rappel (Email / SMS)</h6>
                </div>
                <div class="card-body">
                  <form action="../api/modules/maj_rappels.php" method="post">
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="smtp_host">SMTP Host</label>
                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" placeholder="smtp.exemple.com">
                      </div>
                      <div class="form-group col-md-3">
                        <label for="smtp_port">Port</label>
                        <input type="text" class="form-control" id="smtp_port" name="smtp_port" placeholder="587">
                      </div>
                      <div class="form-group col-md-3">
                        <label for="smtp_secure">Sécurité</label>
                        <select class="form-control" id="smtp_secure" name="smtp_secure">
                          <option value="">Aucune</option>
                          <option value="tls">TLS</option>
                          <option value="ssl">SSL</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="smtp_user">Utilisateur</label>
                        <input type="text" class="form-control" id="smtp_user" name="smtp_user" placeholder="noreply@exemple.com">
                      </div>
                      <div class="form-group col-md-6">
                        <label for="smtp_pass">Mot de passe</label>
                        <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" placeholder="••••••••">
                      </div>
                    </div>

                    <hr>

                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="sms_api_key">SMS API Key</label>
                        <input type="text" class="form-control" id="sms_api_key" name="sms_api_key" placeholder="clé API">
                      </div>
                      <div class="form-group col-md-6">
                        <label for="sms_sender">SMS Sender ID</label>
                        <input type="text" class="form-control" id="sms_sender" name="sms_sender" placeholder="GarageBI">
                      </div>
                    </div>

                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="delai_rappel_entretien">Délai rappel entretien (jours)</label>
                        <input type="number" class="form-control" id="delai_rappel_entretien" name="delai_rappel_entretien" min="0" value="7">
                      </div>
                      <div class="form-group col-md-6">
                        <label for="delai_rappel_facture">Délai rappel facture impayée (jours)</label>
                        <input type="number" class="form-control" id="delai_rappel_facture" name="delai_rappel_facture" min="0" value="3">
                      </div>
                    </div>

                    <div class="form-group form-check">
                      <input type="checkbox" class="form-check-input" id="enable_email" name="enable_email" checked>
                      <label class="form-check-label" for="enable_email">Activer les rappels par Email</label>
                    </div>
                    <div class="form-group form-check">
                      <input type="checkbox" class="form-check-input" id="enable_sms" name="enable_sms">
                      <label class="form-check-label" for="enable_sms">Activer les rappels par SMS</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                  </form>
                </div>
              </div>
            </div>

          </div><!-- /.row -->

          <div class="row">

            <!-- Gestion catégories de pièces -->
            <div class="col-lg-12">
              <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                  <h6 class="m-0 font-weight-bold text-primary">Catégories de pièces</h6>
                  <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal_categorie_nouvelle">Ajouter une catégorie</button>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                      <thead class="thead-light">
                        <tr>
                          <th class="text-center" style="width:70px">ID</th>
                          <th>Libellé</th>
                          <th>Description</th>
                          <th class="text-center" style="width:160px">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if ($categories): ?>
                          <?php foreach ($categories as $cat): ?>
                          <tr>
                            <td class="text-center"><?= (int)$cat['id_categorie'] ?></td>
                            <td><?= e($cat['libelle']) ?></td>
                            <td><?= e($cat['description'] ?? '') ?></td>
                            <td class="text-center">
                              <button
                                class="btn btn-sm btn-primary"
                                data-toggle="modal"
                                data-target="#modal_categorie_edit"
                                data-id="<?= (int)$cat['id_categorie'] ?>"
                                data-libelle="<?= e($cat['libelle']) ?>"
                                data-description="<?= e($cat['description'] ?? '') ?>"
                              >Modifier</button>

                              <form action="../api/modules/supprimer_categorie_piece.php" method="post" style="display:inline-block" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                <input type="hidden" name="id_categorie" value="<?= (int)$cat['id_categorie'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                              </form>
                            </td>
                          </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr><td colspan="4" class="text-center text-muted">Aucune catégorie pour le moment</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

          </div><!-- /.row -->

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

  <!-- Modals -->
  <!-- Nouveau rôle -->
  <div class="modal fade" id="modal_role_nouveau" tabindex="-1" role="dialog" aria-labelledby="roleNouveauLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form action="../api/modules/ajouter_role.php" method="post" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="roleNouveauLabel">Nouveau rôle</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="role_nom">Nom du rôle</label>
            <input type="text" class="form-control" id="role_nom" name="role_nom" placeholder="Ex: Gestionnaire" required>
          </div>
          <small class="text-muted">Les rôles disponibles par défaut: Admin, Mécanicien, Gestionnaire, Caissier.</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modifier rôle -->
  <div class="modal fade" id="modal_role_edit" tabindex="-1" role="dialog" aria-labelledby="roleEditLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form action="../api/modules/modifier_role.php" method="post" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="roleEditLabel">Modifier rôle</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_role_old" name="old_role" value="">
          <div class="form-group">
            <label for="edit_role_new">Nouveau nom du rôle</label>
            <input type="text" class="form-control" id="edit_role_new" name="new_role" required>
          </div>
          <small class="text-muted">Attention: renommer un rôle impacte tous les utilisateurs ayant ce rôle.</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Nouvelle catégorie -->
  <div class="modal fade" id="modal_categorie_nouvelle" tabindex="-1" role="dialog" aria-labelledby="catNouvLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form action="../api/modules/ajouter_categorie_piece.php" method="post" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="catNouvLabel">Nouvelle catégorie de pièces</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="cat_libelle">Libellé</label>
            <input type="text" class="form-control" id="cat_libelle" name="libelle" required>
          </div>
          <div class="form-group">
            <label for="cat_description">Description</label>
            <textarea class="form-control" id="cat_description" name="description" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modifier catégorie -->
  <div class="modal fade" id="modal_categorie_edit" tabindex="-1" role="dialog" aria-labelledby="catEditLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form action="../api/modules/modifier_categorie_piece.php" method="post" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="catEditLabel">Modifier catégorie</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_cat_id" name="id_categorie" value="">
          <div class="form-group">
            <label for="edit_cat_libelle">Libellé</label>
            <input type="text" class="form-control" id="edit_cat_libelle" name="libelle" required>
          </div>
          <div class="form-group">
            <label for="edit_cat_description">Description</label>
            <textarea class="form-control" id="edit_cat_description" name="description" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal déconnexion -->
  <?php include('modals/modal_deconnexion.php'); ?>

  <script>
    // Remplissage du modal "Modifier rôle"
    $('#modal_role_edit').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var role = button.data('role') || '';
      var modal = $(this);
      modal.find('#edit_role_old').val(role);
      modal.find('#edit_role_new').val(role);
    });

    // Remplissage du modal "Modifier catégorie"
    $('#modal_categorie_edit').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var lib = button.data('libelle') || '';
      var desc = button.data('description') || '';
      var modal = $(this);
      modal.find('#edit_cat_id').val(id);
      modal.find('#edit_cat_libelle').val(lib);
      modal.find('#edit_cat_description').val(desc);
    });
  </script>

</body>
</html>
