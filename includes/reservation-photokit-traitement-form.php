<?php function pk_traitement_reservation_form() {
     error_log( 'pk_traitement_reservation_form() a été appelée.' );
    // Vérifications de sécurité -- aidé par IA

        // Vérification de la présence du nonce
        if (!isset($_POST['pk_reservation_nonce_submit'])) {
            wp_die ('Erreur de sécurité : Tentative de soumission du formulaire non autorisée ou nonce invalide');
        }

        // Vérification de la valeur du nonce
         if (!wp_verify_nonce($_POST['pk_reservation_nonce_submit'], 'pk_reservation_form_submit')) { // nom d'abord, action après
            wp_die('Erreur de sécurité : Nonce invalide.');
        }


        
        // Vérification de la méthode de requête
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_die('Méthode invalide');
        }


    // Initialisation de tableaux pour les erreurs et pour les données validées
        $errors = [];
        $verified_data = [];

        // On met tout dans des variables temporaires après sanitization
        $temp_first_name    = isset($_POST['pk_customer_firstname']) ? sanitize_text_field($_POST['pk_customer_firstname']) : '';
        $temp_last_name     = isset($_POST['pk_customer_lastname']) ? sanitize_text_field($_POST['pk_customer_lastname']) : '';
        $temp_company       = isset($_POST['pk_customer_company']) ? sanitize_text_field($_POST['pk_customer_company']) : '';
        $temp_email         = isset($_POST['pk_customer_email']) ? sanitize_email($_POST['pk_customer_email']) : '';
        $temp_phone         = isset($_POST['pk_customer_telephone']) ? sanitize_text_field($_POST['pk_customer_telephone']) : '';
        $temp_address       = isset($_POST['pk_customer_address']) ? sanitize_text_field($_POST['pk_customer_address']) : '';
        $temp_postal_code   = isset($_POST['pk_customer_postal_code']) ? sanitize_text_field($_POST['pk_customer_postal_code']) : '';
        $temp_city          = isset($_POST['pk_customer_city']) ? sanitize_text_field($_POST['pk_customer_city']) : '';
        $temp_start_date    = isset($_POST['pk_reservation_start_date']) ? sanitize_text_field($_POST['pk_reservation_start_date']) : '';
        $temp_end_date      = isset($_POST['pk_reservation_end_date']) ? sanitize_text_field($_POST['pk_reservation_end_date']) : '';
        $temp_message       = isset($_POST['pk_customer_message']) ? wp_kses_post($_POST['pk_customer_message']) : ''; // Pour textarea

    // Traitement et validation
        
    
    // infos personnelles

        // Prénom
        if ( empty( $temp_first_name) ) { // si la variable est vide
            $errors['pk_customer_firstname'] = 'Le prénom est requis.'; // on ajoute une erreur
        } else {
            // étape : vérification  de la longueur de chaîne de caractères
            if (strlen($temp_first_name)>35) {
                $errors['pk_customer_firstname'] = 'Le prénom ne peut dépasser 35 caractères';
            } else {
                $verified_data['pk_customer_firstname'] = $temp_first_name; // on l'enregistre
            }
        }

        // Nom
        if ( empty( $temp_last_name ) ) {
            $errors['pk_customer_lastname'] = 'Le nom de famille est requis.';
        } else { 
            // vérification  de la longueur de chaîne de caractères
            if (strlen($temp_last_name)>50) {
                $errors['pk_customer_lastname'] = 'Le nom de famille ne peut dépasser 50 caractères';
            } else {
                $verified_data['pk_customer_lastname'] = $temp_last_name;
            }
        }

        // Société : puisque optionnel il ne sera ajouté au tableau que si il est rempli
        $verified_data['pk_customer_company'] = $temp_company;

    // Coordonnées

        // Email
        if (empty ($temp_email)) {
            $errors['pk_customer_email'] = 'L\'adresse email est requise';
        } else {
        
            if ( ! is_email($temp_email) ) {
                $errors['pk_customer_email']= 'L\'adresse mail n\'est pas valide';
            }
            
            else {
                $verified_data['pk_customer_email'] = $temp_email;
            }
        }    
        
        // Téléphone
         if (empty ($temp_phone)) {
            $errors['pk_customer_telephone'] = 'Le numéro de téléphone est requis';
        } else {
        
            // Expression régulière pour les numéros de tél français -- assisté par IA 
            $french_phone_regex = '/^(?:(?:\\+|00)33|0)[1-9](?:[\\s.-]*\\d{2}){4}$/';

            $cleaned_telephone = trim($temp_phone); // on enlève lesespaces
            
            if ( !preg_match ($french_phone_regex, $cleaned_telephone)) { // comparaison du numéro entré à l'expression régulière
                $errors['pk_customer_telephone'] = 'Le numéro de téléphone n\'est pas valide';
            } else {
                $verified_data['pk_customer_telephone'] = preg_replace('/[^0-9+]/', '', $cleaned_telephone); // enlève tous les caractères non-numériques lors de l'enregistrement
            }

        }    

        // Adresse 

        if (empty($temp_address)) {
            $errors['pk_customer_address'] = 'L\'adresse est requise.';
        } else {
        
            // Vérification de la longueur
            $min_length = 5;
            $max_length = 100;

            if (strlen($temp_address) < $min_length) { 
                $errors['pk_customer_address'] = 'L\'adresse est trop courte.';
            } elseif (strlen($temp_address) > $max_length) {
                $errors['pk_customer_address'] = 'L\'adresse est trop longue.';
            } else {
                $verified_data['pk_customer_address'] = $temp_address;
            }
        }

        // Code Postal
        if (empty($temp_postal_code)) {
            $errors['pk_customer_postal_code'] = 'Le code postal est requis.';
        } else {
            // Vérification regex du format de code postal français
            if (!preg_match('/^\s*\d{5}\s*$/', $temp_postal_code)) {   
                $errors['pk_customer_postal_code'] = 'Le code postal n\'est pas valide (format incorrect).';
            } else {
                $verified_data['pk_customer_postal_code'] = $temp_postal_code;
            }
        }


        // Ville
        if (empty($temp_city)) {
            $errors['pk_customer_city'] = 'La ville est requise.';
        } else {
            // Vérification de longueur de chaîne
            $max_length = 50;  //  au moins Saint-Remy-en-Bouzemont-Saint-Genest-et-Isson et de la marge

            if (strlen($temp_city) > $max_length) { // comparaison de longueur supérieure au max
                $errors['pk_customer_city'] = 'Le nom de la ville est trop long.';
            } else {
                $verified_data['pk_customer_city'] = $temp_city;
            }
        }   

    // Détails de la réservation 

        // Date de début -- assisté par IA
        if (empty ($temp_start_date) ) {
            $errors['pk_reservation_start_date'] = 'La date de début de réservation est obligatoire';
        } else {
            $start_date = $_POST['pk_reservation_start_date'];
            $sanitized_start_date = sanitize_text_field($start_date);

            // Création d'un objet date à partir de la variable $temp_start_date
            $start_date_object = DateTime::createFromFormat('Y-m-d', $temp_start_date);

            // Vérifie si la date est valide ET si elle correspond au format exact
            if (!$start_date_object || $start_date_object->format('Y-m-d') !== $temp_start_date) { 
            $errors['pk_reservation_start_date'] = 'Format de la date de début invalide.';
            } else {
                // Obtenir la date d'aujourd'hui sans l'heure pour la comparaison
                $today = new DateTime();
                $today->setTime(0, 0, 0);

                // Vérifier si la date de début n'est pas dans le passé
                    if ($start_date_object < $today) {
                        $errors['pk_reservation_start_date'] = 'La date de début ne peut pas être dans le passé.';
                    } else {
                        // Si tout est bon, stocker la date nettoyée
                        $verified_data['pk_reservation_start_date'] = $temp_start_date;
                    } // fin vérificatoin date valide (au moins = aujourd'hui)

            } // fin comparaison date      

        } // fin de la condtion sur start_date

        // Date de fin
        if( !isset ( $verified_data['pk_reservation_start_date'] ) ) { // si aucune date de début n'a été définie
            $errors['pk_reservation_end_date'] = 'La réservation doit d\'abord comporter une date de début valide.'; 

        } elseif (empty( $_POST['pk_reservation_end_date'])) {
                $errors['pk_reservation_end_date'] = 'La date de fin de réservation est obligatoire';

        } else {

            
            // la date de début est bien présente et validée, le champ de date de fin n'est pas vide, on continue
            
            // On calcule la date de fin ATTENDUE basée sur la date de début validée -- asisté par IA
            $start_date_object_validated = DateTime::createFromFormat('Y-m-d', $verified_data['pk_reservation_start_date']); // création d'un objet DateTime début pour la comparaison de dates
            $expected_end_date_object = clone $start_date_object_validated; // on copie la date de début
            $expected_end_date_object->modify('+7 days'); // on la modifie en ajoutant une semaine
            $expected_end_date_string = $expected_end_date_object->format('Y-m-d'); // et on s'assure qu'elle ait un format commun aux autres dates

            $end_date_object = DateTime::createFromFormat('Y-m-d', $temp_end_date); // création d'un objet DateTime pour la date de fin

            // Vérifie si la date saisie est valide ET si elle correspond à la date ATTENDUE

            // Si l'objet end_date existe et que la valeur end_date récupérée du formulaire sont égales à la date de fin prévue (1 semaine après)
            if (!$end_date_object || $temp_end_date !== $expected_end_date_string) {
                $errors['pk_reservation_end_date'] = 'La date de fin de réservation doit être une semaine après la date de début (' . esc_html($expected_end_date_string) . ').';
            } else {
                $verified_data['pk_reservation_end_date'] = $temp_end_date;
            }

        } // fin conditoin date de fin


    // Message du client

    if( !empty ($temp_message ) ) {
        $max_message_length = 500; // longeur max du messsage

        if (strlen($temp_message) > $max_message_length) { // si le message sanitizé et nettoyé dépasse 500 caractères
            $errors['pk_customer_message'] = 'Votre message est trop long (maximum ' . $max_message_length . ' caractères).';
        } else {
            $verified_data['pk_customer_message'] = $temp_message;
        }
    }


// --- GESTION DES ERREURS ET DES DONNEES VALIDEES ---

    // Si des erreurs existent, on ne fait rien de plus que les stocker
    if ( ! empty($errors) ) {
        // Stocke les erreurs et les données soumises (même invalides) en session pour pouvoir les réafficher sur le formulaire.
        // stockage dans des transient pour redirection.
        set_transient( 'pk_form_errors', $errors, HOUR_IN_SECONDS ); // set transient = mise en cache temporaire dans la base de données ~ options mais limitées
        set_transient( 'pk_form_data', $_POST, HOUR_IN_SECONDS ); // Stocke $_POST tel quel pour pré-remplir le formulaire (amélioration UX)
        
        // Redirection vers la page du formulaire avec les erreurs
        $form_page_url = get_permalink( get_option('pk_formulaire_reservation_id') ); // récupère l'ID de la page du formulaire
        wp_redirect( $form_page_url. '?status=error1'); // redirige cette page avec un message dans l'URL pour indiquer une erreur dans le remplissage
        exit; // nécessaire après un wp_redirect, assure que le code suivant n'est pas traité
    } else {

        // AUCUNE ERREUR, on peut procéder

        // Créer un nouveau post 
        $post_id = wp_insert_post([
            'post_title' => 'Réservation du ' . $verified_data['pk_reservation_start_date'] . ' par ' . $verified_data['pk_customer_firstname'] . ' ' . $verified_data['pk_customer_lastname'],
            'post_status' => 'pending', // pour être sûr qu'il soiten attente par défaut
            'post_type' => 'pk_reservation', // C'est un post du CPT pk_reservation
        ]);

        // Gérer l'erreur si la création du post échoue -- assisté par IA
        if ( is_wp_error( $post_id ) ) {
            
            $errors['general'] = 'Une erreur est survenue lors de l\'enregistrement de votre réservation.';
            set_transient( 'pk_form_errors', $errors, HOUR_IN_SECONDS );
            set_transient( 'pk_form_data', $_POST, HOUR_IN_SECONDS );
            $form_page_url = get_permalink( get_option('pk_formulaire_reservation_id') );
            wp_redirect( $form_page_url . '?status=error2');
            exit;
        }

        // Enregistrer les données validées en tant que post meta
        update_post_meta( $post_id, '_pk_customer_firstname', $verified_data['pk_customer_firstname'] );
        update_post_meta( $post_id, '_pk_customer_lastname', $verified_data['pk_customer_lastname'] );
        update_post_meta( $post_id, '_pk_customer_company', $verified_data['pk_customer_company'] ); // Même si vide, c'est enregistré
        update_post_meta( $post_id, '_pk_customer_email', $verified_data['pk_customer_email'] );
        update_post_meta( $post_id, '_pk_customer_telephone', $verified_data['pk_customer_telephone'] );
        update_post_meta( $post_id, '_pk_customer_address', $verified_data['pk_customer_address'] );
        update_post_meta( $post_id, '_pk_customer_postal_code', $verified_data['pk_customer_postal_code'] );
        update_post_meta( $post_id, '_pk_customer_city', $verified_data['pk_customer_city'] );
        update_post_meta( $post_id, '_pk_reservation_start_date', $verified_data['pk_reservation_start_date'] );
        update_post_meta( $post_id, '_pk_reservation_end_date', $verified_data['pk_reservation_end_date'] );
        update_post_meta( $post_id, '_pk_customer_message', $verified_data['pk_customer_message'] ); // Même si vide, c'est enregistré

        // Suppression des transients une fois le traitement réussi
        delete_transient( 'pk_form_errors' );
        delete_transient( 'pk_form_data' );

        // Redirection vers la page de succès
        $success_page_url = get_permalink( get_option('pk_page_confirmation_id') );
        wp_redirect( $success_page_url . '?status=success');
        exit;
    }

} // fin de la fonction de traitement


 

    