$(document).ready(function () {
  var table = $('#dataTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '../api/modules/client_data.php',
      type: 'GET',
      dataType: 'json',
      error: function (xhr, status, error) {
        console.error('DataTables AJAX Error:', status, error);
      }
    },
    dom: 'Blfrtip',
    buttons: [
      { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL', title: 'Liste_des_clients' },
      'copyHtml5',
      'excelHtml5',
      'csvHtml5'
    ],
    columns: [
      { data: 'id_client' },
      { 
        data: null,
        render: function(data, type, row) {
          return '<div class="client-avatar d-inline-flex align-items-center justify-content-center">' + 
                 (row.prenom ? row.prenom.charAt(0).toUpperCase() : '') + (row.nom ? row.nom.charAt(0).toUpperCase() : '') +
                 '</div> ' + row.nom + ' ' + row.prenom;
        }
      },
      { data: 'telephone' },
      { data: 'ville' },
      { 
        data: 'total_depense',
        visible: false,  // ✅ Colonne masquée
        render: function(data) {
          return new Intl.NumberFormat('fr-FR').format(data || 0) + ' FCFA';
        }
      },
      { data: 'nombre_visites' },
      { 
        data: 'date_derniere_visite',
        render: function(data) {
          if (!data || data === '0000-00-00') return 'Jamais';
          var date = new Date(data);
          if (isNaN(date.getTime())) return 'Jamais';
          return date.toLocaleDateString('fr-FR');
        }
      },
      {
        data: null,
        orderable: false,
        render: function (_data, _type, row) {
          return '<button type="button" class="btn btn-info btn-sm view-profil-btn" data-id="' + row.id_client + '">' +
                    '<i class="fas fa-eye"></i> Voir' +
                  '</button>';
        }
      },
      {
        data: null,
        orderable: false,
        render: function (_data, _type, row) {
          return '<button type="button" class="btn btn-warning btn-sm edit-client-btn" data-id="' + row.id_client + '">' +
                    '<i class="fas fa-pencil-alt"></i> Modifier' +
                  '</button>';
        }
      },
      {
        data: null,
        orderable: false,
        render: function (_data, _type, row) {
          return '<button type="button" class="btn btn-danger btn-sm delete-client-btn" data-id="' + row.id_client + '">' +
                    '<i class="fas fa-trash"></i> Supprimer' +
                  '</button>';
        }
      }
    ],
    order: [[0, 'desc']],
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Tout']],
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

  function loadClientDataForEdit(id) {
    $.ajax({
      url: '../api/modules/get_client.php',
      type: 'GET',
      data: { id: id },
      dataType: 'json',
      success: function(data) {
        if (data.success) {
          $('#id_client_modif').val(data.client.id_client);
          $('#nom_modif').val(data.client.nom);
          $('#prenom_modif').val(data.client.prenom);
          $('#telephone_modif').val(data.client.telephone);
          $('#email_modif').val(data.client.email || '');
          $('#adresse_modif').val(data.client.adresse || '');
          $('#ville_modif').val(data.client.ville || '');
          $('#notes_modif').val(data.client.notes || '');
          $('#modifier_client').modal('show');
        } else {
          Swal.fire('Erreur', data.error || 'Impossible de charger les données du client', 'error');
        }
      },
      error: function() {
        Swal.fire('Erreur', 'Erreur lors du chargement des données', 'error');
      }
    });
  }

  function loadClientDataForProfil(id) {
    $.ajax({
      url: '../api/modules/get_client.php',
      type: 'GET',
      data: { id: id },
      dataType: 'json',
      success: function(data) {
        if (data.success) {
          $('#profil_nom_complet').text(data.client.nom + ' ' + data.client.prenom);
          $('#profil_telephone').text(data.client.telephone);
          $('#profil_email').text(data.client.email || 'Non renseigné');
          $('#profil_adresse').text(data.client.adresse || 'Non renseignée');
          $('#profil_ville').text(data.client.ville || 'Non renseignée');
          $('#profil_total_depense').text(new Intl.NumberFormat('fr-FR').format(data.client.total_depense || 0) + ' FCFA');
          $('#profil_nb_visites').text(data.client.nombre_visites || 0);
          
          var derniereVisite = 'Jamais';
          if (data.client.date_derniere_visite && data.client.date_derniere_visite !== '0000-00-00 00:00:00') {
            var date = new Date(data.client.date_derniere_visite);
            if (!isNaN(date.getTime())) {
              derniereVisite = date.toLocaleDateString('fr-FR');
            }
          }
          $('#profil_date_derniere_visite').text(derniereVisite);
          $('#profil_points_fidelite').text(data.client.points_fidelite || 0);
          $('#profil_notes').text(data.client.notes || 'Aucune note');
          
          chargerMesures(id);
          chargerConfections(id);
          chargerAchats(id);
        } else {
          Swal.fire('Erreur', data.error || 'Impossible de charger les données du client', 'error');
        }
      },
      error: function() {
        Swal.fire('Erreur', 'Erreur lors du chargement des données', 'error');
      }
    });
  }

  function chargerMesures(idClient) {
    $.ajax({
      url: '../api/modules/get_mesures_client.php',
      type: 'GET',
      data: { id_client: idClient },
      dataType: 'json',
      success: function(data) {
        if (data.success && data.mesures && data.mesures.length > 0) {
          var html = '';
          data.mesures.forEach(function(m) {
            html += '<tr>';
            html += '<td>' + (m.date_mesure_formatee || '-') + '</td>';
            html += '<td>' + (m.tour_cou || '-') + '</td>';
            html += '<td>' + (m.largeur_epaule || '-') + '</td>';
            html += '<td>' + (m.tour_poitrine || '-') + '</td>';
            html += '<td>' + (m.tour_taille || '-') + '</td>';
            html += '<td>' + (m.tour_hanches || '-') + '</td>';
            html += '<td>' + (m.longueur_dos || '-') + '</td>';
            html += '<td>' + (m.longueur_bras || '-') + '</td>';
            html += '<td>' + (m.longueur_manche || '-') + '</td>';
            html += '<td>' + (m.longueur_totale_tenue || '-') + '</td>';
            html += '<td>' + (m.hauteur_totale || '-') + '</td>';
            html += '<td>' + (m.poids || '-') + '</td>';
            html += '</tr>';
          });
          $('#mesures-tbody').html(html);
        } else {
          $('#mesures-tbody').html('<tr><td colspan="12" class="text-center text-muted">Aucune mesure enregistrée</td></tr>');
        }
      },
      error: function() {
        $('#mesures-tbody').html('<tr><td colspan="12" class="text-center text-danger">Erreur de chargement</td></tr>');
      }
    });
  }

  function chargerConfections(idClient) {
    $.ajax({
      url: '../api/modules/get_client.php',
      type: 'GET',
      data: { id: idClient },
      dataType: 'json',
      success: function(data) {
        if (data.success && data.client.commandes_confection && data.client.commandes_confection.length > 0) {
          var html = '';
          data.client.commandes_confection.forEach(function(c) {
            var statutClass = '';
            switch(c.statut) {
              case 'en_attente': statutClass = 'badge-warning'; break;
              case 'en_cours': statutClass = 'badge-info'; break;
              case 'termine': statutClass = 'badge-success'; break;
              case 'livre': statutClass = 'badge-primary'; break;
              case 'annule': statutClass = 'badge-danger'; break;
              default: statutClass = 'badge-secondary';
            }
            
            var dateLivraison = '-';
            if (c.date_livraison_prevue && c.date_livraison_prevue !== '0000-00-00') {
              var d = new Date(c.date_livraison_prevue);
              if (!isNaN(d.getTime())) {
                dateLivraison = d.toLocaleDateString('fr-FR');
              }
            }
            
            html += '<tr>';
            html += '<td>' + (c.numero_commande || '-') + '</td>';
            html += '<td>' + (c.type_tenue || '-') + '</td>';
            html += '<td>' + (c.montant_total || 0).toLocaleString() + ' FCFA</td>';
            html += '<td>' + (c.montant_avance || 0).toLocaleString() + ' FCFA</td>';
            html += '<td>' + ((c.total_paye || 0)).toLocaleString() + ' FCFA</td>';
            html += '<td>' + ((c.solde_restant_calcule || 0)).toLocaleString() + ' FCFA</td>';
            html += '<td><span class="badge ' + statutClass + '">' + c.statut + '</span></td>';
            html += '<td>' + (c.prestataire_nom ? c.prestataire_nom + ' ' + (c.prestataire_prenom || '') : '-') + '</td>';
            html += '<td>' + dateLivraison + '</td>';
            html += '</tr>';
          });
          $('#confections-tbody').html(html);
        } else {
          $('#confections-tbody').html('<tr><td colspan="9" class="text-center text-muted">Aucune commande de confection</td></tr>');
        }
      },
      error: function() {
        $('#confections-tbody').html('<tr><td colspan="9" class="text-center text-danger">Erreur de chargement</td></tr>');
      }
    });
  }

  function chargerAchats(idClient) {
    $.ajax({
      url: '../api/modules/get_client.php',
      type: 'GET',
      data: { id: idClient },
      dataType: 'json',
      success: function(data) {
        if (data.success && data.client.historique_achats && data.client.historique_achats.length > 0) {
          var html = '';
          data.client.historique_achats.forEach(function(a) {
            var dateVente = '-';
            if (a.date_vente && a.date_vente !== '0000-00-00 00:00:00') {
              var d = new Date(a.date_vente);
              if (!isNaN(d.getTime())) {
                dateVente = d.toLocaleDateString('fr-FR');
              }
            }
            html += '<tr>';
            html += '<td>' + (a.numero_vente || '-') + '</td>';
            html += '<td>' + dateVente + '</td>';
            html += '<td>' + (a.total_ttc || 0).toLocaleString() + ' FCFA</td>';
            html += '<td>' + (a.mode_paiement || '-') + '</td>';
            html += '<td>' + (a.caissier || '-') + '</td>';
            html += '</tr>';
          });
          $('#achats-tbody').html(html);
          $('#total_achats').text((data.client.total_achats || 0).toLocaleString() + ' FCFA');
        } else {
          $('#achats-tbody').html('<tr><td colspan="5" class="text-center text-muted">Aucun achat enregistré</td></tr>');
          $('#total_achats').text('0 FCFA');
        }
      },
      error: function() {
        $('#achats-tbody').html('<tr><td colspan="5" class="text-center text-danger">Erreur de chargement</td></tr>');
      }
    });
  }

  var currentClientId = null;

  window.ajouterMesure = function() {
    if (currentClientId) {
      $('#mesure_id_client').val(currentClientId);
      $('#mesure_id_mesure').val('');
      $('#form_mesure')[0].reset();
      $('#modal_mesure').modal('show');
    }
  };

  $('#form_mesure').on('submit', function(e) {
    e.preventDefault();
    
    var formData = $(this).serialize();
    
    $.ajax({
      url: '../api/modules/save_mesure_client.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          Swal.fire('Succès', 'Mesures enregistrées avec succès', 'success');
          $('#modal_mesure').modal('hide');
          if (currentClientId) {
            chargerMesures(currentClientId);
          }
        } else {
          Swal.fire('Erreur', response.error || 'Erreur lors de l\'enregistrement', 'error');
        }
      },
      error: function() {
        Swal.fire('Erreur', 'Erreur réseau', 'error');
      }
    });
  });

  $('#dataTable tbody').on('click', '.edit-client-btn', function() {
    var id = $(this).data('id');
    loadClientDataForEdit(id);
  });

  $('#dataTable tbody').on('click', '.delete-client-btn', function() {
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
        window.location.href = '../api/modules/supprimer_client.php?id_client=' + id;
      }
    });
  });

  $('#dataTable tbody').on('click', '.view-profil-btn', function() {
    var id = $(this).data('id');
    currentClientId = id;
    loadClientDataForProfil(id);
    $('#voir_profil_client').modal('show');
  });
});