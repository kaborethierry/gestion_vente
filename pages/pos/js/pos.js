// Variables globales
let cart = [];
let currentPaymentMethod = 'especes';
let currentDiscount = 0;
let currentDiscountType = 'amount';

// Chargement initial
$(document).ready(function() {
    loadCart();
    loadDailyStats();
    loadRecentSales();
    loadPaymentBreakdown();
    
    setInterval(function() {
        loadDailyStats();
        loadRecentSales();
        loadPaymentBreakdown();
    }, 30000);
    
    $(document).on('click', '.btn-num', function() { 
        let currentVal = $('#montant_recu').val();
        $('#montant_recu').val(currentVal + $(this).data('value')); 
        calculateMonnaie(); 
    });
    $(document).on('click', '.btn-clear', function() { 
        $('#montant_recu').val(''); 
        calculateMonnaie(); 
    });
    $(document).on('click', '.btn-validate', function() { 
        $('#numeric_keypad_modal').modal('hide'); 
        processSale(); 
    });
    
    $('#printDetailTicket').on('click', function() {
        var content = $('#saleDetailsContent').html();
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Ticket de vente</title><style>body{font-family:monospace;margin:20px;}table{width:100%;border-collapse:collapse;}th,td{padding:8px;text-align:left;}hr{margin:10px 0;}</style></head><body>' + content + '</body></html>');
        printWindow.document.close();
        printWindow.print();
    });
});



// Fallback si QZ Tray indisponible
function fallbackPrint(id_vente) {
    window.open('../api/modules/imprimer_thermal.php?id_vente=' + id_vente, '_blank', 'width=450,height=650');
}

// Appelé automatiquement après chaque vente// Impression automatique via Python local
function autoPrintTicket(id_vente) {
    if (id_vente && typeof window.imprimerTicketQZ === 'function') {
        window.imprimerTicketQZ(id_vente);
    } else if (id_vente) {
        window.open('../api/modules/imprimer_thermal.php?id_vente=' + id_vente, '_blank', 'width=450,height=650');
    }
}

// Impression du ticket depuis le modal
function printTicketContent() {
    var content = $('#ticket_content').html();
    if (!content || content.trim() === '') {
        Swal.fire('Erreur', 'Aucun ticket à imprimer', 'error');
        return;
    }
    
    var printWindow = window.open('', '_blank', 'width=450,height=650');
    printWindow.document.write('<!DOCTYPE html><html><head><title>Ticket DANFANIMENT</title><style>');
    printWindow.document.write('body{font-family:"Courier New",monospace;margin:20px;font-size:12px;width:80mm;}');
    printWindow.document.write('table{width:100%;border-collapse:collapse;}');
    printWindow.document.write('th,td{padding:5px;text-align:left;}');
    printWindow.document.write('hr{margin:10px 0;}');
    printWindow.document.write('.text-center{text-align:center;}');
    printWindow.document.write('.text-right{text-align:right;}');
    printWindow.document.write('</style></head><body>');
    printWindow.document.write(content);
    printWindow.document.write('<script>window.onload=function(){window.print();setTimeout(function(){window.close();},500);};<\/script>');
    printWindow.document.write('</body></html>');
    printWindow.document.close();
}

function loadDailyStats() {
    if (typeof sessionActive === 'undefined' || !sessionActive) return;
    
    $.ajax({
        url: '../api/modules/pos_data.php',
        type: 'GET',
        data: { action: 'get_daily_stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#total_encaissements_jour').text(formatNumber(response.total_encaissements) + ' CFA');
                $('#nb_ventes_jour').text(response.nb_ventes + ' vente(s)');
                $('#total_depenses_jour').text(formatNumber(response.total_depenses) + ' CFA');
                $('#nb_depenses_jour').text(response.nb_depenses + ' dépense(s)');
                $('#solde_caisse').text(formatNumber(response.solde_caisse) + ' CFA');
            }
        },
        error: function() {
            console.error('Erreur chargement statistiques');
        }
    });
}

function loadPaymentBreakdown() {
    if (typeof sessionActive === 'undefined' || !sessionActive) return;
    
    $.ajax({
        url: '../api/modules/pos_data.php',
        type: 'GET',
        data: { action: 'get_payment_breakdown' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#total_especes').text(formatNumber(response.especes.total) + ' CFA');
                $('#nb_especes').text(response.especes.nb + ' vente(s)');
                $('#total_mobile_money').text(formatNumber(response.mobile_money.total) + ' CFA');
                $('#nb_mobile_money').text(response.mobile_money.nb + ' vente(s)');
                $('#total_carte').text(formatNumber(response.carte.total) + ' CFA');
                $('#nb_carte').text(response.carte.nb + ' vente(s)');
                $('#total_virement').text(formatNumber(response.virement.total) + ' CFA');
                $('#nb_virement').text(response.virement.nb + ' vente(s)');
            }
        },
        error: function() {
            console.error('Erreur chargement répartition paiements');
        }
    });
}

function loadRecentSales() {
    if (typeof sessionActive === 'undefined' || !sessionActive) return;
    
    $.ajax({
        url: '../api/modules/pos_data.php',
        type: 'GET',
        data: { action: 'get_recent_sales' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.sales.length > 0) {
                let html = '';
                response.sales.forEach(function(sale) {
                    html += '<tr>';
                    html += '<td><strong>' + escapeHtml(sale.numero_vente) + '</strong></td>';
                    html += '<td>' + (sale.client_nom ? escapeHtml(sale.client_nom) : '<span class="text-muted">Anonyme</span>') + '</td>';
                    html += '<td class="text-right"><strong>' + formatNumber(sale.total_ttc) + ' CFA</strong></td>';
                    html += '<td>' + getPaymentIcon(sale.mode_paiement) + ' ' + getPaymentLabel(sale.mode_paiement) + '</td>';
                    html += '<td>' + sale.date_heure + '</td>';
                    html += '<td>' + escapeHtml(sale.caissier) + '</td>';
                    html += '<td class="text-center">';
                    html += '<div class="btn-group btn-group-sm">';
                    html += '<button class="btn btn-info" onclick="viewSaleDetails(' + sale.id_vente + ')" title="Voir détails"><i class="fas fa-eye"></i></button>';
                    html += '<button class="btn btn-secondary" onclick="printSaleTicket(' + sale.id_vente + ')" title="Imprimer ticket"><i class="fas fa-print"></i></button>';
                    html += '<button class="btn btn-warning" onclick="editSale(' + sale.id_vente + ')" title="Modifier"><i class="fas fa-edit"></i></button>';
                    html += '<button class="btn btn-danger" onclick="deleteSale(' + sale.id_vente + ')" title="Supprimer"><i class="fas fa-trash"></i></button>';
                    html += '</div>';
                    html += '概';
                    html += '</tr>';
                });
                $('#recent-sales-tbody').html(html);
            } else {
                $('#recent-sales-tbody').html('<tr><td colspan="7" class="text-center text-muted">Aucune vente aujourd\'hui<\/td><\/tr>');
            }
        },
        error: function() {
            $('#recent-sales-tbody').html('<tr><td colspan="7" class="text-center text-danger">Erreur de chargement<\/td><\/tr>');
        }
    });
}

function getPaymentIcon(method) {
    const icons = {
        'especes': '<i class="fas fa-money-bill-wave text-success"></i>',
        'carte': '<i class="fas fa-credit-card text-primary"></i>',
        'mobile_money': '<i class="fas fa-mobile-alt text-warning"></i>',
        'virement': '<i class="fas fa-university text-info"></i>'
    };
    return icons[method] || '<i class="fas fa-question-circle"></i>';
}

function getPaymentLabel(method) {
    const labels = {
        'especes': 'Espèces',
        'carte': 'Carte',
        'mobile_money': 'Mobile Money',
        'virement': 'Virement'
    };
    return labels[method] || method;
}

function viewSaleDetails(id_vente) {
    $.ajax({
        url: '../api/modules/vente_details.php',
        type: 'GET',
        data: { id_vente: id_vente },
        dataType: 'html',
        success: function(html) {
            $('#saleDetailsContent').html(html);
            $('#saleDetailsModal').modal('show');
        },
        error: function() {
            Swal.fire('Erreur', 'Impossible de charger les détails', 'error');
        }
    });
}

function printSaleTicket(id_vente) {
    if (typeof window.imprimerTicketQZ === 'function') {
        window.imprimerTicketQZ(id_vente);
    } else {
        window.open('../api/modules/imprimer_thermal.php?id_vente=' + id_vente, '_blank');
    }
}

function editSale(id_vente) {
    Swal.fire({
        title: 'Modifier la vente ?',
        text: 'Cette action annulera la vente actuelle. Vous pourrez ensuite recréer une nouvelle vente.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Oui, annuler',
        cancelButtonText: 'Non',
        confirmButtonColor: '#F59E0B'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../api/modules/annuler_vente.php?action=annuler_vente&id_vente=' + id_vente;
        }
    });
}

function deleteSale(id_vente) {
    Swal.fire({
        title: 'Supprimer la vente ?',
        text: 'Cette action est irréversible !',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#DC2626'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../api/modules/supprimer_vente.php?id_vente=' + id_vente;
        }
    });
}

function showManualSaleModal() {
    if (typeof sessionActive === 'undefined' || !sessionActive) {
        Swal.fire('Session requise', 'Veuillez ouvrir une session de caisse', 'warning');
        return;
    }
    $('#manual_product_name').val('');
    $('#manual_product_price').val('');
    $('#manual_product_qty').val('1');
    $('#manual_product_description').val('');
    $('#manual_client_nom').val('');
    $('#manual_client_telephone').val('');
    $('#manual_client_email').val('');
    $('#manual_client_ville').val('');
    $('#manual_client_adresse').val('');
    $('#manual_sale_modal').modal('show');
}

function addManualProductToCart() {
    let nom = $('#manual_product_name').val().trim();
    let prix = parseFloat($('#manual_product_price').val());
    let quantite = parseInt($('#manual_product_qty').val());
    let description = $('#manual_product_description').val().trim();
    
    let clientNom = $('#manual_client_nom').val().trim();
    let clientTelephone = $('#manual_client_telephone').val().trim();
    let clientEmail = $('#manual_client_email').val().trim();
    let clientVille = $('#manual_client_ville').val().trim();
    let clientAdresse = $('#manual_client_adresse').val().trim();
    
    if (!nom) {
        Swal.fire('Erreur', 'Veuillez saisir le nom du produit/service', 'warning');
        return;
    }
    if (isNaN(prix) || prix <= 0) {
        Swal.fire('Erreur', 'Veuillez saisir un prix valide', 'warning');
        return;
    }
    if (isNaN(quantite) || quantite < 1) {
        Swal.fire('Erreur', 'La quantité doit être au moins 1', 'warning');
        return;
    }
    
    let nomComplet = nom;
    if (description) {
        nomComplet += ' (' + description + ')';
    }
    
    let clientInfo = {};
    if (clientNom) clientInfo.nom_client = clientNom;
    if (clientTelephone) clientInfo.telephone_client = clientTelephone;
    if (clientEmail) clientInfo.email_client = clientEmail;
    if (clientVille) clientInfo.ville_client = clientVille;
    if (clientAdresse) clientInfo.adresse_client = clientAdresse;
    
    cart.push({
        id_produit: 4,
        code_produit: 'MANUAL',
        nom: nomComplet,
        prix_vente: prix,
        quantite: quantite,
        stock_max: 999999,
        is_manual: true,
        client_info: clientInfo
    });
    
    saveCart();
    updateCartDisplay();
    $('#manual_sale_modal').modal('hide');
    
    Swal.fire('Ajouté', 'Produit ajouté au panier', 'success');
}

function updateCartDisplay() {
    if (cart.length === 0) {
        $('#cart-items').html('<div class="text-center text-muted py-5"><i class="fas fa-shopping-cart fa-4x mb-3"></i><p>Panier vide</p></div>');
        updateTotals();
        return;
    }
    
    let html = '';
    let subtotal = 0;
    
    cart.forEach(function(item, index) {
        let total = item.prix_vente * item.quantite;
        subtotal += total;
        
        let itemClass = item.is_manual ? 'manual-sale-item' : '';
        
        html += '<div class="cart-item row align-items-center ' + itemClass + '" style="padding: 12px; margin-bottom: 8px; border-radius: 8px;">';
        html += '<div class="col-12 col-md-5">';
        html += '<strong>' + escapeHtml(item.nom) + '</strong><br>';
        html += '<small class="text-muted">' + formatNumber(item.prix_vente) + ' CFA/unité</small>';
        
        if (item.client_info && Object.keys(item.client_info).length > 0) {
            html += '<br><small class="text-info"><i class="fas fa-user"></i> ';
            if (item.client_info.nom_client) html += item.client_info.nom_client;
            if (item.client_info.telephone_client) html += ' - ' + item.client_info.telephone_client;
            html += '</small>';
        }
        html += '</div>';
        
        html += '<div class="col-12 col-md-4 mt-2 mt-md-0">';
        html += '<div class="input-group input-group-sm">';
        html += '<div class="input-group-prepend"><button class="btn btn-outline-secondary" onclick="updateQuantity(' + index + ', ' + (item.quantite - 1) + ')"><i class="fas fa-minus"></i></button></div>';
        html += '<input type="number" class="form-control text-center" value="' + item.quantite + '" min="1" max="' + item.stock_max + '" onchange="updateQuantity(' + index + ', this.value)" style="max-width: 70px;">';
        html += '<div class="input-group-append"><button class="btn btn-outline-secondary" onclick="updateQuantity(' + index + ', ' + (item.quantite + 1) + ')"><i class="fas fa-plus"></i></button></div>';
        html += '</div></div>';
        
        html += '<div class="col-12 col-md-2 text-right mt-2 mt-md-0"><strong>' + formatNumber(total) + ' CFA</strong></div>';
        html += '<div class="col-12 col-md-1 text-right mt-2 mt-md-0"><button class="btn btn-sm btn-danger" onclick="removeFromCart(' + index + ')"><i class="fas fa-trash"></i></button></div>';
        html += '</div>';
    });
    
    $('#cart-items').html(html);
    updateTotals();
}

function updateQuantity(index, newQuantity) {
    newQuantity = parseInt(newQuantity);
    if (isNaN(newQuantity) || newQuantity < 1) newQuantity = 1;
    if (newQuantity > cart[index].stock_max) {
        Swal.fire('Limite atteinte', 'Quantité maximale: ' + cart[index].stock_max, 'warning');
        newQuantity = cart[index].stock_max;
    }
    cart[index].quantite = newQuantity;
    saveCart();
    updateCartDisplay();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    saveCart();
    updateCartDisplay();
}

function clearCart() {
    if (cart.length === 0) {
        Swal.fire('Panier vide', 'Aucun article à supprimer', 'info');
        return;
    }
    Swal.fire({
        title: 'Vider le panier ?',
        text: 'Tous les articles seront supprimés',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, vider',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            cart = [];
            saveCart();
            updateCartDisplay();
        }
    });
}

function updateTotals() {
    let subtotal = cart.reduce((sum, item) => sum + (item.prix_vente * item.quantite), 0);
    let discountAmount = 0;
    if (currentDiscountType === 'percentage') {
        discountAmount = subtotal * (currentDiscount / 100);
    } else {
        discountAmount = currentDiscount;
    }
    let total = Math.max(0, subtotal - discountAmount);
    
    $('#subtotal').text(formatNumber(subtotal));
    $('#total').text(formatNumber(total));
    
    if (currentPaymentMethod === 'especes') calculateMonnaie();
}

function updateTotal() {
    currentDiscount = parseFloat($('#discount-input').val()) || 0;
    currentDiscountType = $('#discount-type').val();
    if (currentDiscountType === 'percentage' && currentDiscount > 100) {
        currentDiscount = 100;
        $('#discount-input').val(100);
    }
    updateTotals();
}

function selectPayment(method) {
    currentPaymentMethod = method;
    $('.payment-method').removeClass('active');
    $(`.payment-method[data-method="${method}"]`).addClass('active');
    if (method === 'especes') {
        $('#especes-details').show();
    } else {
        $('#especes-details').hide();
    }
}

function calculateMonnaie() {
    let montantRecu = parseFloat($('#montant_recu').val()) || 0;
    let total = parseFloat($('#total').text().replace(/\s/g, '')) || 0;
    $('#monnaie_rendue').val(formatNumber(Math.max(0, montantRecu - total)));
}

function processSale() {
    if (typeof sessionActive === 'undefined' || !sessionActive) {
        Swal.fire('Session requise', 'Veuillez ouvrir une session de caisse', 'warning');
        return;
    }
    if (cart.length === 0) {
        Swal.fire('Panier vide', 'Ajoutez des produits au panier', 'warning');
        return;
    }
    
    let total = parseFloat($('#total').text().replace(/\s/g, '')) || 0;
    if (currentPaymentMethod === 'especes') {
        let montantRecu = parseFloat($('#montant_recu').val()) || 0;
        if (montantRecu < total) {
            Swal.fire('Montant insuffisant', 'Le montant reçu est inférieur au total', 'error');
            return;
        }
    }
    
    let clientId = $('#client-id').val() || '';
    
    Swal.fire({
        title: 'Confirmation',
        text: 'Valider cette vente de ' + formatNumber(total) + ' CFA ?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Oui, valider',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#10B981'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Traitement...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            
            let formData = new FormData();
            formData.append('cart', JSON.stringify(cart));
            formData.append('payment_method', currentPaymentMethod);
            formData.append('discount', currentDiscount);
            formData.append('discount_type', currentDiscountType);
            formData.append('montant_recu', $('#montant_recu').val() || 0);
            formData.append('reference_transaction', $('#reference-transaction').val());
            formData.append('client_id', clientId);
            
            $.ajax({
                url: '../api/modules/ajouter_pos.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        cart = [];
                        saveCart();
                        updateCartDisplay();
                        $('#montant_recu, #reference-transaction, #discount-input').val('');
                        currentDiscount = 0;
                        loadDailyStats();
                        loadRecentSales();
                        loadPaymentBreakdown();
                        
                        Swal.fire('Succès !', 'Vente effectuée !<br>N°: ' + response.numero_vente, 'success');
                        
                        autoPrintTicket(response.id_vente);
                        
                        if (response.ticket_html) {
                            $('#ticket_content').html(response.ticket_html);
                            $('#ticket_modal').modal('show');
                        }
                    } else {
                        Swal.fire('Erreur', response.message || 'Erreur lors de la vente', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    let errorMsg = xhr.responseJSON?.message || 'Erreur lors de la validation';
                    Swal.fire('Erreur', errorMsg, 'error');
                }
            });
        }
    });
}

function searchClient() {
    let query = $('#client-search').val();
    if (query.length < 2) {
        Swal.fire('Recherche', 'Entrez au moins 2 caractères', 'info');
        return;
    }
    
    $.ajax({
        url: '../api/modules/pos_data.php',
        type: 'GET',
        data: { action: 'search_client', query: query },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.clients && response.clients.length > 0) {
                let inputOptions = {};
                response.clients.forEach((client, index) => {
                    inputOptions[index] = client.nom_complet + ' - ' + client.telephone + (client.points_fidelite ? ' (' + client.points_fidelite + ' pts)' : '');
                });
                
                Swal.fire({
                    title: 'Sélectionnez un client',
                    input: 'select',
                    inputOptions: inputOptions,
                    showCancelButton: true,
                    confirmButtonText: 'Sélectionner',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed && result.value !== undefined) {
                        selectClient(response.clients[result.value]);
                    }
                });
            } else {
                Swal.fire({
                    title: 'Aucun client',
                    text: 'Voulez-vous en créer un ?',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Créer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) showAddClientModal();
                });
            }
        },
        error: function() {
            Swal.fire('Erreur', 'Erreur lors de la recherche', 'error');
        }
    });
}

function selectClient(client) {
    $('#client-id').val(client.id_client);
    $('#client-nom').text(client.nom_complet);
    $('#client-points').text(client.points_fidelite || 0);
    $('#client-info').show();
    $('#client-search').val('');
    Swal.fire('Client sélectionné', client.nom_complet, 'success');
}

function clearClient() {
    $('#client-id').val('');
    $('#client-info').hide();
    Swal.fire('Client retiré', 'Le client a été retiré', 'info');
}

function showAddClientModal() {
    $('#form-ajout-client')[0].reset();
    $('#ajouter_client_modal').modal('show');
}

function quickSale() {
    if (typeof sessionActive === 'undefined' || !sessionActive) {
        Swal.fire('Session requise', 'Veuillez ouvrir une session de caisse', 'warning');
        return;
    }
    let total = parseFloat($('#total').text().replace(/\s/g, '')) || 0;
    if (total > 0) {
        $('#montant_recu').val(total);
        calculateMonnaie();
        selectPayment('especes');
        processSale();
    } else {
        showManualSaleModal();
    }
}

function openNumerique() {
    $('#numeric_keypad_modal').modal('show');
}

function saveCart() {
    $.ajax({ url: '../api/modules/save_cart.php', type: 'POST', data: { cart: JSON.stringify(cart) }, async: false });
}

function loadCart() {
    $.ajax({ url: '../api/modules/load_cart.php', type: 'GET', dataType: 'json', async: false, success: function(response) { if (response.cart) { cart = response.cart; updateCartDisplay(); } } });
}

function formatNumber(n) { 
    return n.toLocaleString('fr-FR'); 
}

function escapeHtml(t) { 
    if (!t) return ''; 
    return t.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); 
}

function openCaissePage() { 
    window.location.href = 'caisse.php'; 
}