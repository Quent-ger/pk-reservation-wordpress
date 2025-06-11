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

  <form id="pk-reservation-form" method="post">

    <fieldset>
        <legend>Informations personnelles</legend>

        <p> <?php // name nécessaire pour récupérer les données par la suite, id doit être lié au for du label, préremplissage avec les données de compte, autocomplete pour l'UX ?>
            <label for="pk_customer_firstname">Prénom : * </label>
            <input type="text" name="pk_customer_firstname" id="pk_customer_firstname" placeholder="Votre prénom" value="<?php echo esc_attr($customer_first_name); ?>" autocomplete="given-name" required maxlength="35">
        </p>

        <p>
            <label for="pk_customer_lastname">Nom : * </label>
            <input type="text" name="pk_customer_lastname" id="pk_customer_lastname" placeholder="Votre nom" value="<?php echo esc_attr($customer_last_name); ?>" autocomplete="family-name" required maxlength="50">
        </p>

        <p>
            <label for="pk_customer_company">Société <span> (optionnel) </span> : </label>
            <input type="text" name="pk_customer_company" id="pk_customer_company" placeholder="Société ou association" autocomplete="organization">
        </p>

    </fieldset>

    <fieldset>
        <legend>Coordonnées</legend>

        <p>
            <label for="pk_customer_email"> Email : * </label>
            <input type="email" id="pk_customer_email" name="pk_customer_email" placeholder="adresse@messagerie.fr"value="<?php echo esc_attr($customer_email); ?>" autocomplete="email" required>
        </p>

        <p>
            <label for="pk_customer_address"> Adresse de livraison : * </label>
            <input type="text" id="pk_customer_address" name="pk_customer_address" placeholder="3, rue de la fraise" autocomplete="street-address" required>
        </p>

        <p>
            <label for="pk_customer_postal_code">Code Postal : * </label>
            <input type="text" id="pk_customer_postal_code" name="pk_customer_postal_code" placeholder="29000" autocomplete="postal-code" required maxlength="5">
        </p>

        <p>
            <label for="pk_customer_city">Ville : * </label>
            <input type="text" id="pk_customer_city" name="pk_customer_city" placeholder="Brest" autocomplete="address-level2" required>
        </p>

        <p>
            <label for="pk_customer_telephone"> Numéro de téléphone : * </label>
            <input type="tel" id="pk_customer_telephone" name="pk_customer_phone" pattern="^0[1-9](?:[-. ]?\d{2}){4}$" placeholder="06 70 28 81 19" required>
            <?php // placeholder pour donner une indication, expression du patter conforme aux numéros francais (en local)?>    
        </p>

    </fieldset>

    <fieldset>
        <legend>Détails de la réservation</legend>

        <p>
            <label for="pk_reservation_start_date"> Date de début de réservation : * </label>
            <input type="date" id="pk_reservation_start_date" name="pk_reservation_start_date" min="<?php echo esc_attr($min_date); ?>" max="<?php echo esc_attr($min_date_plus_one_year); ?>" required>
        </p>

        <p>
            <label for="pk_reservation_end_date"> Date de fin de réservation : * </label>
            <input type="date" id="pk_reservation_end_date" name="pk_reservation_end_date" required>
        </p>

        <p>
            <label for="pk_customer_message"> Votre message <span> (optionnel) </span> : </label>
            <textarea id="pk_customer_message" name="pk_customer_message" placeholder="Votre message ici" rows="5"></textarea>
        </p>

    </fieldset>

    <?php wp_nonce_field('pk_reservation_form_submit', 'pk_reservation_nonce_field'); ?>
    <input type="hidden" name="action" value="pk_traitement_reservation_form"> <?php // nécessaire pour indiquer l'actoin à wordpress ?>
    <button type="submit">Envoyer ma réservation</button>

    </form>

    <?php return ob_get_clean(); // récupération des données dans la mémoire tampon
    }


};

add_shortcode('pk_reservation_form','pk_formulaire');

function pk_traitement_reservation_form() {
    // Vérifications de sécurité

        // Vérification du nonce
        if (!isset($_POST['pk_reservation_nonce_field'])) {
            wp_die ('Erreur de sécurité : Tentative de soumission du formulaire non autorisée ou nonce invalide');
        }
        
        // Vérification de la méthode de requête
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_die('Méthode invalide');
        }


    // Initialisation de tableaux pour les erreurs et pour les données validées
        $errors = array();
        $verified_data = array();

    // Traitement et validation

    // Prénom
    if ( empty( $_POST['pk_customer_firstname']) ) {
        $errors[] = 'Le prénom est requis.';
    } else {
        // étape 1 : 
        $first_name_sanitized = sanitize_text_field( $_POST['pk_customer_firstname']);

        // étape 2 : vérification  de la longueur de chaîne de caractères
        if (strlen($first_name_sanitized)>35) {
            $errors[] = 'Le prénom ne peut dépasser 35 caractères';
        } else {
            $verified_data['pk_customer_firstname'] = $first_name_sanitized;
        }
    }

    // Nom
    if ( empty( $_POST['pk_customer_lastname']) ) {
        $errors[] = 'Le nom de famille est requis.';
    } else {
        // étape 1 : 
        $last_name_sanitized = sanitize_text_field( $_POST['pk_customer_lastname']);

        // étape 2 : vérification  de la longueur de chaîne de caractères
        if (strlen($first_name_sanitized)>50) {
            $errors[] = 'Le nom de famille ne peut dépasser 50 caractères';
        } else {
            $verified_data['pk_customer_lastname'] = $last_name_sanitized;
        }
    }

    // Société (optionnel) 
    if (! empty($_POST['pk_customer_company'])) {
        $verified_data['pk_customer_company'] = sanitize_text_field( $_POST['pk_customer_company']) ;
    } else {
        $verified_data['pk_customer_company'] = ''; // s'assurer qu'il-y-a une valeur pour l'enregistrement 
    }

}