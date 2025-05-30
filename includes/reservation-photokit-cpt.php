<?php // Permet à Wordpress de retrouver et charger ses fichiers - protège d'un accès direct au plugin
if ( ! defined( 'ABSPATH' ) ) {
    exit; // engage la fin du script si on accède directement au fichier
}

// Création de la fonction qui gères les CPT "Réservation"S
function photokit_register_pkreservation_post_type() {  // préfixe photokit pour éviter les conflits avec les autres extensions/thèmes
    $args = array(
        'labels' => array(
            'name'          => 'Réservations',
            'singular_name' => 'Réservation',
            'menu_name'     => 'Réservations',
            'add_new'       => 'Ajouter une réservation',
            'add_new_item'  => 'Ajouter une réservation',
            'new_item'      => 'Nouvelle Réservation',
            'edit_item'     => 'Éditer la réservation',
            'view_item'     => 'Voir la réservation',
            'all_items'     => 'Toutes les réservations',
        ),

        'menu_icon'     => 'dashicons-calendar-alt',  // icône dans le menu
        'public' => true,
        'menu_position' => 7, 
        'has_archive' => 'historique_des_reservations', //has_archive peut être de type 'string', c'est le slug qui comportera les archives de nos réservations
        'show_in_admin_bar'     => true, // est visible dans la barre d'admin
        'show_in_rest' => true,
        'supports' => array('title', 'author'),
    );
    register_post_type( 'pk_reservation', $args );
}

add_action( 'init', 'photokit_register_pkreservation_post_type' ); // Déclenche l'action d'enregistrement du CPT réservation au démarrage démarrage du coeur Wordpress (init)
?>