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
        'public' => false, // il ne faut pas qu'on puisse accèder à une réservation en tappant son id dans l'url par ex.
        'show_ui' => true, // en revanche il n'est plus accessible dans le menu latéral par défaut si public est false -- très casse-tête 
        'menu_position' => 7, 
        'has_archive' => 'historique_des_reservations', //has_archive peut être de type 'string', c'est le slug qui comportera les archives de nos réservations
        'show_in_admin_bar'     => true, // est visible dans la barre d'admin
        'show_in_rest' => true,
        'supports' => array('title', 'author'),

        
        'capabilities' => array( // définition des capabilités - nécessaires pour que le rôle pk_customer ne puisse pas modifier ou accèder aux posts des autres (confidentialité)
        'edit_post'          => 'edit_pk_reservation',         // Peut éditer un post individuel
        'read_post'          => 'read_pk_reservation',         // Peut lire un post individuel
        'delete_post'        => 'delete_pk_reservation',       // Peut supprimer un post individuel
        'edit_posts'         => 'edit_pk_reservations',        // Peut éditer plusieurs posts (vue liste)
        'edit_others_posts'  => 'edit_others_pk_reservations', // Peut éditer les posts d'autres utilisateurs
        'publish_posts'      => 'publish_pk_reservations',     // Peut publier/créer des posts
        'read_private_posts' => 'read_private_pk_reservations',// Peut lire les posts privés
        'create_posts'       => 'edit_pk_reservations',         // Souvent lié à 'edit_posts' pour la création
        ),
        'map_meta_cap' => true // fonction wordpress qui permet de traduire les rôles personnalisés et de les mettre en accord avec son moteur interne
    );
    register_post_type( 'pk_reservation', $args );
}

add_action( 'init', 'photokit_register_pkreservation_post_type' ); // Déclenche l'action d'enregistrement du CPT réservation au démarrage démarrage du coeur Wordpress (init)

/* tentative de résolution des problèmes de droits pour afficher les réservations
on donne toutes les permissions du CPT pk_reservation à l'admin
https://wordpress.stackexchange.com/questions/191703/shold-i-manually-add-cap-to-admin-role

*/

function photokit_add_admin_caps(){
    $role = get_role('administrator');
    

    $pk_admin_caps = array (
            'edit_pk_reservation',         // Peut éditer un post individuel
            'read_pk_reservation',         // Peut lire un post individuel
            'delete_pk_reservation',       // Peut supprimer un post individuel
            'edit_pk_reservations',        // Peut éditer plusieurs posts (vue liste)
            'edit_others_pk_reservations', // Peut éditer les posts d'autres utilisateurs
            'publish_pk_reservations',     // Peut publier/créer des posts
            'read_private_pk_reservations',// Peut lire les posts privés
            'edit_pk_reservations',         // Souvent lié à 'edit_posts' pour la création
    );

    if($role) {
        foreach ($pk_admin_caps as $pk_admin_cap) {
        $role->add_cap($pk_admin_cap);
        }
    }
}


function photokit_remove_admin_caps(){
    $pk_admin_caps = array (
            'edit_pk_reservation',         // Peut éditer un post individuel
            'read_pk_reservation',         // Peut lire un post individuel
            'delete_pk_reservation',       // Peut supprimer un post individuel
            'edit_pk_reservations',        // Peut éditer plusieurs posts (vue liste)
            'edit_others_pk_reservations', // Peut éditer les posts d'autres utilisateurs
            'publish_pk_reservations',     // Peut publier/créer des posts
            'read_private_pk_reservations',// Peut lire les posts privés
            'edit_pk_reservations',         // Souvent lié à 'edit_posts' pour la création
    );

    $role = get_role('administrator');

    if($role) {
        foreach ($pk_admin_caps as $pk_admin_cap) {
        $role->remove_cap($pk_admin_cap);
        }
    }
}
