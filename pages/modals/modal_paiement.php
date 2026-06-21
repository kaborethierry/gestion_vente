<!-- Modal : Ajouter un paiement -->
<div class="modal fade" id="ajouter_paiement" tabindex="-1" role="dialog" aria-labelledby="ajouterPaiementLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669); color: white;">
                <h5 class="modal-title" id="ajouterPaiementLabel">
                    <i class="fas fa-plus-circle"></i> Nouveau paiement
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="col-md-12">
                        <label for="id_commande">Commande *</label>
                        <select class="form-control" id="id_commande" name="id_commande" required onchange="updateSoldeInfo()">
                            <option value="">Chargement des commandes...</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group row" id="solde_info" style="display: none;">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <strong>Montant total:</strong> <span id="montant_total">0</span> CFA<br>
                            <strong>Solde restant:</strong> <span id="solde_restant">0</span> CFA
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-success" id="apres_paiement_info" style="display: none;">
                            <strong>Après paiement:</strong><br>
                            Nouveau solde: <span id="nouveau_solde">0</span> CFA
                        </div>
                    </div>
                </div>
                
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="montant">Montant (FCFA) *</label>
                        <input type="number" step="1" class="form-control" id="montant" name="montant" required oninput="updateNouveauSolde()">
                    </div>
                    <div class="col-md-6">
                        <label for="type_paiement">Type de paiement *</label>
                        <select class="form-control" id="type_paiement" name="type_paiement" required>
                            <option value="avance">Avance</option>
                            <option value="acompte_supplementaire">Acompte supplémentaire</option>
                            <option value="solde">Solde final</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="mode_paiement">Mode de paiement *</label>
                        <select class="form-control" id="mode_paiement" name="mode_paiement" required>
                            <option value="especes">Espèces</option>
                            <option value="carte">Carte bancaire</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="virement">Virement</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="reference_transaction">Référence transaction</label>
                        <input type="text" class="form-control" id="reference_transaction" name="reference_transaction" placeholder="Optionnel">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="remarques">Remarques</label>
                    <textarea class="form-control" id="remarques" name="remarques" rows="2" placeholder="Notes optionnelles"></textarea>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-print"></i> Un reçu sera automatiquement affiché après l'enregistrement.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" id="btn_enregistrer_paiement">Enregistrer le paiement</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal : Modifier un paiement -->
<div class="modal fade" id="modifier_paiement" tabindex="-1" role="dialog" aria-labelledby="modifierPaiementLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #F59E0B, #DC2626); color: white;">
                <h5 class="modal-title" id="modifierPaiementLabel">
                    <i class="fas fa-edit"></i> Modifier le paiement
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_modifier_paiement" action="../api/modules/modifier_paiement.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="modif_id_paiement" name="id_paiement">
                    
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label>Commande</label>
                            <input type="text" class="form-control" id="modif_numero_commande" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Client</label>
                            <input type="text" class="form-control" id="modif_client_nom" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="modif_montant">Montant (FCFA) *</label>
                            <input type="number" step="1" class="form-control" id="modif_montant" name="montant" required>
                        </div>
                        <div class="col-md-6">
                            <label for="modif_type_paiement">Type de paiement *</label>
                            <select class="form-control" id="modif_type_paiement" name="type_paiement" required>
                                <option value="avance">Avance</option>
                                <option value="acompte_supplementaire">Acompte supplémentaire</option>
                                <option value="solde">Solde final</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="modif_mode_paiement">Mode de paiement *</label>
                            <select class="form-control" id="modif_mode_paiement" name="mode_paiement" required>
                                <option value="especes">Espèces</option>
                                <option value="carte">Carte bancaire</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="virement">Virement</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="modif_reference_transaction">Référence transaction</label>
                            <input type="text" class="form-control" id="modif_reference_transaction" name="reference_transaction">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="modif_remarques">Remarques</label>
                        <textarea class="form-control" id="modif_remarques" name="remarques" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal : Ticket / Reçu -->
<div class="modal fade" id="recu_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-receipt"></i> Reçu de paiement
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="recu_content" style="font-family: monospace;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="printRecuContent()">Imprimer</button>
            </div>
        </div>
    </div>
</div>

<script>
// Charger les commandes via AJAX au chargement du modal
function chargerCommandes() {
    $.ajax({
        url: '../api/modules/paiement_data.php',
        type: 'GET',
        data: { action: 'get_commandes_select' },
        dataType: 'json',
        success: function(response) {
            var select = $('#id_commande');
            select.empty();
            
            if (response.success && response.commandes && response.commandes.length > 0) {
                select.append('<option value="">Sélectionnez une commande</option>');
                $.each(response.commandes, function(i, cmd) {
                    var solde = parseFloat(cmd.solde_restant);
                    if (solde > 0) {
                        select.append('<option value="' + cmd.id_commande + '" data-solde="' + cmd.solde_restant + '" data-total="' + cmd.montant_total + '">' +
                            cmd.numero_commande + ' - ' + cmd.client_nom + ' (Solde: ' + new Intl.NumberFormat('fr-FR').format(solde) + ' CFA)' +
                            '</option>');
                    }
                });
            } else {
                select.append('<option value="">Aucune commande avec solde restant</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur chargement commandes:', error);
            var select = $('#id_commande');
            select.empty();
            select.append('<option value="">Erreur de chargement des commandes</option>');
        }
    });
}

function updateSoldeInfo() {
    var select = document.getElementById('id_commande');
    var option = select.options[select.selectedIndex];
    var solde = parseFloat(option.getAttribute('data-solde')) || 0;
    var total = parseFloat(option.getAttribute('data-total')) || 0;
    
    if (select.value && solde > 0) {
        document.getElementById('solde_info').style.display = 'block';
        document.getElementById('montant_total').innerHTML = total.toLocaleString('fr-FR');
        document.getElementById('solde_restant').innerHTML = solde.toLocaleString('fr-FR');
        document.getElementById('montant').max = solde;
        document.getElementById('montant').value = solde;
        updateNouveauSolde();
    } else {
        document.getElementById('solde_info').style.display = 'none';
        document.getElementById('apres_paiement_info').style.display = 'none';
    }
}

function updateNouveauSolde() {
    var select = document.getElementById('id_commande');
    var option = select.options[select.selectedIndex];
    var solde = parseFloat(option.getAttribute('data-solde')) || 0;
    var montant = parseFloat(document.getElementById('montant').value) || 0;
    var nouveauSolde = solde - montant;
    
    document.getElementById('apres_paiement_info').style.display = 'block';
    document.getElementById('nouveau_solde').innerHTML = nouveauSolde.toLocaleString('fr-FR');
    
    if (nouveauSolde < 0) {
        document.getElementById('apres_paiement_info').className = 'alert alert-danger';
        document.getElementById('nouveau_solde').innerHTML = 'Montant trop élevé !';
    } else if (nouveauSolde == 0) {
        document.getElementById('apres_paiement_info').className = 'alert alert-success';
        document.getElementById('type_paiement').value = 'solde';
    } else {
        document.getElementById('apres_paiement_info').className = 'alert alert-info';
    }
}

function printRecuContent() {
    var content = document.getElementById('recu_content').innerHTML;
    var printWindow = window.open('', '_blank', 'width=500,height=600');
    printWindow.document.write('<html><head><title>Reçu de paiement</title>');
    printWindow.document.write('<style>body { font-family: monospace; margin: 20px; } .text-center { text-align: center; } .text-right { text-align: right; } table { width: 100%; } hr { border: none; border-top: 1px dashed #000; }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// AJAX SUBMIT - Gestion de l'enregistrement du paiement
$(document).ready(function() {
    chargerCommandes();
    
    $('#btn_enregistrer_paiement').on('click', function(e) {
        e.preventDefault();
        
        // Récupérer les valeurs
        var id_commande = $('#id_commande').val();
        var montant = $('#montant').val();
        var type_paiement = $('#type_paiement').val();
        var mode_paiement = $('#mode_paiement').val();
        var reference_transaction = $('#reference_transaction').val();
        var remarques = $('#remarques').val();
        
        // Validation
        if (!id_commande) {
            Swal.fire('Erreur', 'Veuillez sélectionner une commande', 'error');
            return;
        }
        
        if (!montant || montant <= 0) {
            Swal.fire('Erreur', 'Veuillez saisir un montant valide', 'error');
            return;
        }
        
        // Désactiver le bouton pendant l'envoi
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');
        
        // Envoi AJAX
        $.ajax({
            url: '../api/modules/ajouter_paiement.php',
            type: 'POST',
            data: {
                id_commande: id_commande,
                montant: montant,
                type_paiement: type_paiement,
                mode_paiement: mode_paiement,
                reference_transaction: reference_transaction,
                remarques: remarques
            },
            dataType: 'json',
            success: function(response) {
                $btn.prop('disabled', false).html('Enregistrer le paiement');
                
                if (response.success) {
                    // Fermer le modal
                    $('#ajouter_paiement').modal('hide');
                    
                    // Afficher le reçu
                    $('#recu_content').html(response.recu_html);
                    $('#recu_modal').modal('show');
                    
                    // Réinitialiser le formulaire
                    $('#id_commande').val('');
                    $('#montant').val('');
                    $('#type_paiement').val('avance');
                    $('#mode_paiement').val('especes');
                    $('#reference_transaction').val('');
                    $('#remarques').val('');
                    $('#solde_info').hide();
                    $('#apres_paiement_info').hide();
                    
                    // Recharger les DataTables
                    if ($.fn.DataTable.isDataTable('#dataTablePaiements')) {
                        $('#dataTablePaiements').DataTable().ajax.reload();
                    }
                    if ($.fn.DataTable.isDataTable('#dataTableCommandes')) {
                        $('#dataTableCommandes').DataTable().ajax.reload();
                    }
                    
                    // Recharger les commandes du select
                    chargerCommandes();
                    
                    Swal.fire({
                        title: 'Succès !',
                        text: 'Le paiement a été enregistré avec succès.',
                        icon: 'success',
                        confirmButtonColor: '#10B981',
                        timer: 2000
                    });
                } else {
                    Swal.fire('Erreur', response.message || 'Une erreur est survenue', 'error');
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html('Enregistrer le paiement');
                console.error('Erreur AJAX:', error);
                Swal.fire('Erreur', 'Erreur lors de l\'enregistrement du paiement', 'error');
            }
        });
    });
});
</script>