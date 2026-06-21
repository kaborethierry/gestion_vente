<?php
// Fichier : pages/modals/modal_intervention.php

require_once dirname(__DIR__, 2) . '/api/modules/connect_db_pdo.php';

// Récupération des véhicules et des employés
$listeVehicules = [];
$listeEmployes = [];

try {
  $stmtVehicules = $bdd->query("
    SELECT id_vehicule, CONCAT(marque, ' ', modele, ' [', immatriculation, ']') AS libelle
    FROM vehicules
    WHERE supprimer = 'Non'
    ORDER BY libelle ASC
  ");
  $listeVehicules = $stmtVehicules->fetchAll(PDO::FETCH_ASSOC);

  $stmtEmployes = $bdd->query("
    SELECT id_employe, nom, prenom, poste
    FROM employes
    WHERE supprimer = 'Non'
    ORDER BY nom ASC, prenom ASC
  ");
  $listeEmployes = $stmtEmployes->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  echo '<div class="alert alert-warning">Erreur chargement données : ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>

<!-- Modal : Ajouter une intervention -->
<div class="modal fade" id="ajouter_intervention" tabindex="-1" role="dialog" aria-labelledby="ajouterInterventionLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ajouterInterventionLabel">Ajout d'une intervention</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/ajouter_intervention.php" method="POST" novalidate>
        <div class="modal-body">

          <div class="form-group row">
            <div class="col-md-6">
              <label for="vehicule_add">Véhicule <span class="text-danger">*</span></label>
              <select class="form-control select2" id="vehicule_add" name="id_vehicule" required style="width:100%">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listeVehicules as $v): ?>
                  <option value="<?= htmlspecialchars($v['id_vehicule']) ?>">
                    <?= htmlspecialchars($v['libelle']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="employe_add">Employé <span class="text-danger">*</span></label>
              <select class="form-control select2" id="employe_add" name="id_employe" required style="width:100%">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listeEmployes as $e): ?>
                  <option value="<?= htmlspecialchars($e['id_employe']) ?>">
                    <?= htmlspecialchars($e['nom'] . ' ' . $e['prenom'] . ' (' . $e['poste'] . ')') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="type_add">Type d’intervention <span class="text-danger">*</span></label>
              <select class="form-control" id="type_add" name="type_intervention" required>
                <option value="">-- Sélectionner --</option>
                <option value="Diagnostic">Diagnostic</option>
                <option value="Réparation">Réparation</option>
                <option value="Révision">Révision</option>
                <option value="Vidange">Vidange</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="kilometrage_add">Kilométrage</label>
              <input type="number" class="form-control" id="kilometrage_add" name="kilometrage" min="0" placeholder="Ex : 120000">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="date_debut_add">Date de début</label>
              <input type="datetime-local" class="form-control" id="date_debut_add" name="date_debut">
            </div>
            <div class="col-md-6">
              <label for="date_fin_add">Date de fin</label>
              <input type="datetime-local" class="form-control" id="date_fin_add" name="date_fin">
            </div>
          </div>

          <div class="form-group">
            <label for="description_add">Description</label>
            <textarea class="form-control" id="description_add" name="description" rows="3" placeholder="Détails de l’intervention"></textarea>
          </div>

          <div class="form-group row">
            <div class="col-md-4">
              <label for="statut_add">Statut</label>
              <select class="form-control" id="statut_add" name="statut">
                <option value="En attente" selected>En attente</option>
                <option value="En cours">En cours</option>
                <option value="Terminé">Terminé</option>
                <option value="Livré">Livré</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="priorite_add">Priorité</label>
              <select class="form-control" id="priorite_add" name="priorite">
                <option value="Normale" selected>Normale</option>
                <option value="Faible">Faible</option>
                <option value="Haute">Haute</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="main_oeuvre_add">Main d’œuvre HT</label>
              <input type="number" step="0.01" min="0" class="form-control" id="main_oeuvre_add" name="main_oeuvre_ht" placeholder="Ex : 15000.00">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="temps_estime_add">Temps estimé (h)</label>
              <input type="number" step="0.1" min="0" class="form-control" id="temps_estime_add" name="temps_estime" placeholder="Ex : 2.5">
            </div>
            <div class="col-md-6">
              <label for="temps_reel_add">Temps réel (h)</label>
              <input type="number" step="0.1" min="0" class="form-control" id="temps_reel_add" name="temps_reel" placeholder="Ex : 3">
            </div>
          </div>

          <div class="form-group">
            <small class="text-muted"><span class="text-danger">*</span> Champs obligatoires.</small>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Ajouter</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Activation de la recherche dynamique via Select2 sur Véhicule et Employé
(function($){
  function initSelect2($root) {
    $root.find('select.select2').each(function () {
      const $el = $(this);
      if ($el.data('select2')) return; // déjà initialisé
      $el.select2({
        width: '100%',
        placeholder: '-- Sélectionner --',
        allowClear: true,
        dropdownParent: $el.closest('.modal')
      });
    });
  }

  $('#ajouter_intervention').on('shown.bs.modal', function(){
    initSelect2($(this));
  });

  // Si besoin d'init au chargement (si le modal existe déjà visible dans le DOM)
  // $(document).ready(function(){ initSelect2($(document)); });
})(jQuery);
</script>


<!-- Modal : Modifier une intervention -->
<div class="modal fade" id="modifier_intervention" tabindex="-1" role="dialog" aria-labelledby="modifierInterventionLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modifierInterventionLabel">Modification d'une intervention</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/modifier_intervention.php" method="POST" novalidate>
        <div class="modal-body">

          <input type="hidden" id="id_intervention_modif" name="id_intervention" value="">

          <div class="form-group row">
            <div class="col-md-6">
              <label for="vehicule_modif">Véhicule <span class="text-danger">*</span></label>
              <select class="form-control" id="vehicule_modif" name="id_vehicule" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listeVehicules as $v): ?>
                  <option value="<?= htmlspecialchars($v['id_vehicule']) ?>">
                    <?= htmlspecialchars($v['libelle']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="employe_modif">Employé <span class="text-danger">*</span></label>
              <select class="form-control" id="employe_modif" name="id_employe" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listeEmployes as $e): ?>
                  <option value="<?= htmlspecialchars($e['id_employe']) ?>">
                    <?= htmlspecialchars($e['nom'] . ' ' . $e['prenom'] . ' (' . $e['poste'] . ')') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="type_modif">Type d’intervention <span class="text-danger">*</span></label>
              <select class="form-control" id="type_modif" name="type_intervention" required>
                <option value="Diagnostic">Diagnostic</option>
                <option value="Réparation">Réparation</option>
                <option value="Révision">Révision</option>
                <option value="Vidange">Vidange</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="kilometrage_modif">Kilométrage</label>
              <input type="number" class="form-control" id="kilometrage_modif" name="kilometrage" min="0">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="date_debut_modif">Date de début</label>
              <input type="datetime-local" class="form-control" id="date_debut_modif" name="date_debut">
            </div>
            <div class="col-md-6">
              <label for="date_fin_modif">Date de fin</label>
              <input type="datetime-local" class="form-control" id="date_fin_modif" name="date_fin">
            </div>
          </div>

          <div class="form-group">
            <label for="description_modif">Description</label>
            <textarea class="form-control" id="description_modif" name="description" rows="3"></textarea>
          </div>

          <div class="form-group row">
            <div class="col-md-4">
              <label for="statut_modif">Statut</label>
              <select class="form-control" id="statut_modif" name="statut">
                <option value="En attente">En attente</option>
                <option value="En cours">En cours</option>
                <option value="Terminé">Terminé</option>
                <option value="Livré">Livré</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="priorite_modif">Priorité</label>
              <select class="form-control" id="priorite_modif" name="priorite">
                <option value="Faible">Faible</option>
                <option value="Normale">Normale</option>
                <option value="Haute">Haute</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="main_oeuvre_modif">Main d’œuvre HT</label>
              <input type="number" step="0.01" min="0" class="form-control" id="main_oeuvre_modif" name="main_oeuvre_ht">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="temps_estime_modif">Temps estimé (h)</label>
              <input type="number" step="0.1" min="0" class="form-control" id="temps_estime_modif" name="temps_estime">
            </div>
            <div class="col-md-6">
              <label for="temps_reel_modif">Temps réel (h)</label>
              <input type="number" step="0.1" min="0" class="form-control" id="temps_reel_modif" name="temps_reel">
            </div>
          </div>

          <div class="form-group">
            <small class="text-muted"><span class="text-danger">*</span> Champs obligatoires.</small>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Modifier</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Détails d'une intervention (lecture seule) -->
<div class="modal fade" id="details_intervention" tabindex="-1" role="dialog" aria-labelledby="detailsInterventionLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailsInterventionLabel">Détails de l’intervention</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <div class="row">
          <div class="col-md-6">
            <h6 class="text-primary">Références</h6>
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>ID intervention:</strong> <span id="det_id_intervention"></span></li>
              <li class="list-group-item"><strong>Véhicule:</strong> <span id="det_vehicule_label"></span></li>
              <li class="list-group-item"><strong>Employé:</strong> <span id="det_employe_label"></span></li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6 class="text-primary">Type & Statuts</h6>
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>Type d’intervention:</strong> <span id="det_type_intervention"></span></li>
              <li class="list-group-item"><strong>Statut:</strong> <span id="det_statut"></span></li>
              <li class="list-group-item"><strong>Priorité:</strong> <span id="det_priorite"></span></li>
            </ul>
          </div>
        </div>

        <h6 class="text-primary">Planification</h6>
        <div class="row">
          <div class="col-md-6">
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>Date de début:</strong> <span id="det_date_debut"></span></li>
              <li class="list-group-item"><strong>Date de fin:</strong> <span id="det_date_fin"></span></li>
              <li class="list-group-item"><strong>Kilométrage:</strong> <span id="det_kilometrage"></span></li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6 class="text-primary">Temps & Coût</h6>
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>Temps estimé (h):</strong> <span id="det_temps_estime"></span></li>
              <li class="list-group-item"><strong>Temps réel (h):</strong> <span id="det_temps_reel"></span></li>
              <li class="list-group-item"><strong>Main d’œuvre HT:</strong> <span id="det_main_oeuvre_ht"></span></li>
            </ul>
          </div>
        </div>

        <h6 class="text-primary">Description</h6>
        <div class="row">
          <div class="col-md-12">
            <div class="border rounded p-3" style="min-height: 80px;">
              <span id="det_description"></span>
            </div>
          </div>
        </div>

        <h6 class="text-primary mt-3">Remarques</h6>
        <div class="row">
          <div class="col-md-12">
            <div class="border rounded p-3" style="min-height: 80px;">
              <span id="det_remarques"></span>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>
