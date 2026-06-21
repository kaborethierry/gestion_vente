<?php
// Fichier : pages/modals/modal_intervention_pieces.php

// Connexion PDO
require_once dirname(__DIR__, 2) . '/api/modules/connect_db_pdo.php';

$listeInterventions = [];
$listePieces = [];

try {
  // Liste des interventions actives (non supprimées)
  $sqlInterv = "
    SELECT 
      i.id_intervention,
      i.type_intervention,
      i.date_debut,
      i.statut,
      v.immatriculation,
      v.marque,
      v.modele
    FROM interventions i
    LEFT JOIN vehicules v ON v.id_vehicule = i.id_vehicule
    WHERE i.supprimer = 'Non'
    ORDER BY i.id_intervention DESC
  ";
  $stmtI = $bdd->query($sqlInterv);
  $listeInterventions = $stmtI->fetchAll(PDO::FETCH_ASSOC);

  // Liste des pièces actives (non supprimées)
  $sqlPieces = "
    SELECT 
      p.id_piece,
      p.reference,
      p.designation,
      p.prix_vente,
      p.quantite_stock
    FROM pieces p
    WHERE p.supprimer = 'Non'
    ORDER BY p.designation ASC
  ";
  $stmtP = $bdd->query($sqlPieces);
  $listePieces = $stmtP->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  echo '<div class="alert alert-warning">Erreur chargement des listes : ' . htmlspecialchars($e->getMessage()) . '</div>';
  $listeInterventions = [];
  $listePieces = [];
}
?>

<!-- Modal : Ajouter une pièce à une intervention -->
<div class="modal fade" id="ajouter_intervention_piece" tabindex="-1" role="dialog" aria-labelledby="ajouterInterventionPieceLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ajouterInterventionPieceLabel">Ajouter une pièce à une intervention</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/ajouter_intervention_piece.php" method="POST" novalidate>
        <div class="modal-body">

          <div class="form-group row">
            <div class="col-md-6">
              <label for="id_intervention_add">Intervention <span class="text-danger">*</span></label>
              <select class="form-control select2" id="id_intervention_add" name="id_intervention" required style="width:100%">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listeInterventions as $itv): ?>
                  <?php
                    $labelItv = sprintf(
                      '#%d - %s | %s %s (%s) | %s',
                      (int)$itv['id_intervention'],
                      htmlspecialchars($itv['type_intervention']),
                      htmlspecialchars($itv['marque'] ?? ''),
                      htmlspecialchars($itv['modele'] ?? ''),
                      htmlspecialchars($itv['immatriculation'] ?? '—'),
                      htmlspecialchars(date('d/m/Y H:i', strtotime($itv['date_debut'])))
                    );
                  ?>
                  <option value="<?= (int)$itv['id_intervention'] ?>">
                    <?= $labelItv ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label for="id_piece_add">Pièce <span class="text-danger">*</span></label>
              <select class="form-control select2" id="id_piece_add" name="id_piece" required style="width:100%">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listePieces as $pc): ?>
                  <?php
                    $labelPc = sprintf(
                      '[%s] %s — Stock: %s — PV: %s',
                      htmlspecialchars($pc['reference']),
                      htmlspecialchars($pc['designation']),
                      is_null($pc['quantite_stock']) ? '—' : (int)$pc['quantite_stock'],
                      is_null($pc['prix_vente']) ? '—' : number_format((float)$pc['prix_vente'], 2, '.', ' ')
                    );
                  ?>
                  <option value="<?= (int)$pc['id_piece'] ?>">
                    <?= $labelPc ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="quantite_add">Quantité <span class="text-danger">*</span></label>
              <input type="number" min="1" step="1" class="form-control" id="quantite_add" name="quantite" placeholder="Ex: 1" required>
            </div>
            <div class="col-md-6">
              <label for="prix_unitaire_add">Prix unitaire (au moment de l'utilisation)</label>
              <input type="number" step="0.01" min="0" class="form-control" id="prix_unitaire_add" name="prix_unitaire" placeholder="Ex: 20000.00">
              <small class="form-text text-muted">
                Laisse vide pour utiliser le prix de vente de la pièce (si défini).
              </small>
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

<!-- Modal : Modifier une ligne intervention/pièce -->
<div class="modal fade" id="modifier_intervention_piece" tabindex="-1" role="dialog" aria-labelledby="modifierInterventionPieceLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modifierInterventionPieceLabel">Modifier une ligne intervention/pièce</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/modifier_intervention_piece.php" method="POST" novalidate>
        <div class="modal-body">

          <input type="hidden" id="id_ligne_modif" name="id" value="">

          <div class="form-group row">
            <div class="col-md-6">
              <label for="id_intervention_modif">Intervention <span class="text-danger">*</span></label>
              <select class="form-control select2" id="id_intervention_modif" name="id_intervention" required style="width:100%">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listeInterventions as $itv): ?>
                  <?php
                    $labelItv = sprintf(
                      '#%d - %s | %s %s (%s) | %s',
                      (int)$itv['id_intervention'],
                      htmlspecialchars($itv['type_intervention']),
                      htmlspecialchars($itv['marque'] ?? ''),
                      htmlspecialchars($itv['modele'] ?? ''),
                      htmlspecialchars($itv['immatriculation'] ?? '—'),
                      htmlspecialchars(date('d/m/Y H:i', strtotime($itv['date_debut'])))
                    );
                  ?>
                  <option value="<?= (int)$itv['id_intervention'] ?>">
                    <?= $labelItv ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label for="id_piece_modif">Pièce <span class="text-danger">*</span></label>
              <select class="form-control select2" id="id_piece_modif" name="id_piece" required style="width:100%">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listePieces as $pc): ?>
                  <?php
                    $labelPc = sprintf(
                      '[%s] %s — Stock: %s — PV: %s',
                      htmlspecialchars($pc['reference']),
                      htmlspecialchars($pc['designation']),
                      is_null($pc['quantite_stock']) ? '—' : (int)$pc['quantite_stock'],
                      is_null($pc['prix_vente']) ? '—' : number_format((float)$pc['prix_vente'], 2, '.', ' ')
                    );
                  ?>
                  <option value="<?= (int)$pc['id_piece'] ?>">
                    <?= $labelPc ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="quantite_modif">Quantité <span class="text-danger">*</span></label>
              <input type="number" min="1" step="1" class="form-control" id="quantite_modif" name="quantite" placeholder="Ex: 1" required>
            </div>
            <div class="col-md-6">
              <label for="prix_unitaire_modif">Prix unitaire</label>
              <input type="number" step="0.01" min="0" class="form-control" id="prix_unitaire_modif" name="prix_unitaire" placeholder="Ex: 20000.00">
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

<script>
// Initialisation Select2 sur ouverture des modals pour éviter les collisions d'ID
(function($){
  function initSelect2($root) {
    $root.find('select.select2').each(function () {
      const $el = $(this);
      if ($el.data('select2')) return; // déjà initialisé
      $el.select2({
        width: '100%',
        placeholder: '-- Sélectionner --',
        allowClear: true,
        dropdownParent: $el.closest('.modal') // important pour que le dropdown s'affiche au-dessus du modal
      });
    });
  }

  // Quand les modals s'ouvrent
  $('#ajouter_intervention_piece').on('shown.bs.modal', function(){
    initSelect2($(this));
  });
  $('#modifier_intervention_piece').on('shown.bs.modal', function(){
    initSelect2($(this));
  });

  // Si besoin d'init au chargement (utile si modal déjà ouvert côté DOM)
  $(document).ready(function(){
    // Ne pas initialiser ici si tes modals sont injectés dynamiquement plus tard
    // initSelect2($(document));
  });
})(jQuery);
</script>
