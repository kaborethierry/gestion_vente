<!-- Modal : Ajouter un mouvement -->
<div class="modal fade" id="ajouter_mouvement" tabindex="-1" role="dialog" aria-labelledby="ajouterMouvementLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669); color: white;">
                <h5 class="modal-title" id="ajouterMouvementLabel">
                    <i class="fas fa-plus-circle"></i> Nouveau mouvement de stock
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form_ajouter_mouvement">
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label for="id_produit">Produit *</label>
                            <select class="form-control" id="id_produit" name="id_produit" required onchange="updateStockInfo()">
                                <option value="">Sélectionnez un produit</option>
                                <?php
                                require_once __DIR__ . '/../../api/modules/connect_db_pdo.php';
                                $stmt = $bdd->query("SELECT id_produit, nom, stock_actuel, prix_achat FROM danfaniment_produits WHERE statut = 'actif' ORDER BY nom");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . $row['id_produit'] . '" data-stock="' . $row['stock_actuel'] . '" data-prix="' . $row['prix_achat'] . '">';
                                    echo htmlspecialchars($row['nom']) . ' (Stock: ' . number_format($row['stock_actuel'], 0, ',', ' ') . ')';
                                    echo '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row" id="stock_info" style="display: none;">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong>Stock actuel:</strong> <span id="stock_actuel">0</span> unités<br>
                                <strong>Prix d'achat:</strong> <span id="prix_achat">0</span> CFA
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="type_mouvement">Type de mouvement *</label>
                            <select class="form-control" id="type_mouvement" name="type_mouvement" required onchange="updateTypeInfo()">
                                <option value="entree">Entrée (réapprovisionnement)</option>
                                <option value="sortie">Sortie (utilisation/perte)</option>
                                <option value="ajustement">Ajustement (correction)</option>
                                <option value="retour">Retour fournisseur</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="quantite">Quantité *</label>
                            <input type="number" step="1" class="form-control" id="quantite" name="quantite" required oninput="checkStock()">
                            <small class="text-muted" id="quantite_info"></small>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label for="reference">Référence / N° Bon</label>
                            <input type="text" class="form-control" id="reference" name="reference" placeholder="Facture, BL, N° commande...">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="motif">Motif / Justification *</label>
                        <textarea class="form-control" id="motif" name="motif" rows="2" required placeholder="Raison du mouvement..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning" id="alerte_stock" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i> Attention : Cette sortie rendra le stock négatif !
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" id="btn_enregistrer_mouvement">Enregistrer le mouvement</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal : Modifier un mouvement -->
<div class="modal fade" id="modifier_mouvement" tabindex="-1" role="dialog" aria-labelledby="modifierMouvementLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #F59E0B, #DC2626); color: white;">
                <h5 class="modal-title" id="modifierMouvementLabel">
                    <i class="fas fa-edit"></i> Modifier le mouvement
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modif_id_mouvement">
                
                <div class="form-group row">
                    <div class="col-md-6">
                        <label>Produit</label>
                        <input type="text" class="form-control" id="modif_produit_nom" readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Date du mouvement</label>
                        <input type="text" class="form-control" id="modif_created_at" readonly>
                    </div>
                </div>
                
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="modif_type_mouvement">Type de mouvement *</label>
                        <select class="form-control" id="modif_type_mouvement" required>
                            <option value="entree">Entrée (réapprovisionnement)</option>
                            <option value="sortie">Sortie (utilisation/perte)</option>
                            <option value="ajustement">Ajustement (correction)</option>
                            <option value="retour">Retour fournisseur</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="modif_quantite">Quantité *</label>
                        <input type="number" step="1" class="form-control" id="modif_quantite" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="modif_reference">Référence / N° Bon</label>
                    <input type="text" class="form-control" id="modif_reference">
                </div>
                
                <div class="form-group">
                    <label for="modif_motif">Motif / Justification *</label>
                    <textarea class="form-control" id="modif_motif" rows="2" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" id="btn_modifier_mouvement">Enregistrer les modifications</button>
            </div>
        </div>
    </div>
</div>

<script>
function updateStockInfo() {
    var select = document.getElementById('id_produit');
    var option = select.options[select.selectedIndex];
    var stock = option.getAttribute('data-stock');
    var prix = option.getAttribute('data-prix');
    
    if (select.value) {
        document.getElementById('stock_info').style.display = 'block';
        document.getElementById('stock_actuel').innerHTML = parseInt(stock).toLocaleString('fr-FR');
        document.getElementById('prix_achat').innerHTML = parseInt(prix).toLocaleString('fr-FR');
        if (document.getElementById('type_mouvement').value === 'sortie') {
            document.getElementById('quantite').max = stock;
        }
        checkStock();
    } else {
        document.getElementById('stock_info').style.display = 'none';
    }
}

function updateTypeInfo() {
    var type = document.getElementById('type_mouvement').value;
    var quantiteField = document.getElementById('quantite');
    var infoSpan = document.getElementById('quantite_info');
    
    if (type === 'sortie') {
        var select = document.getElementById('id_produit');
        var option = select.options[select.selectedIndex];
        var stock = parseInt(option.getAttribute('data-stock')) || 0;
        quantiteField.max = stock;
        infoSpan.innerHTML = 'Quantité maximale disponible: ' + stock.toLocaleString('fr-FR');
    } else {
        quantiteField.max = '';
        infoSpan.innerHTML = '';
    }
    checkStock();
}

function checkStock() {
    var type = document.getElementById('type_mouvement').value;
    var quantite = parseInt(document.getElementById('quantite').value) || 0;
    var select = document.getElementById('id_produit');
    var option = select.options[select.selectedIndex];
    var stock = parseInt(option.getAttribute('data-stock')) || 0;
    var alerte = document.getElementById('alerte_stock');
    var btn = document.getElementById('btn_enregistrer_mouvement');
    
    if (type === 'sortie' && quantite > stock && stock > 0) {
        alerte.style.display = 'block';
        if (btn) btn.disabled = true;
    } else {
        alerte.style.display = 'none';
        if (btn) btn.disabled = false;
    }
}

// AJAX Submit
$(document).ready(function() {
    $('#btn_enregistrer_mouvement').on('click', function(e) {
        e.preventDefault();
        
        var id_produit = $('#id_produit').val();
        var type_mouvement = $('#type_mouvement').val();
        var quantite = $('#quantite').val();
        var reference = $('#reference').val();
        var motif = $('#motif').val();
        
        if (!id_produit || !type_mouvement || !quantite || !motif) {
            Swal.fire('Erreur', 'Veuillez remplir tous les champs obligatoires', 'error');
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');
        
        $.ajax({
            url: '../api/modules/ajouter_mouvement_stock.php',
            type: 'POST',
            data: {
                id_produit: id_produit,
                type_mouvement: type_mouvement,
                quantite: quantite,
                reference: reference,
                motif: motif
            },
            dataType: 'json',
            success: function(response) {
                $btn.prop('disabled', false).html('Enregistrer le mouvement');
                
                if (response.success) {
                    $('#ajouter_mouvement').modal('hide');
                    $('#form_ajouter_mouvement')[0].reset();
                    $('#stock_info').hide();
                    $('#alerte_stock').hide();
                    
                    if ($.fn.DataTable.isDataTable('#dataTableMouvements')) {
                        $('#dataTableMouvements').DataTable().ajax.reload();
                    }
                    
                    Swal.fire({
                        title: 'Succès !',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#10B981',
                        confirmButtonText: 'OK',
                        timer: 3000
                    });
                } else {
                    Swal.fire('Erreur', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html('Enregistrer le mouvement');
                Swal.fire('Erreur', 'Erreur lors de l\'enregistrement: ' + error, 'error');
            }
        });
    });
    
    $('#btn_modifier_mouvement').on('click', function(e) {
        e.preventDefault();
        
        var id_mouvement = $('#modif_id_mouvement').val();
        var type_mouvement = $('#modif_type_mouvement').val();
        var quantite = $('#modif_quantite').val();
        var reference = $('#modif_reference').val();
        var motif = $('#modif_motif').val();
        
        if (!type_mouvement || !quantite || !motif) {
            Swal.fire('Erreur', 'Veuillez remplir tous les champs obligatoires', 'error');
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Modification...');
        
        $.ajax({
            url: '../api/modules/modifier_mouvement_stock.php',
            type: 'POST',
            data: {
                id_mouvement: id_mouvement,
                type_mouvement: type_mouvement,
                quantite: quantite,
                reference: reference,
                motif: motif
            },
            dataType: 'json',
            success: function(response) {
                $btn.prop('disabled', false).html('Enregistrer les modifications');
                
                if (response.success) {
                    $('#modifier_mouvement').modal('hide');
                    
                    if ($.fn.DataTable.isDataTable('#dataTableMouvements')) {
                        $('#dataTableMouvements').DataTable().ajax.reload();
                    }
                    
                    Swal.fire({
                        title: 'Succès !',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#10B981',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire('Erreur', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html('Enregistrer les modifications');
                Swal.fire('Erreur', 'Erreur lors de la modification: ' + error, 'error');
            }
        });
    });
});
</script>