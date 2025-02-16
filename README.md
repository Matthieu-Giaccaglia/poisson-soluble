# Météo-Notification

## Prérequis pour lancer en local

- PHP 8.4
- Composer
- Symfony CLI
- Docker

## Lancer l'application

Installer les dépendances composer

```console
composer install
```

Lancer docker

```console
docker-compose up
```

Lancer le serveur

```console
symfony server:start
```

Mettre à jour la base de donnée

```console
php bin/console sql-migrations:execute
```

## Configuration

- Connexion à la base de donnée :
  - USER : root
  - Password : password
  - Port : 5432
- Clé d'api : AZERTY123456 (Modifiable dans le fichier .env)

## Migration de la base de donnée

Pour migrer la base de donnée avec les nouvelles version, il suffit de lancer :

```console
php bin/console sql-migrations:execute
```

Vous pouvez ajouter l'option --dry-run pour vérifier quelles seront les requêtes qui vont être lancées.

```console
php bin/console sql-migrations:execute --dry-run
```

## Importer un fichier CSV

Le CSV devra obligatoirement avoir les colonnes "insee" et "telephone". Un fichier d'exemple est disponible ([insee_telephone.csv](./csv_file/insee_telephone.csv)).
Le numéro de téléphone ne devra pas avoir d'espace, exemple : 0601020304 ou +33601020304.
Le numéro INSEE devra être composé de 5 chiffres.

La commande pour importer le fichier :
```console
php bin/console app:import-csv [CSV_PATH] --error-detail --separator=[CSV_SEPARATOR]
```

- "--error-detail" permet d'afficher quelles sont lignes qui n'ont pas été sauvegardés et pourquoi.
- "--separator" permet de définir quel séparateur le script doit utiliser pour le CSV. ";" par défaut.

## Alerter les destinataires

Il est possible d'alerter les destinataires ayant le même numéro INSEE.
Pour cela il faut lancer l'api ci-dessous en GET uniquement et mettre dans le header "X-API-KEY" qui contiendra la clé d'api :
```link
[YOUR_URL]/api/alerter?insee=[INSEE]&message=[MESSAGE]
```
Les logs des envois des sms sont présents dans ./var/cache/*.log.
