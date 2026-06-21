// pages/DataTables/data_table_depense.js
$(document).ready(function () {
    var chartInstance = null;
    var chartJourInstance = null;
    
    // 1. Initialisation de DataTable
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../api/modules/depense_data.php',
            type: 'GET',
            data: function(d) {
                d.filtre_categorie = $('#filtre_categorie').val();
                d.filtre_date_debut = $('#filtre_date_debut').val();
                d.filtre_date_fin = $('#filtre_date_fin').val();
                d.filtre_montant_min = $('#filtre_montant_min').val();
                d.filtre_montant_max = $('#filtre_montant_max').val();
                d.filtre_statut = $('#filtre_statut').val();
                d.filtre_date_unique = $('#filtre_date_unique').val();
            },
            error: function (xhr, status, error) {
                console.error('DataTables AJAX Error:', status, error);
                console.log('Response text:', xhr.responseText);
            }
        },
        dom: 'Blfrtip',
        buttons: [
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL', title: 'Liste des dépenses' },
            'copyHtml5',
            'excelHtml5',
            'csvHtml5'
        ],
        columns: [
            { data: 'id_depense' },
            { data: 'date_depense_formatee' },
            { data: 'reference' },
            { data: 'libelle' },
            { data: 'categorie_libelle' },
            { data: 'beneficiaire' },
            { data: 'justification_courte' },
            { data: 'montant_formate' },
            { data: 'origine' },
            { data: 'saisi_par' },
            { data: 'statut_badge' },
            {
                data: null,
                orderable: false,
                render: function(data) {
                    return '<button class="btn btn-info btn-sm details-btn" data-id="' + data.id_depense + '"><i class="fa fa-eye"></i> Détails</button>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data) {
                    return '<button class="btn btn-warning btn-sm edit-btn" data-id="' + data.id_depense + '"><i class="fa fa-pencil"></i> Modifier</button>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data) {
                    return '<button class="btn btn-danger btn-sm delete-btn" data-id="' + data.id_depense + '"><i class="fa fa-trash"></i> Supprimer</button>';
                }
            }
        ],
        order: [[0, 'desc']],
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

    // Rafraîchir la table quand les filtres changent
    $('#filtre_categorie, #filtre_date_debut, #filtre_date_fin, #filtre_montant_min, #filtre_montant_max, #filtre_statut, #filtre_date_unique').on('change', function() {
        table.ajax.reload();
        chargerTotaux();
        mettreAJourGraphique();
        mettreAJourGraphiqueParJour();
    });
    
    // Réinitialiser les filtres
    $('#btn_reset_filtres').on('click', function() {
        $('#filtre_categorie').val('');
        $('#filtre_date_debut').val('');
        $('#filtre_date_fin').val('');
        $('#filtre_montant_min').val('');
        $('#filtre_montant_max').val('');
        $('#filtre_statut').val('');
        $('#filtre_date_unique').val('');
        table.ajax.reload();
        chargerTotaux();
        mettreAJourGraphique();
        mettreAJourGraphiqueParJour();
    });

    // Charger les totaux par catégorie
    function chargerTotaux() {
        $.ajax({
            url: '../api/modules/totaux_depenses.php',
            type: 'GET',
            data: {
                categorie: $('#filtre_categorie').val(),
                date_debut: $('#filtre_date_debut').val(),
                date_fin: $('#filtre_date_fin').val(),
                montant_min: $('#filtre_montant_min').val(),
                montant_max: $('#filtre_montant_max').val(),
                statut: $('#filtre_statut').val(),
                date_unique: $('#filtre_date_unique').val()
            },
            dataType: 'json',
            success: function(data) {
                var html = '';
                var totalGeneral = 0;
                
                if (data && data.length && !data.error) {
                    $.each(data, function(i, cat) {
                        // ✅ CORRECTION : S'assurer que total est un nombre
                        var totalCat = parseFloat(cat.total) || 0;
                        totalGeneral += totalCat;
                        html += '<div class="col-md-3 mb-2">' +
                                '<div class="card bg-light">' +
                                '<div class="card-body">' +
                                '<h6 class="card-title">' + (cat.categorie_libelle || cat.categorie) + '</h6>' +
                                '<p class="card-text"><strong>' + new Intl.NumberFormat('fr-FR').format(totalCat) + ' FCFA</strong></p>' +
                                '<small>' + (cat.nombre || 0) + ' dépense(s)</small>' +
                                '</div></div></div>';
                    });
                    
                    // ✅ CORRECTION : S'assurer que totalGeneral est un nombre
                    totalGeneral = parseFloat(totalGeneral) || 0;
                    
                    html += '<div class="col-md-3 mb-2">' +
                            '<div class="card bg-primary text-white">' +
                            '<div class="card-body">' +
                            '<h6 class="card-title">TOTAL GÉNÉRAL</h6>' +
                            '<p class="card-text"><strong>' + new Intl.NumberFormat('fr-FR').format(totalGeneral) + ' FCFA</strong></p>' +
                            '</div></div></div>';
                } else {
                    html = '<div class="col-md-12 text-center">Aucune dépense trouvée</div>';
                }
                
                $('#totaux_categories').html(html);
            },
            error: function() {
                $('#totaux_categories').html('<div class="col-md-12 text-center text-danger">Erreur chargement des totaux</div>');
            }
        });
    }

    // Mettre à jour le graphique camembert
    function mettreAJourGraphique() {
        $.ajax({
            url: '../api/modules/stats_depenses.php',
            type: 'GET',
            data: {
                date_debut: $('#filtre_date_debut').val(),
                date_fin: $('#filtre_date_fin').val(),
                statut: $('#filtre_statut').val(),
                date_unique: $('#filtre_date_unique').val()
            },
            dataType: 'json',
            success: function(data) {
                var ctx = document.getElementById('chartDepenses').getContext('2d');
                
                if (chartInstance) {
                    chartInstance.destroy();
                }
                
                if (data && data.length && !data.error) {
                    var labels = [];
                    var values = [];
                    var couleurs = [
                        '#DC2626', '#F59E0B', '#10B981', '#3B82F6', 
                        '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16',
                        '#F97316', '#6366F1', '#14B8A6', '#D946EF'
                    ];
                    
                    $.each(data, function(i, cat) {
                        labels.push(cat.categorie_libelle);
                        values.push(parseFloat(cat.total) || 0);
                    });
                    
                    if (values.length > 0) {
                        chartInstance = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: values,
                                    backgroundColor: couleurs.slice(0, labels.length),
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                var label = context.label || '';
                                                var value = context.raw || 0;
                                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                var percentage = ((value / total) * 100).toFixed(1);
                                                return label + ': ' + new Intl.NumberFormat('fr-FR').format(value) + ' FCFA (' + percentage + '%)';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                        ctx.font = '16px Arial';
                        ctx.fillStyle = '#999';
                        ctx.fillText('Aucune donnée à afficher', ctx.canvas.width/2 - 100, ctx.canvas.height/2);
                    }
                } else {
                    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                    ctx.font = '16px Arial';
                    ctx.fillStyle = '#999';
                    ctx.fillText('Aucune donnée à afficher', ctx.canvas.width/2 - 100, ctx.canvas.height/2);
                }
            },
            error: function() {
                var ctx = document.getElementById('chartDepenses').getContext('2d');
                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                ctx.font = '16px Arial';
                ctx.fillStyle = '#999';
                ctx.fillText('Erreur de chargement', ctx.canvas.width/2 - 100, ctx.canvas.height/2);
            }
        });
    }
    
    // Mettre à jour le graphique par jour
    function mettreAJourGraphiqueParJour() {
        $.ajax({
            url: '../api/modules/stats_depenses_jour.php',
            type: 'GET',
            data: {
                date_debut: $('#filtre_date_debut').val(),
                date_fin: $('#filtre_date_fin').val(),
                statut: $('#filtre_statut').val(),
                date_unique: $('#filtre_date_unique').val()
            },
            dataType: 'json',
            success: function(data) {
                var ctx = document.getElementById('chartDepensesJour').getContext('2d');
                
                if (chartJourInstance) {
                    chartJourInstance.destroy();
                }
                
                if (data && data.length && !data.error) {
                    var labels = [];
                    var valeurs = [];
                    
                    $.each(data, function(i, item) {
                        labels.push(item.date_label);
                        valeurs.push(parseFloat(item.total) || 0);
                    });
                    
                    if (valeurs.length > 0) {
                        chartJourInstance = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Montant des dépenses (FCFA)',
                                    data: valeurs,
                                    backgroundColor: '#3B82F6',
                                    borderColor: '#1E3A8A',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'Montant: ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' FCFA';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                        ctx.font = '16px Arial';
                        ctx.fillStyle = '#999';
                        ctx.fillText('Aucune donnée à afficher', ctx.canvas.width/2 - 100, ctx.canvas.height/2);
                    }
                } else {
                    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                    ctx.font = '16px Arial';
                    ctx.fillStyle = '#999';
                    ctx.fillText('Aucune donnée à afficher', ctx.canvas.width/2 - 100, ctx.canvas.height/2);
                }
            },
            error: function() {
                var ctx = document.getElementById('chartDepensesJour').getContext('2d');
                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                ctx.font = '16px Arial';
                ctx.fillStyle = '#999';
                ctx.fillText('Erreur de chargement', ctx.canvas.width/2 - 100, ctx.canvas.height/2);
            }
        });
    }

    // Déséchappement HTML
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

    // Clic sur « Modifier »
    $('#dataTable tbody').on('click', '.edit-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        
        $('#id_depense_modif').val(rowData.id_depense);
        $('#libelle_modif').val(unEscape(rowData.libelle));
        $('#categorie_modif').val(rowData.categorie);
        $('#beneficiaire_modif').val(unEscape(rowData.beneficiaire));
        $('#justification_modif').val(unEscape(rowData.justification));
        $('#montant_modif').val(rowData.montant_valeur);
        $('#date_depense_modif').val(rowData.date_depense_originale);
        $('#reference_piece_modif').val(rowData.reference_piece || '');
        $('#mode_paiement_modif').val(rowData.mode_paiement || 'especes');
        $('#reference_transaction_modif').val(rowData.reference_transaction || '');
        $('#statut_modif').val(rowData.statut);
        
        $('#modifier_depense').modal('show');
    });

    // Clic sur « Détails »
    $('#dataTable tbody').on('click', '.details-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        var id = rowData.id_depense;
        
        $.ajax({
            url: '../api/modules/details_depense.php',
            type: 'GET',
            data: { id_depense: id },
            dataType: 'html',
            success: function(html) {
                $('#details_contenu').html(html);
                $('#details_depense').modal('show');
            },
            error: function() {
                $('#details_contenu').html('<div class="alert alert-danger">Erreur lors du chargement des détails</div>');
                $('#details_depense').modal('show');
            }
        });
    });

    // Clic sur « Supprimer »
    $('#dataTable tbody').on('click', '.delete-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        var id = rowData.id_depense;
        var libelle = rowData.libelle;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Vous allez supprimer la dépense : " + libelle + ". Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#F59E0B',
            confirmButtonText: "Oui, supprimer",
            cancelButtonText: "Annuler"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../api/modules/supprimer_depense.php?id_depense=' + id;
            }
        });
    });

    // Initialisation
    chargerTotaux();
    mettreAJourGraphique();
    mettreAJourGraphiqueParJour();
});