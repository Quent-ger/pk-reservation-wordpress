<?php function pk_traitement_reservation_form() {
    // Vérifications de sécurité -- aidé par IA

        // Vérification de la présence du nonce
        if (!isset($_POST['pk_reservation_nonce_field'])) {
            wp_die ('Erreur de sécurité : Tentative de soumission du formulaire non autorisée ou nonce invalide');
        }

        // Vérification de la valeur du nonce
         if (!wp_verify_nonce($_POST['pk_reservation_nonce_field'], 'pk_reservation_nonce_submit')) {
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
        if ( empty( $_POST['pk_customer_lastname']) ) {
            $errors['pk_customer_lastname'] = 'Le nom de famille est requis.';
        } else {
            // étape 1 : 
            $last_name_sanitized = sanitize_text_field( $_POST['pk_customer_lastname']);

            // étape 2 : vérification  de la longueur de chaîne de caractères
            if (strlen($last_name_sanitized)>50) {
                $errors['pk_customer_lastname'] = 'Le nom de famille ne peut dépasser 50 caractères';
            } else {
                $verified_data['pk_customer_lastname'] = $last_name_sanitized;
            }
        }

        // Société (optionnel) 
        if (! empty($_POST['pk_customer_company'])) { // si le champ n'est pas vide
            $verified_data['pk_customer_company'] = sanitize_text_field( $_POST['pk_customer_company']) ;
        } else {
            $verified_data['pk_customer_company'] = ''; // s'assurer qu'il-y-a une valeur, même vide, pour l'enregistrement 
        }

    // Coordonnées

        // Email
        if (empty ($_POST['pk_customer_email'])) {
            $errors['pk_customer_email'] = 'L\'adresse email est requise';
        } else {
            $email = sanitize_email($_POST['pk_customer_email']);

            if ( ! is_email($email) ) {
                $errors['pk_customer_email']= 'L\'adresse mail n\'est pas valide';
            }
            
            else {
                $verified_data['pk_customer_email'] = $email;
            }
        }    
        
        // Téléphone
         if (empty ($_POST['pk_customer_telephone'])) {
            $errors['pk_customer_telephone'] = 'Le numéro de téléphone est requis';
        } else {
            $telephone = $_POST['pk_customer_telephone'];

            // Expression régulière pour les numéros de tél français -- assisté par IA 
            $french_phone_regex = '/^(?:(?:\\+|00)33|0)[1-9](?:[\\s.-]*\\d{2}){4}$/';

            $cleaned_telephone = trim($telephone); // on enlève lesespaces
            
            if ( !preg_match ($french_phone_regex, $cleaned_telephone)) { // comparaison du numéro entré à l'expression régulière
                $errors['pk_customer_telephone'] = 'Le numéro de téléphone n\'est pas valide';
            } else {
                $verified_data['pk_customer_telephone'] = preg_replace('/[^0-9+]/', '', $cleaned_telephone); // enlève tous les caractères non-numériques lors de l'enregistrement
            }

        }    

        // Adresse 

        if (empty($_POST['pk_customer_address'])) {
            $errors['pk_customer_address'] = 'L\'adresse est requise.';
        } else {
            $address = $_POST['pk_customer_address'];

            $sanitized_address = sanitize_text_field($address);

            // Vérification de la longueur
            $min_length = 5;
            $max_length = 100;

            if (strlen($sanitized_address) < $min_length) { 
                $errors['pk_customer_address'] = 'L\'adresse est trop courte.';
            } elseif (strlen($sanitized_address) > $max_length) {
                $errors['pk_customer_address'] = 'L\'adresse est trop longue.';
            } else {
                $verified_data['pk_customer_address'] = $sanitized_address;
            }
        }

        // Code Postal
        if (empty($_POST['pk_customer_postal_code'])) {
            $errors['pk_customer_postal_code'] = 'Le code postal est requis.';
        } else {
            $postal_code = $_POST['pk_customer_postal_code'];

            $sanitized_postal_code = sanitize_text_field($postal_code);

            // Vérification regex du format de code postal français
            if (!preg_match('/^\s*\d{5}\s*$/', $sanitized_postal_code)) {   
                $errors['pk_customer_postal_code'] = 'Le code postal n\'est pas valide (format incorrect).';
            } else {
                $verified_data['pk_customer_postal_code'] = $sanitized_postal_code;
            }
        }


        // Ville
        if (empty($_POST['pk_customer_city'])) {
            $errors['pk_customer_city'] = 'La ville est requise.';
        } else {
            $city = $_POST['pk_customer_city'];

            $sanitized_city = sanitize_text_field($city);

            // Vérification de longueur de chaîne
            $max_length = 50;  //  au moins Saint-Remy-en-Bouzemont-Saint-Genest-et-Isson et de la marge

            if (strlen($sanitized_city) > $max_length) { // comparaison de longueur supérieure au max
                $errors['pk_customer_city'] = 'Le nom de la ville est trop long.';
            } else {
                $verified_data['pk_customer_city'] = $sanitized_city;
            }
        }   

    // Détails de la réservation 

        // Date de début -- assisté par IA
        if (empty ($_POST['pk_reservation_start_date'])) {
            $errors['pk_reservation_start_date'] = 'La date de début de réservation est obligatoire';
        } else {
            $start_date = $_POST['pk_reservation_start_date'];
            $sanitized_start_date = sanitize_text_field($start_date);

            // Création d'un objet date à partir de la variable $sanitized_start_date
            $start_date_object = DateTime::createFromFormat('Y-m-d', $sanitized_start_date);

            // Vérifie si la date est valide ET si elle correspond au format exact
            if (!$start_date_object || $start_date_object->format('Y-m-d') !== $sanitized_start_date) {
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
                        $verified_data['pk_reservation_start_date'] = $sanitized_start_date;
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

            $sanitized_end_date = sanitize_text_field($_POST['pk_reservation_end_date']);
            $end_date_object = DateTime::createFromFormat('Y-m-d', $sanitized_end_date); // création d'un objet DateTime pour la date de fin

            // Vérifie si la date saisie est valide ET si elle correspond à la date ATTENDUE

            // Si l'objet end_date existe et que la valeur end_date récupérée du formulaire sont égales à la date de fin prévue (1 semaine après)
            if (!$end_date_object || $sanitized_end_date !== $expected_end_date_string) {
                $errors['pk_reservation_end_date'] = 'La date de fin de réservation doit être une semaine après la date de début (' . esc_html($expected_end_date_string) . ').';
            } else {
                $verified_data['pk_reservation_end_date'] = $sanitized_end_date;
            }

        } // fin conditoin date de fin


    // Message du client

    if( !empty ($_POST['pk_customer_message']) ) {
        $message_input = ($_POST['pk_customer_message']);
        $sanitized_message = wp_kses_post($message_input); // fonction Wordpress pour sanitizer les champs textarea
        $sanitized_message = trim($sanitized_message); // nettoyage

        $max_message_length = 500; // longeur max du messsage

        if (strlen($sanitized_message) > $max_message_length) { // si le message sanitizé et nettoyé dépasse 500 caractères
            $errors['pk_customer_message'] = 'Votre message est trop long (maximum ' . $max_message_length . ' caractères).';
        } else {
            $verified_data['pk_customer_message'] = $sanitized_message;
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
        wp_redirect( $form_page_url. '?status=error'); // redirige cette page avec un message dans l'URL pour indiquer une erreur dans le remplissage
        exit; // nécessaire après un wp_redirect, assure que le code suivant n'est pas traité
    } 

} // fin de la fonction de traitement


 

    