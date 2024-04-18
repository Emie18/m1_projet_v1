# Projet Gestion des stages
## Emilie le Rouzic & Thibault Tanné

# Configuration de la machine
- Adresse IP : 10.10.64.11
- Mot de passe : ******
- host : admin

# Configuration de l'interface (fichier ".env")
- Pour vider le cache: dans le répertoire "/var/www/ProjetM1Stage", lancer la commande "sudo php bin/console cache:clear" (utiliser le mot de passe de la machine)
## Configuration de la base de données
- ligne 27 : DATABASE_URL="mysql://stageUser:stagePwd@127.0.0.1:3306/stage?serverVersion=mariadb-10.3.39&charset=utf8mb4"
- User: stageUser
- Mot de passe : stagePwd
- Nom de la base de données : stage
## Mise en production
- ligne 18
- remplacer "dev" par "prod"
- dans le répertoire "/var/www/ProjetM1Stage", lancer la commande "sudo php bin/console cache:clear --env=prod" (utiliser le mot de passe de la machine)

# Configuration des constantes
## Couleurs 
- fichier "public/css/nav_footer.css
- ligne 1 ":root"
## Nombre d'éléments pour la pagination
- fichier "public/js/pagination.js"
- ligne 2
## Logo de l'ISEN
- fichier "public/logo/ISEN-Brest-blanc.png"
## Attributs pour le fichier .CSV
- fichier "src/Controller/BackController.php"
- Dans le constructeur, ligne 83
# Liste des chemins
- /
- /statistique
- /back/
- /back/statistique
- /back/tuteur-isen
- /back/tuteur-stage
- /back/apprenant
- /back/entreprise
- /back/import-file
