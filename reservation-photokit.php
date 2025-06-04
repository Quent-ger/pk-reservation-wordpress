<?php
/*
Plugin Name: Reservation Photokit
Description: Module développé pour gérer les réservations du site Photokit
Version: 1.2
Author : QgeR
Author URI : https://qger-portfolio.alwaysdata.net/
*/

// Permet à Wordpress de retrouver et charger ses fichiers - protège d'un accès direct au plugin
if ( ! defined( 'ABSPATH' ) ) {
    exit; // engage la fin du script si on accède directement au fichier
}

// Définit la localisation des dossiers en tant que constante
if ( ! defined( 'PKRESERVATION_PLUGIN_DIR' ) ) {
    define( 'PKRESERVATION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'PKRESERVATION_INC_DIR' ) ) {
    define( 'PKRESERVATION_INC_DIR', PKRESERVATION_PLUGIN_DIR . 'includes/' );
}

// Inclusion des fichiers de fonctionalité
$pk_reservation_required_files = array (
    'reservation-photokit-cpt.php',
    'reservation-photokit-meta.php',
    'reservation-photokit-custom-role.php',
    'reservation-photokit-reservation-form.php',
);

foreach ($pk_reservation_required_files as $pk_plugin_file) {
    require_once PKRESERVATION_INC_DIR . $pk_plugin_file;
} 

/* Ancienne strucure d'inclusion conservée en cas
require_once PKRESERVATION_PLUGIN_DIR . 'includes/reservation-photokit-cpt.php';

// Charge les meta boxes custom
require_once PKRESERVATION_PLUGIN_DIR . 'includes/reservation-photokit-meta.php';

// Charge le rôle client Photokit (pk_customer)
require_once PKRESERVATION_PLUGIN_DIR . 'includes/reservation-photokit-custom-role.php';

// Charge le formulaire de réservation front-end
require_once PKRESERVATION_PLUGIN_DIR . 'includes/reservation-photokit-reservation-form.php';
*/

// Hooks d'activation et désactivation
register_activation_hook (__FILE__, 'photokit_add_custom_role'); // active le rôle à l'intialisation du plugin mais pas à chaque lecture de page
register_deactivation_hook( __FILE__, 'photokit_remove_custom_role'); // retire le rôle lors de la désinstallation

