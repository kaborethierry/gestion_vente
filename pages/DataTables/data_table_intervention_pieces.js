// Fichier : pages/DataTables/data_table_intervention_pieces.js

$(document).ready(function () {
    // 0) Ciblage de la table
    const $tableEl = $('#dataTable, #dataTableInterventionPieces').first();
    if ($tableEl.length === 0) {
      console.error('Table cible introuvable (#dataTable ou #dataTableInterventionPieces).');
      return;
    }
  
    // 1) Initialisation DataTable (server-side)
    const table = $tableEl.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '../api/modules/intervention_piece_data.php',
        type: 'GET',
        dataType: 'json',
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
        { data: 'id', className: 'text-center' },                    // N°
        { 
          data: null, className: 'text-center',                      // Intervention
          render: function (_d, _t, row) {
            const id = row.id_intervention ?? '';
            const type = row.type_intervention ?? '';
            const statut = row.statut_intervention ?? '';
            const dateDebut = row.date_debut ?? '';
            return `#${id} - ${type} (${statut})<br><small>${dateDebut}</small>`;
          }
        },
        { 
          data: null, className: 'text-center',                      // Véhicule
          render: function (_d, _t, row) {
            const im = row.immatriculation ?? '—';
            const marque = row.marque ?? '';
            const modele = row.modele ?? '';
            return `${marque} ${modele}<br><small>${im}</small>`;
          }
        },
        { data: 'designation', className: 'text-center' },           // Pièce (désignation)
        { data: 'reference', className: 'text-center' },             // Référence
        { data: 'quantite', className: 'text-center' },              // Quantité
        { 
          data: 'prix_unitaire', className: 'text-center',           // Prix unitaire
          render: function (val) {
            if (val === null || val === undefined) return '—';
            return Number(val).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' FCFA';
          }
        },
        { 
          data: 'montant_total', className: 'text-center',           // Montant total
          render: function (val) {
            if (val === null || val === undefined) return '—';
            return Number(val).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' FCFA';
          }
        },
        { data: 'date_ajout', className: 'text-center' },            // Date d'ajout
        {
          data: null,
          orderable: false,
          className: 'text-center',
          render: function (_data, _type, row) {
            return (
              '<button type="button" ' +
              'class="btn btn-warning edit-btn" ' +
              'data-toggle="modal" ' +
              'data-target="#modifier_intervention_piece" ' +
              'data-id="' + row.id + '">' +
              '<i class="fa fa-pencil"></i>' +
              '</button>'
            );
          }
        },
        {
          data: null,
          orderable: false,
          className: 'text-center',
          render: function (_data, _type, row) {
            return (
              '<button type="button" ' +
              'class="btn btn-danger delete-btn" ' +
              'data-id="' + row.id + '">' +
              '<i class="fa fa-trash"></i>' +
              '</button>'
            );
          }
        }
      ],
      order: [[0, 'desc']],
      pageLength: 5,
      lengthMenu: [
        [5, 10, 25, 50, -1],
        [5, 10, 25, 50, 'Tout']
      ],
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
  
    // Utilitaires
    function unEscape(html) {
      if (html === null || html === undefined) return '';
      return String(html)
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&#039;/g, "'")
        .replace(/&amp;/g, '&')
        .replace(/<br\s*\/?>/gi, '\n');
    }
  
    // 3) Préremplissage du modal de modification
    $tableEl.on('click', '.edit-btn', function () {
      const rowData = table.row($(this).closest('tr')).data();
      if (!rowData) return;
  
      $('#id_ligne_modif').val(rowData.id);
      $('#id_intervention_modif').val(rowData.id_intervention);
      $('#id_piece_modif').val(rowData.id_piece);
      $('#quantite_modif').val(rowData.quantite);
      $('#prix_unitaire_modif').val(rowData.prix_unitaire);
      // Optionnel: ouvrir le modal via JS (si nécessaire)
      // const modal = new bootstrap.Modal(document.getElementById('modifier_intervention_piece'));
      // modal.show();
    });
  
    // 4) Suppression avec confirmation
    $tableEl.on('click', '.delete-btn', function () {
      const rowData = table.row($(this).closest('tr')).data();
      if (!rowData) return;
  
      const id = rowData.id;
  
      if (typeof Swal === 'undefined') {
        if (confirm('Confirmer la suppression ?')) {
          window.location.href = '../api/modules/supprimer_intervention_piece.php?id=' + encodeURIComponent(id);
        }
        return;
      }
  
      Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Cette action est irréversible.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: "Oui, supprimer",
        cancelButtonText: "Annuler"
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = '../api/modules/supprimer_intervention_piece.php?id=' + encodeURIComponent(id);
        }
      });
    });
  });
  