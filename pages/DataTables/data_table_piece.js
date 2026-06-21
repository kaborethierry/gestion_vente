// Fichier : pages/DataTables/data_table_piece.js

$(document).ready(function () {
  // 0) Vérifications d’éléments DOM attendus
  const $tableEl = $('#dataTable, #dataTablePieces').first(); // compat: id="dataTable" ou "dataTablePieces"
  const tableId = $tableEl.attr('id') || 'dataTable';
  if ($tableEl.length === 0) {
    console.error('Table cible introuvable (#dataTable ou #dataTablePieces).');
    return;
  }

  // 1) Initialisation de DataTable
  const table = $tableEl.DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '../api/modules/piece_data.php',
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
      { data: 'id_piece', className: 'text-center' },        // N°
      { data: 'reference', className: 'text-center' },       // Référence
      { data: 'designation', className: 'text-center' },     // Désignation
      { data: 'prix_achat', className: 'text-center' },      // Prix d'achat
      { data: 'prix_vente', className: 'text-center' },      // Prix de vente
      { data: 'quantite_stock', className: 'text-center' },  // Quantité
      { data: 'seuil_minimal', className: 'text-center' },   // Seuil minimal
      { data: 'fournisseur', className: 'text-center' },     // Fournisseur
      { data: 'nom_categorie', className: 'text-center' },   // Catégorie
      {
        data: 'date_ajout',
        className: 'text-center'
      },                                                     // Date d'ajout
      {
        data: null,
        orderable: false,
        className: 'text-center',
        render: function (_data, _type, row) {
          return (
            '<button type="button" ' +
            'class="btn btn-warning edit-btn" ' +
            'data-toggle="modal" ' +
            'data-target="#modifier_piece" ' +
            'data-id="' + row.id_piece + '">' +
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
            'data-id="' + row.id_piece + '">' +
            '<i class="fa fa-trash"></i>' +
            '</button>'
          );
        }
      }
    ],
    // Aligner avec le mapping du backend (ordre des colonnes)
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

  // 2) Déséchappement HTML (si vos données contiennent des entités)
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

  // 3) Clic sur « Modifier » : préremplissage + ouverture (optionnel en JS si besoin)
  $tableEl.on('click', '.edit-btn', function () {
    const rowData = table.row($(this).closest('tr')).data();
    if (!rowData) return;

    $('#id_piece_modif').val(rowData.id_piece);
    $('#ref_modif').val(unEscape(rowData.reference));
    $('#designation_modif').val(unEscape(rowData.designation));
    $('#prix_achat_modif').val(rowData.prix_achat);
    $('#prix_vente_modif').val(rowData.prix_vente);
    $('#quantite_modif').val(rowData.quantite_stock);
    $('#seuil_modif').val(rowData.seuil_minimal);
    $('#fournisseur_modif').val(unEscape(rowData.fournisseur));
    $('#categorie_modif').val(rowData.id_categorie); // Assure-toi que l'option existe dans le select

    // Si tu veux forcer l'ouverture par JS (utile en Bootstrap 5):
    // const modal = new bootstrap.Modal(document.getElementById('modifier_piece'));
    // modal.show();
  });

  // 4) Clic sur « Supprimer » : SweetAlert2 + redirection
  $tableEl.on('click', '.delete-btn', function () {
    const rowData = table.row($(this).closest('tr')).data();
    if (!rowData) return;

    const id = rowData.id_piece;

    if (typeof Swal === 'undefined') {
      // Fallback simple si SweetAlert2 non chargé
      if (confirm('Confirmer la suppression ?')) {
        window.location.href = '../api/modules/supprimer_piece.php?id_piece=' + encodeURIComponent(id);
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
        window.location.href = '../api/modules/supprimer_piece.php?id_piece=' + encodeURIComponent(id);
      }
    });
  });
});
