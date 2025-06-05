<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // engage la fin du script si on accède directement au fichier
}

function pk_reservation_add_custom_boxes() {  // ajout de meta boxes custom pour les statuts de réservations

    if (current_user_can('manage_options')) {   // nécessaire que le coeur de wordpress soit chargé avant d'identifier si l'utiliasteur est admin
        
        // meta box pour le statut de réservation
        add_meta_box(
			'pk_box_reservation_status',                 // Unique ID
			'Statut de réservation',      // Box title
			'pk_reservation_custom_box_status_html',  // Content callback, must be of type callable
			'pk_reservation'                          // Post type
	        );

        // meta box pour le date de début de réservation 
        add_meta_box(
            'pk_box_reservation_begin',                 // Unique ID
			'Début de réservation',      // Box title
			'pk_reservation_custom_box_reservation_begin_html',  // Content callback, must be of type callable
			'pk_reservation'                          // Post type
        );

        // meta box pour le date de fin de réservation
        add_meta_box(
            'pk_box_reservation_end',                 // Unique ID
			'Fin de réservation',      // Box title
			'pk_reservation_custom_box_reservation_end_html',  // Content callback, must be of type callable
			'pk_reservation'                          // Post type
        );


	    }
    }

add_action( 'add_meta_boxes', 'pk_reservation_add_custom_boxes' ); // quand wordpress charge les meta boxes, il viendra charger notre custom box


    function pk_reservation_custom_box_status_html($post){  //code html pour la meta box Statut de réservation 

        wp_nonce_field('pk_reservation_save_metaboxes_data', 'pk_reservation_meta_box_nonce'); // le nonce sera vérifié peu importe le champ soumis, meilleure UX, moins de code


        $current_status = get_post_meta( $post->ID, '_pk_reservation_status', true ); //WP récupére le statut de réservation actuellement défini pour ce post - assisté par IA

        if ( empty( $current_status ) ) { // Si c'est un nouveau post (l'ID est 0) et que la variable current_status n'est pas définie, cette dernière prend pending/en attente par défaut -- assisté par IA
            $current_status = 'pending'; // Définit le statut de réservation par défaut comme "en attente"
        }
    ?>
        
        <!--le HTML de la meta box -->
        <label for="pk_reservation_status_field">État de la réservation</label>
        <select name="pk_reservation_status_field" id="pk_reservation_status_field" class="postbox">
            <option value="">Veuillez sélectionner une option</option>
            <option value="confirm" <?php selected( $current_status, 'confirm' ); ?> > <span class="dashicons dashicons-yes"></span>  Approuvée </option> <?php /* Utilisation de la fonction helper selected() de WP */ ?>
            <option value="pending" <?php selected( $current_status, 'pending' ); ?> > <span class="dashicons dashicons-clock"></span> En attente </option>
            <option value="past" <?php selected( $current_status, 'past' ); ?> > <span class="dashicons dashicons-archive"></span>  Archivée </option>
            <option value="denied" <?php selected( $current_status, 'denied' ); ?> > <span class="dashicons dashicons-no"></span>  Refusée </option>
        </select>

    <?php }
    function pk_reservation_custom_box_reservation_begin_html($post){

        // Obtenir la valeur de la date de début de réservation
        $today_date = current_time("Y-m-d");
        $current_begin_date = get_post_meta($post->ID, '_pk_reservation_begin_date', true);
        ?>

            <!--le HTML de la meta box -->
            <label for="pk_reservation_begin_field">Début de la réservation</label>
            <input type="date" name="pk_reservation_begin_field" id="pk_reservation_begin_field" min="<?php echo esc_attr($today_date); ?>" value="<?php echo esc_attr($current_begin_date); ?>" required>
    <?php }
    function pk_reservation_custom_box_reservation_end_html($post){

    // Obtenir la valeur de la date de fin de réservation
    $current_begin_date = get_post_meta($post->ID, '_pk_reservation_begin_date', true); // il faut récupérer la date de début de la réservation pour pouvoir éviter des erreurs logiques
    $min_date = empty($current_begin_date) ? current_time("Y-m-d") : $current_begin_date; // si la date de début n'est pas attribuée, la date min est aujd, sinon elle devient le min - assisté par IA 
    $current_end_date = get_post_meta($post->ID, '_pk_reservation_end_date', true);    
    ?>

        <!--le HTML de la meta box -->
        <label for="pk_reservation_end_field">Fin de la réservation</label>
        <input type="date" name="pk_reservation_end_field" id="pk_reservation_end_field" min="<?php echo esc_attr($min_date); ?>"value="<?php echo esc_attr($current_end_date); ?>" required>

    <?php } 

    function pk_reservation_save_metaboxes_data( $post_id ) { // Enregistre les données des metaboxes 

        // Vérifications de sécurité : assisté par IA
        
        // 1. Vérifie si le nonce existe
        if ( ! isset( $_POST['pk_reservation_meta_box_nonce'] ) ) {
            return $post_id; // Si ce n'est pas le cas, n'enregistre pas
        }

        // 2. Vérification de la validité du nonce
        if ( ! wp_verify_nonce( $_POST['pk_reservation_meta_box_nonce'], 'pk_reservation_save_metaboxes_data' ) ) {
            return $post_id; 
        }

        // 3. Cas d'une auto sauvegarde
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // 4. Vérifie si l'utilisateur a le rôle éditeur sinon pas d'enregistrement
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }

        // ENREGISTREMENT

        // Vérifie si le statut de réservation a été changé
        if ( isset( $_POST['pk_reservation_status_field'] ) ) {
            // Sanitization de la valeur du statut de réservation
            $new_status = sanitize_text_field( $_POST['pk_reservation_status_field'] );

        // Enregistre le nouveau statut de réservation dans la bdd
            update_post_meta( $post_id, '_pk_reservation_status', $new_status );
        }

        //Variables pour enregistrer les dates (vérification logique) --assisté par IA
        $new_begin_date = isset( $_POST['pk_reservation_begin_field']) ? sanitize_text_field( $_POST['pk_reservation_begin_field']) : ''; // Pour pouvoir fonctionner avec empty()
        $new_end_date = isset( $_POST['pk_reservation_end_field']) ? sanitize_text_field( $_POST['pk_reservation_end_field']) : '';


        // Vérification de la logique des dates côté serveur -- assisté par IA


        if ( empty( $new_begin_date ) ) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p><strong>Erreur de réservation : </strong>La date de début est obligatoire.</p></div>';
            });
            return $post_id;
        }

        if ( empty( $new_end_date ) ) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p><strong>Erreur de réservation : </strong>La date de fin est obligatoire.</p></div>';
            });
            return $post_id;
        }
        
            $begin_timestamp=strtotime($new_begin_date);
            $end_timestamp=strtotime($new_end_date);

        if ( $end_timestamp < $begin_timestamp ) // si le timestamp (la date) de fin est inférieur à celui du début
        {
            // Message d'erreur
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p><strong>Erreur avec les dates de réservation  : </strong>
                La date de fin ne peut pas être antérieure à la date de début !</p></div>';
            });

            //la date de fin arrive avant la date de début : ces dates ne sont pas enregistrées dans la bdd (affichage du message d'erreur).
            return $post_id;
        }

        // Enregistre les dates de réservation si elles sont renseignées, conformes et non vides
        
            update_post_meta( $post_id, '_pk_reservation_begin_date', $new_begin_date );
            update_post_meta( $post_id, '_pk_reservation_end_date', $new_end_date );
        
}

// Indique à Wordpress d'effectuer l'action d'enregsitrement de status après chaque post général
add_action( 'save_post', 'pk_reservation_save_metaboxes_data' );
// Indique à Wordpress d'effectuer l'action d'enregsitrement de status après chaque post réservation (reccomendation technique d'utiliser les deux)
add_action( 'save_pkreservation', 'pk_reservation_save_metaboxes_data' );


