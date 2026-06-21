// Fichier : pages/DataTables/data_table_mouvement.js

$(document).ready(function () {
    const $tableEl = $('#dataTableMouvements, #dataTableMouvement, #dataTable').first();
    if ($tableEl.length === 0) {
      console.error('Table cible introuvable (#dataTableMouvements, #dataTableMouvement ou #dataTable).');
      return;
    }
  
    const table = $tableEl.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '../api/modules/mouvement_data.php',
        type: 'GET',
        dataType: 'json',
        dataSrc: function (json) {
          if (!json || !Array.isArray(json.data)) {
            console.error('Réponse inattendue du serveur:', json);
            return [];
          }
          return json.data;
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
        { data: 'id_mouvement', className: 'text-center' },
        { data: 'piece_label', className: 'text-center' },
        { data: 'type_mouvement', className: 'text-center' },
        { data: 'quantite', className: 'text-center' },
        { data: 'motif', className: 'text-center', render: (d) => d || '' },
        { data: 'date_mouvement', className: 'text-center' },
        {
          data: null, orderable: false, className: 'text-center',
          render: function (_d, _t, row) {
            return (
              '<button type="button" class="btn btn-warning edit-btn" ' +
              'data-toggle="modal" data-target="#modifier_mouvement" ' +
              'data-id="' + row.id_mouvement + '"><i class="fa fa-pencil"></i></button>'
            );
          }
        },
        {
          data: null, orderable: false, className: 'text-center',
          render: function (_d, _t, row) {
            return (
              '<button type="button" class="btn btn-danger delete-btn" ' +
              'data-id="' + row.id_mouvement + '"><i class="fa fa-trash"></i></button>'
            );
          }
        }
      ],
      order: [[0, 'desc']],
      pageLength: 5,
      lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Tout']],
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
  
    function toDatetimeLocal(value) {
      if (!value) return '';
      const m = /^(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2})/.exec(value);
      return m ? `${m[1]}T${m[2]}` : '';
    }
  
    function getRowDataFromBtn(btn) {
      let $tr = $(btn).closest('tr');
      if ($tr.hasClass('child')) $tr = $tr.prev();
      return table.row($tr).data();
    }
  
    // Préremplissage du modal Modifier
    $tableEl.on('click', '.edit-btn', function () {
      const rowData = getRowDataFromBtn(this);
      if (!rowData) return;
  
      // Champs hidden
      $('#id_mouvement_modif').val(rowData.id_mouvement);
  
      // Pièce (id="piece_modif")
      const $piece = $('#piece_modif');
      // Si l'option n’existe pas (liste construite au chargement), on la crée pour éviter un select vide.
      if ($piece.find(`option[value="${rowData.id_piece}"]`).length === 0) {
        $piece.append(new Option(rowData.piece_label || `#${rowData.id_piece}`, rowData.id_piece, true, true));
      }
      $piece.val(rowData.id_piece);
  
      // Type de mouvement (id="type_modif") — valeurs attendues: ENTREE, SORTIE, AJUSTEMENT
      // Le backend renvoie "Entrée|Sortie|Ajustement" → on mappe vers la valeur du select (clé ENUM majuscules).
      const mapTypeToKey = { 'Entrée': 'ENTREE', 'Sortie': 'SORTIE', 'Ajustement': 'AJUSTEMENT' };
      const typeKey = mapTypeToKey[rowData.type_mouvement] || '';
      $('#type_modif').val(typeKey);
  
      // Quantité
      $('#quantite_modif').val(rowData.quantite);
  
      // Date (datetime-local)
      $('#date_modif').val(toDatetimeLocal(rowData.date_mouvement));
  
      // Motif
      $('#motif_modif').val(rowData.motif || '');
  
      // Prix unitaire et Référence document (optionnels)
      // Ces champs existent dans le modal, mais ne sont pas renvoyés par l'API actuelle.
      // On les vide proprement pour éviter des résidus d'une édition précédente.
      $('#prix_modif').val('');
      $('#refdoc_modif').val('');
    });
  
    // Suppression
    $tableEl.on('click', '.delete-btn', function () {
      const rowData = getRowDataFromBtn(this);
      if (!rowData) return;
  
      const id = rowData.id_mouvement;
  
      if (typeof Swal === 'undefined') {
        if (confirm('Confirmer la suppression du mouvement ?')) {
          window.location.href = '../api/modules/supprimer_mouvement.php?id_mouvement=' + encodeURIComponent(id);
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
          window.location.href = '../api/modules/supprimer_mouvement.php?id_mouvement=' + encodeURIComponent(id);
        }
      });
    });
  });
  