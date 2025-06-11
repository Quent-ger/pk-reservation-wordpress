<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // engage la fin du script si on accède directement au fichier
}

function pk_reservation_add_custom_boxes() {  // ajout de meta boxes custom pour les statuts de réservations

    if (current_user_can('manage_options')) {   // nécessaire que le coeur de wordpress soit chargé avant d'identifier si l'utiliasteur est admin
        
        // meta box pour les infos de réservation
        add_meta_box(
			'pk_box_reservation_details',                 // Unique ID
			'Détails de la réservation',      // Box title
			'pk_reservation_details_box_html',  // Content callback, must be of type callable
			'pk_reservation'                          // Post type
	        );

        // meta box pour les infos clients
        add_meta_box(
			'pk_box_customer_details',                 // Unique ID
			'Informations client',      // Box title
			'pk_reservation_customer_box_html',  // Content callback, must be of type callable
			'pk_reservation'                          // Post type
	        );    
        
        // meta pour les infos de livraison
        add_meta_box(
			'pk_box_delivery_details',                 // Unique ID
			'Informations de livraison',      // Box title
			'pk_reservation_delivery_box_html',  // Content callback, must be of type callable
			'pk_reservation'                          // Post type
	        );
	    }

        // meta pour le message client (textarea)
        add_meta_box(
			'pk_box_customer_message',                 // Unique ID
			'Message client',      // Box title
			'pk_reservation_customer_message_html',  // Content callback, must be of type callable
			'pk_reservation'                          // Post type
	        );
    }

add_action( 'add_meta_boxes', 'pk_reservation_add_custom_boxes' ); // quand wordpress charge les meta boxes, il viendra charger notre custom box


    function pk_reservation_details_box_html($post){  //code html pour la meta box Statut de réservation 

        // nonce pour la vérif
        wp_nonce_field('pk_reservation_save_metaboxes_data', 'pk_reservation_meta_box_nonce'); // le nonce sera vérifié peu importe le champ soumis, meilleure UX, moins de code
        
        // Récupération du statut
        $current_status = get_post_meta( $post->ID, '_pk_reservation_status', true ); //WP récupére le statut de réservation actuellement défini pour ce post - assisté par IA

        if ( empty( $current_status ) ) { // Si c'est un nouveau post (l'ID est 0) et que la variable current_status n'est pas définie, cette dernière prend pending/en attente par défaut -- assisté par IA
            $current_status = 'pending'; // Définit le statut de réservation par défaut comme "en attente"
        }

        // Obtenir la valeur de la date de début de réservation
        $current_begin_date = get_post_meta($post->ID, '_pk_reservation_begin_date', true);

        // Obtenir la valeur de la date de fin de réservation
        $today_date = current_time("Y-m-d");
        $min_date = empty($current_begin_date) ? $today_date : $current_begin_date; // si la date de début n'est pas attribuée, la date min est aujd, sinon elle devient le min - assisté par IA 
        $current_end_date = get_post_meta($post->ID, '_pk_reservation_end_date', true);    

    ?>
        
        <!--le HTML de la meta box détails de réservation -->
        <label for="pk_reservation_status_field">Statut de la réservation </label>
        <select name="pk_reservation_status_field" id="pk_reservation_status_field" class="postbox">
            <option value="">Veuillez sélectionner une option </option>
            <option value="confirm" <?php selected( $current_status, 'confirm' ); ?> > <span class="dashicons dashicons-yes"></span>  Approuvée </option> <?php /* Utilisation de la fonction helper selected() de WP */ ?>
            <option value="pending" <?php selected( $current_status, 'pending' ); ?> > <span class="dashicons dashicons-clock"></span> En attente </option>
            <option value="past" <?php selected( $current_status, 'past' ); ?> > <span class="dashicons dashicons-archive"></span>  Archivée </option>
            <option value="denied" <?php selected( $current_status, 'denied' ); ?> > <span class="dashicons dashicons-no"></span>  Refusée </option>
        </select>

        <label for="pk_reservation_begin_field">Début de la réservation</label>
            <input type="date" name="pk_reservation_begin_field" id="pk_reservation_begin_field" value="<?php echo esc_attr($current_begin_date); ?>" required>

        <!--le HTML de la meta box -->
        <label for="pk_reservation_end_field">Fin de la réservation</label>
        <input type="date" name="pk_reservation_end_field" id="pk_reservation_end_field" min="<?php echo esc_attr($min_date); ?>" value="<?php echo esc_attr($current_end_date); ?>" required>

    <?php }


    function pk_reservation_customer_box_html ($post) { // HTML pour la box infos client
        $first_name = get_post_meta ( $post->ID, '_pk_customer_firstname', true );
        $last_name = get_post_meta ( $post->ID, '_pk_customer_lastname', true );
        $company = get_post_meta ( $post->ID, '_pk_customer_company', true );
        $email = get_post_meta ( $post->ID, '_pk_customer_email', true );
        $phone = get_post_meta ( $post->ID, '_pk_customer_telephone', true );
    ?>             

        <p>
        <strong>Prénom : </strong><?php echo esc_html($first_name); ?> 
        <strong>Nom : </strong><?php echo esc_html(' ' . $last_name); ?> <br />
        </p> 
        <?php if ( ! empty ($company) ) : ?>
        <p><strong>Société : </strong><?php echo esc_html($company); ?></p> <br />
        <?php endif; ?>
        <p>
            <strong>Email : </strong><a href="mailto:<?php echo esc_attr($email) ?>"><?php echo esc_html( $email ); ?></a><br />
            <strong>Téléphone : </strong><a href="mailto:<?php echo esc_attr($phone) ?>"><?php echo esc_html( $phone ); ?></a>
        </p>

    <?php }

    function pk_reservation_delivery_box_html ($post) { // HTML pour la box infos de livraison
        $address = get_post_meta ( $post->ID, '_pk_customer_address', true );
        $postal_code = get_post_meta ( $post->ID, '_pk_customer_postal_code', true );
        $city = get_post_meta ( $post->ID, '_pk_customer_city', true );
    ?>             

        <p>
        <strong>Adresse : </strong><?php echo esc_html($address); ?> <br /> 
        <strong>Code postal : </strong><?php echo esc_html($postal_code); ?> <br />
        <strong>Ville : </strong><?php echo esc_html($city); ?>
        </p> 

    <?php }

    function pk_reservation_customer_message_html ($post) { // HTML pour le message client
        $message = get_post_meta ( $post->ID, '_pk_customer_message', true );
    ?>             

    <p>
    <?php if (! empty ($message)) {
        echo esc_html($message); 
    } else {
        echo esc_html ('Le client n\'a pas ajouté de message supplémentaire.');
    } 
    ?>
    </p> 

    <?php }
    
