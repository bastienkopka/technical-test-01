# Explication

## La partie "block custom"
* Mise en place d'une méthode ```getEventsNodes()``` qui permet de récupérer les contenus
événements à afficher.
* Utilisation de cette méthode dans la fonction ```build()``` ou ont récupère l'affichage
des contenus via le view Builder. A noter que l'on récupérera le display "teaser".
* Dans le preprocess ```adimeo_events_preprocess_node()```, je fais appel à un service qui
permets de build le block et le rendre sous forme de variable afin de l'utiliser dans le twig.
* Mise en place d'un fichier twig ```node--event.html.twig``` dans le thème.

## La partie "Cron"
* Dans le ```.module```, j'ai ajouté le hook cron ```adimeo_events_cron``` qui se charge
de récupérer l'ensemble des nodes qui ont une date de fin inférieur à la date du jour et de créer
un item dans la queue.
* Mise en place d'un QueueWorker qui va simplement dépublier et sauvegarder le contenu
pour chaque item valide.

## Composer
* Modifications des packages drupal core pour avoir la dernière version de drupal 9.
* J'ai placé devel dans les packages de dév
* J'ai ajouté des packages pour phpcs et phpstan

## Temps passé
* Environ 1 heure sur le montage de l'environnement (modification de ma stack docker pour coller
aux fichiers données).
* Environ 30 minutes de réflexion sur la meilleure facon de faire.
* Environ 2 heures pour faire la partie code et tests.


## Remarques
* Les versions n'étaient pas fixées au niveau de composer.
  Il est important de spécifier une version pour éviter tout problème.
* Devel se trouvait dans une la section "require", ca n'aurait pas du être le cas.
* Faire attention aux "system name" donnés, j'ai pu remarquer que le type de contenu
  "Evénements" a un "system name" en francais alors qu'il aurait dû être en Anglais.
