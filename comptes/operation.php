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
if(!isset($_SESSION['user']))	@header("location:../utilisateurs/connexion.php");
/*
	Verification du droit d'acces de cette page
	Verifier que le codepage existe dans nos listedroit, ceci
	empeche de proceder par saisie de l'url et d'acceder a la page
*/
$codepage = "OPERATION_COMPTE";
$titre = "Gestion des op&eacute;rations";
require_once("../includes/header_inc.php");
require_once("./compte_inc.php");
/**
*/
	if((isset($_POST['operation']) && !empty($_POST['operation']))||(isset($_GET['operation']) && !empty($_GET['operation'])))
		valider();
	else
		operation();
	/*
		Intergration du modele de page
	*/
	require_once("../includes/footer_inc.php");
/*
	Fonction propre  a la   page
*/
function operation(){
	/*$query = "SELECT CONCAT(t.IDTYPE,'@',c.IDCOMPTE) AS ID, CONCAT(c.MATEL,'-',e.NOMEL,' ',e.PRENOM) AS PROPRIETAIRE 
		FROM compte_eleve c 
		LEFT JOIN eleve e ON (e.MATEL = c.MATEL) 
		LEFT JOIN compte_type t ON (t.IDTYPE = c.TYPE) 
		UNION 
		SELECT CONCAT(t.IDTYPE,'@',c.IDCOMPTE) AS ID, 
		CONCAT(c.IDPROF,'-',p.NOMPROF,' ',p.PRENOM) AS PROPRIETAIRE  
		FROM compte_professeur c 
		LEFT JOIN professeur p ON (p.IDPROF = c.IDPROF) 
		LEFT JOIN compte_type t ON (t.IDTYPE = c.TYPE) 
		UNION 
		SELECT CONCAT(t.IDTYPE,'@',c.IDCOMPTE) AS ID, CONCAT(c.IDSTAFF,'-',s.NOM,' ',s.PRENOM) AS PROPRIETAIRE 
		FROM compte_staff c 
		LEFT JOIN staff s ON (s.IDSTAFF = c.IDSTAFF) 
		LEFT JOIN compte_type t ON (t.IDTYPE = c.TYPE) 
		ORDER BY 1";*/
?>
	<script>
		function choixtype(){
			if(document.forms['frm'].type.value == "")
				alert("-Choisir un type-");
			else
				document.forms['frm'].submit();
		}
		function loadConcerner(){
			var frm = document.forms['frm'];
			if(frm.comptetype.value != ""){
				var url = "operation2.php?comptetype=" + frm.comptetype.value;
				callajax(url, "concerner", "loader");
			}else{
				html = "<select name='concerner'><option value=''>--Choisir le concern&eacute;--</option></select>";
				document.getElementById("concerner").innerHTML = html;
			}
		}
	</script>
    <div id="zonetravail"><div class="titre">EFFECTUER UNE OPERATION</div>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="choixtype(); return false;" name="frm" method="post" enctype="multipart/form-data">
        <div class="cadre">
            <fieldset><legend>Choix d' un compte courant</legend>
                <table cellspacing="5"><tr>
                    <td class="lib">Choisir un compte: </td>
                    <td><?php
						/** Compte des eleves inscrit a cette periode a cette periode */
						$query = "SELECT c.* 
						FROM compte c 
						INNER JOIN inscription i ON (i.MATEL = c.CORRESPONDANT AND i.PERIODE = :periode)";
						$combo = new Combo($query, "operation", 0, 0, false);
						$combo->param = array('periode' => $_SESSION['periode']);
						$combo->first = '-Choisir un compte-';
						$combo->onchange = "";
						$combo->view('300px');
					?></td>
                 </tr>
                </table>
            </fieldset>
        </div>
        <div class="navigation">
            <input type="button" onClick="home();" value="Annuler"/>
           <input type="submit" value="Valider"/>
        </div>
    </form>
    </div>
<?php
}
/**
	Validation de donnees dans la BD
*/
function valider(){
?>
<div id="zonetravail">
	<div class="titre">
		<span>N&deg; De Compte associ&eacute; : </span>
		<span style="position:relative;color:#000;"><?php echo isset($_POST['operation'])?$_POST['operation']:$_GET['operation']; ?></span>
		<!-- Recuperation des informations relatives au compte courant -->
		<?php 
			$periode = $_SESSION["periode"];
			$compte = isset($_POST['operation'])?$_POST['operation']:$_GET['operation'];
			$con = Database::connect2db();
			$query =  $con->query("SELECT * FROM compte WHERE IDCOMPTE = '$compte'");
			$result = $query->fetch(PDO::FETCH_BOTH);
		?>
		<span style="position:relative; float:right;"><em>Cr&eacute;e le </em><font color="#000">
		<?php print $result["DATECREATION"]; ?></font> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; par : <em style="color:#000">
		<?php print $result["AUTEUR"]; ?></em></span>
	</div>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" onSubmit="" name="frm" method="post" enctype="multipart/form-data">
	<div class="cadre">
	<table>
		<tr><td rowspan="3">
		<fieldset style="position:relative;text-align:left; border:none; border-top:solid 1px black;">
			<legend style="color:#444; font-weight:bold; text-transform:capitalize; border:1px solid #EEE;">
				Informations Compte <?php $tab = tableConcerned($result["CORRESPONDANT"]);
				//print_r($tab);
				print $tab["correspondant"]; ?>
            </legend> 
		  <?php afficheConcerned($result["CORRESPONDANT"],isset($_GET['operation'])?isset($_GET["classe"])?$_GET["classe"]:"":"");
		 	
		    ?>    
		 </fieldset>
		 </td>
		 <td valign="top">
		 <fieldset style="position:relative;border:none; border-top:solid 1px black;">
			<legend style="color:#444; font-weight:bold; border:1px solid #EEE;">Solde Du Compte</legend>
			<table>
				<?php
				$table = afficheSolde($compte);
				print "<tr>    
					<td class=\"lib\">Solde :</td>
					<td style=\"background-color:#FF8D1C;\" width=\"100\" align=\"right\"><strong id=\"solde\">".$table["solde"]."</strong>
					<strong>&nbsp;Fcfa&nbsp;&nbsp;</strong></td> 
					<td><strong id=\"inc\">".$table["inc"]."</strong></td>
				</tr>
				<tr>
					<td colspan=\"2\" align=\"center\"><font id = \"message_solde\">".$table["message"]."</font></td>
				</tr> ";
				?>
			</table>
		 </fieldset>
		 </td></tr>
		 <tr></tr>
		 <tr></tr>
		 <tr>
		 <td colspan="2" valign="top">
		 <a style="position:relative;top:0.7em;" >Imprimer</a>
		 <fieldset style="border:none; border-top:solid 1px black; max-height:350px; overflow-y:auto;overflow-x:hidden;">
			<legend style="position:relative;margin-left:42%;color:#444; font-weight:bold;border:1px solid #EEE;">Journal Du Compte</legend>
		   <div id="journal"> <?php afficheJournal($compte); ?> </div>
		 </fieldset>
		 </td></tr>
		 <tr>
		 <td id="info_payement"><?php afficheInfoPayement($result["CORRESPONDANT"],$compte,isset($_GET['operation'])?isset($_GET["classe"])?$_GET["classe"]:"":""); ?></td>
		 <td valign="top">
			<fieldset style="border:none; border-top:solid 1px black;">
			<legend style="margin-left:36%;color:#444; font-weight:bold;border:1px solid #EEE;">Operation Courante</legend>	
			<table><tr>
			<td>	
				<label for="debit" class="lib" style="cursor:pointer;" 
				onclick="javascript: document.getElementById('debit').disabled = false;document.getElementById('credit').disabled = true;
				 document.getElementById('credit').value=''; ">D&eacute;bit</label>
			</td>
			<td>
				<input type="text" id="debit" name="debit" class="montant" disabled="disabled" />
			</td>
			<td rowspan="3" id="chargement" align="left"></td>
			</tr>
			<tr>
			<td>
				<label for="credit" class="lib" style="cursor:pointer;" 
				onclick="javascript: document.getElementById('credit').disabled = false;document.getElementById('debit').disabled = true;
				document.getElementById('debit').value='';">Cr&eacute;dit</label>
			</td>
			<td>
			<input type="text" id="credit" name="credit" class="montant"  />
			</td>
			</tr>
			<tr>
			<td class="lib">Libelle</td>
			<td><input type="text" id="libelle" name="libelle" /></td>
			</tr>
			</table>
			</fieldset>
		 </td>
		 </tr>
	</table>
	</div>
	<input type="hidden" name="operation" value="<?php print $compte; ?>" />
    <input type="hidden" id="montant" name="montant" value="" />
	<div class="navigation">	  
	  <?php
	  if(isset($_GET["operation"]) && isset($_GET["classe"]) ){
	  	print "<input type=\"button\" onClick=\"document.location = '../inscriptions/nouvelle.php'\" value=\"Pr&eacute;c&eacute;dent\"/>";
		print "<input type=\"hidden\" name=\"appel\" value=\"1\">";
	  }else
	  	print "<input type=\"button\" onClick=\"document.location = '../comptes/operation.php'\" value=\"Pr&eacute;c&eacute;dent\"/>";
	  $date = date("Y-m-j");
	  print "<input type=\"hidden\" name=\"date\" value=\"$date\" />";
	 // print "<input type=\"button\" value=\"Valider\" onclick=\"operation('$compte','$result[CORRESPONDANT]','$date');\"/>";
	  print "<input type=\"button\" value=\"Valider\" onclick=\"validation();\"/>";
	   ?>
	</div>
	</form>
</div>

<?php 
}
?>