// pages/DataTables/data_table_confection.js
$(document).ready(function () {
    // 1. Initialisation de DataTable
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../api/modules/confection_data.php',
            type: 'GET',
            dataType: 'json',
            data: function(d) {
                d.filtre_statut = $('#filtre_statut').val();
                d.filtre_type = $('#filtre_type').val();
                d.filtre_date_debut = $('#filtre_date_debut').val();
                d.filtre_date_fin = $('#filtre_date_fin').val();
            },
            error: function (xhr, status, error) {
                console.error('DataTables AJAX Error:', status, error);
                console.log('Response text:', xhr.responseText);
            }
        },
        dom: 'Blfrtip',
        buttons: [
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL' },
            'copyHtml5',
            'excelHtml5',
            'csvHtml5'
        ],
        columns: [
            { data: 'id_commande' },
            { data: 'numero_commande' },
            { data: 'client_nom', render: function(data, type, row) {
                return row.client_nom + ' ' + (row.client_prenom || '');
            }},
            { data: 'prestataire_nom' },
            { data: 'type_tenue' },
            { data: 'date_commande_formatee' },
            { data: 'date_livraison_prevue' },
            { data: 'montant_total', render: function(data) {
                return new Intl.NumberFormat('fr-FR').format(data) + ' FCFA';
            }},
            { data: 'montant_avance', render: function(data) {
                return new Intl.NumberFormat('fr-FR').format(data) + ' FCFA';
            }},
            { data: 'statut_badge' },
            {
                data: null,
                orderable: false,
                render: function (_data, _type, row) {
                    return '<button type="button" ' +
                                'class="btn btn-info btn-sm details-btn" ' +
                                'data-toggle="modal" ' +
                                'data-target="#details_confection" ' +
                                'data-id="' + row.id_commande + '">' +
                                '<i class="fa fa-eye"></i> Détails' +
                            '</button>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function (_data, _type, row) {
                    return '<button type="button" ' +
                                'class="btn btn-warning btn-sm statut-btn" ' +
                                'data-toggle="modal" ' +
                                'data-target="#changer_statut_confection" ' +
                                'data-id="' + row.id_commande + '" ' +
                                'data-statut="' + row.statut + '">' +
                                '<i class="fa fa-exchange-alt"></i> Statut' +
                            '</button>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function (_data, _type, row) {
                    return '<button type="button" ' +
                                'class="btn btn-primary btn-sm edit-btn" ' +
                                'data-toggle="modal" ' +
                                'data-target="#modifier_confection" ' +
                                'data-id="' + row.id_commande + '">' +
                                '<i class="fa fa-pencil"></i> Modifier' +
                            '</button>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function (_data, _type, row) {
                    return '<button type="button" ' +
                                'class="btn btn-danger btn-sm delete-btn" ' +
                                'data-id="' + row.id_commande + '" ' +
                                'data-numero="' + row.numero_commande + '">' +
                                '<i class="fa fa-trash"></i> Supprimer' +
                            '</button>';
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, 'Tout']
        ],
        language: {
            sProcessing:     "Traitement en cours...",
            sSearch:         "Rechercher :",
            sLengthMenu:     "Afficher _MENU_ éléments",
            sInfo:           "Affichage _START_ à _END_ sur _TOTAL_ éléments",
            sInfoEmpty:      "Affichage 0 à 0 sur 0 éléments",
            sInfoFiltered:   "(filtré de _MAX_ éléments au total)",
            sLoadingRecords: "Chargement en cours...",
            sZeroRecords:    "Aucun élément à afficher",
            sEmptyTable:     "Aucune donnée disponible dans le tableau",
            oPaginate: {
                sFirst:    "Premier",
                sPrevious: "Précédent",
                sNext:     "Suivant",
                sLast:     "Dernier"
            },
            oAria: {
                sSortAscending:  ": activer pour trier la colonne par ordre croissant",
                sSortDescending: ": activer pour trier la colonne par ordre décroissant"
            }
        }
    });
  
    // Rafraîchir la table quand les filtres changent
    $('#filtre_statut, #filtre_type, #filtre_date_debut, #filtre_date_fin').on('change', function() {
        table.ajax.reload();
    });
  
    // 2. Déséchappement HTML
    function unEscape(html) {
        if (!html) return '';
        return html
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&quot;/g, '"')
            .replace(/&#039;/g, "'")
            .replace(/&amp;/g, '&')
            .replace(/<br>/g, '\n');
    }
  
    // 3. Charger les clients dans les select (avec ID visible)
    function chargerClients() {
        $.ajax({
            url: '../api/modules/get_clients.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var options = '<option value="">Sélectionnez un client</option>';
                $.each(data, function(i, client) {
                    options += '<option value="' + client.id_client + '">#' + client.id_client + ' - ' + client.nom + ' ' + client.prenom + '</option>';
                });
                $('#id_client, #id_client_modif').html(options);
            },
            error: function() {
                console.error('Erreur chargement clients');
            }
        });
    }
  
    // 4. Charger les prestataires pour les sélecteurs multiples
    var prestatairesOptions = '';
    function chargerPrestatairesOptions() {
        $.ajax({
            url: '../api/modules/get_prestataires.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                prestatairesOptions = '<option value="">Sélectionnez un prestataire</option>';
                $.each(data, function(i, p) {
                    prestatairesOptions += '<option value="' + p.id_prestataire + '">#' + p.id_prestataire + ' - ' + p.nom + ' ' + p.prenom + ' (' + p.type_prestataire + ')</option>';
                });
                $('.prestataire-select').html(prestatairesOptions);
            },
            error: function() {
                console.error('Erreur chargement prestataires');
            }
        });
    }
  
    chargerClients();
    chargerPrestatairesOptions();
  
    // Variable pour l'index des prestataires
    var prestataireIndex = 1;
    
    // Ajouter une ligne prestataire
    $('#add-prestataire').on('click', function() {
        var newRow = `
            <div class="row prestataire-row mb-2">
                <div class="col-md-5">
                    <select class="form-control prestataire-select" name="prestataires[${prestataireIndex}][id_prestataire]">
                        ${prestatairesOptions}
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" step="0.01" class="form-control prestataire-montant" name="prestataires[${prestataireIndex}][montant]" placeholder="Montant (FCFA)">
                </div>
                <div class="col-md-2">
                    <select class="form-control prestataire-type" name="prestataires[${prestataireIndex}][type_production]">
                        <option value="tenue">Tenue</option>
                        <option value="pagne">Pagne</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-prestataire"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
        $('#prestataires-container').append(newRow);
        prestataireIndex++;
    });
    
    // Supprimer une ligne prestataire
    $(document).on('click', '.remove-prestataire', function() {
        if ($('.prestataire-row').length > 1) {
            $(this).closest('.prestataire-row').remove();
        } else {
            Swal.fire('Info', 'Il doit y avoir au moins un prestataire', 'info');
        }
    });
  
    // 5. Clic sur « Modifier » : préremplissage
    $('#dataTable tbody').on('click', '.edit-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        
        if (!rowData) return;
        
        $('#id_commande_modif').val(rowData.id_commande);
        $('#id_client_modif').val(rowData.id_client);
        $('#type_tenue_modif').val(rowData.type_tenue);
        $('#date_livraison_prevue_modif').val(rowData.date_livraison_prevue_originale || rowData.date_livraison_prevue);
        $('#description_tenue_modif').val(unEscape(rowData.description_tenue || ''));
        $('#tissu_fourni_par_modif').val(rowData.tissu_fourni_par || 'client');
        $('#quantite_tissu_modif').val(rowData.quantite_tissu || '');
        $('#reference_tissu_modif').val(rowData.reference_tissu || '');
        $('#montant_total_modif').val(rowData.montant_total);
        $('#montant_avance_modif').val(rowData.montant_avance || 0);
        $('#instructions_couturier_modif').val(unEscape(rowData.instructions_couturier || ''));
        $('#remarques_modif').val(unEscape(rowData.remarques || ''));
        
        // Charger les prestataires de la commande pour modification
        $.ajax({
            url: '../api/modules/get_commande_prestataires.php',
            type: 'GET',
            data: { id_commande: rowData.id_commande },
            dataType: 'json',
            success: function(data) {
                var container = $('#prestataires-container-modif');
                container.empty();
                
                if (data.prestataires && data.prestataires.length > 0) {
                    $.each(data.prestataires, function(i, p) {
                        var row = `
                            <div class="row prestataire-row-modif mb-2">
                                <div class="col-md-5">
                                    <select class="form-control prestataire-select-modif" name="prestataires_modif[${i}][id_prestataire]">
                                        ${prestatairesOptions}
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" step="0.01" class="form-control prestataire-montant-modif" name="prestataires_modif[${i}][montant]" value="${p.montant_unitaire || ''}" placeholder="Montant (FCFA)">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-control prestataire-type-modif" name="prestataires_modif[${i}][type_production]">
                                        <option value="tenue" ${p.type_production === 'tenue' ? 'selected' : ''}>Tenue</option>
                                        <option value="pagne" ${p.type_production === 'pagne' ? 'selected' : ''}>Pagne</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-prestataire-modif"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        `;
                        container.append(row);
                        container.find('.prestataire-select-modif:last').val(p.id_prestataire);
                    });
                } else {
                    var row = `
                        <div class="row prestataire-row-modif mb-2">
                            <div class="col-md-5">
                                <select class="form-control prestataire-select-modif" name="prestataires_modif[0][id_prestataire]">
                                    ${prestatairesOptions}
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="number" step="0.01" class="form-control prestataire-montant-modif" name="prestataires_modif[0][montant]" placeholder="Montant (FCFA)">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control prestataire-type-modif" name="prestataires_modif[0][type_production]">
                                    <option value="tenue">Tenue</option>
                                    <option value="pagne">Pagne</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-prestataire-modif"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                    container.append(row);
                }
                
                $('#modifier_confection').modal('show');
            },
            error: function() {
                $('#modifier_confection').modal('show');
            }
        });
    });
    
    // Ajouter une ligne prestataire dans le modal modification
    $(document).on('click', '#add-prestataire-modif', function() {
        var index = $('#prestataires-container-modif .prestataire-row-modif').length;
        var row = `
            <div class="row prestataire-row-modif mb-2">
                <div class="col-md-5">
                    <select class="form-control prestataire-select-modif" name="prestataires_modif[${index}][id_prestataire]">
                        ${prestatairesOptions}
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" step="0.01" class="form-control prestataire-montant-modif" name="prestataires_modif[${index}][montant]" placeholder="Montant (FCFA)">
                </div>
                <div class="col-md-2">
                    <select class="form-control prestataire-type-modif" name="prestataires_modif[${index}][type_production]">
                        <option value="tenue">Tenue</option>
                        <option value="pagne">Pagne</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-prestataire-modif"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
        $('#prestataires-container-modif').append(row);
    });
    
    // Supprimer une ligne prestataire dans le modal modification
    $(document).on('click', '.remove-prestataire-modif', function() {
        if ($('#prestataires-container-modif .prestataire-row-modif').length > 1) {
            $(this).closest('.prestataire-row-modif').remove();
        } else {
            Swal.fire('Info', 'Il doit y avoir au moins un prestataire', 'info');
        }
    });
  
    // 6. Clic sur « Statut »
    $('#dataTable tbody').on('click', '.statut-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        if (!rowData) return;
        $('#id_commande_statut').val(rowData.id_commande);
        $('#nouveau_statut').val(rowData.statut);
    });
  
    // 7. Clic sur « Détails »
    $('#dataTable tbody').on('click', '.details-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        if (!rowData) return;
        var id = rowData.id_commande;
        
        $.ajax({
            url: '../api/modules/details_confection.php',
            type: 'GET',
            data: { id_commande: id },
            dataType: 'html',
            success: function(html) {
                $('#details_contenu').html(html);
            },
            error: function() {
                $('#details_contenu').html('<div class="alert alert-danger">Erreur lors du chargement des détails</div>');
            }
        });
    });
  
    // 8. Clic sur « Supprimer »
    $('#dataTable tbody').on('click', '.delete-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        if (!rowData) return;
        var id = rowData.id_commande;
        var numero = rowData.numero_commande;
  
        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Vous allez supprimer la commande " + numero + ". Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#F59E0B',
            confirmButtonText: "Oui, supprimer",
            cancelButtonText: "Annuler"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../api/modules/supprimer_confection.php?id_commande=' + id;
            }
        });
    });
});