<?php 
/***************************************************************************************************/
require_once("../includes/commun_inc.php");
/*
	Verification de la duree de la sesssion
*/
if(!isset($_SESSION['user'])) header("location:../utilisateurs/connexion.php");
$titre = "Edition des frais";
/*
	Verification du droit d'acces a cette page
	empeche l'acces par saisie d'url
*/
	$codepage = "CAISSE_FRAIS";
/***************************************************************************************************/
require_once("../includes/header_inc.php");
if(isset($_POST['classe']))
	lister_frais();
else
	choisir();
/***************************************************************************************************
	Fonction relative de la page
	
/***************************************************************************************************/
function choisir(){?>
	<script>
		function choisir(){
			var obj = document.forms['frm'];
			if(obj.classe.value == "")
				alert("-Veuillez choisir une classe-");
			else
				obj.submit();
		}
	</script>
	<div id="zonetravail"><div class = 'titre'>EDITION DES FRAIS</div>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frm" enctype="multipart/form-data" onsubmit="choisir(); return false;" method="POST">
        	<div class="cadre"><fieldset style="padding:0"><legend>Classe</legend>
                <table style="margin:auto;"><tr><td class="lib">Choisir la classe</td>
                    <td><?php $combo = new Combo("SELECT * FROM classe ORDER BY NIVEAU", "classe", 0, 1, false);
                        $combo->first = 'Veuillez Choisir une classe';
                        $combo->view('150px');?></td>
                </tr></table>
            </fieldset></div>
        	<div class="navigation">
    			<input type="button" value="Annuler" onclick="rediriger('../accueil/index.php');"/>
        		<input type="submit" value="Valider">
    		</div>
        </form>
    </div>
<?php } 
/****************************************************
*
*	LISTE LES FRAIS SOUS FORME D'ONGLETS
*
*****************************************************/
function lister_frais(){?>
<script>
    function saveFrais(type){
		var frm = document.forms['frm'];
		code = document.getElementsByName("code".concat(String(type))).item(0).value;
		libelle = document.getElementsByName("libelle".concat(String(type))).item(0).value;
		montant = document.getElementsByName("montant".concat(String(type))).item(0).value;
		datedebut = document.getElementsByName("datedebut".concat(String(type))).item(0).value;
		datefin = document.getElementsByName("datefin".concat(String(type))).item(0).value;
		if(code == "" || libelle == "" || montant == ""){
			alert(decodeURIComponent("- Veuillez entrer tous les paramètres - "));
			return;
		}
		if(isNaN(montant) || parseInt(montant) < 0){
			alert("-Entrer un nombre positif dans la zone montant-");
			return;
		}
		var id = "code=" + code + "&libelle=" + libelle + "&montant=" + montant + "&classe=" + frm.classe.value;
		id += "&datedebut=" + datedebut + "&datefin=" + datefin + "&type="+ type;
		var url = "frais2.php?"+ id;
		loader = String("loader").concat(String(type));
		frais = (type == 0) ? "fraisOfficiels" : "fraisOccasionnels";
		callajax(url, frais, loader);
		/* pour mettre a jour le combobox de reduction */
		callajax("frais2.php?reloadreduction=true", "appliquerareduction", loader);
	}
	function supprimerFrais(id, type){
		if(window.confirm(decodeURIComponent("Vous êtes sur le point de supprimer un frais"))){
			var frm = document.forms['frm'];
			var url = "frais2.php?idfrais="+ id + "&supprimer=" + true + "&classe=" + frm.classe.value + "&type=" + type;
			frais = (type == 0) ? "fraisOfficiels" : "fraisOccasionnels";
			loader = String("loader").concat(String(type));
			callajax(url, frais, loader);
			callajax("frais2.php?reloadreduction=true", "appliquerareduction", loader);
		}
	}
</script>
<div id="zonetravail"><div class = 'titre'>EDITION DES FRAIS</div>
<form action="frais.php" method="POST" enctype="multipart/form-data" name="frm" >
	<div class="cadre">
    <?php
		$print = new printlink('imprimerfrais.php?id='.$_POST['classe'], false);
		$print->display();
	?>
    <div style="background-color: #EEE; margin-bottom:10px;">
    <?php
	/**
		Afficher quelques infos de la classe selectionner
	*/
	$query = "SELECT c.*, p.*, CONCAT(prof.NOMPROF,' ', prof.PRENOM) AS PROF 
				FROM classe c 
				LEFT JOIN classe_parametre p ON (p.IDCLASSE = c.IDCLASSE AND p.PERIODE = :periode) 
				LEFT JOIN professeur prof ON (prof.IDPROF = p.PROFPRINCIPAL) 
				WHERE c.IDCLASSE = :classe";
	$db = new Database($query, 0, array("classe"=>$_POST['classe'], "periode"=>$_SESSION['periode']));
	if($db->select()){
		$row = $db->getRow();
		print "<label style=\"font-weight:bold;\">IDCLASSE : </label>".$row->item('IDCLASSE')."<br/>";
		print "<label style=\"font-weight:bold;\">LIBELLE : </label>".$row->item('LIBELLE')."<br/>";
		print "<label style=\"font-weight:bold;\">CAPACITE Max. : </label>".$row->item('TAILLEMAX')."<br/>";
		print "<label style=\"font-weight:bold;\">PROF Princ.: </label>".$row->item('PROF')."<br/>";
	}else
		die($db->getLog('error'));
	unset($db);
	?>
    </div><!-- Fermeture de la fiche classe -->
        <div id="TabbedFrais" class="TabbedPanels">
            <ul class="TabbedPanelsTabGroup">
                <li class="TabbedPanelsTab" tabindex="0">Frais officiels</li>
                <li class="TabbedPanelsTab" tabindex="0">Frais occasionnels</li>
                <li class="TabbedPanelsTab" tabindex="0">R&eacute;ductions</li>
            </ul>
            <div class="TabbedPanelsContentGroup">
                <div class="TabbedPanelsContent"><?php frais(0); ?></div>
                <div class="TabbedPanelsContent"><?php frais(1); ?></div>
                <div class="TabbedPanelsContent"><?php reduction(); ?></div>
            </div>
     	 </div>
    </div>
    <div class="navigation">
    	<input type="hidden" value="<?php echo $_POST['classe']; ?>" name="classe" />
        <input type="button" value="Annuler" onclick="rediriger('../accueil/index.php');"/>
        <input type="button" onclick = "rediriger('../caisses/fichefrais.php?classe=<?php echo $_POST['classe'];?>');" value="Valider">
	</div>
  </form>
</div>
<?php } 
/******************************************
*
*	FUNCTION POUR LES FRAIS OFFICIELS
*
*******************************************/
function frais($type){?>
    <table class="grid" style="margin:auto;">
        <tr><th>Code</th><th>Libell&eacute;</th><th>Date d&eacute;but</th><th>Date fin</th><th>Montant</th><th>Save</th></tr>
        <tr><td><input style="width:50px;" type="text" name="code<?php echo $type; ?>" /></td>
            <td><input type="text" name="libelle<?php echo $type; ?>"  style="width:200px;" /></td>
            <td><input  style="width:100px;" type = "text" name = "datedebut<?php echo $type; ?>" id="datedebut<?php echo $type; ?>"/></td>
            <td><input  style="width:100px;" type = "text" name = "datefin<?php echo $type; ?>" id="datefin<?php echo $type; ?>" /></td>
            <td><input  style="width:100px;" type = 'text' name = 'montant<?php echo $type; ?>' /></td>
            <td><img src="../images/add.gif" title="Enr&eacute;gistrer..." onclick="saveFrais(<?php echo $type; ?>);" style="cursor:pointer;" />
                <img id="loader<?php echo $type; ?>" style="visibility:hidden" src="../images/loader.gif"/>
            </td>
        </tr>
    </table>
    <hr/>
<?php 
/*******************************************

	COLLECTE ET AFFICHAGE DES FRAIS OFFICIELS
	PRECENDENT UTILISANT LA CLASSE GRID
********************************************/
?>
	<div style="max-height:500px; overflow:auto;padding:2px;">
   	<table class="grid" width="100%">
        <thead>
            <tr><th>CODE</th><th>LIBELLE</th><th>DATE DEBUT</th><th>DATE FIN</th><th>MONTANT</th><th>ETAT</th></tr>
        </thead>
        <?php $zoneid = ($type == 0) ? "fraisOfficiels" : "fraisOccasionnels";
			print "<tbody id='".$zoneid."'>";
        	$param = array("classe"=>$_POST['classe'], "periode"=>$_SESSION['periode'], "type"=>$type);
            $db = new Database("SELECT * FROM classe_frais WHERE IDCLASSE = :classe AND PERIODE = :periode AND TYPE = :type", 0, $param);
			if($db->select()){
				if($db->length){
					foreach($db->data as $row){
						print "<tr><td>".$row->item("CODE")."</td><td>".$row->item("LIBELLE")."</td><td>".$row->item("DATEDEBUT")."</td>";
						print "<td>".$row->item("DATEFIN")."</td><td>".$row->item("MONTANT")."</td>";
						print "<td><img style = 'cursor:pointer' src = '../images/supprimer.png' 
						onclick = \"supprimerFrais('".$row->item('ID')."', '".$type."')\"/></td></tr>";
					}
                }else
                    print "<tr><td colspan=\"6\" align=\"center\">AUCUN FRAIS ENREGISTRE...</td></tr>";
			}else
				die($db->getLog('error'));
			print "</tbody>";
            ?>
        </table>
       </div>
 <?php
}
/*******************************************
*
*	Function qui gere l'insertion des reduction
*	Les suppression sont envoyer par ajax au 
*	fichier frais2.php
*
********************************************/
function reduction(){?>
	<script>
		function saveReduction(){
			var frm = document.forms['frm'];
			code = frm.codereduction.value;
			libelle = frm.libellereduction.value;
			montant = frm.montantreduction.value;
			type = frm.typereduction.value;
			appliquera = frm.appliquerareduction.value;
			if(code == "" || libelle == "" || montant == ""){
				alert(decodeURIComponent("- Veuillez entrer tous les paramètres - "));
				return;
			}
			if(isNaN(montant) || parseInt(montant) < 0){
				alert("-Entrer un nombre positif dans la zone montant-");
				return;
			}
			var id = "codereduction=" + code + "&libellereduction=" + libelle + "&montantreduction=" + montant + "&classe=" + frm.classe.value;
			id += "&typereduction="+ type + "&appliquera=" + appliquera;
			var url = "frais2.php?"+ id;
			callajax(url, "zonereduction", "loaderreduction");
		}
		function supprimerReduction(id){
			var id = "supprimer=" + true + "&idreduction=" + id + "&classe=" + document.forms['frm'].classe.value;
			callajax("frais2.php?" + id, "zonereduction", "loaderreduction");
		}
	</script>
	<table class="grid" style="margin:auto;">
        <tr><th>Code</th><th>Libell&eacute;</th><th>Type</th><th>Appliqu&eacute; &agrave;</th><th>Montant</th><th>Save</th></tr>
        <tr><td><input style="width:50px;" type="text" name="codereduction" /></td>
            <td><input type="text" name="libellereduction" style="width:200px;" /></td>
            <td>
				<select name = "typereduction" style = "width:100px;">
					<option value = "pourcentage">En pourcentage</option>
					<option value = "valeur">En valeur</option>
				</select>
			</td>
            <td>
				<?php
					$reducombo = new Combo("SELECT ID, LIBELLE FROM classe_frais ORDER BY ID", "appliquerareduction", 0, 1);
					$reducombo->idname = "appliquerareduction";
					$reducombo->view("200px");
				?>
			</td>
            <td><input  style="width:100px;" type = 'text' name = 'montantreduction' /></td>
            <td><img src="../images/add.gif" title="Enr&eacute;gistrer..." onclick="saveReduction();" style="cursor:pointer;" />
                <img id="loaderreduction" style="visibility:hidden" src="../images/loader.gif"/>
            </td>
        </tr>
    </table>
    <hr/>
<?php
/*******************************************

	COLLECTE ET AFFICHAGE DES REDUCTIONS
	PRECENDENT UTILISANT LA CLASSE GRID
********************************************/
?>
	<div style="max-height:500px; overflow:auto;padding:2px;">
   	<table class="grid" width="100%">
        <thead>
            <tr><th>CODE</th><th>LIBELLE</th><th>APPLIQUE A</th><th>MONTANT</th><th>ETAT</th></tr>
        </thead>
        <?php
		print "<tbody id='zonereduction'>";
		$param = array("classe" => $_POST['classe'], "periode" => $_SESSION['periode']);
		$db = new Database("SELECT r.*, f.LIBELLE AS APPLIQUEA 
		FROM classe_reduction r 
		LEFT JOIN classe_frais f ON (r.IDFRAIS = f.ID) 
		WHERE r.IDFRAIS IN 
		(SELECT f2.ID FROM classe_frais f2 WHERE f2.IDCLASSE = :classe AND f2.PERIODE = :periode)", 0, $param);
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
				print "<tr><td colspan=\"6\" align=\"center\">AUCUNE REDUCTION ENREGISTREE...</td></tr>";
		}else
			die($db->getLog('error'));
		print "</tbody>";
        ?>
    </table>
    </div>
<?php
}
/********************************************
*
*	Fin de la function reduction
*
********************************************/
?>
<script type="text/javascript">
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedFrais");
</script>
<?php
	require_once("../includes/footer_inc.php");
?>