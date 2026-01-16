
BLUE='\033[1;34m'
GREEN='\033[1;32m'
RED='\033[1;31m'
PURPLE='\033[1;35m'
YELLOW='\033[1;33m'
NC=$'\033[0m'


utilisateur=""
mdp=""
serveur=""
base=""
chaine=""
scriptCreationBdChemin="./Windisgn/V6.SQL"

# Function qui gére l'interaction avec les utilisateurs
function interaction(){
  echo -e "${BLUE}---------------------"
  echo  " Initialisation du projet"
  echo -e "---------------------"
  echo ""
  echo " 1) Tout en Un"
  echo " 2) Initialisation des dépendances"
  echo " 3) Executer le projet en mode dev"
  echo " 4) Suppression des données de test"
  echo -e "${PURPLE} 5) Partie développeur"
  echo -e "${BLUE}"

  read -p "Entrez un numéro : " choice

  case $choice in
  1)
    toutEnUn  
    ;;
  2)
    installDep  
    ;;
  3)
    dev      
    ;;
  4)
    sqlSuppression      
    ;;
  5)
    modeDev
    ;;
  *)
    echo -e "${RED}Vous avez saisi un numéro non géré : $choice${NC}"
    exit 1
    ;;
  esac
}
# Interaction secondaire pour la partie développement
function modeDev(){
  echo -e "${PURPLE}\n---------------------"
  echo  " Partie développeur"
  echo -e "---------------------"
  echo ""
  echo " 1) Formater le code au format PSR-12"
  echo " 2) Vider le cache de développement"
  echo ""
  read -p "Entrez un numéro : " choiceDev

  case $choiceDev in
  1)
    PSR12  
    ;;
  2)
    php bin/console cache:clear 
    ;;
  *)
    echo -e "${RED}Vous avez saisi un numéro non géré : $choiceDev${NC}"
    exit 1
    ;;
  esac
}

# Methode qui appele en cascade d'autre méthode. Cherche également si les diffèrents langages sont présent
function toutEnUn() {
  echo ""
  echo -e "${GREEN}Procédure d'installation"
  echo -e "------------------------\n"
  if ! command -v php >/dev/null 2>&1; then
    installPHP
  fi
  if ! command -v symfony >/dev/null 2>&1; then
    installSymfony
  fi

  if ! command -v mysql >/dev/null 2>&1; then
   installSql
  fi

  if ! command -v python3 >/dev/null 2>&1; then
   instalPython
  fi

  configSQL
  creaData
  installDep
  sqlRemplissage
  creationOrga
  read -p "Voulez-vous ajouter le serveur SMTP ? (o/n) " server_smtp
    if [[ $server_smtp == "o" ]]; then
      smtpEmail
    fi
}

# Installe les dépendances relatif au projet
function installDep(){
  echo -e "\n${GREEN}Installation des dépendances du projet"
  echo -e "------------------------${NC}\n"
  installComposer
  installTailwind

}

# Build tailwind et qui execute ensuite le serveur symfony
function dev() {
    php bin/console tailwind:build --watch &
    symfony serve
}

# Applique la convention de codage PSR2 au projet
function PSR12(){
    vendor/bin/phpcbf --standard=PSR12 src/
}

# Installe php sur la machine de l'utilisateur
function installPhp() {
  echo -e "${GREEN}PHP n'est pas installé. "
    read -p "Voulez-vous l'installer ? (o/n) " install_php
    if [[ $install_php == "o" ]]; then
      sudo apt-get install php php-cli php-common php-mysql php-xml php-curl php-mbstring php-zip php-intl php-bcmath -y
      if [ $? -ne 0 ]; then
        echo -e "${RED}Échec de l'installation de PHP. Veuillez l'installer manuellement${NC}"
        exit 1
      fi
    fi
}

# Installe Symfony sur la machine de l'utilisateur
function installSymfony() {
  echo -e "${GREEN}Symfony CLI n'est pas installé. "
    read -p "Voulez-vous l'installer ? (o/n) " install_symfony
    if [[ $install_symfony == "o" ]]; then
      curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | sudo -E bash
      sudo apt install symfony-cli -y
      export PATH="$HOME/.symfony7/bin:$PATH"
      if [ $? -ne 0 ]; then
        echo -e "${RED}Échec de l'installation de Symfony CLI. Veuillez l'installer manuellement${NC}"
        exit 1
      fi
    fi
}

# Installe Composer en local dans le projet de l'utilisateur
function installComposer() {
    echo -e "${GREEN}Installation de Composer"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('sha384', 'composer-setup.php') === 'c8b085408188070d5f52bcfe4ecfbee5f727afa458b2573b8eaaf77b3419b0bf2768dc67c86944da1544f06fa544fd47') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"

    php composer.phar install
} 

# Installe TailWind et le construit pour le projet
function installTailwind(){
   echo -e "${GREEN}Installation et construction de Tailwind ${NC}"
   php bin/console tailwind:init
   bin/console tailwind:build
}

# Installe MySQL sur la machine de l'utilisateur
function installSql() {
  echo -e "${GREEN}MYSQL n'est pas installé"
    read -p "Voulez-vous l'installer ? (o/n) " install_sql
    if [[ $install_sql == "o" ]]; then
      sudo apt-get install mysql-server -y
      if [ $? -ne 0 ]; then
        echo -e "${RED}Échec de l'installation de SQL. Veuillez l'installer manuellement${NC}"
        exit 1
      fi
    fi
}

# Installe Python sur la machine de l'utilisateur
function instalPython(){
  echo -e "${GREEN}Python n'est pas installé. "
    read -p "Voulez-vous l'installer ? (o/n) " install_python
    if [[ $install_python == "o" ]]; then
      sudo apt-get install -y python3 python3-venv python3-pip
      if [ $? -ne 0 ]; then
        echo -e "${RED}Échec de l'installation de Python. Veuillez l'installer manuellement${NC}"
        exit 1
      fi
    fi

}
# Interaction avec l'utilisateur. Choix de la méthode de configuration de la base de données
function configSQL(){
  echo -e "${BLUE}Nous allons maintenant configurer votre base de données"
  echo -e "Comment souhaitez-vous proceder ?"
  echo -e "1) Via une chaine de connexion Symfony/Doctrine"
  echo -e "2) En renseignant vos informations"
  read -p "Entrez un numéro : " choiceSQL
  case $choiceSQL in
  1)
    sqlChaineConnexion  
    ;;
  2)
    sqlReiseignement      
    ;;
  *)
    echo -e "${RED}Vous avez saisi un numéro non géré : $choiceSQL${NC}"
    exit 1
    ;;
esac

}

# Récupére la chaine de connexion fournit par l'utilisateur et la split dans des variables pour l’usage ultérieur dans le script
function sqlChaineConnexion {
  read -r -p $'\nRenseignez votre chaine de connexion : ' chaine
  local chaineP="${chaine#mysql://}"
  utilisateur="${chaineP%%:*}"
  reste="${chaineP#*:}"
  mdp="${reste%%@*}"
  reste="${reste#*@}"
  serveur="${reste%%/*}"
  base="${reste##*/}"
  sqlInit "$utilisateur" "$mdp" "$serveur" "$base"
}

# Récupère les différentes informations une par une suivant le choix de l'utilisateur
function sqlReiseignement(){
  read -p $'\nRenseignez votre nom utilisateur : ' utilisateur
  read -p "Renseignez le nom de la base à utiliser : " base
  read -p "Renseignez votre mot de passe : " mdp
  read -p "Choix du serveur : " serveur
  sqlInit "$utilisateur" "$mdp" "$serveur" "$base"
}

#  Initialise la base de données pour le projet
#  @param string $1 Nom de l'utilisateur MySQL
#  @param string $2 Mot de passe MySQL
#  @param string $3 Serveur MySQL (ex: localhost)
#  @param string $4 Nom de la base de données
sqlInit() {
  utilisateur="$1"
  mdp="$2"
  serveur="$3"
  base="$4"

  if [[ -z "$chaine" ]]; then
    chaine="mysql://${utilisateur}:${mdp}@${serveur}/${base}"
  fi

  if ! mysql -h "$serveur" -u "$utilisateur" -p"$mdp" -e "SELECT 1;" >/dev/null 2>&1; then
    echo -e "${RED}Connexion impossible.${NC}"
    echo -e "${RED}Vérifiez que le serveur '$serveur' est accessible depuis cette machine.${NC}"
    exit 1
  fi

  if [ -f .env.local ]; then
    if grep -q '^DATABASE_URL=' .env.local; then
      sed -i "s|^DATABASE_URL=.*|DATABASE_URL=\"$chaine\"|" .env.local
    else
      echo "DATABASE_URL=\"$chaine\"" >> .env.local
    fi
  else
    echo "DATABASE_URL=\"$chaine\"" > .env.local
  fi
  if [ ! -f "$scriptCreationBdChemin" ]; then
    echo -e "${RED}Le fichier SQL $scriptCreationBdChemin n'existe pas${NC}"
    return
  fi

  echo -e "${YELLOW}Votre base va être vidée. Si vous ne voulez pas, merci de changer le nom de la base à utiliser (pour annuler faite Ctrl+c)."
  echo -e "Supression dans 6 secondes.${NC}"
  sleep 6

  sqlQuery "DROP DATABASE IF EXISTS \`$base\`;"
  sqlCreateTable
  sleep 3

  mysql -u "$utilisateur" -p"$mdp" -h "$serveur" "$base" < "$scriptCreationBdChemin"
}

# Remplis la base de données en appelant le script Python ./Python/creationDonnée.py
sqlRemplissage(){
  echo -e "${BLUE} Remplissage de la base de donnée en cours...${NC}"
  rm -rf ./Python/.venv 
  python3 -m venv ./Python/.venv 
  source ./Python/.venv/bin/activate 
  ./Python/.venv/bin/pip install pymysql lorem-text 
  ./Python/.venv/bin/python ./Python/creationDonnée.py 
  deactivate 
  rm -rf ./Python/.venv
} 

# Supprimme les données de test de la base de données le script Python ./Python/suppressionDonnes.py 
sqlSuppression(){
  creaData
  echo -e "${BLUE} Suppression des données de test de la base de donnée en cours...${NC}"
  rm -rf ./Python/.venv 
  python3 -m venv ./Python/.venv 
  source ./Python/.venv/bin/activate 
  ./Python/.venv/bin/pip install pymysql lorem-text 
  ./Python/.venv/bin/python ./Python/suppressionDonnes.py 
  deactivate 
  rm -rf ./Python/.venv

  echo -e "${GREEN} Suppression des données de test réussie"
} 

# Interaction avec l'utilisateur pour créer le premier compte organisateur
creationOrga(){
  echo ""
  echo -e "${BLUE}Création du compte organisateur"
  read -p "Renseignez l'email du compte organisateur : " emailOrg
  read -p "Renseignez le prénom de l'organisateur : " prenomOrg
  read -p "Renseignez le nom de l'organisateur : " nomOrg

  while true; do
  read -s -p "Renseignez le mot de passe de l'organisateur (6 caractères minimum) : " mdpOrg
  echo ""

  if [[ ${#mdpOrg} -ge 6 ]]; then
    break
  else
    echo -e "${RED}Le mot de passe doit contenir au moins 6 caractères"
  fi
  echo -e "${BLUE}"
  done

  hashDepart=$(php bin/console security:hash-password "$mdpOrg" --no-interaction --no-ansi ) 

  hashOrg=$(echo "$hashDepart" | grep -Eo '\$(argon2id|2y)\$[^ ]+')

  if [[ -z "$hashOrg" || "$hashOrg" != \$* ]]; then
    echo -e "${RED}Impossible de générer le hash Symfony.${NC}"
    echo -e "Erreur : $rawOutput"
    return 1
  fi

  sqlOrga="INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Prenom, UTL_Nom, UTL_MotDePasse,UTL_Id) VALUES (1, '$emailOrg', '$nomOrg', '$prenomOrg', '$hashOrg', NULL)"
  sqlQuery "$sqlOrga"
}
#  Extrait les informations de connexion à la base depuis le fichier .env.local et les exporte en variables d'environnement
creaData() {
    if [ -f .env.local ]; then
        chaine=$(grep '^DATABASE_URL=' .env.local | cut -d'"' -f2)

        local chaineP="${chaine#mysql://}"
        DB_UTILISATEUR="${chaineP%%:*}"
        reste="${chaineP#*:}"
        DB_MDP="${reste%%@*}"
        reste="${reste#*@}"
        DB_SERVEUR="${reste%%/*}"
        DB_BASE="${reste##*/}"

        export DB_SERVEUR DB_UTILISATEUR DB_MDP DB_BASE
    else
        echo -e "${RED}.env.local introuvable !${NC}"
        exit 1
    fi
}
# Interaction avec l'utilisateur pour la configuration SMTP
smtpEmail(){
  echo -e "${BLUE}Configuration SMTP pour l'envoi des emails"
  read -p "Renseignez le serveur SMTP (ex: smtp.gmail.com) :" smtpServeur
  read -p "Renseignez l'email d'envoi :" emailDe

  ecritureSMTP "$smtpServeur" "$emailDe"
}
# Ecrit les informations du SMTP dans .env.local
ecritureSMTP(){
  configurationSMTP="$1"
  envoyeurEmail="$2"
  fichierEnv="${3:-.env.local}" 

  sed -i '/^MAILER_DSN=/d' "$fichierEnv"
  sed -i '/^MAILER_FROM=/d' "$fichierEnv"

  echo "MAILER_DSN=$configurationSMTP" >> "$fichierEnv"
  echo "MAILER_FROM=\"$envoyeurEmail\"" >> "$fichierEnv"
}

# Créer la table dans la base de données en se basant sur les logins récupérés précédemment
#  @param string $1 Requête MySQL
sqlCreateTable() {
  local query="$1"
  mysql -u "$utilisateur" -p"$mdp" -h "$serveur" -e "CREATE DATABASE \`$base\`;"
}

# Méthode permettant de faire des requêtes à la base de données
#  @param string $1 Requête MySQL
sqlQuery() {
  local query="$1"
  mysql -u "$utilisateur" -p"$mdp" -h "$serveur" "$base" -e "$query"
}

interaction