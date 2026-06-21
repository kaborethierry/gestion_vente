<?php
// Fichier : pages/modals/modal_vehicule.php

// Connexion PDO
require_once dirname(__DIR__, 2) . '/api/modules/connect_db_pdo.php';

$listeClients = [];

try {
  // Charger les clients actifs non supprimés
  $stmt = $bdd->query("
    SELECT id_client, type_client, nom, prenom, raison_sociale
    FROM clients
    WHERE statut = 'Actif' AND supprimer = 'Non'
    ORDER BY type_client ASC, nom ASC, prenom ASC, raison_sociale ASC
  ");
  $listeClients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  echo '<div class="alert alert-warning">Erreur chargement clients : ' . htmlspecialchars($e->getMessage()) . '</div>';
  $listeClients = [];
}
?>

<!-- Modal : Ajouter un véhicule -->
<div class="modal fade" id="ajouter_vehicule" tabindex="-1" role="dialog" aria-labelledby="ajouterVehiculeLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ajouterVehiculeLabel">Ajout d'un nouveau véhicule</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/ajouter_vehicule.php" method="POST" novalidate>
        <div class="modal-body">

          <!-- Propriétaire (client) + Immatriculation -->
          <div class="form-group row">
            <div class="col-md-6">
              <label for="id_client_vehicule_add">Client (ID — Nom Prénom) <span class="text-danger">*</span></label>
              <select class="form-control" id="id_client_vehicule_add" name="id_client" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listeClients as $cli): ?>
                  <?php
                    $label = trim(($cli['nom'] ?? '') . ' ' . ($cli['prenom'] ?? ''));
                    if ($cli['type_client'] === 'Entreprise' && !empty($cli['raison_sociale'])) {
                      $label = $cli['raison_sociale'];
                    }
                    $text = $cli['id_client'] . ' — ' . $label;
                  ?>
                  <option value="<?= htmlspecialchars($cli['id_client']) ?>">
                    <?= htmlspecialchars($text) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted">Sélectionnez le propriétaire du véhicule.</small>
            </div>
            <div class="col-md-6">
              <label for="immatriculation_add">Immatriculation <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="immatriculation_add" name="immatriculation" placeholder="Ex: 1234-AB-01" maxlength="20" required>
            </div>
          </div>

          <!-- Identification véhicule -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="marque_add">Marque</label>
              <input type="text" class="form-control" id="marque_add" name="marque" maxlength="50" placeholder="Ex: Toyota">
            </div>
            <div class="col-md-4">
              <label for="modele_add">Modèle</label>
              <input type="text" class="form-control" id="modele_add" name="modele" maxlength="50" placeholder="Ex: Corolla">
            </div>
            <div class="col-md-4">
              <label for="categorie_add">Catégorie</label>
              <input type="text" class="form-control" id="categorie_add" name="categorie" maxlength="30" placeholder="Ex: SUV, Berline">
            </div>
          </div>

          <!-- Motorisation / Caractéristiques -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="type_moteur_add">Type moteur</label>
              <input type="text" class="form-control" id="type_moteur_add" name="type_moteur" maxlength="30" placeholder="Essence, Diesel, Hybride...">
            </div>
            <div class="col-md-4">
              <label for="capacite_moteur_add">Cylindrée (L)</label>
              <input type="number" step="0.01" min="0" class="form-control" id="capacite_moteur_add" name="capacite_moteur" placeholder="Ex: 1.60">
            </div>
            <div class="col-md-4">
              <label for="puissance_cv_add">Puissance (CV)</label>
              <input type="number" min="0" class="form-control" id="puissance_cv_add" name="puissance_cv" placeholder="Ex: 110">
            </div>
          </div>

          <!-- Année / Kilométrage / Couleur -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="annee_add">Année</label>
              <input type="number" min="1900" max="2100" class="form-control" id="annee_add" name="annee" placeholder="Ex: 2020">
            </div>
            <div class="col-md-4">
              <label for="kilometrage_add">Kilométrage</label>
              <input type="number" min="0" class="form-control" id="kilometrage_add" name="kilometrage" placeholder="Ex: 85000">
            </div>
            <div class="col-md-4">
              <label for="couleur_add">Couleur</label>
              <input type="text" class="form-control" id="couleur_add" name="couleur" maxlength="30" placeholder="Ex: Blanc">
            </div>
          </div>

          <!-- VIN / Transmission / Portes -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="vin_add">VIN</label>
              <input type="text" class="form-control" id="vin_add" name="vin" maxlength="30" placeholder="N° de série">
            </div>
            <div class="col-md-4">
              <label for="transmission_add">Transmission <span class="text-danger">*</span></label>
              <select class="form-control" id="transmission_add" name="transmission" required>
                <option value="Manuelle" selected>Manuelle</option>
                <option value="Automatique">Automatique</option>
                <option value="Séquentielle">Séquentielle</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="nbr_portes_add">Nombre de portes</label>
              <input type="number" min="2" max="6" class="form-control" id="nbr_portes_add" name="nbr_portes" placeholder="Ex: 4">
            </div>
          </div>

          <!-- Consommations / Émissions -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="conso_urbaine_add">Conso urbaine (L/100km)</label>
              <input type="number" step="0.01" min="0" class="form-control" id="conso_urbaine_add" name="conso_urbaine" placeholder="Ex: 7.50">
            </div>
            <div class="col-md-4">
              <label for="conso_extra_urbaine_add">Conso extra-urbaine (L/100km)</label>
              <input type="number" step="0.01" min="0" class="form-control" id="conso_extra_urbaine_add" name="conso_extra_urbaine" placeholder="Ex: 5.20">
            </div>
            <div class="col-md-4">
              <label for="emission_co2_add">Émissions CO₂ (g/km)</label>
              <input type="number" min="0" class="form-control" id="emission_co2_add" name="emission_co2" placeholder="Ex: 120">
            </div>
          </div>

          <!-- Assurance -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="type_assurance_add">Type d'assurance</label>
              <input type="text" class="form-control" id="type_assurance_add" name="type_assurance" maxlength="50" placeholder="Ex: Tous risques">
            </div>
            <div class="col-md-4">
              <label for="numero_assurance_add">N° d'assurance</label>
              <input type="text" class="form-control" id="numero_assurance_add" name="numero_assurance" maxlength="50" placeholder="Référence contrat">
            </div>
            <div class="col-md-4">
              <label for="date_expiration_assurance_add">Expiration assurance</label>
              <input type="date" class="form-control" id="date_expiration_assurance_add" name="date_expiration_assurance">
            </div>
          </div>

          <!-- Dates clés / Entretiens / Garantie -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="date_immatriculation_add">Date d'immatriculation</label>
              <input type="date" class="form-control" id="date_immatriculation_add" name="date_immatriculation">
            </div>
            <div class="col-md-4">
              <label for="date_derniere_entretien_add">Date dernier entretien</label>
              <input type="datetime-local" class="form-control" id="date_derniere_entretien_add" name="date_derniere_entretien">
            </div>
            <div class="col-md-4">
              <label for="kilometrage_derniere_entretien_add">Km dernier entretien</label>
              <input type="number" min="0" class="form-control" id="kilometrage_derniere_entretien_add" name="kilometrage_derniere_entretien" placeholder="Ex: 80000">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="date_prochain_entretien_add">Date prochain entretien</label>
              <input type="datetime-local" class="form-control" id="date_prochain_entretien_add" name="date_prochain_entretien">
            </div>
            <div class="col-md-6">
              <label for="garantie_fin_add">Fin de garantie</label>
              <input type="date" class="form-control" id="garantie_fin_add" name="garantie_fin">
            </div>
          </div>

          <!-- Apparence / Statut -->
          <div class="form-group row">
            <div class="col-md-6">
              <label for="couleur_interieur_add">Couleur intérieure</label>
              <input type="text" class="form-control" id="couleur_interieur_add" name="couleur_interieur" maxlength="50" placeholder="Ex: Noir">
            </div>
            <div class="col-md-6">
              <label for="statut_vehicule_add">Statut <span class="text-danger">*</span></label>
              <select class="form-control" id="statut_vehicule_add" name="statut_vehicule" required>
                <option value="En service" selected>En service</option>
                <option value="En réparation">En réparation</option>
                <option value="Hors service">Hors service</option>
              </select>
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

<!-- Modal : Modifier un véhicule -->
<div class="modal fade" id="modifier_vehicule" tabindex="-1" role="dialog" aria-labelledby="modifierVehiculeLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modifierVehiculeLabel">Modification d'un véhicule</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="../api/modules/modifier_vehicule.php" method="POST" novalidate>
        <div class="modal-body">

          <input type="hidden" id="id_vehicule_modif" name="id_vehicule" value="">

          <!-- Propriétaire (client) + Immatriculation -->
          <div class="form-group row">
            <div class="col-md-6">
              <label for="id_client_vehicule_modif">Client (ID — Nom Prénom) <span class="text-danger">*</span></label>
              <select class="form-control" id="id_client_vehicule_modif" name="id_client" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($listeClients as $cli): ?>
                  <?php
                    $label = trim(($cli['nom'] ?? '') . ' ' . ($cli['prenom'] ?? ''));
                    if ($cli['type_client'] === 'Entreprise' && !empty($cli['raison_sociale'])) {
                      $label = $cli['raison_sociale'];
                    }
                    $text = $cli['id_client'] . ' — ' . $label;
                  ?>
                  <option value="<?= htmlspecialchars($cli['id_client']) ?>">
                    <?= htmlspecialchars($text) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="immatriculation_modif">Immatriculation <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="immatriculation_modif" name="immatriculation" maxlength="20" required>
            </div>
          </div>

          <!-- Identification véhicule -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="marque_modif">Marque</label>
              <input type="text" class="form-control" id="marque_modif" name="marque" maxlength="50">
            </div>
            <div class="col-md-4">
              <label for="modele_modif">Modèle</label>
              <input type="text" class="form-control" id="modele_modif" name="modele" maxlength="50">
            </div>
            <div class="col-md-4">
              <label for="categorie_modif">Catégorie</label>
              <input type="text" class="form-control" id="categorie_modif" name="categorie" maxlength="30">
            </div>
          </div>

          <!-- Motorisation / Caractéristiques -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="type_moteur_modif">Type moteur</label>
              <input type="text" class="form-control" id="type_moteur_modif" name="type_moteur" maxlength="30">
            </div>
            <div class="col-md-4">
              <label for="capacite_moteur_modif">Cylindrée (L)</label>
              <input type="number" step="0.01" min="0" class="form-control" id="capacite_moteur_modif" name="capacite_moteur">
            </div>
            <div class="col-md-4">
              <label for="puissance_cv_modif">Puissance (CV)</label>
              <input type="number" min="0" class="form-control" id="puissance_cv_modif" name="puissance_cv">
            </div>
          </div>

          <!-- Année / Kilométrage / Couleur -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="annee_modif">Année</label>
              <input type="number" min="1900" max="2100" class="form-control" id="annee_modif" name="annee">
            </div>
            <div class="col-md-4">
              <label for="kilometrage_modif">Kilométrage</label>
              <input type="number" min="0" class="form-control" id="kilometrage_modif" name="kilometrage">
            </div>
            <div class="col-md-4">
              <label for="couleur_modif">Couleur</label>
              <input type="text" class="form-control" id="couleur_modif" name="couleur" maxlength="30">
            </div>
          </div>

          <!-- VIN / Transmission / Portes -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="vin_modif">VIN</label>
              <input type="text" class="form-control" id="vin_modif" name="vin" maxlength="30">
            </div>
            <div class="col-md-4">
              <label for="transmission_modif">Transmission <span class="text-danger">*</span></label>
              <select class="form-control" id="transmission_modif" name="transmission" required>
                <option value="Manuelle">Manuelle</option>
                <option value="Automatique">Automatique</option>
                <option value="Séquentielle">Séquentielle</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="nbr_portes_modif">Nombre de portes</label>
              <input type="number" min="2" max="6" class="form-control" id="nbr_portes_modif" name="nbr_portes">
            </div>
          </div>

          <!-- Consommations / Émissions -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="conso_urbaine_modif">Conso urbaine (L/100km)</label>
              <input type="number" step="0.01" min="0" class="form-control" id="conso_urbaine_modif" name="conso_urbaine">
            </div>
            <div class="col-md-4">
              <label for="conso_extra_urbaine_modif">Conso extra-urbaine (L/100km)</label>
              <input type="number" step="0.01" min="0" class="form-control" id="conso_extra_urbaine_modif" name="conso_extra_urbaine">
            </div>
            <div class="col-md-4">
              <label for="emission_co2_modif">Émissions CO₂ (g/km)</label>
              <input type="number" min="0" class="form-control" id="emission_co2_modif" name="emission_co2">
            </div>
          </div>

          <!-- Assurance -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="type_assurance_modif">Type d'assurance</label>
              <input type="text" class="form-control" id="type_assurance_modif" name="type_assurance" maxlength="50">
            </div>
            <div class="col-md-4">
              <label for="numero_assurance_modif">N° d'assurance</label>
              <input type="text" class="form-control" id="numero_assurance_modif" name="numero_assurance" maxlength="50">
            </div>
            <div class="col-md-4">
              <label for="date_expiration_assurance_modif">Expiration assurance</label>
              <input type="date" class="form-control" id="date_expiration_assurance_modif" name="date_expiration_assurance">
            </div>
          </div>

          <!-- Dates clés / Entretiens / Garantie -->
          <div class="form-group row">
            <div class="col-md-4">
              <label for="date_immatriculation_modif">Date d'immatriculation</label>
              <input type="date" class="form-control" id="date_immatriculation_modif" name="date_immatriculation">
            </div>
            <div class="col-md-4">
              <label for="date_derniere_entretien_modif">Date dernier entretien</label>
              <input type="datetime-local" class="form-control" id="date_derniere_entretien_modif" name="date_derniere_entretien">
            </div>
            <div class="col-md-4">
              <label for="kilometrage_derniere_entretien_modif">Km dernier entretien</label>
              <input type="number" min="0" class="form-control" id="kilometrage_derniere_entretien_modif" name="kilometrage_derniere_entretien">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-6">
              <label for="date_prochain_entretien_modif">Date prochain entretien</label>
              <input type="datetime-local" class="form-control" id="date_prochain_entretien_modif" name="date_prochain_entretien">
            </div>
            <div class="col-md-6">
              <label for="garantie_fin_modif">Fin de garantie</label>
              <input type="date" class="form-control" id="garantie_fin_modif" name="garantie_fin">
            </div>
          </div>

          <!-- Apparence / Statut -->
          <div class="form-group row">
            <div class="col-md-6">
              <label for="couleur_interieur_modif">Couleur intérieure</label>
              <input type="text" class="form-control" id="couleur_interieur_modif" name="couleur_interieur" maxlength="50">
            </div>
            <div class="col-md-6">
              <label for="statut_vehicule_modif">Statut <span class="text-danger">*</span></label>
              <select class="form-control" id="statut_vehicule_modif" name="statut_vehicule" required>
                <option value="En service">En service</option>
                <option value="En réparation">En réparation</option>
                <option value="Hors service">Hors service</option>
              </select>
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

d<!-- Modal : Détails du véhicule (lecture seule) -->
<iv class="modal fade" id="details_vehicule" tabindex="-1" role="dialog" aria-labelledby="detailsVehiculeLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailsVehiculeLabel">Détails du véhicule</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <div class="row">
          <div class="col-md-6">
            <h6 class="text-primary">Propriétaire</h6>
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>ID véhicule:</strong> <span id="det_id_vehicule"></span></li>
              <li class="list-group-item"><strong>ID client:</strong> <span id="det_id_client"></span></li>
              <li class="list-group-item"><strong>Client (Nom/Prénom/Raison sociale):</strong> <span id="det_client_label"></span></li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6 class="text-primary">Identification</h6>
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>Immatriculation:</strong> <span id="det_immatriculation"></span></li>
              <li class="list-group-item"><strong>VIN:</strong> <span id="det_vin"></span></li>
              <li class="list-group-item"><strong>Date d'ajout:</strong> <span id="det_date_ajout"></span></li>
              <li class="list-group-item"><strong>Date d'immatriculation:</strong> <span id="det_date_immatriculation"></span></li>
            </ul>
          </div>
        </div>

        <h6 class="text-primary">Caractéristiques</h6>
        <div class="row">
          <div class="col-md-6">
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>Marque:</strong> <span id="det_marque"></span></li>
              <li class="list-group-item"><strong>Modèle:</strong> <span id="det_modele"></span></li>
              <li class="list-group-item"><strong>Catégorie:</strong> <span id="det_categorie"></span></li>
              <li class="list-group-item"><strong>Année:</strong> <span id="det_annee"></span></li>
              <li class="list-group-item"><strong>Couleur:</strong> <span id="det_couleur"></span></li>
              <li class="list-group-item"><strong>Couleur intérieure:</strong> <span id="det_couleur_interieur"></span></li>
              <li class="list-group-item"><strong>Nombre de portes:</strong> <span id="det_nbr_portes"></span></li>
              <li class="list-group-item"><strong>Transmission:</strong> <span id="det_transmission"></span></li>
              <li class="list-group-item"><strong>Statut:</strong> <span id="det_statut_vehicule"></span></li>
            </ul>
          </div>
          <div class="col-md-6">
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>Type moteur:</strong> <span id="det_type_moteur"></span></li>
              <li class="list-group-item"><strong>Cylindrée (L):</strong> <span id="det_capacite_moteur"></span></li>
              <li class="list-group-item"><strong>Puissance (CV):</strong> <span id="det_puissance_cv"></span></li>
              <li class="list-group-item"><strong>Kilométrage:</strong> <span id="det_kilometrage"></span></li>
              <li class="list-group-item"><strong>Conso urbaine (L/100km):</strong> <span id="det_conso_urbaine"></span></li>
              <li class="list-group-item"><strong>Conso extra-urbaine (L/100km):</strong> <span id="det_conso_extra_urbaine"></span></li>
              <li class="list-group-item"><strong>Émissions CO₂ (g/km):</strong> <span id="det_emission_co2"></span></li>
            </ul>
          </div>
        </div>

        <h6 class="text-primary">Entretiens & Garantie</h6>
        <div class="row">
          <div class="col-md-6">
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>Date dernier entretien:</strong> <span id="det_date_derniere_entretien"></span></li>
              <li class="list-group-item"><strong>Kilométrage dernier entretien:</strong> <span id="det_kilometrage_derniere_entretien"></span></li>
              <li class="list-group-item"><strong>Date prochain entretien:</strong> <span id="det_date_prochain_entretien"></span></li>
              <li class="list-group-item"><strong>Fin de garantie:</strong> <span id="det_garantie_fin"></span></li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6 class="text-primary">Assurance</h6>
            <ul class="list-group mb-3">
              <li class="list-group-item"><strong>Type d'assurance:</strong> <span id="det_type_assurance"></span></li>
              <li class="list-group-item"><strong>N° d'assurance:</strong> <span id="det_numero_assurance"></span></li>
              <li class="list-group-item"><strong>Date d'expiration:</strong> <span id="det_date_expiration_assurance"></span></li>
            </ul>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>
