<?php
/******************************************************************************************************/
	require_once("../includes/commun_inc.php");
/******************************************************************************************************/
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
$codepage = "DETAILS_CLASSE";
/********************************************************************************************************/
/*
*/
$titre = "D&eacute;tail de la classe";
require_once("../includes/header_inc.php");
/*
*/
if(isset($_GET['id']))
	details();
/**
	Fonction propre a la page
*/
function details(){?>
<div id="zonetravail"><div class="titre">DETAILS DE LA CLASSE.</div>
	<div class="cadre">
		<div class = "icon-pdf">
    			<a href = 'imprimerdetail.php?id=<?php echo $_GET['id']; ?>' target = '_blank'><img src = '../images/icon-pdf.gif'></a>
    		</div>
		<div id="TabbedDetails" class="TabbedPanels">
    			<ul class="TabbedPanelsTabGroup">
        			<li class="TabbedPanelsTab" tabindex="0">Infos. Classe</li>
            		<li class="TabbedPanelsTab" tabindex="1">Frais scolaires</li>
            		<li class="TabbedPanelsTab" tabindex="2">Enseignements</li>
        		</ul>
      		<div class="TabbedPanelsContentGroup">
        			<div class="TabbedPanelsContent"><?php showinfos(); ?></div>
            		<div class="TabbedPanelsContent"><?php showfrais(); ?></div>
            		<div class="TabbedPanelsContent"><?php showenseignement(); ?></div>
        		</div>
    		</div>
    </div>
    	<div class="navigation">
    		<input type="button" value="Modifier" onclick="rediriger('modifier.php?id=<?php echo $_GET['id']; ?>')" />
    	</div>
</div>
<?php
}
/**
	Afficher les infos generales de la classe
*/
function showinfos(){
	try{
		$pdo = Database::connect2db();
		$query = "SELECT c.IDCLASSE AS ID, c.LIBELLE, c.NIVEAU, cp.TAILLEMAX, cp.ACTIF, f.MONTANT AS MONTANTINSCRIPTION,
		 p.NOMPROF, p.PRENOM, p.ID, 
		(SELECT COUNT(MATEL) FROM inscription i WHERE c.IDCLASSE = i.IDCLASSE AND i.PERIODE = '".$_SESSION['periode']."') AS NBRE 
		FROM classe c 
		LEFT JOIN classe_parametre cp ON (cp.IDCLASSE = c.IDCLASSE AND cp.PERIODE = '".$_SESSION['periode']."') 
		LEFT JOIN classe_frais f ON (cp.MONTANTINSCRIPTION = f.ID) 
		LEFT JOIN professeur p ON (p.ID = cp.PROFPRINCIPAL) 
		WHERE c.IDCLASSE = :id";
		$res = $pdo->prepare($query);
		$res->bindValue("id", $_GET['id'], PDO::PARAM_STR);
		$res->execute();
		if(!$res->rowCount()){
			print "<p class = 'infos'>Aucune classe existante sous l'ID : $_GET[id]</p>";
			return;
		}
		$row = $res->fetch(PDO::FETCH_BOTH);
	}catch(PDOException $e){
		var_dump($e->getTrace());
		die($e->getMessage()." : ".$e->getFile()." : ".$e->getLine());
	}
?>
   	<div class="fiche">
        	<div class="titrefiche">CLASSE</div>
   		<div class="fichecontent">
            	<label>Identifiant : </label><?php  echo $row['ID']; ?>.<br />
            <label>Libell&eacute; : </label><?php  echo $row['LIBELLE']; ?>.<br />			
            <label>Niveau : </label><?php  echo $row['NIVEAU']; ?>.<br />
         </div>
         <div class="titrefiche">PARAMETRES</div>
         <div class="fichecontent">
         	<label>Professeur Principal : </label><?php  echo $row['NOMPROF']." ".$row['PRENOM']; ?>.<br />
            	<label>Taille maximale : </label><?php  echo $row['TAILLEMAX']; ?>.<br />
            <label>El&egrave;ves inscrits : </label><?php  echo $row['NBRE']; ?>.<br />
            <label>Actif : </label><?php echo ($row['ACTIF'] == 1) ? "OUI" : "NON"; ?>.<br />	
            <label>Montant inscription : </label><?php  echo $row['MONTANTINSCRIPTION']; ?>.<br />	
            <label>Pour les frais, consulter : </label><a href="../caisses/frais.php" >Frais scolaires</a>
         </div>
    </div>
<?php
}
/**
    Lister les tranches a payer
*/
function showfrais(){
	print "<div>";
    	$query = "SELECT f.CODE, f.LIBELLE, f.DATEDEBUT, f.DATEFIN, f.MONTANT 
    	FROM classe_frais f 
   	WHERE f.IDCLASSE = :id AND f.PERIODE = :periode";
    	$grid = new Grid($query);
    $grid->param = array("id" => $_GET['id'], "periode" => $_SESSION['periode']);
    $grid->addcolonne(0, "CODE", "CODE", true);
    $grid->addcolonne(1, "LIBELLE", "LIBELLE", true);
    $grid->addcolonne(2, "DATEDEBUT", "DEBUT", true);
    $grid->addcolonne(3, "DATEFIN", "FIN", true);
    $grid->addcolonne(4, "MONTANT", "MONTANT", true);
    $grid->setColDate(3);
    $grid->setColDate(2);
    $grid->display("98%", "350px");
    unset($grid);
    print "</div>";
}
/**
	Lister les enseignements de la classe
*/
function showenseignement(){
	print "<div>";
	$query = "SELECT e.IDENSEIGNEMENT,m.LIBELLE,CONCAT(p.NOMPROF,' ', p.PRENOM) AS PROF,e.COEFF,IF(e.ACTIF = 1, 'ACTIF','NON ACTIF') AS ETAT  
	FROM enseigner e 
	LEFT JOIN matiere m ON (e.CODEMAT = m.CODEMAT)    
	LEFT JOIN professeur p ON (e.PROF = p.ID) 
	WHERE e.PERIODE = :periode  AND e.IDCLASSE = :classe   
	ORDER BY e.CODEMAT";
	$grid = new grid($query, 0);
	$grid->param = array("periode" => $_SESSION['periode'], "classe" => $_GET['id']);
	$grid->addcolonne(0, 'ID', "IDENSEIGNEMENT", false);
	$grid->addcolonne(1, 'MATIERE', 'LIBELLE', true);
	$grid->addcolonne(3, 'PROFESSEUR', 'PROF', true);
	$grid->addcolonne(4, 'COEFF', 'COEFF', true);
	$grid->addcolonne(5, 'ETAT', 'ETAT', true);
	$grid->display("98%", "150px");
	unset($grid);
	print "</div>";
}
?>
<script type="text/javascript">
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedDetails");
</script>
<?php
	require_once("../includes/footer_inc.php");
?>