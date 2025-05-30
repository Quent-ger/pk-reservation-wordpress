<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // engage la fin du script si on accède directement au fichier
}

function pk_reservation_add_custom_boxes() {  // ajout de meta boxes custom pour les statuts de réservations

    if (current_user_can('manage_options')) {   // nécessaire que le coeur de wordpress soit chargé avant d'identifier si l'utiliasteur est admin
        // meta box pour le statut de réservation
        add_meta_box(
			'pk_box_id',                 // Unique ID
			'Statut de réservation',      // Box title
			'pk_reservation_custom_box_status_html',  // Content callback, must be of type callable
			'pk_reservation'                          // Post type
	        );

        // meta box pour le date de début de réservation 
        add_meta_box(
            'pk_box_id_2',                 // Unique ID
			'Début de réservation',      // Box title
			'pk_reservation_custom_box_reservation_begin_html',  // Content callback, must be of type callable
			'pk_reservation'                          // Post type
        );

        // meta box pour le date de fin de réservation
        add_meta_box(
            'pk_box_id_3',                 // Unique ID
			'Fin de réservation',      // Box title
			'pk_reservation_custom_box_reservation_end_html',  // Content callback, must be of type callable
			'pk_reservation'                          // Post type
        );

	    }
    }

add_action( 'add_meta_boxes', 'pk_reservation_add_custom_boxes' ); // quand wordpress charge les meta boxes, il viendra charger notre custom box

    function pk_reservation_custom_box_status_html($post)  //code html pour la meta box Statut de réservation
    { 
        $current_status = get_post_meta( $post->ID, '_pkreservation_status', true ); //WP récupére le statut de réservation actuellement défini pour ce post - assisté par IA

        if ( 0 === $post->ID && empty( $current_status ) ) { // Si c'est un nouveau post (l'ID est 0) et que la variable current_status n'est pas définie, cette dernière prend pending/en attente par défaut -- assisté par IA
            $current_status = 'pending'; // Définit le statut de réservation par défaut comme "en attente"
        }

        wp_nonce_field( 'pk_reservation_save_status_data', 'pk_reservation_status_nonce' ); // nonce Wordpress pour assurer la sécurité (champ caché rempli par un hash autogénéré) -- assisté par IA
        ?>
        
        <!--le HTML de la meta box -->
        <label for="pk_reservation_status_field">État de la réservation</label>
        <select name="pk_reservation_status_field" id="pk_reservation_status_field" class="postbox">
            <option value="">Veuillez sélectionner une option</option>
            <option value="past" <?php selected( $current_status, 'past' ); ?> > Archivée </option>
            <option value="confirm" <?php selected( $current_status, 'confirm' ); ?> > Approuvée </option> <?php /* Utilisation de la fonction helper selected() de WP */ ?>
            <option value="pending" <?php selected( $current_status, 'pending' ); ?> > En attente </option>
            <option value="denied" <?php selected( $current_status, 'denied' ); ?> > Refusée </option>
        </select>

    <?php
    function pk_reservation_custom_box_reservation_begin_html($post)
    {
         wp_nonce_field( 'pk_reservation_save_reservation_begin_data', 'pk_reservation_begin_nonce' ); // nonce Wordpress pour assurer la sécurité (champ caché rempli par un hash autogénéré) -- assisté par IA
    ?>
         <!--le HTML de la meta box -->
        <label for="pk_reservation_begin_field">Début de la réservation</label>
        <input type="date" name="pk_reservation_begin_field" id="pk_reservation_begin_field" required>

    <?php
    }

    function pk_reservation_custom_box_reservation_end_html($post){
           wp_nonce_field( 'pk_reservation_save_reservation_end_data', 'pk_reservation_end_nonce' ); // nonce Wordpress pour assurer la sécurité (champ caché rempli par un hash autogénéré) -- assisté par IA
    ?>
         <!--le HTML de la meta box -->
        <label for="pk_reservation_end_field">Fin de la réservation</label>
        <input type="date" name="pk_reservation_end_field" id="pk_reservation_end_field" required>

    <?php
    }
    

    }

    function pk_reservation_save_status_data( $post_id ) { // Enregistre les infos relatives à l'état de réservation 

    // Vérifications de sécurité : assisté par IA

    // 1. Vérifie si le nonce existe
    if ( ! isset( $_POST['pk_reservation_status_nonce'] ) ) {
        return $post_id; // Si ce n'est pas le cas, n'éenregistre pas
    }

    // 2. Vérification de la validité du nonce
    if ( ! wp_verify_nonce( $_POST['pk_reservation_status_nonce'], 'pk_reservation_save_status_data' ) ) {
        return $post_id; 
    }

    // 3. Cas d'une auto sauvegarde
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    // 4. Vérifie si l'utilisateur a le rôle éditeur
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }

    // ENREGISTREMENT

    // Vérifie si le statut de réservation a été changé
    if ( isset( $_POST['pk_reservation_field'] ) ) {
        // Sanitization de la valeur du statut de réservation
        $new_status = sanitize_text_field( $_POST['pk_reservation_field'] );

       // Enregistre le nouveau statut de réservation dans la bdd
        update_post_meta( $post_id, '_pk_reservation_status', $new_status );
    }
}

// Indique à Wordpress d'effectuer l'action d'enregsitrement de status après chaque post général
add_action( 'save_post', 'pk_reservation_save_status_data' );
// Indique à Wordpress d'effectuer l'action d'enregsitrement de status après chaque post réservation (reccomendation technique d'utiliser les deux)
add_action( 'save_pkreservation', 'pk_reservation_save_status_data' );

?>

