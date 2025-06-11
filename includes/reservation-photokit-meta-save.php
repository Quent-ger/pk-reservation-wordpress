<?php function pk_reservation_save_metaboxes_data( $post_id ) { // Enregistre les données des metaboxes 

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



        // ENREGISTREMENT des valeurs des métas

        // STATUTS & DATES (Détails de réservation)

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


