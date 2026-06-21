<?php
// pages/modals/modal_mesure.php
require_once dirname(__DIR__, 2) . '/api/modules/connect_db_pdo.php';
?>

<!-- Modal : Ajouter une mesure -->
<div class="modal fade" id="ajouter_mesure" tabindex="-1" role="dialog" aria-labelledby="ajouterMesureLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
        <h5 class="modal-title" id="ajouterMesureLabel"><i class="fas fa-ruler-combined"></i> Nouvelle prise de mesures</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <form action="../api/modules/ajouter_mesure.php" method="POST">
        <div class="modal-body">
          <input type="hidden" name="id_client" id="mesure_id_client">

          <div class="row">
            <!-- Colonne 1 -->
            <div class="col-md-4">
              <div class="card mb-3">
                <div class="card-header bg-light">Dos & Épaules & Col</div>
                <div class="card-body">
                  <div class="form-group"><label>Dos (cm)</label><input type="number" step="0.5" class="form-control" name="dos"></div>
                  <div class="form-group"><label>Épaule (cm)</label><input type="number" step="0.5" class="form-control" name="epaule"></div>
                  <div class="form-group"><label>Col (cm)</label><input type="number" step="0.5" class="form-control" name="col"></div>
                </div>
              </div>
              <div class="card mb-3">
                <div class="card-header bg-light">Poitrine & Taille</div>
                <div class="card-body">
                  <div class="form-group"><label>Poitrine (cm)</label><input type="number" step="0.5" class="form-control" name="poitrine"></div>
                  <div class="form-group"><label>Tour de taille (cm)</label><input type="number" step="0.5" class="form-control" name="tour_taille"></div>
                  <div class="form-group"><label>Longueur taille (cm)</label><input type="number" step="0.5" class="form-control" name="long_taille"></div>
                </div>
              </div>
              <div class="card mb-3">
                <div class="card-header bg-light">Pinces</div>
                <div class="card-body">
                  <div class="form-group"><label>Pinces (description)</label><textarea class="form-control" name="pinces" rows="2" placeholder="Ex: 2 pinces dos de 3cm chacune"></textarea></div>
                </div>
              </div>
            </div>

            <!-- Colonne 2 -->
            <div class="col-md-4">
              <div class="card mb-3">
                <div class="card-header bg-light">Manches & Poignet</div>
                <div class="card-body">
                  <div class="form-group"><label>Longueur manche (cm)</label><input type="number" step="0.5" class="form-control" name="long_manche"></div>
                  <div class="form-group"><label>Tour manche (cm)</label><input type="number" step="0.5" class="form-control" name="tour_manche"></div>
                  <div class="form-group"><label>Poignet (cm)</label><input type="number" step="0.5" class="form-control" name="poignet"></div>
                </div>
              </div>
              <div class="card mb-3">
                <div class="card-header bg-light">Longueurs vêtements (Haut/Robes)</div>
                <div class="card-body">
                  <div class="form-group"><label>Longueur camisole (cm)</label><input type="number" step="0.5" class="form-control" name="long_camisole"></div>
                  <div class="form-group"><label>Longueur robe (cm)</label><input type="number" step="0.5" class="form-control" name="long_robe"></div>
                  <div class="form-group"><label>Frappe / Fente (cm)</label><input type="number" step="0.5" class="form-control" name="frappe"></div>
                  <div class="form-group"><label>Longueur chemise (cm)</label><input type="number" step="0.5" class="form-control" name="long_chemise"></div>
                </div>
              </div>
            </div>

            <!-- Colonne 3 -->
            <div class="col-md-4">
              <div class="card mb-3">
                <div class="card-header bg-light">Bas (Pantalons & Jupes)</div>
                <div class="card-body">
                  <div class="form-group"><label>Ceinture (cm)</label><input type="number" step="0.5" class="form-control" name="ceinture"></div>
                  <div class="form-group"><label>Bassin (cm)</label><input type="number" step="0.5" class="form-control" name="bassin"></div>
                  <div class="form-group"><label>Cuisse (cm)</label><input type="number" step="0.5" class="form-control" name="cuisse"></div>
                  <div class="form-group"><label>Genoux (cm)</label><input type="number" step="0.5" class="form-control" name="genoux"></div>
                  <div class="form-group"><label>Longueur jupe (cm)</label><input type="number" step="0.5" class="form-control" name="long_jupe"></div>
                  <div class="form-group"><label>Longueur pantalon (cm)</label><input type="number" step="0.5" class="form-control" name="long_pantalon"></div>
                  <div class="form-group"><label>Bas du pantalon (cm)</label><input type="number" step="0.5" class="form-control" name="bas"></div>
                </div>
              </div>
              <div class="card mb-3">
                <div class="card-header bg-light">Mensurations générales</div>
                <div class="card-body">
                  <div class="form-group"><label>Hauteur totale (cm)</label><input type="number" step="0.5" class="form-control" name="hauteur_totale"></div>
                  <div class="form-group"><label>Poids (kg)</label><input type="number" step="0.5" class="form-control" name="poids"></div>
                  <div class="form-group"><label>Pointure chaussure</label><input type="number" step="0.5" class="form-control" name="pointure_chaussure"></div>
                  <div class="form-group"><label>Taille ceinture</label><input type="text" class="form-control" name="taille_ceinture"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group"><label>Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Modifier une mesure -->
<div class="modal fade" id="modifier_mesure" tabindex="-1" role="dialog" aria-labelledby="modifierMesureLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669); color: white;">
        <h5 class="modal-title" id="modifierMesureLabel"><i class="fas fa-edit"></i> Modification des mesures</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <form action="../api/modules/modifier_mesure.php" method="POST">
        <div class="modal-body">
          <input type="hidden" id="modif_id_mesure" name="id_mesure">

          <div class="row">
            <!-- Colonne 1 -->
            <div class="col-md-4">
              <div class="card mb-3">
                <div class="card-header bg-light">Dos & Épaules & Col</div>
                <div class="card-body">
                  <div class="form-group"><label>Dos (cm)</label><input type="number" step="0.5" class="form-control" id="modif_dos" name="dos"></div>
                  <div class="form-group"><label>Épaule (cm)</label><input type="number" step="0.5" class="form-control" id="modif_epaule" name="epaule"></div>
                  <div class="form-group"><label>Col (cm)</label><input type="number" step="0.5" class="form-control" id="modif_col" name="col"></div>
                </div>
              </div>
              <div class="card mb-3">
                <div class="card-header bg-light">Poitrine & Taille</div>
                <div class="card-body">
                  <div class="form-group"><label>Poitrine (cm)</label><input type="number" step="0.5" class="form-control" id="modif_poitrine" name="poitrine"></div>
                  <div class="form-group"><label>Tour de taille (cm)</label><input type="number" step="0.5" class="form-control" id="modif_tour_taille" name="tour_taille"></div>
                  <div class="form-group"><label>Longueur taille (cm)</label><input type="number" step="0.5" class="form-control" id="modif_long_taille" name="long_taille"></div>
                </div>
              </div>
              <div class="card mb-3">
                <div class="card-header bg-light">Pinces</div>
                <div class="card-body">
                  <div class="form-group"><label>Pinces (description)</label><textarea class="form-control" id="modif_pinces" name="pinces" rows="2"></textarea></div>
                </div>
              </div>
            </div>

            <!-- Colonne 2 -->
            <div class="col-md-4">
              <div class="card mb-3">
                <div class="card-header bg-light">Manches & Poignet</div>
                <div class="card-body">
                  <div class="form-group"><label>Longueur manche (cm)</label><input type="number" step="0.5" class="form-control" id="modif_long_manche" name="long_manche"></div>
                  <div class="form-group"><label>Tour manche (cm)</label><input type="number" step="0.5" class="form-control" id="modif_tour_manche" name="tour_manche"></div>
                  <div class="form-group"><label>Poignet (cm)</label><input type="number" step="0.5" class="form-control" id="modif_poignet" name="poignet"></div>
                </div>
              </div>
              <div class="card mb-3">
                <div class="card-header bg-light">Longueurs vêtements (Haut/Robes)</div>
                <div class="card-body">
                  <div class="form-group"><label>Longueur camisole (cm)</label><input type="number" step="0.5" class="form-control" id="modif_long_camisole" name="long_camisole"></div>
                  <div class="form-group"><label>Longueur robe (cm)</label><input type="number" step="0.5" class="form-control" id="modif_long_robe" name="long_robe"></div>
                  <div class="form-group"><label>Frappe / Fente (cm)</label><input type="number" step="0.5" class="form-control" id="modif_frappe" name="frappe"></div>
                  <div class="form-group"><label>Longueur chemise (cm)</label><input type="number" step="0.5" class="form-control" id="modif_long_chemise" name="long_chemise"></div>
                </div>
              </div>
            </div>

            <!-- Colonne 3 -->
            <div class="col-md-4">
              <div class="card mb-3">
                <div class="card-header bg-light">Bas (Pantalons & Jupes)</div>
                <div class="card-body">
                  <div class="form-group"><label>Ceinture (cm)</label><input type="number" step="0.5" class="form-control" id="modif_ceinture" name="ceinture"></div>
                  <div class="form-group"><label>Bassin (cm)</label><input type="number" step="0.5" class="form-control" id="modif_bassin" name="bassin"></div>
                  <div class="form-group"><label>Cuisse (cm)</label><input type="number" step="0.5" class="form-control" id="modif_cuisse" name="cuisse"></div>
                  <div class="form-group"><label>Genoux (cm)</label><input type="number" step="0.5" class="form-control" id="modif_genoux" name="genoux"></div>
                  <div class="form-group"><label>Longueur jupe (cm)</label><input type="number" step="0.5" class="form-control" id="modif_long_jupe" name="long_jupe"></div>
                  <div class="form-group"><label>Longueur pantalon (cm)</label><input type="number" step="0.5" class="form-control" id="modif_long_pantalon" name="long_pantalon"></div>
                  <div class="form-group"><label>Bas du pantalon (cm)</label><input type="number" step="0.5" class="form-control" id="modif_bas" name="bas"></div>
                </div>
              </div>
              <div class="card mb-3">
                <div class="card-header bg-light">Mensurations générales</div>
                <div class="card-body">
                  <div class="form-group"><label>Hauteur totale (cm)</label><input type="number" step="0.5" class="form-control" id="modif_hauteur_totale" name="hauteur_totale"></div>
                  <div class="form-group"><label>Poids (kg)</label><input type="number" step="0.5" class="form-control" id="modif_poids" name="poids"></div>
                  <div class="form-group"><label>Pointure chaussure</label><input type="number" step="0.5" class="form-control" id="modif_pointure_chaussure" name="pointure_chaussure"></div>
                  <div class="form-group"><label>Taille ceinture</label><input type="text" class="form-control" id="modif_taille_ceinture" name="taille_ceinture"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group"><label>Notes</label><textarea class="form-control" id="modif_notes" name="notes" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Modifier</button>
        </div>
      </form>
    </div>
  </div>
</div>