import random
from hashlib import sha256
import pymysql
import datetime
from lorem_text import lorem
import os
import pymysql
import time
config = {
    'host': os.getenv('DB_SERVEUR'),
    'user': os.getenv('DB_UTILISATEUR'),
    'password': os.getenv('DB_MDP'),
    'database': os.getenv('DB_BASE'),
    'cursorclass': pymysql.cursors.DictCursor
}
conn = pymysql.connect(**config)
cursor =conn.cursor()
cursor.execute("DELETE FROM T_EQUIPE_EQP")
cursor.execute(" DELETE FROM T_COMPETITION_CPT")
noms_equipes = [
    "Nano Dragons", "Logic Aigles", "Techno Loups", "Or Aigles", "Magnetic Aigles",
    "Urbain Etoiles", "Furtif Pythons", "Cosmic Robots", "Giga Vikings", "Acier Loups",
    "Futur Serpents", "Cyber Ninjas", "Neo Trolls", "Or Serpents", "Royal Machines",
    "Hyper Phoenix", "Neo Systemes", "Hyper Scorpions", "Gris Soleils", "Mecano Ninjas",
    "Gamma Betes", "Petit Reseaux", "Chrome Eclairs", "Argent Lions", "Acier Requins",
    "Titane Dragons", "Carbone Lions", "Ultra Guepes", "Logic Tortues", "Cyber Orages",
    "Neo Guepes", "Argent Dragons", "Sonic Asteroides", "Carbone Serpents", "Acier Tortues",
    "Magnetic Eclairs", "Rouge Loups", "Mecano Eclairs", "Titane Robots", "Or Moteurs",
    "Super Tigres", "Royal Robots", "Hyper Titans", "Fou Cyclones", "Metal Phoenix",
    "Argent Moteurs", "Atomic Cobras", "Electric Serpents", "Mecano Lezards", "Super Robots",
    "Agile Eclairs", "Furtif Tortues", "Carbone Faucons", "Giga Abeilles", "Urbain Lezards",
    "Techno Serpents", "Cosmic Guepes", "Carbone Dragons", "Royal Orages", "Solar Machines",
    "Agile Moteurs", "Electric Scorpions", "Chrome Soleils", "Blanc Robots", "Grand Robots",
    "Acier Eclairs", "Cosmic Dragons", "Nuclear Cobras", "Alpha Serpents", "Urbain Serpents",
    "Gamma Requins", "Logic Robots", "Chrome Guepes", "Rouge Dragons", "Logic Moteurs",
    "Royal Guepes", "Super Machines", "Mega Faucons", "Plasma Moteurs", "Logic Scorpions",
    "Kinetic Tortues", "Techno Scorpions", "Petit Serpents", "Mega Loups", "Urbain Orages",
    "Magnetic Serpents", "Hyper Dragons", "Acier Robots", "Turbo Eclairs", "Rouge Requins",
    "Noir Robots", "Carbone Orages", "Electro Aigles", "Neo Robots", "Techno Machines",
    "Sauvage Loups", "Urbain Machines", "Or Robots", "Sonic Moteurs", "Acier Machines",
    "Cyber Moteurs", "Titane Moteurs", "Bleu Serpents", "Hyper Faucons", "Nano Scorpions",
    "Turbo Serpents", "Rouge Machines", "Cosmic Moteurs", "Solar Dragons", "Petit Soleils",
    "Metal Serpents", "Sauvage Machines", "Nuclear Robots", "Kinetic Machines", "Gamma Serpents",
    "Futur Orages", "Metal Dragons", "Titane Serpents", "Rouge Moteurs", "Royal Moteurs",
    "Neo Faucons", "Hyper Loups", "Bleu Dragons", "Titane Soleils", "Fou Dragons",
    "Grand Loups", "Gris Robots", "Lunar Serpents", "Cyber Dragons", "Sauvage Scorpions",
    "Electric Dragons", "Alpha Machines", "Gamma Orages", "Rouge Phoenix", "Noir Moteurs",
    "Solar Robots", "Logic Requins", "Electric Moteurs", "Chrome Dragons", "Hyper Moteurs",
    "Bleu Machines", "Furtif Moteurs", "Nuclear Moteurs", "Chrome Serpents", "Electro Dragons",
    "Techno Dragons", "Gamma Dragons", "Petit Dragons", "Acier Moteurs", "Royal Soleils",
    "Ultra Moteurs", "Nano Robots", "Giga Machines", "Titane Loups", "Titane Scorpions",
    "Electro Robots", "Grand Machines", "Cosmic Scorpions", "Neo Machines", "Magnetic Robots",
    "Blanc Moteurs", "Nano Moteurs", "Or Guepes", "Atomic Dragons", "Argent Scorpions",
    "Mega Serpents", "Gamma Moteurs", "Fou Machines", "Electric Robots", "Lunar Machines",
    "Furtif Robots", "Chrome Robots", "Atomic Machines", "Nuclear Serpents", "Blanc Serpents",
    "Acier Scorpions", "Bleu Moteurs", "Electro Moteurs", "Agile Robots", "Urbain Robots",
    "Mecano Robots", "Techno Soleils", "Turbo Robots", "Futur Machines", "Furtif Serpents",
    "Sauvage Serpents", "Noir Serpents", "Gris Moteurs", "Argent Robots", "Neo Moteurs",
    "Or Loups", "Fou Moteurs", "Kinetic Robots", "Urbain Dragons", "Omega Moteurs",
    "Magnetic Moteurs", "Sonic Robots", "Super Serpents", "Petit Moteurs", "Mega Robots",
    "Kinetic Moteurs", "Gamma Robots", "Noir Dragons", "Atomic Robots", "Solar Moteurs",
    "Logic Dragons", "Blanc Machines", "Petit Machines", "Turbo Moteurs", "Fou Serpents",
    "Alpha Moteurs", "Super Moteurs", "Giga Robots", "Carbone Robots", "Grand Moteurs",
    "Carbone Moteurs", "Metal Robots", "Plasma Robots", "Or Machines", "Grand Serpents",
    "Mecano Machines", "Noir Machines", "Giga Moteurs", "Futur Robots", "Bleu Robots",
    "Royal Serpents", "Mega Machines", "Omega Robots", "Chrome Machines", "Futur Moteurs",
    "Mecano Moteurs", "Agile Machines", "Techno Moteurs", "Lunar Moteurs", "Lunar Robots",
    "Techno Robots", "Plasma Machines", "Rouge Serpents", "Acier Dragons", "Alpha Robots",
    "Gamma Machines", "Turbo Machines", "Ultra Robots", "Nano Machines", "Metal Machines",
    "Argent Machines", "Sauvage Robots", "Hyper Robots", "Solar Serpents", "Neo Dragons",
    "Blanc Dragons", "Urbain Moteurs", "Sonic Machines", "Super Dragons", "Alpha Dragons",
    "Omega Machines", "Giga Serpents", "Titane Machines", "Cosmic Machines", "Metal Moteurs",
    "Ultra Machines", "Carbone Machines", "Omega Serpents", "Electric Machines", "Mega Dragons",
    "Atomic Moteurs", "Electro Machines", "Or Dragons", "Magnetic Machines", "Hyper Machines",
    "Furtif Machines", "Fou Robots", "Agile Serpents", "Cyber Machines", "Acier Serpents",
    "Agile Dragons", "Bleu Loups", "Argent Serpents", "Grand Dragons", "Nano Serpents",
    "Kinetic Serpents", "Gris Machines", "Giga Dragons", "Alpha Machines", "Plasma Serpents",
    "Rouge Scorpions", "Furtif Dragons", "Titane Orages", "Omega Dragons", "Sonic Serpents",
    "Lunar Dragons", "Cosmic Serpents", "Electro Serpents", "Turbo Dragons", "Sauvage Dragons",
    "Logic Machines", "Royal Dragons", "Noir Eclairs", "Mega Moteurs", "Sauvage Moteurs",
    "Cyber Serpents", "Urbain Guepes", "Plasma Dragons", "Futur Dragons", "Chrome Moteurs",
    "Petit Robots", "Rouge Eclairs", "Nano Dragons", "Gris Serpents", "Blanc Loups",
    "Kinetic Dragons", "Gamma Loups", "Nuclear Dragons", "Magnetic Dragons", "Sonic Dragons",
    "Techno Eclairs", "Fou Dragons", "Mecano Dragons", "Titane Phoenix", "Mecano Serpents",
    "Ultra Serpents", "Super Loups", "Logic Loups", "Alpha Loups", "Omega Loups",
    "Gamma Scorpions", "Bleu Scorpions", "Vert Robots", "Vert Machines", "Vert Dragons",
    "Vert Moteurs", "Vert Serpents", "Vert Loups", "Vert Scorpions", "Vert Eclairs",
    "Vert Guepes", "Vert Aigles", "Vert Phoenix", "Vert Soleils", "Vert Orages",
    "Vert Tortues", "Vert Requins", "Vert Tigres", "Vert Faucons", "Vert Lions",
    "Jaune Robots", "Jaune Machines", "Jaune Dragons", "Jaune Moteurs", "Jaune Serpents",
    "Jaune Loups", "Jaune Scorpions", "Jaune Eclairs", "Jaune Guepes", "Jaune Aigles",
    "Jaune Phoenix", "Jaune Soleils", "Jaune Orages", "Jaune Tortues", "Jaune Requins",
    "Jaune Tigres", "Jaune Faucons", "Jaune Lions", "Nord Robots", "Nord Machines",
    "Nord Dragons", "Nord Moteurs", "Nord Serpents", "Nord Loups", "Nord Scorpions",
    "Nord Eclairs", "Nord Guepes", "Nord Aigles", "Nord Phoenix", "Nord Soleils",
    "Nord Orages", "Nord Tortues", "Nord Requins", "Nord Tigres", "Nord Faucons",
    "Nord Lions", "Sud Robots", "Sud Machines", "Sud Dragons", "Sud Moteurs",
    "Sud Serpents", "Sud Loups", "Sud Scorpions", "Sud Eclairs", "Sud Guepes",
    "Sud Aigles", "Sud Phoenix", "Sud Soleils", "Sud Orages", "Sud Tortues",
    "Sud Requins", "Sud Tigres", "Sud Faucons", "Sud Lions", "Est Robots",
    "Est Machines", "Est Dragons", "Est Moteurs", "Est Serpents", "Est Loups",
    "Est Scorpions", "Est Eclairs", "Est Guepes", "Est Aigles", "Est Phoenix",
    "Est Soleils", "Est Orages", "Est Tortues", "Est Requins", "Est Tigres",
    "Est Faucons", "Est Lions", "Ouest Robots", "Ouest Machines", "Ouest Dragons",
    "Ouest Moteurs", "Ouest Serpents", "Ouest Loups", "Ouest Scorpions", "Ouest Eclairs",
    "Ouest Guepes", "Ouest Aigles", "Ouest Phoenix", "Ouest Soleils", "Ouest Orages",
    "Ouest Tortues", "Ouest Requins", "Ouest Tigres", "Ouest Faucons", "Ouest Lions"
]

etablissements = [
    "Lycee Louis Pasteur",
    "Lycee Victor Hugo",
    "Lycee Jules Verne",
    "Lycee Gustave Eiffel",
    "Lycee Marie Curie",
    "Lycee Blaise Pascal",
    "Lycee Albert Camus",
    "Lycee Jean Jaures",
    "Lycee Emile Zola",
    "Lycee Claude Monet",
    "Lycee Rene Descartes",
    "Lycee Paul Cezanne",
    "Lycee Henri Matisse",
    "Lycee Pablo Picasso",
    "Lycee Auguste Rodin",
    "Lycee Camille Claudel",
    "Lycee George Sand",
    "Lycee Simone de Beauvoir",
    "Lycee Marguerite Duras",
    "Lycee Francoise Sagan",
    "Lycee Marcel Pagnol",
    "Lycee Alphonse Daudet",
    "Lycee Guy de Maupassant",
    "Lycee Charles Baudelaire",
    "Lycee Arthur Rimbaud",
    "Lycee Paul Verlaine",
    "Lycee Guillaume Apollinaire",
    "Lycee Jacques Prevert",
    "Lycee Boris Vian",
    "Lycee Saint Exupery",
    "Lycee Jean Mermoz",
    "Lycee Clement Ader",
    "Lycee Roland Garros",
    "Lycee Pierre de Coubertin",
    "Lycee Jean Moulin",
    "Lycee Charles de Gaulle",
    "Lycee Georges Pompidou",
    "Lycee Francois Mitterrand",
    "Lycee Jacques Chirac",
    "Lycee Kleber",
    "Lycee Massena",
    "Lycee Thiers",
    "Lycee Fermat",
    "Lycee Montaigne",
    "Lycee Montesquieu",
    "Lycee Voltaire",
    "Lycee Diderot",
    "Lycee Condorcet",
    "Lycee Lavoisier",
    "Lycee Ampere",
    "Universite des Sciences",
    "Universite Polytechnique",
    "Universite Technologique",
    "Institut National des Sciences",
    "Ecole des Mines",
    "Ecole des Ponts",
    "Ecole Centrale",
    "Ecole Navale",
    "Ecole Militaire",
    "Ecole Aeronautique",
    "Institut de Robotique",
    "Campus Innovation",
    "Pole Universitaire",
    "Faculte des Sciences",
    "Faculte de Medecine",
    "IUT Genie Mecanique",
    "IUT Informatique",
    "IUT Genie Electrique",
    "BTS Conception Produits",
    "BTS Systemes Numeriques",
    "Prepa aux Grandes Ecoles",
    "Ecole Superieure du Bois",
    "Ecole du Numerique",
    "Institut du Futur",
    "Academie des Technologies",
    "Club Robotique Junior",
    "Association Jeunes Sciences",
    "Maison des Jeunes",
    "Centre Culturel Sciences",
    "FabLab Municipal",
    "Atelier des Makers",
    "Club Electronique",
    "Association Code et Bots",
    "Cercle des Ingenieurs",
    "Groupe Passion Tech",
    "Laboratoire Ouvert",
    "Espace Numerique",
    "Club Astronomie et Espace",
    "Association Planete Sciences",
    "Club Informatique Local",
    "Garage des Inventeurs",
    "Atelier Soudure et Code",
    "Makerspace Universitaire",
    "Club Drone Racing",
    "Association Intelligence Artificielle",
    "Club Mecatronique",
    "Groupe Robotech",
    "Association Cyberespace",
    "Club Logiciel Libre",
    "Foyer Rural Tech"
]
domaines = [
    "google.com",
    "youtube.com",
    "wikipedia.org",
    "amazon.fr",
    "facebook.com",
    "free.fr",
    "orange.fr",
    "lemonde.fr",
    "github.com",
    "python.org",
    "impotsgouv.fr",
    "stackoverflow.com",
    "microsoft.com",
    "apple.com",
    "spotify.com"
]

prenoms = [
    "Alice", "Adam", "Ambre", "Arthur", "Chloé",
    "Emma", "Gabriel", "Hugo", "Jade", "Jules",
    "Léo", "Lina", "Louis", "Louise", "Lucas",
    "Maël", "Mila", "Noah", "Raphaël", "Rose",
    "Sacha", "Sarah", "Sofia", "Thomas", "Zoé"
]

noms_famille = [
    "Martin", "Bernard", "Thomas", "Petit", "Robert",
    "Richard", "Durand", "Dubois", "Moreau", "Laurent",
    "Simon", "Michel", "Lefebvre", "Leroy", "Roux",
    "David", "Bertrand", "Morel", "Fournier", "Girard",
    "Bonnet", "Dupont", "Lambert", "Fontaine", "Rousseau"
]
competitions_robots = [
    "FIRST Robotics Competition",
    "VEX Robotics Competition",
    "RoboCup",
    "BattleBots",
    "Eurobot",
    "Coupe de France de Robotique",
    "World Robot Olympiad (WRO)",
    "DARPA Subterranean Challenge",
    "FIRST Robot League",
    "Robot Wars",
    "RoboMaster",
    "Botball",
    "Solar Cup",
    "University Rover Challenge (URC)",
    "European Rover Challenge (ERC)",
    "Drone Racing League (DRL)",
    "RoboSub",
    "F1TENTH",
    "Indy Autonomous Challenge",
    "Micromouse",
    "RoboGames",
    "WorldSkills Mobile Robotics",
    "MATE ROV Competition",
    "AWS DeepRacer",
    "FIRA RoboWorld Cup",
    "FIRST Tech Challenge",
    "VEX IQ Challenge",
    "RoboCup Junior",
    "IRO (International Robot Olympiad)",
    "BEST Robotics Challenge",
    "RoboRAVE International",
    "MakeX Robotics Competition",
    "National Robotics Challenge",
    "SeaPerch Challenge",
    "CanSat",
    "DARPA Robotics Challenge",
    "MBZIRC",
    "KUKA Innovation Award",
    "World Robot Summit",
    "Amazon Picking Challenge",
    "European Robotics League (ERL)",
    "IROS Robotic Grasping and Manipulation",
    "RobotX Challenge",
    "RoboBoat",
    "SAUC-E",
    "IARC (International Aerial Robotics Competition)",
    "UAV Challenge Medical Express",
    "Cybathlon",
    "Field Robot Event",
    "RoboCup Home",
    "RoboCup Rescue",
    "Trinity College Fire Fighting Home Robot Contest",
    "RoboOne",
    "All Japan Robot Sumo",
    "ABU Robocon"
]
adresses = [
    "10 Rue de la Paix, 75000 Paris",
    "25 Avenue Victor Hugo, 69000 Lyon",
    "3 Boulevard des Sciences, 31000 Toulouse",
    "8 Impasse du Moulin, 33000 Bordeaux",
    "12 Place de la Mairie, 59000 Lille",
    "45 Rue des Lilas, 44000 Nantes",
    "99 Avenue de la Gare, 13000 Marseille",
    "7 Allee des Robots, 67000 Strasbourg",
    "14 Rue Pasteur, 06000 Nice",
    "22 Boulevard Gambetta, 34000 Montpellier",
    "33 Rue de la Liberte, 35000 Rennes",
    "56 Avenue des Vosges, 51000 Reims",
    "88 Boulevard Carnot, 83000 Toulon",
    "101 Rue Jean Jaures, 42000 Saint-Etienne",
    "4 Place Royale, 76000 Rouen",
    "15 Rue du Commerce, 21000 Dijon",
    "9 Avenue des Champs, 63000 Clermont-Ferrand",
    "60 Boulevard Foch, 49000 Angers",
    "2 Rue des Fleurs, 37000 Tours",
    "30 Impasse des Roses, 87000 Limoges",
    "11 Avenue du Parc, 29000 Brest",
    "78 Rue de la Republique, 72000 Le Mans",
    "5 Boulevard de l Europe, 80000 Amiens",
    "19 Rue de Bretagne, 57000 Metz",
    "24 Avenue du Lac, 74000 Annecy",
    "67 Rue des Vignes, 68000 Colmar",
    "3 Place du Marche, 64000 Pau",
    "90 Boulevard Maritime, 17000 La Rochelle",
    "12 Rue du Stade, 56000 Lorient",
    "8 Avenue de Paris, 45000 Orleans",
    "55 Rue Saint Michel, 14000 Caen",
    "23 Boulevard Clemenceau, 76600 Le Havre",
    "7 Rue de Verdun, 60000 Beauvais",
    "41 Impasse du Bois, 25000 Besancon",
    "100 Avenue Kennedy, 66000 Perpignan",
    "6 Rue de la Victoire, 53000 Laval",
    "82 Boulevard Voltaire, 92000 Nanterre",
    "14 Rue des Ecoles, 93000 Bobigny",
    "35 Avenue des Tilleuls, 94000 Creteil",
    "9 Rue du Port, 85000 La Roche-sur-Yon",
    "20 Place des Arts, 26000 Valence",
    "44 Boulevard Albert Einstein, 44300 Nantes",
    "5 Rue Marie Curie, 31400 Toulouse",
    "18 Avenue du General Leclerc, 75014 Paris",
    "70 Rue La Fayette, 75009 Paris",
    "32 Boulevard Haussmann, 75008 Paris",
    "91 Rue de Rivoli, 75001 Paris",
    "6 Impasse des Jardins, 69100 Villeurbanne",
    "28 Avenue de Grande Bretagne, 31300 Toulouse",
    "150 Boulevard de Suisse, 31200 Toulouse",
    "4 Rue de la Convention, 25000 Besancon",
    "88 Avenue du Drapeau, 21000 Dijon",
    "5 Rue de la Fontaine, 30000 Nimes",
    "12 Boulevard Sergent Triaire, 30000 Nimes",
    "9 Avenue Jean Medecin, 06000 Nice",
    "45 Rue de France, 06000 Nice",
    "22 Promenade des Anglais, 06200 Nice",
    "7 Rue Sainte Catherine, 33000 Bordeaux",
    "10 Cours de l Intendance, 33000 Bordeaux",
    "33 Quai des Chartrons, 33000 Bordeaux",
    "6 Avenue du Prado, 13008 Marseille",
    "14 Rue Saint Ferreol, 13001 Marseille",
    "8 Boulevard de la Corniche, 13007 Marseille",
    "50 Rue de la Loge, 34000 Montpellier",
    "2 Place de la Comedie, 34000 Montpellier",
    "19 Avenue de Grammont, 37000 Tours",
    "4 Rue Nationale, 37000 Tours",
    "11 Boulevard Heurteloup, 37000 Tours",
    "75 Rue de Siam, 29200 Brest",
    "3 Rue de la Porte, 29200 Brest",
    "5 Avenue Foch, 54000 Nancy",
    "21 Place Stanislas, 54000 Nancy",
    "9 Rue Saint Jean, 54000 Nancy",
    "102 Grand Rue, 67000 Strasbourg",
    "4 Quai des Bateliers, 67000 Strasbourg",
    "66 Avenue des Vosges, 67000 Strasbourg",
    "8 Rue de la Mesange, 67000 Strasbourg",
    "30 Rue Royale, 59000 Lille",
    "15 Boulevard de la Liberte, 59800 Lille",
    "7 Place du General de Gaulle, 59800 Lille",
    "24 Rue Esquermoise, 59800 Lille",
    "40 Avenue du Peuple Belge, 59800 Lille",
    "1 Rue de la Republique, 69002 Lyon",
    "8 Place Bellecour, 69002 Lyon",
    "55 Cours Lafayette, 69003 Lyon",
    "12 Boulevard des Brotteaux, 69006 Lyon",
    "9 Rue de la Bourse, 69002 Lyon",
    "3 Avenue Adolphe Max, 69005 Lyon",
    "20 Quai Saint Antoine, 69002 Lyon",
    "14 Rue du President Edouard Herriot, 69001 Lyon",
    "6 Impasse Saint Exupery, 31400 Toulouse",
    "101 Allee des Demoiselles, 31400 Toulouse",
    "5 Route de Bayonne, 31300 Toulouse",
    "33 Chemin de la Loge, 31400 Toulouse",
    "7 Rue des Lois, 31000 Toulouse",
    "9 Place du Capitole, 31000 Toulouse",
    "42 Rue Alsace Lorraine, 31000 Toulouse",
    "8 Boulevard de Strasbourg, 31000 Toulouse",
    "15 Avenue de Lavaur, 31500 Toulouse"
]
id_Organisateur = ""
def creationUtilisateurs():
    
    listeMail=[]
    listeUser=[]
    ListeCaracteres=[0,1,2,3,4,5,6,7,8,9,"a","z","e","r","t","y","u","i","o","p","q","s","d","f","g","h","j","k","l","m","w","x","c","v","b","n"]
    for _ in range(800):
        prenom_aleatoire = random.choice(prenoms)
        domaineAleatoire =random.choice(domaines)
        nomAleatoire = random.choice(noms_famille)
        Mail = prenom_aleatoire+"@"+domaineAleatoire
        role=0
        tirage=random.random()
        if(tirage<0.8):
            role=2
        else:
            role=1
        password=""
        for _ in range(10):
            password+=str(random.choice(ListeCaracteres))
        if(Mail not in listeMail):
            listeMail.append(Mail)
            listeUser.append([role,Mail,nomAleatoire,prenom_aleatoire,sha256(password.encode('utf-8')).hexdigest()])
    return (listeUser)
listeNomUtilisé=[]
def creationCompetition(UtilisateurID):
    nomCompetition=random.choice(competitions_robots)
    while( nomCompetition in listeNomUtilisé):
        nomCompetition=random.choice(competitions_robots)
    listeNomUtilisé.append(nomCompetition)
    lieu=random.choice(adresses)
    if(random.random()>0.5):
        debut =  time.strftime('%Y-%m-%d', time.gmtime(random.randint(0, int(time.time())+random.randint(0,100000))))
        fin= time.strftime('%Y-%m-%d', time.gmtime(random.randint(0, int(time.time())+random.randint(0,100000))))
        while fin<debut:
            fin= time.strftime('%Y-%m-%d', time.gmtime(random.randint(0, int(time.time()))))
    else:
        debut =  time.strftime('%Y-%m-%d', time.gmtime(random.randint(int(time.time())-random.randint(0,100000), int(time.time())+random.randint(0,10000000))))
        fin= time.strftime('%Y-%m-%d', time.gmtime(random.randint(int(time.time())-random.randint(0,100000), int(time.time())+random.randint(0,100000))))
        while fin<debut:
            fin= time.strftime('%Y-%m-%d', time.gmtime(random.randint(int(time.time())-random.randint(0,100000), int(time.time())+random.randint(0,10000000))))
    description = lorem.sentence()[:100]
    return [UtilisateurID,nomCompetition,lieu,debut,fin,description]


o=creationUtilisateurs()
for element in o:
    cursor.execute(f"INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse) VALUES ({element[0]}, '{element[1]}', '{element[2]}', '{element[3]}', '{element[4]}')")
conn.commit()



cursor.execute("SELECT * FROM `T_UTILISATEUR_UTL` WHERE ROL_Id = 1")
listOrganisateur = cursor.fetchall()
listeCompetition = []
for _ in range (50) :
    Organisateur = random.choice(listOrganisateur)  
    cursor.execute(f"SELECT * FROM `T_UTILISATEUR_UTL` WHERE UTL_Mail = '{Organisateur["UTL_Mail"]}'")
    res1 = cursor.fetchone()
    listeCompetition.append(creationCompetition(res1['UTL_Id']))
for element in listeCompetition:
    cursor.execute(f"INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES ({element[0]}, '{element[1]}', '{element[2]}', '{element[3]}', '{element[4]}', '{element[5]}', NULL);")
conn.commit()


for i in range(400):
    cursor.execute("SELECT * FROM `T_COMPETITION_CPT`")
    com = cursor.fetchall()
    compet=random.choice(com)

    NomEqp = noms_equipes[i]
    cursor.execute(f"SELECT * FROM `T_EQUIPE_EQP` where CPT_Id = {compet['CPT_Id']}")
    contrainte = cursor.fetchall()
    contrainteCheck=True
    for element in contrainte:
        if element['EQP_Nom'] == NomEqp:
            contrainteCheck=False
    if(contrainteCheck):
        StatuEquipe=random.randint(1,3)
        EqpEtablissement=random.choice(etablissements)
        dateCrea = str(datetime.datetime.now())
        membre=[]
        for i in range(4):
            prenom=random.choice(prenoms)
            nom=random.choice(noms_famille)
            domaine=random.choice(domaines)
            membre.append({"'email'": "'" + prenom + "@" + domaine + "'","'prenom'": "'" + prenom + "'","'nom'": "'" + nom + "'"})

    cursor.execute("SELECT * FROM `T_UTILISATEUR_UTL` WHERE ROL_Id = 2")
    listeUser = cursor.fetchall()
    createur = random.choice(listeUser)
    crea = createur['UTL_Id']
    createur=crea
    try:
        cursor.execute(f"INSERT INTO `T_EQUIPE_EQP` (`CPT_Id`, `UTL_Id`, `SEQ_Id`, `EQP_Nom`, `EQP_DateCreation`, `EQP_Etablissement`, `EQP_Membres`, `EQP_Id`) VALUES ({compet['CPT_Id']}, {createur}, '2', '{NomEqp}', '{dateCrea}', '{EqpEtablissement}', '{str(membre).replace("\'", "")}', NULL);")
    except pymysql.MySQLError as e :
        continue;
conn.commit()
cursor.close()
conn.close()