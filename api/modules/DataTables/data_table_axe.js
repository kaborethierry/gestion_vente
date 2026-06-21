$(document).ready(function () {
    $('#tab').DataTable({
 dom: 'Blfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'LEGAL',


               /*customize: function ( doc ) {
                    // Splice the image in after the header, but before the table<font></font>
                    doc.content.splice( 1, 0, {
                    margin: [ 0, 0, 0, 12 ],
                    alignment: 'center',
                    image: 'Projet/ACF/1.1/pages/images/admin.jpg'
                    
                } );
                    
                } */
            },
            
            'copyHtml5',
            'excelHtml5',
            'csvHtml5'
            
            
        ],
    order: [ 1, "asc" ],
    "deferRender": true,
    
    "pageLength": 5,
	"lengthMenu": [ [1, 2, 3, 4, 5, 10, 25, 50, 100, 200, -1], [1, 2, 3, 4, 5, 10, 25, 50, 100, 200, "Tout"] ],
	
    "columnDefs": [ 
        {
            "targets": [0],
            "visible": false
            
        }
        
    ],       
    "language": {
        "sProcessing":     "Traitement en cours...",
        "sSearch":         "Rechercher&nbsp;:",
        "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
        "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
        "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
        "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "Chargement en cours...",
        "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
        "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
        "oPaginate": {
            "sFirst":      "Premier",
            "sPrevious":   "Pr&eacute;c&eacute;dent",
            "sNext":       "Suivant",
            "sLast":       "Dernier"
        },
        "oAria": {
            "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
            "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
        }
    }
} );
} );