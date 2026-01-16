
USE VotreBase;
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
   UTL_Mail CHAR(64) NOT NULL  ,
   ROL_Id INTEGER(3) NOT NULL  ,
   UTL_Nom CHAR(32) NOT NULL  ,
   UTL_Prenom CHAR(32) NOT NULL  ,
   UTL_MotDePasse CHAR(32) NOT NULL  
   , PRIMARY KEY (UTL_Mail) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_STATUTEQUIPE_SEQ
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_STATUTEQUIPE_SEQ
 (
   SEQ_Id INT NOT NULL AUTO_INCREMENT ,
   SEQ_Etat CHAR(32) NOT NULL  
   , PRIMARY KEY (SEQ_Id) 
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
   SEQ_Id INT NOT NULL  ,
   EQP_DateCreation DATETIME NOT NULL  ,
   EQP_Etablissement CHAR(32) NOT NULL  ,
   EQP_Membres JSON NULL  
   , PRIMARY KEY (EQP_Nom) 
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
   CPT_Nom CHAR(32) NOT NULL  ,
   UTL_Mail CHAR(64) NOT NULL  ,
   CPT_Lieu CHAR(64) NULL  ,
   CPT_DateDebut DATE NOT NULL  ,
   CPT_DateFin DATE NOT NULL  ,
   CPT_Description CHAR(255) NULL  
   , PRIMARY KEY (CPT_Nom) 
 ) 
 comment = "";

# -----------------------------------------------------------------------------
#       TABLE : T_MATCH_MTC
# -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS T_MATCH_MTC
 (
   MTC_Id INTEGER(4) NOT NULL AUTO_INCREMENT ,
   STM_Id BIGINT(4) NOT NULL  ,
   EQP_Nom CHAR(32) NOT NULL  ,
   EQP_Nom_EquipeB CHAR(32) NOT NULL  ,
   CPT_Nom CHAR(32) NOT NULL  ,
   MTC_Commentaire CHAR(32) NULL  ,
   MTC_Score CHAR(32) NULL  
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
  ADD FOREIGN KEY FK_T_EQUIPE_EQP_T_COMPETITION_CPT (CPT_Nom)
      REFERENCES T_COMPETITION_CPT (CPT_Nom) ;


ALTER TABLE T_EQUIPE_EQP 
  ADD FOREIGN KEY FK_T_EQUIPE_EQP_T_UTILISATEUR_UTL (UTL_Mail)
      REFERENCES T_UTILISATEUR_UTL (UTL_Mail) ;


ALTER TABLE T_EQUIPE_EQP 
  ADD FOREIGN KEY FK_T_EQUIPE_EQP_T_STATUTEQUIPE_SEQ (SEQ_Id)
      REFERENCES T_STATUTEQUIPE_SEQ (SEQ_Id) ;


ALTER TABLE T_COMPETITION_CPT 
  ADD FOREIGN KEY FK_T_COMPETITION_CPT_T_UTILISATEUR_UTL (UTL_Mail)
      REFERENCES T_UTILISATEUR_UTL (UTL_Mail) ;


ALTER TABLE T_MATCH_MTC 
  ADD FOREIGN KEY FK_T_MATCH_MTC_T_STATUTSMATCH_STM (STM_Id)
      REFERENCES T_STATUTSMATCH_STM (STM_Id) ;


ALTER TABLE T_MATCH_MTC 
  ADD FOREIGN KEY FK_T_MATCH_MTC_T_EQUIPE_EQP (EQP_Nom)
      REFERENCES T_EQUIPE_EQP (EQP_Nom) ;


ALTER TABLE T_MATCH_MTC 
  ADD FOREIGN KEY FK_T_MATCH_MTC_T_EQUIPE_EQP1 (EQP_Nom_EquipeB)
      REFERENCES T_EQUIPE_EQP (EQP_Nom) ;


ALTER TABLE T_MATCH_MTC 
  ADD FOREIGN KEY FK_T_MATCH_MTC_T_COMPETITION_CPT (CPT_Nom)
      REFERENCES T_COMPETITION_CPT (CPT_Nom) ;

