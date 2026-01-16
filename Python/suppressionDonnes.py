import pymysql
import os
config = {
    'host': os.getenv('DB_SERVEUR'),
    'user': os.getenv('DB_UTILISATEUR'),
    'password': os.getenv('DB_MDP'),
    'database': os.getenv('DB_BASE'),
    'cursorclass': pymysql.cursors.DictCursor
}
conn = pymysql.connect(**config)
cursor = conn.cursor()
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
competitions_robots = [
    "FIRST Robotics Competition", "VEX Robotics Competition", "RoboCup", "BattleBots",
    "Eurobot", "Coupe de France de Robotique", "World Robot Olympiad (WRO)",
    "DARPA Subterranean Challenge", "FIRST Robot League", "Robot Wars", "RoboMaster",
    "Botball", "Solar Cup", "University Rover Challenge (URC)", "European Rover Challenge (ERC)",
    "Drone Racing League (DRL)", "RoboSub", "F1TENTH", "Indy Autonomous Challenge",
    "Micromouse", "RoboGames", "WorldSkills Mobile Robotics", "MATE ROV Competition",
    "AWS DeepRacer", "FIRA RoboWorld Cup", "FIRST Tech Challenge", "VEX IQ Challenge",
    "RoboCup Junior", "IRO (International Robot Olympiad)", "BEST Robotics Challenge",
    "RoboRAVE International", "MakeX Robotics Competition", "National Robotics Challenge",
    "SeaPerch Challenge", "CanSat", "DARPA Robotics Challenge", "MBZIRC",
    "KUKA Innovation Award", "World Robot Summit", "Amazon Picking Challenge",
    "European Robotics League (ERL)", "IROS Robotic Grasping and Manipulation",
    "RobotX Challenge", "RoboBoat", "SAUC-E", "IARC (International Aerial Robotics Competition)",
    "UAV Challenge Medical Express", "Cybathlon", "Field Robot Event", "RoboCup Home",
    "RoboCup Rescue", "Trinity College Fire Fighting Home Robot Contest", "RoboOne",
    "All Japan Robot Sumo", "ABU Robocon"
]
prenoms = [
    "Alice", "Adam", "Ambre", "Arthur", "Chloé", "Emma", "Gabriel", "Hugo", "Jade", "Jules",
    "Léo", "Lina", "Louis", "Louise", "Lucas", "Maël", "Mila", "Noah", "Raphaël", "Rose",
    "Sacha", "Sarah", "Sofia", "Thomas", "Zoé"
]
noms_famille = [
    "Martin", "Bernard", "Thomas", "Petit", "Robert", "Richard", "Durand", "Dubois", "Moreau",
    "Laurent", "Simon", "Michel", "Lefebvre", "Leroy", "Roux", "David", "Bertrand", "Morel",
    "Fournier", "Girard", "Bonnet", "Dupont", "Lambert", "Fontaine", "Rousseau"
]
cursor.execute("SET FOREIGN_KEY_CHECKS = 0")
for nom_eqp in noms_equipes:
    cursor.execute(f"DELETE FROM T_EQUIPE_EQP WHERE EQP_Nom = '{nom_eqp}'")
for nom_compet in competitions_robots:
    cursor.execute(f"DELETE FROM T_COMPETITION_CPT WHERE CPT_Nom = '{nom_compet}'")
liste_prenoms_sql = "', '".join(prenoms)
liste_noms_sql = "', '".join(noms_famille)
cursor.execute(f"DELETE FROM T_UTILISATEUR_UTL WHERE UTL_Prenom IN ('{liste_prenoms_sql}') AND UTL_Nom IN ('{liste_noms_sql}')")
cursor.execute("SET FOREIGN_KEY_CHECKS = 1")
conn.commit()
cursor.close()
conn.close()
