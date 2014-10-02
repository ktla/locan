<?php
/**
	Ce fichier permet de transmettre a une periode
	les valeurs d'une periode precedente a savoir
	les classes, les enseignement, les professeur, les utilisateur systeme
	le nombre des parametre, et certain parametre important
*/
/*****************************************************/
	require_once("../includes/commun_inc.php");
/*****************************************************/
/*
	Verifie l'authencite de l'utilisateur
	- Redirection si utilisateur non autorise
	- Redirection si la duree de la session est terminer 
	- Confere commun_in.php pour la duree de la session dans la variable $_SESSION['timeconnect'];
*/
if(!isset($_SESSION['user']))	header("location:../utilisateurs/connexion.php");
/*
	Verification du droit d'acces de cette page
	Verifier que le codepage existe dans nos listedroit, ceci
	empeche de proceder par saisie de l'url et d'acceder a la page
*/
	$codepage = "INTER_PERIODE";
$titre = "Inter P&eacute;riode";
/******************************************
*
*	Integration du modele de haut de page
*
*******************************************/
require_once("../includes/header_inc.php");
/**
*
*/
if(isset($_POST['interperiode'])){
	interperiode();
}else
	selectionnerPeriode();
/**
	Function propre a la page
*/
function selectionnerPeriode(){?>
<script>
	function selectionnerPeriode(){
		var frm = document.forms['frm'];
		frm.submit();
	}
</script>
<div id="zonetravail"><div class="titre">Effectuer une interp&eacute;riode</div>
	<form action="interperiode.php" name="frm" method="POST" enctype="multipart/form-data" onsubmit="selectionnerPeriode(); return false;">
    	<div class="cadre">
        	<fieldset><legend>Choisir les deux ann&eacute;es acad&eacute;miques</legend>
            	<table cellpadding="2" cellspacing="2">
                	<tr><td class="lib">Ann&eacute;e acad&eacute;mique de d&eacute;part : </td>
                    	<td><?php
							$annee = new Combo("SELECT * FROM annee_academique ORDER BY ANNEEACADEMIQUE", 'ancienneperiode', 0, 0, 
							isset($_POST['ancienneperiode']) ? true : false);
							$annee->first = "--Choisir une ann&eacute;e acad&eacute;mique--";
							$annee->selectedid = isset($_POST['ancienneperiode']) ? $_POST['ancienneperiode'] : "";
							$annee->view('100%');
						 ?></td>
                         <td class="lib">Ann&eacute;e acad&eacute;mique de fin : </td>
                    	<td><?php
							$annee = new Combo("SELECT * FROM annee_academique ORDER BY ANNEEACADEMIQUE", 'nouvelleperiode', 0, 0, 
							isset($_POST['nouvelleperiode']) ? true : false);
							$annee->first = "--Choisir une ann&eacute;e acad&eacute;mique--";
							$annee->selectedid = isset($_POST['nouvelleperiode']) ? $_POST['nouvelleperiode'] : "";
							$annee->view('100%');
						 ?></td>
                    </tr>
                    <tr>
                    	<td colspan="2">Intervalle de diff&eacute;rence entre les deux ann&eacute;es : </td>
                        <td colspan="2"><select name="intervalle" style="width:100%"><?php
							for($i = -10; $i <= 10; $i++){
								print "<option value = '".$i."'>".$i."</option>";
							}
						?></select></td>
                    </tr>
                </table>
            </fieldset>
    	</div>
    	<div class="navigation">
        	<input type="button" value="Retour" onclick="home();" />
            <input type="hidden" value="0" name="interperiode" />
            <input type="submit" value="Valider" />
    	</div>
    </form>
</div>
<?php 
}
/**
	Interperioder les periodes
*/
function interperiode(){
	if(!strcmp($_POST['ancienneperiode'], $_POST['nouvelleperiode'])){
		print "<p class = 'infos'>Les p&eacute;riodes selectionn&eacute;es doivent &ecirc;tre diff&eacute;rentes</p>";
		selectionnerPeriode();
		return;
	}
	/*$pdo = Database::connect2db();
	$anc = $_POST['ancienneperiode'];
	$nouv = $_POST['nouvelleperiode'];
	/* Insert dans la table classe_frais 
		Augmenter datedebut et datefin de POST['intervalle'] ou soustraire.
	*/
	/*$query = "INSERT INTO classe_frais(CODE, IDCLASSE, LIBELLE, DATEDEBUT, DATEFIN, MONTANT, TYPE, PERIODE) 
	SELECT CODE, IDCLASSE, LIBELLE, DATEDEBUT FROM classe_frais WHERE PERIODE = :periode";
	$res = $pdo->prepare($query);
	$res->bindValue('ancperiode', $anc, PDO::PARAM_STR);
	$pdo->exec();*/
	print "<p class = 'infos'>";
	/** Transmettre les parametre de classes	*/
	$bool = Classe::interperiode($_POST['ancienneperiode']);
	if($bool){
		print "Succ&egrave;s : Param&egrave;tres classse<br/>";
	}else{
		print "Echec : Param&egrave;tres classes <br/>";
	}
	/** Transmettre les reduction et frais applique au classe a la nouvelle periode */
	$bool = Frais::interperiode($_POST['ancienneperiode']);
	if($bool){
		print "Succ&egrave;s : Frais | R&eacute;duction appliqu&eacute;s <br/>";
	}else{
		print "Echec : Frais | R&eacute;duction appliqu&eacute;s  <br/>";
	}	
	/** Transmettre les enseignements */
	$bool = Enseignement::interperiode($_POST['ancienneperiode']);
	if($bool){
		print "Succ&egrave;s : Enseignement pour chaque classe <br/>";
	}else{
		print "Echec : Enseignement pour chaque classe<br/>";
	}
	
	print "</p>";

}
/***************************************************
*	Integration du modele de bas de page
*
**************************************************/
require_once('../includes/footer_inc.php');
?>