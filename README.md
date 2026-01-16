<div align="center">

# Robot Clash

![LOGO](https://i.imgur.com/seo4CdJ.png)

![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![Symfony](https://img.shields.io/badge/symfony-%23000000.svg?style=for-the-badge&logo=symfony&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/tailwindcss-%2338B2AC.svg?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Twig](https://img.shields.io/badge/twig-%238dc53f.svg?style=for-the-badge&logo=twig&logoColor=white)
![JavaScript](https://img.shields.io/badge/javascript-%23F7DF1E.svg?style=for-the-badge&logo=javascript&logoColor=black)
![Python](https://img.shields.io/badge/python-3670A0?style=for-the-badge&logo=python&logoColor=ffdd54)
![Bash](https://img.shields.io/badge/shell_script-%23121011.svg?style=for-the-badge&logo=gnu-bash&logoColor=white)
![HTML5](https://img.shields.io/badge/html5-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/css3-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white)

</div>

## Configuration initiale

> [!WARNING]
> La base de données doit être sur MariaDB

## Installation du projet
<strong>Cloner le projet</strong> 
```
git@gitlab-ce.iut.u-bordeaux.fr:timeunier/dev-application-tournois.git
```

<strong>Ouvrez un terminal dans le dossier du projet `/dev-application-tournois`
<br/>
Vous allez maintenant pouvoir lancer le projet avec une commande qui vous permettra de tout installer, puis vous devrez renseigner votre chaîne de connexion à la base.
<br/>
Cette commande vous permettra aussi de créer votre premier organisateur,
vous devrez choisir son mail, nom, prénom et mot de passe.
<br/>
Vous devrez aussi choisir si vous voulez configurer le Mailer, si oui vous devrez renseigner un serveur SMTP
<br/>
Pour commencer exécutez la commande 
</strong>
```
./setup.sh
```
<strong>Sélectionner avec `1` l'option "Tout en un" puis suivez les indications</strong>

<strong>Vous pouvez aussi sélectionner `2` si vous souhaitez uniquement installer les dépendances</strong>

> [!caution]
> Certaines actions peuvent nécessiter un accès en tant que super-utilisateur (sudo)
---

## Lancer le projet en mode développeur

<strong> Ouvrez un terminal dans le dossier du projet `/dev-application-tournois`
<br/>
Executer la commande </strong> 
```
./setup.sh
```
 <strong>Sélectionner <i>Exécuter le projet en mode dev</i> avec le numéro `4`
 <br/>
 Cette commande vous permettra de pouvoir faire respecter la norme PSR-12 avec le `1` ou clear le cache avec le `2`
 </strong>

<strong>Vous pouvez aussi sélectionner le numéro `3` du setup qui lance le serveur symfony et build tailwind</strong>

 ---

 # Contributeur

| Prénom/Nom | Gitlab |
|-----|--------|
|   Maxime Davy  | [@mdavy](https://gitlab-ce.iut.u-bordeaux.fr/mdavy)       |
|  Karla De Robillard de Beaurepaire   |    [@kderobillard](https://gitlab-ce.iut.u-bordeaux.fr/kderobillard)    |
|  Lucie Ges   |   [@lges](https://gitlab-ce.iut.u-bordeaux.fr/lges)      |
|  Mathis Gesson   |   [@mgesson](https://gitlab-ce.iut.u-bordeaux.fr/mgesson)     |
|   Louan Hochet  |   [@lohochet](https://gitlab-ce.iut.u-bordeaux.fr/lohochet)     |
|  Timéo Meunier   |   [@timeunier](https://gitlab-ce.iut.u-bordeaux.fr/timeunier)     |

--- 

# Notes

Tout le projet suit la convention de codage Symfony [PSR-12](https://www.php-fig.org/psr/psr-12/) :


<strong>Structure : </strong> Une ligne vide après la déclaration du namespace et après le bloc des use.

<strong>Nommage (camelCase) : </strong> Les fonctions, les méthodes ainsi que les variables doivent être nommées en camelCase (ex: getPrenom())

<strong>Visibilité : </strong> Propriétés et méthodes doivent explicitement déclarer leur visibilité (public, protected, private).

<strong>Accolades : </strong> Les accolades d'ouverture pour les classes et les méthodes doivent être sur la ligne suivante.
