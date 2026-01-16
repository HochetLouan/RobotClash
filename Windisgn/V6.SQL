
# -----------------------------------------------------------------------------
#       TABLE : T_ROLE_ROL
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_ROLE_ROL
 (
   ROL_Id INTEGER(3) NOT NULL AUTO_INCREMENT ,
   ROL_Nom CHAR(32) NOT NULL  
   , PRIMARY KEY (ROL_Id) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_UTILISATEUR_UTL
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_UTILISATEUR_UTL
 (
   ROL_Id INTEGER(3) NOT NULL  ,
   UTL_Mail CHAR(64) NOT NULL  ,
   UTL_Nom CHAR(32) NOT NULL  ,
   UTL_Prenom CHAR(32) NOT NULL  ,
   UTL_MotDePasse CHAR(255) NOT NULL  ,
   UTL_Id BIGINT(4) NOT NULL AUTO_INCREMENT,
   UTL_Langue CHAR(5) NOT NULL  DEFAULT 'fr'
   , PRIMARY KEY (UTL_Id) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_STATUTEQUIPE_SEQ
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_STATUTEQUIPE_SEQ
 (
   SEQ_Id BIGINT(4) NOT NULL AUTO_INCREMENT ,
   SEQ_Etat CHAR(32) NOT NULL  
   , PRIMARY KEY (SEQ_Id) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_EQUIPE_EQP
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_EQUIPE_EQP
 (
   CPT_Id BIGINT(4) NOT NULL  ,
   UTL_Id BIGINT(4) NOT NULL  ,
   SEQ_Id BIGINT(4) NOT NULL  ,
   EQP_Nom CHAR(64) NOT NULL  ,
   EQP_DateCreation DATETIME NOT NULL  ,
   EQP_Etablissement CHAR(64) NOT NULL  ,
   EQP_Membres JSON NULL  ,
   EQP_Id INTEGER(4) NOT NULL AUTO_INCREMENT 
   , PRIMARY KEY (EQP_Id) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_STATUTSMATCH_STM
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_STATUTSMATCH_STM
 (
   STM_Id BIGINT(4) NOT NULL AUTO_INCREMENT ,
   STM_Nom CHAR(32) NOT NULL  
   , PRIMARY KEY (STM_Id) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_COMPETITION_CPT
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_COMPETITION_CPT
 (
   UTL_Id BIGINT(4) NOT NULL  ,
   CPT_Nom CHAR(64) NOT NULL  ,
   CPT_Lieu CHAR(64) NULL  ,
   CPT_DateDebut DATE NOT NULL  ,
   CPT_DateFin DATE NOT NULL  ,
   CPT_Description CHAR(255) NULL  ,
   CPT_NbMaxEquipesBracket INT DEFAULT 0 NOT NULL,
   CPT_PetiteFinale BOOLEAN DEFAULT FALSE NOT NULL,
   CPT_ArbreGenere BOOLEAN DEFAULT FALSE NOT NULL,
   CPT_Id BIGINT(4) NOT NULL AUTO_INCREMENT,
   CPT_NbTerrain BIGINT(4) DEFAULT 1 NOT NULL  
   , PRIMARY KEY (CPT_Id) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_MATCH_MTC
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_MATCH_MTC
 (
   MTC_Id INTEGER(4) NOT NULL AUTO_INCREMENT ,
   EQP_Id INTEGER(2) NOT NULL  ,
   EQP_Id_EquipeB INTEGER(2) NOT NULL  ,
   STM_Id BIGINT(4) NOT NULL  ,
   CPT_Id BIGINT(4) NOT NULL  ,
   MTC_Commentaire CHAR(32) NULL  ,
   MTC_Score CHAR(32) NULL,
   MTC_ForfaitEquipeA  tinyint(1) not null,
   MTC_ForfaitEquipeB  tinyint(1) not null,
   MTC_Horaire DATETIME NULL,
   MTC_Terrain CHAR(3)
   , PRIMARY KEY (MTC_Id) 
 ) 
 comment = "";


# -----------------------------------------------------------------------------
#       CREATION DES REFERENCES DE TABLE
# -----------------------------------------------------------------------------


ALTER TABLE T_UTILISATEUR_UTL 
  ADD FOREIGN KEY FK_T_UTILISATEUR_UTL_T_ROLE_ROL (ROL_Id)
      REFERENCES T_ROLE_ROL (ROL_Id) ;


ALTER TABLE T_EQUIPE_EQP 
  ADD FOREIGN KEY FK_T_EQUIPE_EQP_T_COMPETITION_CPT (CPT_Id)
      REFERENCES T_COMPETITION_CPT (CPT_Id) ;


ALTER TABLE T_EQUIPE_EQP 
  ADD FOREIGN KEY FK_T_EQUIPE_EQP_T_UTILISATEUR_UTL (UTL_Id)
      REFERENCES T_UTILISATEUR_UTL (UTL_Id) ;


ALTER TABLE T_EQUIPE_EQP 
  ADD FOREIGN KEY FK_T_EQUIPE_EQP_T_STATUTEQUIPE_SEQ (SEQ_Id)
      REFERENCES T_STATUTEQUIPE_SEQ (SEQ_Id) ;


ALTER TABLE T_COMPETITION_CPT 
  ADD FOREIGN KEY FK_T_COMPETITION_CPT_T_UTILISATEUR_UTL (UTL_Id)
      REFERENCES T_UTILISATEUR_UTL (UTL_Id) ;


ALTER TABLE T_MATCH_MTC 
  ADD FOREIGN KEY FK_T_MATCH_MTC_T_EQUIPE_EQP (EQP_Id)
      REFERENCES T_EQUIPE_EQP (EQP_Id) ;


ALTER TABLE T_MATCH_MTC 
  ADD FOREIGN KEY FK_T_MATCH_MTC_T_EQUIPE_EQP1 (EQP_Id_EquipeB)
      REFERENCES T_EQUIPE_EQP (EQP_Id) ;


ALTER TABLE T_MATCH_MTC 
  ADD FOREIGN KEY FK_T_MATCH_MTC_T_STATUTSMATCH_STM (STM_Id)
      REFERENCES T_STATUTSMATCH_STM (STM_Id) ;


ALTER TABLE T_MATCH_MTC 
  ADD FOREIGN KEY FK_T_MATCH_MTC_T_COMPETITION_CPT (CPT_Id)
      REFERENCES T_COMPETITION_CPT (CPT_Id) ;


DELIMITER $$
CREATE OR REPLACE TRIGGER T_COMPETITION_CPT_Check_Organisateur BEFORE INSERT ON T_COMPETITION_CPT FOR EACH ROW BEGIN DECLARE v_role_nom VARCHAR(64); SELECT R.ROL_Nom INTO v_role_nom FROM T_UTILISATEUR_UTL U INNER JOIN T_ROLE_ROL R ON U.ROL_Id = R.ROL_Id WHERE U.UTL_Id = NEW.UTL_Id; IF v_role_nom <> 'Organisateur' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur : L''utilisateur assigné à la compétition doit avoir le rôle Organisateur.'; END IF; END$$

CREATE OR REPLACE TRIGGER T_COMPETITION_CPT_Check_Organisateur_Update BEFORE UPDATE ON T_COMPETITION_CPT FOR EACH ROW BEGIN DECLARE v_role_nom VARCHAR(64); IF NEW.UTL_Id <> OLD.UTL_Id THEN SELECT R.ROL_Nom INTO v_role_nom FROM T_UTILISATEUR_UTL U INNER JOIN T_ROLE_ROL R ON U.ROL_Id = R.ROL_Id WHERE U.UTL_Id = NEW.UTL_Id; IF v_role_nom <> 'Organisateur' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur : Le nouvel organisateur doit avoir le rôle Organisateur.'; END IF; END IF; END$$ 
DELIMITER ;

alter TABLE T_MATCH_MTC ADD CONSTRAINT CH_Equipe CHECK (T_MATCH_MTC.EQP_Id <> T_MATCH_MTC.EQP_ID_EquipeB);



alter table T_COMPETITION_CPT
add CONSTRAINT CHECK_DATE CHECK(T_COMPETITION_CPT.CPT_DateDebut<= T_COMPETITION_CPT.CPT_DateFin);

alter TABLE T_STATUTSMATCH_STM ADD CONSTRAINT STM_Unique_Nom UNIQUE (STM_Nom); 

ALTER TABLE T_STATUTEQUIPE_SEQ ADD CONSTRAINT SEQ_Unique_Nom UNIQUE (SEQ_Etat); 
ALTER TABLE T_ROLE_ROL ADD CONSTRAINT ROL_Unique_Nom UNIQUE (ROL_Nom);

ALTER TABLE T_COMPETITION_CPT ADD CONSTRAINT CPT_Unique_Nom UNIQUE (CPT_Nom);
ALTER TABLE T_UTILISATEUR_UTL ADD CONSTRAINT UTL_Unique_Mail UNIQUE (UTL_Mail);
ALTER TABLE T_EQUIPE_EQP ADD CONSTRAINT EQP_Unique_Nom UNIQUE (EQP_Nom,CPT_Id);
alter TABLE T_MATCH_MTC ADD CONSTRAINT MTC_Unique_Match UNIQUE (EQP_Id_EquipeB ,EQP_Id); 
alter table T_MATCH_MTC add CONSTRAINT MTC_Unique_Date UNIQUE (MTC_Horaire,CPT_Id,MTC_Terrain);
DELIMITER $$
CREATE OR REPLACE TRIGGER T_Match_Verif_Compet 
BEFORE INSERT ON T_MATCH_MTC
fOR EACH ROW
BEGIN
	DECLARE Id_Competition_Equipe_A INT;
    DECLARE Id_Competition_Equipe_b INT;
 	SELECT CPT_Id INTO Id_Competition_Equipe_A FROM T_EQUIPE_EQP WHERE EQP_Id = NEW.EQP_Id;
    SELECT CPT_Id INTO Id_Competition_Equipe_b FROM T_EQUIPE_EQP WHERE EQP_Id = NEW.EQP_Id_EquipeB;
    IF Id_Competition_Equipe_A <> NEW.CPT_Id OR  Id_Competition_Equipe_b <> NEW.CPT_Id THEN 
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur : Une des equipes renseigné ne fait pas partie de la competition'; 
END IF; 
END$$

DELIMITER $$
CREATE OR REPLACE TRIGGER SuppressionCompetition
BEFORE DELETE ON T_COMPETITION_CPT
FOR EACH ROW
BEGIN
    DELETE FROM T_MATCH_MTC WHERE T_MATCH_MTC.CPT_Id = OLD.CPT_Id;
    DELETE FROM T_EQUIPE_EQP WHERE T_EQUIPE_EQP.CPT_Id = OLD.CPT_Id;
END$$

DELIMITER $$
CREATE OR REPLACE TRIGGER `SuppressionUtilisateur` BEFORE DELETE ON T_UTILISATEUR_UTL
 FOR EACH ROW BEGIN
    DELETE FROM T_EQUIPE_EQP WHERE T_EQUIPE_EQP.UTL_Id = OLD.UTL_Id;
    DELETE FROM T_COMPETITION_CPT WHERE T_COMPETITION_CPT.UTL_Id = OLD.UTL_Id;
END$$
INSERT INTO `T_ROLE_ROL` (`ROL_Id`, `ROL_Nom`) VALUES (1, 'Organisateur'), (2, 'Utilisateur');
INSERT INTO `T_STATUTEQUIPE_SEQ` (`SEQ_Id`, `SEQ_Etat`) VALUES (NULL, 'Acceptee'), (NULL, 'En attente'),(NULL, 'Refusée');
INSERT INTO `T_STATUTSMATCH_STM` (`STM_Id`, `STM_Nom`) VALUES (NULL, 'A venir'), (NULL, 'Annule');
INSERT INTO `T_STATUTSMATCH_STM` (`STM_Id`, `STM_Nom`) VALUES (NULL, 'Termine');
