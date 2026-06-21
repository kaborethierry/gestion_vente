$(document).ready(function () {
    // 1. Initialisation de DataTable
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../api/modules/caisse_data.php',
            type: 'GET',
            dataType: 'json',
            error: function (xhr, status, error) {
                console.error('DataTables AJAX Error:', status, error);
                console.log('Response text:', xhr.responseText);
            }
        },
        dom: 'Blfrtip',
        buttons: [
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL', title: 'Liste des sessions de caisse' },
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'print'
        ],
        columns: [
            { data: 'id_caisse', title: 'ID Caisse' },
            { data: 'id_session', title: 'Session ID' },
            { data: 'caissier', title: 'Caissier' },
            { data: 'date_ouverture', title: 'Date ouverture' },
            { data: 'date_fermeture', title: 'Date fermeture' },
            { 
                data: 'montant_initial',
                title: 'Montant initial',
                render: function(data) {
                    if (!data) return '-';
                    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(data) + ' FCFA';
                }
            },
            { 
                data: 'montant_final_reel',
                title: 'Montant final',
                render: function(data) {
                    if (!data) return '-';
                    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(data) + ' FCFA';
                }
            },
            { 
                data: 'statut',
                title: 'Statut',
                render: function(data) {
                    if (data === 'ouverte') return '<span class="badge badge-success">Ouverte</span>';
                    if (data === 'fermee') return '<span class="badge badge-secondary">Fermée</span>';
                    if (data === 'suspendue') return '<span class="badge badge-warning">Suspendue</span>';
                    if (data === 'cloturee') return '<span class="badge badge-info">Clôturée</span>';
                    return '<span class="badge badge-light">' + data + '</span>';
                }
            },
            { 
                data: 'chiffre_affaires',
                title: 'Chiffre d\'affaires',
                render: function(data, type, row) {
                    if (!data || data == 0) return '-';
                    var ventes = row.total_ventes_net || 0;
                    var confections = row.total_avance_confection || 0;
                    var title = 'Ventes POS: ' + new Intl.NumberFormat('fr-FR').format(ventes) + ' FCFA\n';
                    title += 'Confections: ' + new Intl.NumberFormat('fr-FR').format(confections) + ' FCFA';
                    return '<span title="' + title + '" style="cursor: help; border-bottom: 1px dotted #999;">' + 
                           new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(data) + ' FCFA' +
                           '</span>';
                }
            },
            {
                data: null,
                title: 'Modifier',
                orderable: false,
                render: function (_data, _type, row) {
                    if (row.statut === 'ouverte') {
                        var isPermanente = row.id_session && row.id_session.indexOf('SES-PERMANENTE') === 0;
                        if (isPermanente) {
                            return '<button type="button" class="btn btn-warning edit-btn" title="Modifier (session permanente)" ' +
                                   'data-toggle="modal" data-target="#modifier_caisse" data-id="' + row.id_caisse + '">' +
                                   '<i class="fa fa-pencil"></i> Modifier</button>';
                        }
                        return '<button type="button" class="btn btn-warning edit-btn" ' +
                               'data-toggle="modal" data-target="#modifier_caisse" data-id="' + row.id_caisse + '">' +
                               '<i class="fa fa-pencil"></i> Modifier</button>';
                    }
                    return '<button type="button" class="btn btn-secondary" disabled><i class="fa fa-pencil"></i> Modifier</button>';
                }
            },
            {
                data: null,
                title: 'Fermer',
                orderable: false,
                render: function (_data, _type, row) {
                    if (row.statut === 'ouverte') {
                        var isPermanente = row.id_session && row.id_session.indexOf('SES-PERMANENTE') === 0;
                        if (isPermanente) {
                            return '<button type="button" class="btn btn-secondary" disabled title="Les sessions permanentes ne peuvent pas être fermées">' +
                                   '<i class="fa fa-lock"></i> Permanente</button>';
                        }
                        return '<button type="button" class="btn btn-danger close-btn" ' +
                               'data-toggle="modal" data-target="#fermer_caisse" data-id="' + row.id_caisse + '">' +
                               '<i class="fa fa-lock"></i> Fermer</button>';
                    }
                    return '<button type="button" class="btn btn-secondary" disabled><i class="fa fa-check"></i> Fermée</button>';
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
  
    // 2. Déséchappement HTML pour les notes
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
  
    // 3. Clic sur « Modifier » : préremplissage du modal
    $('#dataTable tbody').on('click', '.edit-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        
        $('#id_caisse_modif').val(rowData.id_caisse);
        $('#id_session_modif').val(rowData.id_session);
        $('#id_utilisateur_modif').val(rowData.id_utilisateur);
        $('#montant_initial_modif').val(rowData.montant_initial);
        $('#montant_final_reel_modif').val(rowData.montant_final_reel || '');
        $('#statut_modif').val(rowData.statut);
        $('#notes_modif').val(unEscape(rowData.notes_ouverture || ''));
        
        var ca = rowData.chiffre_affaires || 0;
        $('#chiffre_affaires').val(new Intl.NumberFormat('fr-FR').format(ca) + ' FCFA');
        
        var isPermanente = rowData.id_session && rowData.id_session.indexOf('SES-PERMANENTE') === 0;
        if (isPermanente) {
            $('#statut_modif option[value="fermee"]').prop('disabled', true);
            $('#statut_modif option[value="cloturee"]').prop('disabled', true);
            $('#statut_modif').val('ouverte');
        } else {
            $('#statut_modif option').prop('disabled', false);
        }
    });
  
    // 4. Clic sur « Fermer » : préremplissage du modal de fermeture
    $('#dataTable tbody').on('click', '.close-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        $('#id_caisse_fermer').val(rowData.id_caisse);
        $('#montant_final_reel_fermer').val('');
    });
  
    // 5. Rafraîchir la table après chaque action
    function refreshTable() {
        table.ajax.reload(null, false);
    }
  
    window.refreshCaisseTable = refreshTable;
  });