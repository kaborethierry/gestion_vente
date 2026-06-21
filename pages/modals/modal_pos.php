<!-- DANFANIMENT POS - Modals pour le système POS -->

<!-- Modal : Ajouter un client -->
<div class="modal fade" id="ajouter_client_modal" tabindex="-1" role="dialog" aria-labelledby="ajouterClientLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #10B981, #059669); color: white;">
        <h5 class="modal-title" id="ajouterClientLabel">
          <i class="fas fa-user-plus"></i> Ajouter un client
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="form-ajout-client" action="../api/modules/ajouter_client_pos.php" method="POST">
        <div class="modal-body">
          <div class="form-group">
            <label for="client_nom">Nom *</label>
            <input type="text" class="form-control" id="client_nom" name="nom" required>
          </div>
          <div class="form-group">
            <label for="client_prenom">Prénom *</label>
            <input type="text" class="form-control" id="client_prenom" name="prenom" required>
          </div>
          <div class="form-group">
            <label for="client_telephone">Téléphone *</label>
            <input type="tel" class="form-control" id="client_telephone" name="telephone" required>
          </div>
          <div class="form-group">
            <label for="client_email">Email</label>
            <input type="email" class="form-control" id="client_email" name="email">
          </div>
          <div class="form-group">
            <label for="client_adresse">Adresse</label>
            <textarea class="form-control" id="client_adresse" name="adresse" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label for="client_ville">Ville</label>
            <input type="text" class="form-control" id="client_ville" name="ville">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-success">Ajouter le client</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : Vente manuelle (produit non référencé) avec champs clients optionnels -->
<div class="modal fade" id="manual_sale_modal" tabindex="-1" role="dialog" aria-labelledby="manualSaleLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #F59E0B, #DC2626); color: white;">
        <h5 class="modal-title" id="manualSaleLabel">
          <i class="fas fa-edit"></i> Vente manuelle
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> Remplissez les informations du produit/service à vendre
        </div>
        
        <div class="form-group">
          <label for="manual_product_name">Nom du produit / service *</label>
          <input type="text" class="form-control" id="manual_product_name" placeholder="Ex: Confection coutume, Tissu spécial, Service de livraison...">
        </div>
        <div class="form-group">
          <label for="manual_product_price">Prix unitaire (CFA) *</label>
          <input type="number" class="form-control" id="manual_product_price" placeholder="0">
        </div>
        <div class="form-group">
          <label for="manual_product_qty">Quantité *</label>
          <input type="number" class="form-control" id="manual_product_qty" value="1" min="1">
        </div>
        <div class="form-group">
          <label for="manual_product_description">Description (optionnel)</label>
          <textarea class="form-control" id="manual_product_description" rows="2" placeholder="Détails supplémentaires..."></textarea>
        </div>
        
        <hr>
        <div class="alert alert-secondary">
          <i class="fas fa-user"></i> <strong>Informations client (optionnel)</strong><br>
          <small>Ces champs sont optionnels - Vous pouvez les laisser vides</small>
        </div>
        
        <div class="form-group">
          <label for="manual_client_nom">Nom complet du client</label>
          <input type="text" class="form-control" id="manual_client_nom" placeholder="Nom et prénom du client (optionnel)">
        </div>
        <div class="form-group">
          <label for="manual_client_telephone">Téléphone</label>
          <input type="tel" class="form-control" id="manual_client_telephone" placeholder="Téléphone (optionnel)">
        </div>
        <div class="form-group">
          <label for="manual_client_email">Email</label>
          <input type="email" class="form-control" id="manual_client_email" placeholder="Email (optionnel)">
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="manual_client_ville">Ville</label>
              <input type="text" class="form-control" id="manual_client_ville" placeholder="Ville (optionnel)">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="manual_client_adresse">Adresse</label>
              <input type="text" class="form-control" id="manual_client_adresse" placeholder="Adresse (optionnel)">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-warning" onclick="addManualProductToCart()">
          <i class="fas fa-cart-plus"></i> Ajouter au panier
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal : Clavier numérique -->
<div class="modal fade" id="numeric_keypad_modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Clavier numérique</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="numeric-keypad text-center">
          <div class="row">
            <div class="col-4"><button class="btn btn-outline-secondary btn-num" data-value="1">1</button></div>
            <div class="col-4"><button class="btn btn-outline-secondary btn-num" data-value="2">2</button></div>
            <div class="col-4"><button class="btn btn-outline-secondary btn-num" data-value="3">3</button></div>
          </div>
          <div class="row">
            <div class="col-4"><button class="btn btn-outline-secondary btn-num" data-value="4">4</button></div>
            <div class="col-4"><button class="btn btn-outline-secondary btn-num" data-value="5">5</button></div>
            <div class="col-4"><button class="btn btn-outline-secondary btn-num" data-value="6">6</button></div>
          </div>
          <div class="row">
            <div class="col-4"><button class="btn btn-outline-secondary btn-num" data-value="7">7</button></div>
            <div class="col-4"><button class="btn btn-outline-secondary btn-num" data-value="8">8</button></div>
            <div class="col-4"><button class="btn btn-outline-secondary btn-num" data-value="9">9</button></div>
          </div>
          <div class="row">
            <div class="col-4"><button class="btn btn-outline-secondary btn-num" data-value="0">0</button></div>
            <div class="col-4"><button class="btn btn-outline-secondary btn-clear">C</button></div>
            <div class="col-4"><button class="btn btn-outline-secondary btn-validate">OK</button></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal : Ticket de vente -->
<div class="modal fade" id="ticket_modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white;">
        <h5 class="modal-title">Ticket de vente</h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body" id="ticket_content" style="font-family: monospace;">
        <!-- Contenu du ticket chargé dynamiquement -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
        <button type="button" class="btn btn-primary" onclick="printTicketContent()">Imprimer</button>
      </div>
    </div>
  </div>
</div>