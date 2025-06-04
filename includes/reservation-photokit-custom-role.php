<?php 
// Permet à Wordpress de retrouver et charger ses fichiers - protège d'un accès direct au plugin
if ( ! defined( 'ABSPATH' ) ) {
    exit; // engage la fin du script si on accède directement au fichier
}

function photokit_add_custom_role() {  
    add_role(
        'pk_customer', // nom du rôle
        'Client Photokit', // nom du rôle à l'affichage
        array(
            'read' => true, // peut lire les contenus publics du site
            'create_pk_reservations' => true, // peut créer des réservations (formulaire de réservation)
        ),
    );

    $pk_admin_caps = array( // array qui liste toutes les permissions custom à donner à l'admin
        'edit_pk_reservation',
        'read_pk_reservation',
        'delete_pk_reservation',
        'edit_pk_reservations',
        'edit_others_pk_reservations',
        'publish_pk_reservations',
        'read_private_pk_reservations',
    );

    $pk_admin_role = get_role('administrator'); // récupére le rôle admin et l'assigne à la variable $pk_admin_role


    if ( $pk_admin_role ) {
        foreach ( $pk_admin_caps as $pk_cap ) {
            if( ! $pk_admin_role->has_cap( $pk_cap ) ) {
                $pk_admin_role->add_cap( $pk_cap );
            }
        }
    }
}

function photokit_remove_custom_role() {  // nécessaire pour que le role puisse être désactivé
    remove_role('pk_customer');  
}



