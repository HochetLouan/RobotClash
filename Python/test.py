import pymysql
import datetime
import unittest

#conn = pymysql.connect(host='info-titania',user='etu_mdavy', password = '8Bx8S3KA',database='etu_mdavy')
config = {
    'host': 'info-titania',
    'user': 'etu_mdavy',
    'password': '8Bx8S3KA',
    'database': 'etu_mdavy',
    'cursorclass': pymysql.cursors.DictCursor
}

def executer_scenario_et_tests():
    conn = None
    try:
        conn = pymysql.connect(**config)
        conn.autocommit = False 
        
        with conn.cursor() as cursor:
            print("--- 1. CRÉATION DU JEU DE DONNÉES (SETUP) ---")


            cursor.execute("DELETE FROM T_MATCH_MTC")
            cursor.execute("DELETE FROM T_EQUIPE_EQP")
            cursor.execute("DELETE FROM T_COMPETITION_CPT")
            cursor.execute("DELETE FROM T_UTILISATEUR_UTL")
            cursor.execute("DELETE FROM T_ROLE_ROL")
            cursor.execute("DELETE FROM T_STATUTEQUIPE_SEQ")
            cursor.execute("DELETE FROM T_STATUTSMATCH_STM")

            print("> Insertion des rôles et status...")

            cursor.execute("INSERT INTO T_ROLE_ROL (ROL_Nom,ROL_ID) VALUES ('Organisateur',1)")
            id_role_organisateur = cursor.lastrowid
            
            cursor.execute("INSERT INTO T_ROLE_ROL (ROL_Nom,ROL_Id) VALUES ('Utilisateur',2)")
            id_role_joueur = cursor.lastrowid

            cursor.execute("INSERT INTO T_STATUTEQUIPE_SEQ (SEQ_Etat,SEQ_ID) VALUES ('Validée',1)")
            id_statut_eq_ok = cursor.lastrowid
            
            cursor.execute("INSERT INTO T_STATUTSMATCH_STM (STM_Nom,STM_Id) VALUES ('A venir',1)")
            id_statut_match_prevu = cursor.lastrowid

            print("> Insertion des utilisateurs...")
            
            cursor.execute("""
                INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse)
                VALUES (%s, 'alice@test.fr', 'Alice', 'Orga', 'secret')
            """, (id_role_organisateur,))
            id_alice = cursor.lastrowid

            cursor.execute("""
                INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse)
                VALUES (%s, 'bob@test.fr', 'Bob', 'Joueur', 'secret')
            """, (id_role_joueur,))
            id_bob = cursor.lastrowid

            cursor.execute("""
                INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse)
                VALUES (%s, 'charlie@test.fr', 'Charlie', 'Joueur', 'secret')
            """, (id_role_joueur,))
            id_charlie = cursor.lastrowid

            # D. Création de la Compétition
            # Le trigger va vérifier que id_alice a bien le rôle 'Organisateur' -> Ce qui est le cas maintenant.
            print("> Création de la compétition 'Coupe PyMySQL'...")
            cursor.execute("""
                INSERT INTO T_COMPETITION_CPT (UTL_Id, CPT_Nom, CPT_DateDebut, CPT_DateFin)
                VALUES (%s, 'Coupe PyMySQL 2026', '2026-06-01', '2026-06-30')
            """, (id_alice,))
            id_compet = cursor.lastrowid

            print("> Inscription des équipes 'Les Pythons' et 'Les Requins'...")
            cursor.execute("""
                INSERT INTO T_EQUIPE_EQP (CPT_Id, UTL_Id, SEQ_Id, EQP_Nom, EQP_DateCreation, EQP_Etablissement, EQP_Membres)
                VALUES (%s, %s, %s, 'Les Pythons', NOW(), 'IUT Informatique', '["Bob", "Dave"]')
            """, (id_compet, id_bob, id_statut_eq_ok))
            id_eq_pythons = cursor.lastrowid

            cursor.execute("""
                INSERT INTO T_EQUIPE_EQP (CPT_Id, UTL_Id, SEQ_Id, EQP_Nom, EQP_DateCreation, EQP_Etablissement, EQP_Membres)
                VALUES (%s, %s, %s, 'Les Requins', NOW(), 'Fac de Sciences', '["Charlie", "Eve"]')
            """, (id_compet, id_charlie, id_statut_eq_ok))
            id_eq_requins = cursor.lastrowid
            print("> Organisation du match Pythons vs Requins...")
            cursor.execute("""
                INSERT INTO `T_MATCH_MTC` (`MTC_Id`, `EQP_Id`, `EQP_Id_EquipeB`, `STM_Id`, `CPT_Id`, `MTC_Commentaire`, `MTC_Score`, `MTC_ForfaitEquipeA`, `MTC_ForfaitEquipeB`) VALUES (NULL, '%s', '%s', '%s', '%s', 'sdv', 'sdvsd', '0', '0');
            """, (id_eq_pythons, id_eq_requins, id_statut_match_prevu, id_compet))
            id_match = cursor.lastrowid
            conn.commit()
            print("--- JEU DE DONNÉES CRÉÉ AVEC SUCCÈS ---\n")
            print("--- Démarrage des Tests Automatisés ---\n")

            print("Test 1: Vérification organisateur...", end=" ")
            cursor.execute("""
                SELECT c.CPT_Nom, u.UTL_Nom, r.ROL_Nom
                FROM T_COMPETITION_CPT c
                JOIN T_UTILISATEUR_UTL u ON c.UTL_Id = u.UTL_Id
                JOIN T_ROLE_ROL r ON u.ROL_Id = r.ROL_Id
                WHERE c.CPT_Id = %s
            """, (id_compet,))
            res1 = cursor.fetchone()
            
            if res1['ROL_Nom'] == 'Organisateur':
                print("OK")
            else:
                print(f"ERREUR (Le rôle est {res1['ROL_Nom']} au lieu d'Organisateur)")


            print("Test 2: Vérification des équipes inscrites...", end=" ")
            cursor.execute("""
                SELECT COUNT(*) as nb FROM T_EQUIPE_EQP 
                WHERE CPT_Id = %s AND EQP_Nom IN ('Les Pythons', 'Les Requins')
            """, (id_compet,))
            if cursor.fetchone()['nb'] == 2:
                print("OK")
            else:
                print("ERREUR")

            print("Test 3: Vérification du Match...", end=" ")
            cursor.execute("""
                SELECT ea.EQP_Nom as A, eb.EQP_Nom as B, s.STM_Nom
                FROM T_MATCH_MTC m
                JOIN T_EQUIPE_EQP ea ON m.EQP_Id = ea.EQP_Id
                JOIN T_EQUIPE_EQP eb ON m.EQP_Id_EquipeB = eb.EQP_Id
                JOIN T_STATUTSMATCH_STM s ON m.STM_Id = s.STM_Id
                WHERE m.MTC_Id = %s
            """, (id_match,))
            match_data = cursor.fetchone()
            
            if match_data and match_data['A'] == 'Les Pythons' and match_data['B'] == 'Les Requins':
                print("OK")
            else:
                print("ERREUR")

    except pymysql.MySQLError as e:
        print(f"\nERREUR SQL CRITIQUE : {e}")
        if conn:
            conn.rollback()
            print("Rollback effectué (aucune donnée insérée).")
    finally:
        if conn:
            conn.close()
            print("\n--- Fin d'insertion ---")
    try:
        conn = pymysql.connect(**config)
        conn.autocommit = False
        cursor =conn.cursor()
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'jbj@j.com', 'Bonjour', 'Jean', 'Poop',80000)")
        cursor.execute("SELECT * FROM `T_UTILISATEUR_UTL` WHERE UTL_Mail = 'jbj@j.com'")
        id=cursor.fetchone()
        cursor.execute("INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES (80000, 'sdvsv', 'sdvs', '2000-01-01', '2000-01-01', 'sdv', NULL);")
    except pymysql.MySQLError as e :
        print(f"Contrainte T_COMPETITION_CPT_Check_Organisateur fonctionnelle : {e}")
    try:
        if conn:
            conn.close()
        conn = pymysql.connect(**config)
        conn.autocommit = False
        cursor =conn.cursor()
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (1, 'jbj@j.com', 'Bonjour', 'Jean', 'Poopfghfgh',40000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'Azerty@pop.com', 'Bonjour', 'Jean', 'Poop',78000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'Ytreza@pop.com', 'Bonjour', 'Jean', 'Poop',79000)")
        cursor.execute("INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES (40000, 'hng', 'sdvs', '2000-01-01', '2000-01-01', 'sdv', 900);")
        cursor.execute("INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES (40000, 'sdsdv', 'sdvs', '2000-01-01', '2000-01-01', 'sdv', 1000);")
        cursor.execute("INSERT INTO `T_EQUIPE_EQP` (`CPT_Id`, `UTL_Id`, `SEQ_Id`, `EQP_Nom`, `EQP_DateCreation`, `EQP_Etablissement`, `EQP_Membres`, `EQP_Id`) VALUES ('900', '78000', '1', 'ghjghfjfgjgf', '2026-01-12 08:47:41.000000', 'jfgjhfggjjf', 'jfjjfjfj', 1000);")
        cursor.execute("INSERT INTO `T_MATCH_MTC` (`MTC_Id`, `EQP_Id`, `EQP_Id_EquipeB`, `STM_Id`, `CPT_Id`, `MTC_Commentaire`, `MTC_Score`, `MTC_ForfaitEquipeA`, `MTC_ForfaitEquipeB`) VALUES (NULL, '1000', '1000', '1', '900', 'sdv', 'sdvsd', '0', '0') ")
    except pymysql.MySQLError as e :
        print(f"Contrainte CH_Equipe fonctionnelle : {e}")
    try:
        if conn:
            conn.close()
        conn = pymysql.connect(**config)
        conn.autocommit = False
        cursor =conn.cursor()
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (1, 'jbj@j.com', 'Bonjour', 'Jean', 'Poopfghfgh',40000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'Azerty@pop.com', 'Bonjour', 'Jean', 'Poop',78000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'Ytreza@pop.com', 'Bonjour', 'Jean', 'Poop',79000)")
        cursor.execute("INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES (40000, 'hng', 'sdvs', '2000-01-01', '2000-01-01', 'sdv', 900);")
        cursor.execute("INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES (40000, 'sdsdv', 'sdvs', '2000-01-01', '2000-01-01', 'sdv', 1000);")
        cursor.execute("INSERT INTO `T_EQUIPE_EQP` (`CPT_Id`, `UTL_Id`, `SEQ_Id`, `EQP_Nom`, `EQP_DateCreation`, `EQP_Etablissement`, `EQP_Membres`, `EQP_Id`) VALUES ('900', '78000', '1', 'ghjghfjfgjgf', '2026-01-12 08:47:41.000000', 'jfgjhfggjjf', 'jfjjfjfj', 1000);")
        cursor.execute("INSERT INTO `T_EQUIPE_EQP` (`CPT_Id`, `UTL_Id`, `SEQ_Id`, `EQP_Nom`, `EQP_DateCreation`, `EQP_Etablissement`, `EQP_Membres`, `EQP_Id`) VALUES ('1000', '79000', '1', 'ghjghfjfgjgf', '2026-01-12 08:47:41.000000', 'jfgjhfggjjf', 'jfjjfjfj', 2000);")
        cursor.execute("INSERT INTO `T_MATCH_MTC` (`MTC_Id`, `EQP_Id`, `EQP_Id_EquipeB`, `STM_Id`, `CPT_Id`, `MTC_Commentaire`, `MTC_Score`, `MTC_ForfaitEquipeA`, `MTC_ForfaitEquipeB`) VALUES (NULL, '1000', '2000', '1', '900', 'sdv', 'sdvsd', '0', '0') ")
    except pymysql.MySQLError as e :
        print(f"Contrainte T_Match_Verif_Compet fonctionnelle : {e}")
    try:
        if conn:
            conn.close()
        conn = pymysql.connect(**config)
        conn.autocommit = False
        cursor =conn.cursor()
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (1, 'jbj@j.com', 'Bonjour', 'Jean', 'Poopfghfgh',40000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'Azerty@pop.com', 'Bonjour', 'Jean', 'Poop',78000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'Ytreza@pop.com', 'Bonjour', 'Jean', 'Poop',79000)")
        cursor.execute("INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES (40000, 'hng', 'sdvs', '2000-02-01', '2000-01-01', 'sdv', 900);")
    except pymysql.MySQLError as e :
        print(f"Contrainte CHECK_DATE fonctionnelle : {e}")
    try:
        if conn:
            conn.close()
        conn = pymysql.connect(**config)
        conn.autocommit = False
        cursor =conn.cursor()
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (1, 'jbj@j.com', 'Bonjour', 'Jean', 'Poopfghfgh',40000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'jbj@j.com', 'dfbdbf', 'Jefdbdfban', 'Poop',78000)")
    except pymysql.MySQLError as e :
        print(f"Contrainte UTL_Unique_Mail fonctionnelle : {e}")
    try:
        if conn:
            conn.close()
        conn = pymysql.connect(**config)
        conn.autocommit = False
        cursor =conn.cursor()
        cursor.execute("INSERT INTO T_ROLE_ROL (ROL_Nom,ROL_ID) VALUES ('Organisateur',4)")
    except pymysql.MySQLError as e :
        print(f"Contrainte ROL_Unique_Nom fonctionnelle : {e}")
    try:
        if conn:
            conn.close()
        conn = pymysql.connect(**config)
        conn.autocommit = False
        cursor =conn.cursor()
        cursor.execute("INSERT INTO T_STATUTEQUIPE_SEQ (SEQ_Etat) VALUES ('Validée')")
    except pymysql.MySQLError as e :
        print(f"Contrainte SEQ_Unique_Nom fonctionnelle : {e}")
    try:
        if conn:
            conn.close()
        conn = pymysql.connect(**config)
        conn.autocommit = False
        cursor =conn.cursor()
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (1, 'jbj@j.com', 'Bonjour', 'Jean', 'Poopfghfgh',40000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'Azerty@pop.com', 'Bonjour', 'Jean', 'Poop',78000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'Ytreza@pop.com', 'Bonjour', 'Jean', 'Poop',79000)")
        cursor.execute("INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES (40000, 'hng', 'sdvs', '2000-01-01', '2000-01-01', 'sdv', 900);")
        cursor.execute("INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES (40000, 'hng', 'sdvs', '2000-01-01', '2000-01-01', 'sdv', 1000);")
    except pymysql.MySQLError as e :
        print(f"Contrainte CPT_Unique_Nom fonctionnelle : {e}")
    try:
        if conn:
            conn.close()
        conn = pymysql.connect(**config)
        conn.autocommit = False
        cursor =conn.cursor()
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (1, 'jbj@j.com', 'Bonjour', 'Jean', 'Poopfghfgh',40000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'Azerty@pop.com', 'Bonjour', 'Jean', 'Poop',78000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'Ytreza@pop.com', 'Bonjour', 'Jean', 'Poop',79000)")
        cursor.execute("INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES (40000, 'hng', 'sdvs', '2000-01-01', '2000-01-01', 'sdv', 900);")
        cursor.execute("INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES (40000, 'sdsdv', 'sdvs', '2000-01-01', '2000-01-01', 'sdv', 1000);")
        cursor.execute("INSERT INTO `T_EQUIPE_EQP` (`CPT_Id`, `UTL_Id`, `SEQ_Id`, `EQP_Nom`, `EQP_DateCreation`, `EQP_Etablissement`, `EQP_Membres`, `EQP_Id`) VALUES ('900', '78000', '1', 'ghjghfjfgjgf', '2026-01-12 08:47:41.000000', 'jfgjhfggjjf', 'jfjjfjfj', 1000);")
        cursor.execute("INSERT INTO `T_EQUIPE_EQP` (`CPT_Id`, `UTL_Id`, `SEQ_Id`, `EQP_Nom`, `EQP_DateCreation`, `EQP_Etablissement`, `EQP_Membres`, `EQP_Id`) VALUES ('900', '79000', '1', 'ghjghfjfgjgf', '2026-01-12 08:47:41.000000', 'jfgjhfggjjf', 'jfjjfjfj', 2000);")
    except pymysql.MySQLError as e :
        print(f"Contrainte EQP_Unique_Nom fonctionnelle : {e}")
    try:
        if conn:
            conn.close()
        conn = pymysql.connect(**config)
        conn.autocommit = False
        cursor =conn.cursor()
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (1, 'jbj@j.com', 'Bonjour', 'Jean', 'Poopfghfgh',40000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'Azerty@pop.com', 'Bonjour', 'Jean', 'Poop',78000)")
        cursor.execute("INSERT INTO T_UTILISATEUR_UTL  (ROL_Id, UTL_Mail, UTL_Nom, UTL_Prenom, UTL_MotDePasse,UTL_Id) VALUES (2, 'Ytreza@pop.com', 'Bonjour', 'Jean', 'Poop',79000)")
        cursor.execute("INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES (40000, 'hng', 'sdvs', '2000-01-01', '2000-01-01', 'sdv', 900);")
        cursor.execute("INSERT INTO `T_COMPETITION_CPT` (`UTL_Id`, `CPT_Nom`, `CPT_Lieu`, `CPT_DateDebut`, `CPT_DateFin`, `CPT_Description`, `CPT_Id`) VALUES (40000, 'sdsdv', 'sdvs', '2000-01-01', '2000-01-01', 'sdv', 1000);")
        cursor.execute("INSERT INTO `T_EQUIPE_EQP` (`CPT_Id`, `UTL_Id`, `SEQ_Id`, `EQP_Nom`, `EQP_DateCreation`, `EQP_Etablissement`, `EQP_Membres`, `EQP_Id`) VALUES ('900', '78000', '1', 'ghjghfjfgjgf', '2026-01-12 08:47:41.000000', 'jfgjhfggjjf', 'jfjjfjfj', 1000);")
        cursor.execute("INSERT INTO `T_EQUIPE_EQP` (`CPT_Id`, `UTL_Id`, `SEQ_Id`, `EQP_Nom`, `EQP_DateCreation`, `EQP_Etablissement`, `EQP_Membres`, `EQP_Id`) VALUES ('900', '79000', '1', 'ghjghfjfjklgjgf', '2026-01-12 08:47:41.000000', 'jfgjhfggjjf', 'jfjjfjfj', 2000);")
        cursor.execute("INSERT INTO `T_MATCH_MTC` (`MTC_Id`, `EQP_Id`, `EQP_Id_EquipeB`, `STM_Id`, `CPT_Id`, `MTC_Commentaire`, `MTC_Score`, `MTC_ForfaitEquipeA`, `MTC_ForfaitEquipeB`) VALUES (NULL, '1000', '2000', '1', '900', 'sdv', 'sdvsd', '0', '0') ")
        cursor.execute("INSERT INTO `T_MATCH_MTC` (`MTC_Id`, `EQP_Id`, `EQP_Id_EquipeB`, `STM_Id`, `CPT_Id`, `MTC_Commentaire`, `MTC_Score`, `MTC_ForfaitEquipeA`, `MTC_ForfaitEquipeB`) VALUES (NULL, '1000', '2000', '1', '900', 'sdv', 'sdvsd', '0', '0') ")
    except pymysql.MySQLError as e :
        print(f"Contrainte STM_Unique_Match fonctionnelle : {e}")



if __name__ == "__main__":
    executer_scenario_et_tests()
#cursor = conn.cursor()
#cursor.execute('select * from T_EQUIPE_EQP;')
#output = cursor.fetchall()
#for i in output:
#    for elements in i:
#        print(elements)
    
#cursor.execute('select * from table1;')
#cursor.execute('INSERT INTO `T_UTILISATEUR_UTL` (`ROL_Id`, `UTL_Mail`, `UTL_Nom`, `UTL_Prenom`, `UTL_MotDePasse`, `UTL_Id`) VALUES (\'1\', \'Maxime@test.com\', \'Bonjour\', \'Hello\', \'Plop\', NULL);')
#conn.commit()

#cursor.close()

#conn.close()
