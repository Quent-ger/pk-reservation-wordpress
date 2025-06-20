    document.addEventListener('DOMContentLoaded', function() {  
        // Le script prend effet que lorsque le HTML a été chargé :  anticipation d'erreurs de variables = null  

        const reservationStartDate = document.getElementById('pk_reservation_start_date');
            
        const reservationEndDate = document.getElementById('pk_reservation_end_date');

// regarde l'évènement changement sur le champ du formulaire
        reservationStartDate.addEventListener('change', function() { 
            // Récupération de la valeur de l'élèment HTML date de début (pk_start... etc)

                const startDateString = reservationStartDate.value;


            // On transforme la date de départ en objet date pour luis appliquer une méthode plus tard   
             
                const startDateObj = new Date(startDateString);


            // Création d'une nouvelle date pour l'objet date de fin pour éviter de faire une référence

                let reservationEndDateObj=new Date(startDateObj);
                // la méthode pour modifier, ici on ajoute 7 jours
                reservationEndDateObj.setDate(reservationEndDateObj.getDate() + 7);


            // création d'une variable dédiée à la mise au bon format de l'objet date de fin vers une chaîne de caractères
                const tempEndString=reservationEndDateObj.toISOString();
                let reservationEndString = tempEndString.slice(0,10);


            // on assigne la valeur de la chaîne de caractères à la valeur de l'élèment HTML date de fin
                reservationEndDate.value = reservationEndString;
                

            // on l'assigne également à l'attribut min pour l'effet de coloration en gris sur le calendrier    
                reservationEndDate.min = reservationEndString;

        });
    });