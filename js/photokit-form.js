document.addEventListener('DOMContentLoaded', function() {  
    // Le script prend effet que lorsque le HTML a été chargé :  anticipation d'erreurs de variables = null  

    const reservationStartDate = document.getElementById('pk_reservation_start_date');
        
    const reservationEndDate = document.getElementById('pk_reservation_end_date');


    reservationStartDate.addEventListener('change', function() { // regarde l'évènement changement sur le champ du formulaire
        // Récupération de la valeur de l'élèmen HTML date de début (pk_start... etc)
            const startDateString = reservationStartDate.value;


        console.log("Date de début sélectionnée :", startDateString);

    });
});