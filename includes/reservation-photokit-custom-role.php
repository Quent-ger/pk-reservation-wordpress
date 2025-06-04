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
}

function photokit_remove_custom_role() {  // nécessaire pour que le role puisse être désactivé
    remove_role('pk_customer');  
}



