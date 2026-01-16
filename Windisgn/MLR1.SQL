DROP DATABASE IF EXISTS MLR1;

CREATE DATABASE IF NOT EXISTS MLR1;
USE MLR1;
# -----------------------------------------------------------------------------
#       TABLE : T_ROLE_ROL
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_ROLE_ROL
 (
   ROL_Id CHAR(32) NOT NULL  ,
   ROL_Nom CHAR(32) NOT NULL  
   , PRIMARY KEY (ROL_Id) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_RESULTATMATCH_RMT
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_RESULTATMATCH_RMT
 (
   RMT_Id INTEGER(2) NOT NULL  ,
   MTC_Id CHAR(32) NOT NULL  ,
   EQP_Nom CHAR(32) NOT NULL  ,
   RMT_Etat CHAR(32) NOT NULL  ,
   Score INTEGER(2) NULL  
   , PRIMARY KEY (RMT_Id) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_UTILISATEUR_UTL
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_UTILISATEUR_UTL
 (
   UTL_Mail CHAR(64) NOT NULL  ,
   ROL_Id CHAR(32) NOT NULL  ,
   UTL_Nom CHAR(32) NOT NULL  ,
   UTL_Prenom CHAR(32) NOT NULL  ,
   UTL_MotDePasse CHAR(32) NOT NULL  
   , PRIMARY KEY (UTL_Mail) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_EQUIPE_EQP
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_EQUIPE_EQP
 (
   EQP_Nom CHAR(32) NOT NULL  ,
   CPT_Nom CHAR(32) NOT NULL  ,
   UTL_Mail CHAR(64) NOT NULL  ,
   EQP_DateCreation DATETIME NOT NULL  ,
   EQP_ListeMembres ALV NOT NULL  ,
   EQP_Etablissement CHAR(32) NOT NULL  
   , PRIMARY KEY (EQP_Nom) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_COMPETITION_CPT
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_COMPETITION_CPT
 (
   CPT_Nom CHAR(32) NOT NULL  ,
   CPT_Lieu CHAR(64) NULL  ,
   CPT_DateDebut DATE NOT NULL  ,
   CPT_DateFin DATE NOT NULL  ,
   CPT_Description CHAR(256) NULL  
   , PRIMARY KEY (CPT_Nom) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_MATCH_MTC
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_MATCH_MTC
 (
   MTC_Id CHAR(32) NOT NULL  ,
   STM_Id CHAR(32) NOT NULL  ,
   CPT_Nom CHAR(32) NOT NULL  ,
   MTC_Commentaire CHAR(32) NULL  
   , PRIMARY KEY (MTC_Id) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_STATUSMATCH_STM
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_STATUSMATCH_STM
 (
   STM_Id CHAR(32) NOT NULL  ,
   STM_Nom CHAR(32) NOT NULL  
   , PRIMARY KEY (STM_Id) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : Organise
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS Organise
 (
   UTL_Mail CHAR(64) NOT NULL  ,
   CPT_Nom CHAR(32) NOT NULL  
   , PRIMARY KEY (UTL_Mail,CPT_Nom) 
 ) 
 comment = "";


# -----------------------------------------------------------------------------
#       CREATION DES REFERENCES DE TABLE
# -----------------------------------------------------------------------------


ALTER TABLE T_RESULTATMATCH_RMT 
  ADD FOREIGN KEY FK_T_RESULTATMATCH_RMT_T_MATCH_MTC (MTC_Id)
      REFERENCES T_MATCH_MTC (MTC_Id) ;


ALTER TABLE T_RESULTATMATCH_RMT 
  ADD FOREIGN KEY FK_T_RESULTATMATCH_RMT_T_EQUIPE_EQP (EQP_Nom)
      REFERENCES T_EQUIPE_EQP (EQP_Nom) ;


ALTER TABLE T_UTILISATEUR_UTL 
  ADD FOREIGN KEY FK_T_UTILISATEUR_UTL_T_ROLE_ROL (ROL_Id)
      REFERENCES T_ROLE_ROL (ROL_Id) ;


ALTER TABLE T_EQUIPE_EQP 
  ADD FOREIGN KEY FK_T_EQUIPE_EQP_T_COMPETITION_CPT (CPT_Nom)
      REFERENCES T_COMPETITION_CPT (CPT_Nom) ;


ALTER TABLE T_EQUIPE_EQP 
  ADD FOREIGN KEY FK_T_EQUIPE_EQP_T_UTILISATEUR_UTL (UTL_Mail)
      REFERENCES T_UTILISATEUR_UTL (UTL_Mail) ;


ALTER TABLE T_MATCH_MTC 
  ADD FOREIGN KEY FK_T_MATCH_MTC_T_STATUSMATCH_STM (STM_Id)
      REFERENCES T_STATUSMATCH_STM (STM_Id) ;


ALTER TABLE T_MATCH_MTC 
  ADD FOREIGN KEY FK_T_MATCH_MTC_T_COMPETITION_CPT (CPT_Nom)
      REFERENCES T_COMPETITION_CPT (CPT_Nom) ;


ALTER TABLE Organise 
  ADD FOREIGN KEY FK_Organise_T_UTILISATEUR_UTL (UTL_Mail)
      REFERENCES T_UTILISATEUR_UTL (UTL_Mail) ;


ALTER TABLE Organise 
  ADD FOREIGN KEY FK_Organise_T_COMPETITION_CPT (CPT_Nom)
      REFERENCES T_COMPETITION_CPT (CPT_Nom) ;

