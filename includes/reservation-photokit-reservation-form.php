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