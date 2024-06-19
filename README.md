# CalendrierVacances v2
Version 2 de calendrier vacances

## Objectif

Créez la version 2 de l'application Calendrier Vacances en améliorant ses performances et l'expérience utilisateur.
Les collaborateurs doivent pouvoir soumettre leurs demandes de congés via l'application, tout en ayant la possibilité de consulter leur solde de congés disponibles ainsi que les demandes des autres collaborateurs.
Un suivi rigoureux des demandes est essentiel, et il sera assuré par l'envoi automatique de notifications par courriel depuis l'application.


### Documentations

- **FullCalender :** https://fullcalendar.io/docs
- **Github de FullCalender :** https://github.com/fullcalendar/fullcalendar/blob/d26ef0e7677a7236b31a5b530826dfd5c9926c10/packages/core/src/styles/button.css

### Versions

- **Symfony :** 7.0.6
- **PHP :** 8.2.7
- **MariaDB :** mariadb-10.11.4

-- [ Preparer la BDD Pour les Tests ] --
Supprimer la BDD_test
```
symfony console doctrine:database:drop --force --env=test
```

Recréer la BDD_test
```
symfony console doctrine:database:create --env=test
```

Aprés avoir créé la BDD_test
```
symfony console doctrine:schema:c --env=test
```
Ce qui a pour interet de ne pas avoir a creer la bdd et la supprimer entre chaque test, c'est très long.


-- [ Lancer les Test avec PEST 2 ] --
```
./vendor/bin/pest
```

-- [ Verifier la couverture du Code ] --
```
XDEBUG_MODE=coverage ./vendor/bin/pest --coverage
```
