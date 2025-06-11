<?php function pk_traitement_reservation_form() {
    // Vérifications de sécurité -- aidé par IA

        // Vérification du nonce
        if (!isset($_POST['pk_reservation_nonce_field'])) {
            wp_die ('Erreur de sécurité : Tentative de soumission du formulaire non autorisée ou nonce invalide');
        }
        
        // Vérification de la méthode de requête
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_die('Méthode invalide');
        }


    // Initialisation de tableaux pour les erreurs et pour les données validées
        $errors = array();
        $verified_data = array();

    // Traitement et validation
        
    // infos personnelles

        // Prénom
        if ( empty( $_POST['pk_customer_firstname']) ) { // si le champ est vide
            $errors[] = 'Le prénom est requis.'; // on ajoute une erreur
        } else {
            // étape 1 : 
            $first_name_sanitized = sanitize_text_field( $_POST['pk_customer_firstname']); 

            // étape 2 : vérification  de la longueur de chaîne de caractères
            if (strlen($first_name_sanitized)>35) {
                $errors[] = 'Le prénom ne peut dépasser 35 caractères';
            } else {
                $verified_data['pk_customer_firstname'] = $first_name_sanitized; // on l'enregistre
            }
        }

        // Nom
        if ( empty( $_POST['pk_customer_lastname']) ) {
            $errors[] = 'Le nom de famille est requis.';
        } else {
            // étape 1 : 
            $last_name_sanitized = sanitize_text_field( $_POST['pk_customer_lastname']);

            // étape 2 : vérification  de la longueur de chaîne de caractères
            if (strlen($first_name_sanitized)>50) {
                $errors[] = 'Le nom de famille ne peut dépasser 50 caractères';
            } else {
                $verified_data['pk_customer_lastname'] = $last_name_sanitized;
            }
        }

        // Société (optionnel) 
        if (! empty($_POST['pk_customer_company'])) {
            $verified_data['pk_customer_company'] = sanitize_text_field( $_POST['pk_customer_company']) ;
        } else {
            $verified_data['pk_customer_company'] = ''; // s'assurer qu'il-y-a une valeur, même vide, pour l'enregistrement 
        }

    // Coordonnées

        // Email
        if (empty ($_POST['pk_customer_email'])) {
            $errors[] = 'L\'adresse email est requise';
        } else {
            $email = sanitize_email($_POST['pk_customer_email']);

            if ( ! is_email($email) ) {
                $errors[]= 'L\'adresse mail n\'est pas valide';
            }
            
            else {
                $verified_data['pk_customer_email'] = $email;
            }
        }    
        
        // Téléphone
         if (empty ($_POST['pk_customer_telephone'])) {
            $errors[] = 'Le numéro de téléphone est requis';
        } else {
            $telephone = $_POST['pk_customer_telephone'];

            // Expression régulière pour les numéros de tél français -- assisté par IA 
            $french_phone_regex = '/^(?:(?:\\+|00)33|0)[1-9](?:[\\s.-]*\\d{2}){4}$/';

            $cleaned_telephone = trim($telephone); // on enlève lesespaces
            
            if ( !preg_match ($french_phone_regex, $cleaned_telephone)) { // comparaison du numéro entré à l'expression régulière
                $errors[] = 'Le numéro de téléphone n\'est pas valide';
            } else {
                $verified_data[] = preg_replace('/[^0-9+]/', '', $cleaned_telephone); // enlève tous les caractères non-numériques lors de l'enregistrement
            }

        }    

        // Adresse 