<?php
// Fichier : pages/modals/modal_mouvement.php

// Connexion PDO
require_once dirname(__DIR__, 2) . '/api/modules/connect_db_pdo.php';

$listePieces = [];
$typesMouvement = [
  'ENTREE' => 'Entrée',
  'SORTIE' => 'Sortie',
  'AJUSTEMENT' => 'Ajustement'
];

try {
  // Liste des pièces actives
  $stmt = $bdd->query("
    SELECT id_piece, reference, designation
    FROM pieces
    WHERE supprimer = 'Non'
    ORDER BY reference ASC, designation ASC
  ");
  $listePieces = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  echo '<div class="alert alert-warning">Erreur chargement pièces : ' . htmlspecialchars($e->getMessage()) . '</div>';
  $listePieces = [];
}
?>

<!-- Modal : Ajouter un mouvement de stock -->
<div class="modal fade" id="ajouter_mouvement" tabindex="-1" role="dialog" aria-labelledby="ajouterMouvementLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ajouterMouvementLabel">Ajout d'un mouvement de stock</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/ajouter_mouvement.php" method="POST" novalidate>
        <div class="modal-body">

          <div class="form-group row">
            <div class="col-md-6">
              <label for="piece_add">Pièce <span class="text-danger">*</span></label>
              <select class="form-control" id="piece_add" name="id_piece" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listePieces as $p): ?>
                  <option value="<?= htmlspecialchars($p['id_piece']) ?>">
                    <?= htmlspecialchars($p['reference'] . ' — ' . $p['designation']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="type_add">Type de mouvement <span class="text-danger">*</span></label>
              <select class="form-control" id="type_add" name="type_mouvement" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($typesMouvement as $val => $lbl): ?>
                  <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($lbl) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="quantite_add">Quantité <span class="text-danger">*</span></label>
              <input type="number" min="1" step="1" class="form-control" id="quantite_add" name="quantite" placeholder="Ex: 5" required>
            </div>
            <div class="col-md-6">
              <label for="date_add">Date et heure</label>
              <input type="datetime-local" class="form-control" id="date_add" name="date_mouvement" placeholder="Laisser vide pour maintenant">
            </div>
          </div>

          <div class="form-group">
            <label for="motif_add">Motif / Observation (optionnel)</label>
            <textarea class="form-control" id="motif_add" name="motif" rows="2" maxlength="255" placeholder="Ajoutez un commentaire si nécessaire"></textarea>
          </div>

          <div class="form-group">
            <small class="text-muted">
              <span class="text-danger">*</span> Champs obligatoires.
            </small>
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

<!-- Modal : Modifier un mouvement de stock -->
<div class="modal fade" id="modifier_mouvement" tabindex="-1" role="dialog" aria-labelledby="modifierMouvementLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modifierMouvementLabel">Modification d'un mouvement de stock</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/modifier_mouvement.php" method="POST" novalidate>
        <div class="modal-body">

          <input type="hidden" id="id_mouvement_modif" name="id_mouvement" value="">

          <div class="form-group row">
            <div class="col-md-6">
              <label for="piece_modif">Pièce <span class="text-danger">*</span></label>
              <select class="form-control" id="piece_modif" name="id_piece" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listePieces as $p): ?>
                  <option value="<?= htmlspecialchars($p['id_piece']) ?>">
                    <?= htmlspecialchars($p['reference'] . ' — ' . $p['designation']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="type_modif">Type de mouvement <span class="text-danger">*</span></label>
              <select class="form-control" id="type_modif" name="type_mouvement" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($typesMouvement as $val => $lbl): ?>
                  <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($lbl) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="quantite_modif">Quantité <span class="text-danger">*</span></label>
              <input type="number" min="1" step="1" class="form-control" id="quantite_modif" name="quantite" placeholder="Ex: 5" required>
            </div>
            <div class="col-md-6">
              <label for="date_modif">Date et heure</label>
              <input type="datetime-local" class="form-control" id="date_modif" name="date_mouvement" placeholder="Laisser vide pour maintenant">
            </div>
          </div>

          <div class="form-group">
            <label for="motif_modif">Motif / Observation (optionnel)</label>
            <textarea class="form-control" id="motif_modif" name="motif" rows="2" maxlength="255" placeholder="Ajoutez un commentaire si nécessaire"></textarea>
          </div>

          <div class="form-group">
            <small class="text-muted">
              <span class="text-danger">*</span> Champs obligatoires.
            </small>
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
