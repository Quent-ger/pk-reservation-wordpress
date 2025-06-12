<?php 
// Permet à Wordpress de retrouver et charger ses fichiers - protège d'un accès direct au plugin
if ( ! defined( 'ABSPATH' ) ) {
    exit; // engage la fin du script si on accède directement au fichier
}

 function create_pk_reservation_form_page() { // fonction por créer la page formulaire, sera appelé par le hook à l'activatoin du plugin
       
    $pk_reservation_form_page_args = array( // tableau qui définit les options du type de post (page)
        'post_name' => 'pk_formulaire-reservation',
        'post_title' => 'Ma réservation Photokit',
        'post_content' => '[pk_reservation_form]', // le contenu sera le shortcode indiqué
        'post_status' => 'publish',
        'post_type' => 'page', // IMPORTANT sinon c'est un article
        'comment_status' => 'closed',
        'ping_status' => 'closed',
    );

    // On veut que notre page soit créée seulement si elle n'existe pas déjà
    $pk_reversation_form_exists = get_page_by_path('pk_formulaire-reservation');  // on récupère le slug et on l'assigne à une variable 
        if (! $pk_reversation_form_exists) { // vérif conditionelle du slug
            $pk_reservation_form_id = wp_insert_post($pk_reservation_form_page_args); // si elle n'existe pas, création, et retour de l'id donné par wp_insert_post stocké dans une variable

            if ( ! is_wp_error($pk_reservation_form_id)){ // si l'ID de la page ne retourne pas d'erreur, celle-ci existe
            update_option('pk_formulaire_reservation_id', $pk_reservation_form_id); // Permet la créatoin et la mise à jour de l'ID dans la BDD
            }
        }       
}

function deactivate_pk_reservation_form_page(){ // fonction pour la suppression de la page à la désinstallation du plugin - utilse pour éviter l'encombrement
    $pk_reservation_page_exists = get_option('pk_formulaire_reservation_id'); // on récupère l'ID qui a été donné à la page
    if ($pk_reservation_page_exists) {  // si cette page existe
        wp_delete_post($pk_reservation_page_exists, true); // on la supprime, true indique une suppression définitive, pas une mise en corbeille
        delete_option('pk_formulaire_reservation_id'); // on enlève l'ID de la base de données 
    } 
}

// Le code du formulaire 

function pk_formulaire(){

    if(current_user_can('create_pk_reservations')){ // vérification que le client ait bien la permission pour afficher le formulaire
        
        
        // Récupération des erreurs et des données transmises via les transients
        $errors = get_transient('pk_form_errors');
        $old_data = get_transient('pk_form_data');

        // Supprimer les transients après les avoir lus 
        delete_transient('pk_form_errors');
        delete_transient('pk_form_data');

        // S'assurer que $errors et $old_data sont bien des tableaux même s'il n'y-a pas de transients
        if ( ! is_array( $errors ) ) {
        $errors = [];
        }

        if ( ! is_array( $old_data ) ) {
        $old_data = [];
        }
      
      
        // Initialisation de variables pour récupérer les infos du compte, si non renseignées : vides, elles pourront être renseignées dans le formulaire
        $customer_first_name = ''; 
        $customer_last_name = ''; 
        $customer_email = ''; 
        
        $current_user = wp_get_current_user(); // récupération de l'objet utilisateur en cours
        $customer_first_name = $current_user->user_firstname; // méthode des objets pour récupérer les valeurs
        $customer_last_name = $current_user->user_lastname;
        $customer_email = $current_user->user_email;

        // pour la logique des dates (simplifiée)

        $min_date = date('Y-m-d');
        $min_date_plus_one_year =  date('Y-m-d', strtotime('+1 year')); // limite générique max d'un an dans le futur 


        ob_start(); // début de la mémoire tampon -- HTML plus lisible
    ?>

  <form id="pk-reservation-form" method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); // admin post script spécial WP pour traiter formulaires non-public ?>">

    <fieldset>
        <legend>Informations personnelles</legend>

        <p> <?php 
            // name nécessaire pour récupérer les données par la suite, id doit être lié au for du label, préremplissage avec les données de compte, autocomplete pour l'UX ?>
            <label for="pk_customer_firstname">Prénom <small>*</small> :   </label>
            <input type="text" name="pk_customer_firstname" id="pk_customer_firstname" placeholder="Votre prénom" value="<?php echo esc_attr($customer_first_name); ?>" autocomplete="given-name" required maxlength="35">
            
            <?php
            // Affichage d'un message d'erreur si une erreur a été détectée pour ce champ avec l'association clé-paire de $errors
            if ( isset( $errors['pk_customer_firstname'] ) ) :  ?> 
            <span class="error-message"><?php echo esc_html( $errors['pk_customer_firstname'] ); ?></span>
            <?php endif; // fin condition et retour au HTML ?>    
        </p>

        <p>
            <label for="pk_customer_lastname">Nom  <small>*</small> :  </label>
            <input type="text" name="pk_customer_lastname" id="pk_customer_lastname" placeholder="Votre nom" 
            value="<?php 
            // Pré-remplissage avec la valeur dans $old_data ou nom de famille WP si renseigné  
            echo esc_attr(isset($old_data['pk_customer_lastname']) ? $old_data['pk_customer_lastname'] : $customer_last_name ) ?>" autocomplete="family-name" required maxlength="50">
            
            <?php 
            // Affichage d'un message d'erreur si une erreur a été détectée pour ce champ avec l'association clé-paire de $errors
            if ( isset( $errors['pk_customer_lastname'] ) ) : ?>
            <span class="error-message"><?php echo esc_html( $errors['pk_customer_lastname'] ); ?></span>
            <?php endif; // fin condition et retour au HTML ?>
        </p>

        <p>
            <label for="pk_customer_company">Société <span> (optionnel) </span> <small>*</small> : </label>
            <input type="text" name="pk_customer_company" id="pk_customer_company" placeholder="Société ou association" 
            value="<?php
            //  Pré-remplissage avec la valeur dans $old_data sinon vide 
            echo esc_attr( isset( $old_data['pk_customer_company'] ) ? $old_data['pk_customer_company'] : '' ); ?>"
            autocomplete="organization">
        </p>

    </fieldset>

    <fieldset>
        <legend>Coordonnées</legend>

        <p>
            <label for="pk_customer_email"> Email <small>*</small> :  </label>
            <input type="email" id="pk_customer_email" name="pk_customer_email" placeholder="adresse@messagerie.fr" 
            value="<?php
            // Préremplissage avec la valeur email dans $old_data sinon email du compte WP si renseigné
            echo esc_attr( isset( $old_data['pk_customer_email']) ? $old_data['pk_customer_email'] : $customer_email); ?>" autocomplete="email" required>
            
            <?php 
            // Affichage d'un message d'erreur si une erreur a été détectée pour ce champ avec l'association clé-paire de $errors
            if ( isset( $errors['pk_customer_email'] ) ) : ?>
            <span class="error-message"><?php echo esc_html( $errors['pk_customer_email'] ); ?></span>
            <?php endif; // fin conditoin et retour au HTML ?>
        </p>

        <p>
            <label for="pk_customer_telephone"> Numéro de téléphone <small>*</small> :  </label>
            <input type="tel" id="pk_customer_telephone" name="pk_customer_telephone" pattern="^0[1-9](?:[-. ]?\d{2}){4}$" 
            <?php // placeholder pour donner une indication, expression du patter conforme aux numéros francais (en local)?>

            value ="<?php
            //  Pré-remplissage avec la valeur dans $old_data sinon vide 
            echo esc_attr( isset( $old_data['pk_customer_telephone']) ? $old_data['pk_customer_telephone'] : ''); ?>"
            placeholder="06 70 28 81 19" required> 
            
            <?php
            // Affichage d'un message d'erreur si une erreur a été détectée pour ce champ avec l'association clé-paire de $errors
            if ( isset( $errors['pk_customer_telephone'] ) ) : ?>
            <span class="error-message"><?php echo esc_html( $errors['pk_customer_telephone'] ); ?></span>
            <?php endif; // Fin conditoin et retour au HTML ?>   
        </p>

        <p>
            <label for="pk_customer_address"> Adresse de livraison <small>*</small> :  </label>
            <input type="text" id="pk_customer_address" name="pk_customer_address" placeholder="3, rue de la fraise" 
            value="<?php
            //  Pré-remplissage avec la valeur dans $old_data sinon vide 
            echo esc_attr( isset( $old_data['pk_customer_address']) ? $old_data['pk_customer_address'] : ''); ?>" 
            autocomplete="street-address" required>
            <?php // Valeurs pour l'auto-complétion définies sur https://html.spec.whatwg.org/multipage/form-control-infrastructure.html ?>
            
            <?php
            // Affichage d'un message d'erreur si une erreur a été détectée pour ce champ avec l'association clé-paire de $errors
            if ( isset( $errors['pk_customer_address'] ) ) : ?>
            <span class="error-message"><?php echo esc_html( $errors['pk_customer_address'] ); ?></span>
            <?php endif; // fin condition et retour au HTML ?>
        </p>

        <p>
            <label for="pk_customer_postal_code">Code Postal <small>*</small> : </label>
            <input type="text" id="pk_customer_postal_code" name="pk_customer_postal_code" placeholder="29000" 
            value="<?php
            //  Pré-remplissage avec la valeur dans $old_data sinon vide 
            echo esc_attr( isset( $old_data['pk_customer_postal_code']) ? $old_data['pk_customer_postal_code'] : ''); ?>" 
            autocomplete="postal-code" required maxlength="5"> 
        
            <?php 
            // Affichage d'un message d'erreur si une erreur a été détectée pour ce champ avec l'association clé-paire de $errors
            if ( isset( $errors['pk_customer_postal_code'] ) ) : ?>
            <span class="error-message"><?php echo esc_html( $errors['pk_customer_postal_code'] ); ?></span>
            <?php endif; // fin condition et retour HTML ?>
        </p>

        <p>
            <label for="pk_customer_city">Ville <small>*</small> :  </label>
            <input type="text" id="pk_customer_city" name="pk_customer_city" placeholder="Brest" 
            value="<?php
            //  Pré-remplissage avec la valeur dans $old_data sinon vide 
            echo esc_attr( isset( $old_data['pk_customer_city']) ? $old_data['pk_customer_city'] : ''); ?>" 
            autocomplete="address-level2" required>  
            
            <?php 
            // Affichage d'un message d'erreur si une erreur a été détectée pour ce champ avec l'association clé-paire de $errors
            if ( isset( $errors['pk_customer_city'] ) ) : ?>
            <span class="error-message"><?php echo esc_html( $errors['pk_customer_city'] ); ?></span>
            <?php endif; // fin condition et retour HTML ?>
        </p>

    </fieldset>

    <fieldset>
        <legend>Détails de la réservation</legend>

        <p>
            <label for="pk_reservation_start_date"> Date de début de réservation <small>*</small> : </label>
            <input type="date" id="pk_reservation_start_date" name="pk_reservation_start_date" min="<?php echo esc_attr($min_date); ?>" max="<?php echo esc_attr($min_date_plus_one_year); ?>" 
            value="<?php
            //  Pré-remplissage avec la valeur dans $old_data sinon vide 
            echo esc_attr( isset( $old_data['pk_reservation_start_date']) ? $old_data['pk_reservation_start_date'] : '');?>" required>
                
            <?php 
            // Affichage d'un message d'erreur si une erreur a été détectée pour ce champ avec l'association clé-paire de $errors
            if ( isset( $errors['pk_reservation_start_date'] ) ) : ?>
            <span class="error-message"><?php echo esc_html( $errors['pk_reservation_start_date'] ); ?></span>
            <?php endif; // fin condition et retour HTML ?>
        </p>

        <p>
            <label for="pk_reservation_end_date"> Date de fin de réservation <small>*</small> : </label>
            <input type="date" id="pk_reservation_end_date" name="pk_reservation_end_date" 
            value="<?php
            //  Pré-remplissage avec la valeur dans $old_data sinon vide 
            echo esc_attr( isset( $old_data['pk_reservation_end_date']) ? $old_data['pk_reservation_end_date'] : '');?>" required>

            <?php 
            // Affichage d'un message d'erreur si une erreur a été détectée pour ce champ avec l'association clé-paire de $errors
            if ( isset( $errors['pk_reservation_end_date'] ) ) : ?>
            <span class="error-message"><?php echo esc_html( $errors['pk_reservation_end_date'] ); ?></span>
            <?php endif;// fin condition et retour HTML ?>
        </p>

        <p>
            <label for="pk_customer_message"> Votre message <span> (optionnel) </span> : </label>
            <textarea id="pk_customer_message" name="pk_customer_message" 
            value="<?php
            //  Pré-remplissage avec la valeur dans $old_data sinon vide
            
            echo esc_attr( isset( $old_data['pk_customer_message']) ? $old_data['pk_reservation_customer_message'] : '');?>" placeholder="Votre message ici... (500 caractères)" rows="5" maxlength="500">
</textarea>
        
            <?php
            // Affichage d'un message d'erreur si une erreur a été détectée pour ce champ avec l'association clé-paire de $errors
            if ( isset( $errors['pk_customer_message'] ) ) : ?>
            <span class="error-message"><?php echo esc_html( $errors['pk_customer_message'] ); ?></span>
            <?php endif;// fin condition et retour HTML ?>
        </p>

    </fieldset>

    <?php wp_nonce_field('pk_reservation_form_submit', 'pk_reservation_nonce_submit'); ?>
    <input type="hidden" name="action" value="pk_traitement_reservation_form"> <?php // nécessaire pour indiquer l'actoin à wordpress ?>
    <button type="submit">Envoyer ma réservation</button>

    </form>

    <?php return ob_get_clean(); // récupération des données dans la mémoire tampon
    }


};

add_shortcode('pk_reservation_form','pk_formulaire');
