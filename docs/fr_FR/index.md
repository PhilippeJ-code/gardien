# Plugin gardien pour Jeedom

    Ce plugin permet de surveiller des équipements et des commandes.
        Contrôle de la date de dernière communication pour l'équipement.
        Contrôle de la date de dernière collecte ou de sa valeur pour la commande.

## 1. Configuration du plugin

    Rien de particulier dans la configuration ce plugin. Le cron à la minute est indispensable pour le bon
    fonctionnement du plugin.

## 2. Onglet "Horaire"

    Cet onglet permet de choisir un cron pour la surveillance des équipements.

## 3. Onglet "Surveillance"

    Cet onglet permet de choisir les équipements et les commandes à surveiller. On retrouve dans cet onglet
    le statut de la surveillance et la valeur calculée lors du changement de statut de la surveillance. 
    Pour des raisons de performance cette valeur de statut n'est mémorisée qu'au changement d'état du contrôle,
    la commande Refresh permet de forcer la mémorisation.

## 3.1 Choix d'un équipement

    On choisit un équipement pour lequel la date de dernière communication sera surveillée. On ajoute
    une condition par exemple "< 3600" pour indiquer que la différence entre la date/heure actuelle et la
    date de dernière communication doit être inférieure à 3600 secondes.

## 3.2 Choix d'une commande

    On choisit une commande pour laquelle la date de dernière collecte ou la valeur sera surveillée. On coche 
    "Date de collecte" pour la surveiller et sinon ce sera la valeur de la commande qui sera surveillée. On ajoute
    une condition par exemple "< 3600" pour indiquer que la différence entre la date/heure actuelle et la
    date de dernière collecte doit être inférieure à 3600 secondes ( date de collecte cochée ) ou "> 17" pour indiquer 
    que la valeur de la commande doit être > à 17 ( date de collecte non cochée ).
    
## 4. Onglet "Actions"

    Cet onglet permet de choisir les actions qui seront effectuées lors d'un changement d'état de la surveillance.

