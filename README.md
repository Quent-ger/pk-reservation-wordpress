# Photokit Réservation pour WordPress
Extension Wordpress réalisée au cours de mon stage pour Photokit (printemps 2025).
Cette extension met en place une logique de réservations avec affichages des données clients dans l'admin WordPress par le biais de "Meta Boxes". 

## Fonctionnalités
* Création d'un type de publication personnalisé "Réservation"
* Gestion du statut de la réservation via une meta box personnalisée dans l'interface d'administration
* Ajout d'un rôle client "Photokit Client" pour gérer les droits d'accès aux réservations et au formulaire de réservation côté client
* Auto-création d'une page "Ma réservation Photokit", comportant un shortcode qui contient le formulaire de réservation

## Installation
1.  Téléchargez le plugin.
2.  Rendez-vous sur la page d'installation d'extensions sur votre installation Wordpress et téléchargez le dossier
OU Téléchargez le dossier `pk_reservation` dans le répertoire `wp-content/plugins/` de votre installation WordPress.
3.  Activez le plugin via l'interface d'administration de WordPress.

## Utilisation
Après activation, un nouveau type de publication "Réservations" apparaîtra dans votre tableau de bord WordPress. Vous pouvez y ajouter de nouvelles réservations et gérer leur statut.
Une nouvelle page "Ma réservation Photokit" sera crée, elle contient le shortcode du formulaire.

## Note sur le Développement
Ce plugin a été développé dans le cadre d'un projet d'apprentissage et certaines sections ont été assistées par l'IA (comme indiqué dans les commentaires du code), ce qui a permis d'explorer rapidement les concepts de base de WordPress.