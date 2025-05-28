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

// Définit la localisation du dossier en tant que constante
if ( ! defined( 'PKRESERVATION_PLUGIN_DIR' ) ) {
    define( 'PKRESERVATION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Charge le CPT Réservation
require_once PKRESERVATION_PLUGIN_DIR . 'includes/reservation-photokit-cpt.php';

// Charge les meta boxes custom
require_once PKRESERVATION_PLUGIN_DIR . 'includes/reservation-photokit-meta.php';

?>