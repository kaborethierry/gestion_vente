// pages/DataTables/data_table_facture.js
$(document).ready(function () {
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../api/modules/facture_data.php',
            type: 'GET',
            data: function(d) {
                d.filtre_statut = $('#filtre_statut').val();
                d.filtre_date_debut = $('#filtre_date_debut').val();
                d.filtre_date_fin = $('#filtre_date_fin').val();
                d.filtre_client = $('#filtre_client').val();
            },
            error: function (xhr, status, error) {
                console.error('DataTables AJAX Error:', status, error);
            }
        },
        dom: 'Blfrtip',
        buttons: [
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL', title: 'Liste des factures' },
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'print'
        ],
        columns: [
            { data: 'numero_facture' },
            { data: 'client_nom' },
            { data: 'date_facture' },
            { data: 'date_echeance' },
            { data: 'total_ttc_formate' },
            { 
                data: 'statut',
                render: function(data) {
                    const classes = {
                        'brouillon': 'facture-brouillon',
                        'envoyee': 'facture-envoyee',
                        'payee': 'facture-payee',
                        'annulee': 'facture-annulee'
                    };
                    const labels = {
                        'brouillon': 'Brouillon',
                        'envoyee': 'Envoyée',
                        'payee': 'Payée',
                        'annulee': 'Annulée'
                    };
                    return '<span class="facture-badge ' + (classes[data] || '') + '">' + (labels[data] || data) + '</span>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data) {
                    return '<button class="btn btn-info btn-sm view-btn" data-id="' + data.id_facture + '" title="Voir"><i class="fas fa-eye"></i></button>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data) {
                    return '<button class="btn btn-secondary btn-sm print-btn" data-id="' + data.id_facture + '" title="Imprimer"><i class="fas fa-print"></i></button>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data) {
                    if (data.statut !== 'payee' && data.statut !== 'annulee') {
                        return '<button class="btn btn-warning btn-sm edit-btn" data-id="' + data.id_facture + '" title="Modifier"><i class="fas fa-edit"></i></button>';
                    }
                    return '<button class="btn btn-secondary btn-sm" disabled title="Facture payée ou annulée"><i class="fas fa-edit"></i></button>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data) {
                    if (data.statut !== 'payee' && data.statut !== 'annulee') {
                        return '<button class="btn btn-danger btn-sm delete-btn" data-id="' + data.id_facture + '" title="Supprimer"><i class="fas fa-trash"></i></button>';
                    }
                    return '<button class="btn btn-secondary btn-sm" disabled title="Facture payée ou annulée"><i class="fas fa-trash"></i></button>';
                }
            }
        ],
        order: [[2, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Tout']],
        language: {
            sProcessing: "Traitement en cours...",
            sSearch: "Rechercher :",
            sLengthMenu: "Afficher _MENU_ éléments",
            sInfo: "Affichage _START_ à _END_ sur _TOTAL_ éléments",
            sInfoEmpty: "Affichage 0 à 0 sur 0 éléments",
            sInfoFiltered: "(filtré de _MAX_ éléments au total)",
            sLoadingRecords: "Chargement en cours...",
            sZeroRecords: "Aucun élément à afficher",
            sEmptyTable: "Aucune donnée disponible dans le tableau",
            oPaginate: {
                sFirst: "Premier",
                sPrevious: "Précédent",
                sNext: "Suivant",
                sLast: "Dernier"
            },
            oAria: {
                sSortAscending: ": activer pour trier la colonne par ordre croissant",
                sSortDescending: ": activer pour trier la colonne par ordre décroissant"
            }
        }
    });

    // Filtres
    $('#filtre_statut, #filtre_date_debut, #filtre_date_fin, #filtre_client').on('change', function() {
        table.ajax.reload();
    });
    
    $('#btn_reset_filtres').on('click', function() {
        $('#filtre_statut').val('');
        $('#filtre_date_debut').val('');
        $('#filtre_date_fin').val('');
        $('#filtre_client').val('');
        table.ajax.reload();
    });

    // Voir facture
    $('#dataTable tbody').on('click', '.view-btn', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '../api/modules/voir_facture.php',
            type: 'GET',
            data: { id_facture: id },
            dataType: 'html',
            success: function(html) {
                $('#voir_facture_content').html(html);
                $('#voir_facture').modal('show');
            },
            error: function() {
                Swal.fire('Erreur', 'Impossible de charger la facture', 'error');
            }
        });
    });

    // Imprimer facture
    $('#dataTable tbody').on('click', '.print-btn', function() {
        var id = $(this).data('id');
        window.open('../api/modules/imprimer_facture.php?id_facture=' + id, '_blank');
    });
    
    $('#printFactureBtn').on('click', function() {
        window.print();
    });

    // Modifier facture
    $('#dataTable tbody').on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        chargerFacturePourModification(id);
    });

    // Supprimer facture
    $('#dataTable tbody').on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Cette action est irréversible !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#F59E0B',
            confirmButtonText: "Oui, supprimer",
            cancelButtonText: "Annuler"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../api/modules/supprimer_facture.php?id_facture=' + id;
            }
        });
    });

    function chargerFacturePourModification(id) {
        $.ajax({
            url: '../api/modules/get_facture.php',
            type: 'GET',
            data: { id_facture: id },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    $('#modif_id_facture').val(data.facture.id_facture);
                    $('#modif_numero_facture').val(data.facture.numero_facture);
                    $('#modif_date_facture').val(data.facture.date_facture);
                    $('#modif_client_id').val(data.facture.id_client);
                    $('#modif_statut').val(data.facture.statut);
                    $('#modif_date_echeance').val(data.facture.date_echeance);
                    $('#modif_taux_tva').val(data.facture.taux_tva);
                    $('#modif_notes').val(data.facture.notes);
                    
                    $('#modif_lignes_facture_tbody').empty();
                    
                    if (data.lignes && data.lignes.length > 0) {
                        data.lignes.forEach(function(ligne, index) {
                            ajouterLigneModification(ligne, index);
                        });
                    } else {
                        ajouterLigneModification(null, 0);
                    }
                    
                    recalculerTotauxModification();
                    $('#modifier_facture').modal('show');
                } else {
                    Swal.fire('Erreur', data.message || 'Impossible de charger la facture', 'error');
                }
            },
            error: function() {
                Swal.fire('Erreur', 'Erreur lors du chargement', 'error');
            }
        });
    }
    
    let ligneModifIndex = 0;
    
    function ajouterLigneModification(ligne, index) {
        const designation = ligne ? ligne.designation : '';
        const description = ligne ? (ligne.description || '') : '';
        const quantite = ligne ? ligne.quantite : 1;
        const prix = ligne ? ligne.prix_unitaire_ht : 0;
        const remise = ligne ? ligne.remise_ligne : 0;
        
        const newRow = `
            <tr>
                <td><input type="text" class="form-control form-control-sm modif-ligne-designation" name="lignes[${index}][designation]" value="${designation.replace(/"/g, '&quot;')}" required></td>
                <td><input type="text" class="form-control form-control-sm" name="lignes[${index}][description]" value="${description.replace(/"/g, '&quot;')}"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm modif-ligne-quantite" name="lignes[${index}][quantite]" value="${quantite}"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm modif-ligne-prix" name="lignes[${index}][prix_unitaire_ht]" value="${prix}"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm modif-ligne-remise" name="lignes[${index}][remise_ligne]" value="${remise}"></td>
                <td class="modif-ligne-total-ht">0</td>
                <td class="modif-ligne-tva">0</td>
                <td class="modif-ligne-total-ttc">0</td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm modif-supprimer-ligne"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        $('#modif_lignes_facture_tbody').append(newRow);
        attacherEvenementsModification();
        calculerLigneModification($('#modif_lignes_facture_tbody tr:last'));
    }
    
    function attacherEvenementsModification() {
        $('.modif-ligne-quantite, .modif-ligne-prix, .modif-ligne-remise').off('input').on('input', function() {
            const row = $(this).closest('tr');
            calculerLigneModification(row);
            recalculerTotauxModification();
        });
        
        $('.modif-supprimer-ligne').off('click').on('click', function() {
            if ($('#modif_lignes_facture_tbody tr').length > 1) {
                $(this).closest('tr').remove();
                recalculerTotauxModification();
            } else {
                Swal.fire('Information', 'Vous devez conserver au moins une ligne', 'info');
            }
        });
    }
    
    function calculerLigneModification(row) {
        const quantite = parseFloat(row.find('.modif-ligne-quantite').val()) || 0;
        const prixUnitaire = parseFloat(row.find('.modif-ligne-prix').val()) || 0;
        const remiseLigne = parseFloat(row.find('.modif-ligne-remise').val()) || 0;
        
        let totalHt = quantite * prixUnitaire;
        totalHt = totalHt - remiseLigne;
        if (totalHt < 0) totalHt = 0;
        
        const tauxTva = parseFloat($('#modif_taux_tva').val()) || 0;
        const montantTva = totalHt * (tauxTva / 100);
        const totalTtc = totalHt + montantTva;
        
        row.find('.modif-ligne-total-ht').text(totalHt.toLocaleString('fr-FR'));
        row.find('.modif-ligne-tva').text(montantTva.toLocaleString('fr-FR'));
        row.find('.modif-ligne-total-ttc').text(totalTtc.toLocaleString('fr-FR'));
    }
    
    function recalculerTotauxModification() {
        let sousTotalHt = 0;
        let remiseTotale = 0;
        
        $('#modif_lignes_facture_tbody tr').each(function() {
            const quantite = parseFloat($(this).find('.modif-ligne-quantite').val()) || 0;
            const prixUnitaire = parseFloat($(this).find('.modif-ligne-prix').val()) || 0;
            const remiseLigne = parseFloat($(this).find('.modif-ligne-remise').val()) || 0;
            const totalHtLigne = quantite * prixUnitaire;
            sousTotalHt += totalHtLigne;
            remiseTotale += remiseLigne;
        });
        
        const totalHt = sousTotalHt - remiseTotale;
        const tauxTva = parseFloat($('#modif_taux_tva').val()) || 0;
        const montantTva = totalHt * (tauxTva / 100);
        const totalTtc = totalHt + montantTva;
        
        $('#modif_sous_total_ht').text(sousTotalHt.toLocaleString('fr-FR'));
        $('#modif_remise_totale').text(remiseTotale.toLocaleString('fr-FR'));
        $('#modif_total_ht').text(totalHt.toLocaleString('fr-FR'));
        $('#modif_montant_tva').text(montantTva.toLocaleString('fr-FR'));
        $('#modif_total_ttc').text(totalTtc.toLocaleString('fr-FR'));
    }
    
    $('#modif_taux_tva').on('input', function() {
        $('#modif_lignes_facture_tbody tr').each(function() { calculerLigneModification($(this)); });
        recalculerTotauxModification();
    });
    
    $('#modif_ajouter_ligne').on('click', function() {
        const index = $('#modif_lignes_facture_tbody tr').length;
        ajouterLigneModification(null, index);
    });
    
    attacherEvenementsModification();
});