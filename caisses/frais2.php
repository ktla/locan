<?php
//sleep(5);
require_once("../includes/commun_inc.php");
/*
	Verifier les droit d'acces a cette page
*/
if(!isset($_SESSION['user']))
	@header("Location:../utilisateurs/connexion.php");
/**
	Droit d'acces a la page
*/
if(!is_autorized("CAISSE_FRAIS")){
	print "<p class = 'infos'>Vous n avez pas des droits d acces sur cette page</p>";
	return;
}
/*********************************************************************************************************/
/*
	Validation d'information dans la BD 
	Validation des frais
*/
if(isset($_GET['code']) && isset($_GET['montant']) && isset($_GET['libelle'])){
	/*
		Verifier les donnees
	*/
	if(empty($_GET['code']) || empty($_GET['libelle']) || empty($_GET['montant']) || empty($_GET['classe'])){
		print "<p class = 'infos'>Les champs Code, libelle, montant sont obligatoires</p>";
		return;
	}
	if(!is_numeric($_GET['montant']) || intval($_GET['montant']) < 0){
		print "<p class = 'infos'>Le montant doit etre un nombre positif</p>";
		return;
	}
	$query = "INSERT INTO classe_frais(CODE, IDCLASSE, LIBELLE, DATEDEBUT, DATEFIN, MONTANT, TYPE, PERIODE) 
	VALUES(:code, :idclasse, :libelle, :datedebut, :datefin, :montant, :type, :periode)";
	$param = array("code"=>$_GET['code'],
					"idclasse"=>$_GET['classe'],
					"libelle"=>$_GET['libelle'],
					"datedebut"=>parseDate($_GET['datedebut']),
					"datefin"=>parseDate($_GET['datefin']),
					"montant"=>$_GET['montant'],
					"type"=>$_GET['type'],
					"periode"=>$_SESSION['periode']
				);
	$db = new Database($query,0, $param);
	if(!$db->insert())
		die($db->getLog('error'));
	/*
		Affichage de la zone montrant les nouveaux frais ajoutes
	*/
	afficher($db);
	unset($db);
}
/*********************************************
*
*	Validation des reductions
*********************************************/
if(isset($_GET['codereduction'])){
	if(empty($_GET['codereduction']) || empty($_GET['libellereduction']) || empty($_GET['typereduction'])
	|| empty($_GET['appliquera']) || empty($_GET['montantreduction'])){
		print "<tr><td colspan = '5'>Entrer tous les param&egrave;tres</td></tr>";
		return;
	}
	$query = "INSERT INTO classe_reduction(CODE, IDFRAIS, LIBELLE, MONTANT, TYPE) 
	VALUES (:codereduction, :appliquera, :libellereduction, :montantreduction, :typereduction)";
	$param = array(
		"codereduction" => $_GET['codereduction'], 
		"appliquera" => $_GET['appliquera'], 
		"libellereduction" => $_GET['libellereduction'], 
		"montantreduction" => $_GET['montantreduction'],
		"typereduction" => $_GET['typereduction']
	);
	$db = new Database($query, 0, $param);
	if($db->insert()){
		afficherReduction($db);
		unset($db);
	}else
		die($db->getLog('error'));

}
/**********************************************
*
*	LOSRQU'ON CLIQUE SUR L'ICONE SUPPRIMER
*
***********************************************/
if(isset($_GET['supprimer'])){
	/* Verifier que l'id frais a ete envoyer par ajax */
	if(isset($_GET['idfrais']) && !empty($_GET['idfrais'])){
		$db = new Database("DELETE FROM classe_frais WHERE ID = :id", 0, array("id"=>$_GET['idfrais']));
		if($db->delete()){
			afficher($db);
			unset($db);
		}else
			die($db->getLog('error'));
	}elseif(isset($_GET['idreduction']) && !empty($_GET['idreduction'])){
		$db = new Database("DELETE FROM classe_reduction WHERE ID = :id", 0, array("id" => $_GET['idreduction']));
		if($db->delete()){
			afficherReduction($db);
			unset($db);
		}else
			die($db->getLog('error'));
	}
}
/************************************************
*		Function qui afficher la liste des lignes
*************************************************/
function afficher($db){
	if(!isset($_GET['classe']) || empty($_GET['classe'])){
		print "<tr><td colspan = '5'><p class = 'infos'>Variables classe not set</p></td></tr>";
		return;
	}
	$query = "SELECT * FROM classe_frais WHERE IDCLASSE = :classe AND PERIODE = :periode AND TYPE = :type";
	$db->setQuery($query, array("classe"=>$_GET['classe'], "periode"=>$_SESSION['periode'], "type"=>$_GET['type']));
	/*
		Tableau de cours enseigner dans la classe
	*/
	if($db->select()){
		if($db->length){
			foreach($db->data as $row){
				print "<tr><td>".$row->item('CODE')."</td><td>".$row->item('LIBELLE')."</td><td>".$row->item('DATEDEBUT')."</td>";
				print "<td>".$row->item('DATEFIN')."</td><td>".$row->item('MONTANT')."</td>";
				print "<td><img style = 'cursor:pointer' src = '../images/supprimer.png' 
				onclick = \"supprimerFrais('".$row->item('ID')."', '".$_GET['type']."')\" /></td></tr>";
			}
		}else
             print "<tr><td colspan=\"6\" align=\"center\">AUCUN FRAIS ENREGISTRE...</td></tr>";
	}else
		die($db->getLog('error'));
}
/***************************************
*
*	Affichage des reduction apres appel d'ajax
*
*****************************************/
function afficherReduction($db){
	if(!isset($_GET['classe']) || empty($_GET['classe'])){
		print "<tr><td colspan = '5'><p class = 'infos'>Variables classe not set</p></td></tr>";
		return;
	}
	$param = array("classe" => $_GET['classe'], "periode" => $_SESSION['periode']);
	$db->setQuery("SELECT r.*, f.LIBELLE AS APPLIQUEA 
	FROM classe_reduction r 
	LEFT JOIN classe_frais f ON (r.IDFRAIS = f.ID) 
	WHERE r.IDFRAIS IN 
	(SELECT f2.ID FROM classe_frais f2 WHERE f2.IDCLASSE = :classe AND f2.PERIODE = :periode)", $param);
	if($db->select()){
		if($db->length){
			foreach($db->data as $row){
				print "<tr><td>".$row->item("CODE")."</td><td>".$row->item("LIBELLE")."</td><td>".$row->item("APPLIQUEA")."</td>";
				/* Si le type = Pourcentage, afficher % devant la valeur,
				*	Sinon, afficher juste le montant 
				*/
				$val = $row->item("MONTANT");
				if(!strcmp($row->item("TYPE"), "pourcentage"))
					$val = $row->item("MONTANT")."%";
				print "<td>".$val."</td><td><img style = 'cursor:pointer' src = '../images/supprimer.png' onclick = \"supprimerReduction('".$row->item('ID')."')\"/></td></tr>";
			}
		}else
			print "<tr><td colspan=\"5\" align=\"center\">AUCUNE REDUCTION ENREGISTREE...</td></tr>";
	}else
		die($db->getLog('error'));
}
/*************************************
*	Code executer pour reload le combobox de reduction
**************/
if(isset($_GET['reloadreduction'])){
	/*ajax etant asynchrone, s'assurer que l'insertion a d'abord eu lieu, donc attendre  2s*/
	sleep(2);
	$db = new Database("SELECT ID, LIBELLE FROM classe_frais ORDER BY ID", 0);
	if($db->select()){
		if($db->length){
			foreach($db->data as $row){
				print "<option value = '".$row->item("ID")."'>".$row->item("LIBELLE")."</option>";
			}
		}else
			print "<option value = ''>Aucun enregistrement...</option>";
	}else
		die($db->getLog('error'));
}
?>