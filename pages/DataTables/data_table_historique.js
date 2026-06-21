// Fichier : pages/DataTables/data_table_historique.js

$(document).ready(function () {
    // 0) Vérification de l'élément tableau
    const $tableEl = $('#datatable');
    if ($tableEl.length === 0) {
      console.error('Table cible introuvable (#datatable).');
      return;
    }
  
    // 1) Initialisation de DataTable
    const table = $tableEl.DataTable({
      processing: true,
      serverSide: false, // pas de traitement côté serveur : l'API retourne toutes les données
      ajax: {
        url: '../api/modules/historique_data.php',
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
        { data: 'id', className: 'text-center' },              // N°
        { data: 'adresse_ip', className: 'text-center' },      // Adresse IP
        {                                                      // Date & Heure
          data: 'date_heure_ajout',
          className: 'text-center',
          render: function (data) {
            if (!data) return '-';
            // Data attendue au format "YYYY-MM-DD HH:MM:SS"
            const parts = String(data).split(' ');
            if (parts.length !== 2) return data;
            const d = parts[0].split('-').map(x => parseInt(x, 10));
            const t = parts[1].split(':').map(x => parseInt(x, 10));
            if (d.length !== 3 || t.length < 2) return data;
            const dt = new Date(d[0], d[1] - 1, d[2], t[0], t[1], t[2] || 0);
            return dt.toLocaleString('fr-FR', {
              day: '2-digit',
              month: 'long',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
              second: '2-digit'
            });
          }
        },
        {                                                      // Utilisateur
          data: null,
          className: 'text-center',
          render: function (row) {
            // Affiche le nom utilisateur si dispo
            return row.nom_utilisateur || row.username || '-';
          }
        },
        { data: 'nom_action', className: 'text-center' },      // Action
        { data: 'nom_table', className: 'text-center' },       // Table
        { data: 'id_concerne', className: 'text-center' },     // ID concerné
        {                                                      // Ancienne valeur
          data: 'ancienne_valeur',
          orderable: false,
          render: function (txt) {
            if (!txt) return '-';
            const safe = String(txt)
              .replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;');
            return '<pre style="white-space: pre-wrap; margin:0;">' + safe + '</pre>';
          }
        },
        {                                                      // Nouvelle valeur
          data: 'nouvelle_valeur',
          orderable: false,
          render: function (txt) {
            if (!txt) return '-';
            const safe = String(txt)
              .replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;');
            return '<pre style="white-space: pre-wrap; margin:0;">' + safe + '</pre>';
          }
        }
      ],
      order: [[2, 'desc']], // tri par date_heure_ajout
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
        sZeroRecords:    "Aucun enregistrement trouvé",
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
  
    // Ici, pas de boutons d'édition/suppression pour l'historique
    // mais on pourrait ajouter un clic pour afficher les valeurs complètes si besoin
    /*
    $tableEl.on('click', 'tbody tr', function () {
      const data = table.row(this).data();
      // afficher un modal avec data.ancienne_valeur et data.nouvelle_valeur
    });
    */
  });
  