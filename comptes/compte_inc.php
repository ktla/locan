<?php
/*
	Cette fonction renvoi un tableau associatif contenant information sur les tables eleve,
	professeur,et staff en fonction du ID passer en parametre
*/
function tableConcerned($correspondant){ // cette fonction founit la table du correspondant
	$con = Database::connect2db();
	$query1 =  $con->query("SELECT * FROM eleve WHERE MATEL = '$correspondant'");
	$query2 =  $con->query("SELECT * FROM professeur WHERE IDPROF = '$correspondant'");
	$query3 =  $con->query("SELECT * FROM staff WHERE IDSTAFF = '$correspondant'");
	$table = array(); 
	if($query1->rowCount() > 0 && $query2->rowCount() <= 0 && $query3->rowCount() <= 0){
		$table["correspondant"] = "eleve";
		$table["details"] = $query1->fetch();
	}else
		if($query1->rowCount() <= 0 && $query2->rowCount() > 0 && $query3->rowCount() <= 0){
			$table["correspondant"] = "professeur";
			$table["details"] = $query2->fetch();
		}else
			if($query1->rowCount() <= 0 && $query2->rowCount() <= 0 && $query3->rowCount() > 0){
				$table["correspondant"] = "staff";
				$table["details"] = $query3->fetch();
			}
			
	return $table;
}

/*
	Cette fonction renvoi une ligne dans une table en altenant celle ci en fonction que
	son index soit paire ou impair 

*/
function alterneLigne($index){
	return (($index % 2) != 0)?"<tr class=\"journal-line1\">":"<tr class=\"journal-line2\">";	
}

/*
	Cette fonction renvoi une cellule de table en fonction du montant (debit ou credit)
*/
function debitCredit($montant){
	$str = "";
	if($montant > 0){
		$str = "<td class=\"journal-content\"></td><td class=\"journal-content\" align=\"justify\"><strong>";
		return $str."<font color=\"blue\">".abs($montant)."</font></strong></td>";
	}else{
		$str = "<td class=\"journal-content\" align=\"justify\"><strong>";
		return $str."<font color=\"blue\">".abs($montant)."</font></strong></td><td class=\"journal-content\"></td>";
	}
}

/*
	Cette fonction permet l' affichage du journal par rapport au compte passe en parametre
*/

function afficheJournal($compte){
	$con = Database::connect2db();
	$periode = $_SESSION["periode"];
	$query =  $con->query("SELECT * FROM operation WHERE PERIODE = '$periode' AND IDCOMPTE = '$compte' ORDER BY DATE ASC ");
	if($query->rowCount() > 0){
		print "<table cellspacing =\"2px\" bgcolor=\"#DDD\" id=\"journal\">";
		print "<tr bgcolor=\"#ECFFF1\">
		<td width=\"340\" class=\"journal_ent\">LIBELLE</td>
		<td width=\"100\" class=\"journal_ent\">DATE</td>
		<td width=\"100\" class=\"journal_ent\">AUTEUR</td>
		<td width=\"100\" class=\"journal_ent\">DEBIT</td>
		<td width=\"100\" class=\"journal_ent\">CREDIT</td>
			  </tr>";		
		$result = $query->fetchALL(PDO::FETCH_ASSOC);
		$i = 1;
		foreach($result as $row){
			print alterneLigne($i);
			$date = new dateFR($row["DATE"]);
			print "<td class=\"journal-content\">".$row["LIBELLE"]."</td>
				   <td class=\"journal-content\" align=\"left\">".$date->getDateMessage(3)."</td>
				   <td class=\"journal-content\"><em>".$row["AUTEUR"]."</em></td>";
			print debitCredit($row['ACTION']); 
			print "</tr>";
			$i++;	
		}
		$sql = "
				SELECT SUM( IF( p1.ACTION >0, p1.ACTION, 0 ) ) AS credit, SUM( IF( p2.ACTION <0, p2.ACTION, 0 ) ) AS debit
				FROM operation p1
				JOIN operation p2 ON p1.IDOPERATION = p2.IDOPERATION
				WHERE p1.PERIODE = '$periode' 
				AND p2.PERIODE = '$periode'
				AND p1.IDCOMPTE = '$compte'
				AND p2.IDCOMPTE = '$compte'";
				
				$query =  $con->query($sql);
				$result = $query->fetchAll();
					print "<tr bgcolor=\"#FFFFB5\">
							<td colspan=\"3\" align=\"center\"><strong><font color=\"red\">TOTAL</font></strong></td>
							<td class=\"journal-content\"><strong><font color=\"red\">".abs($result[0]["debit"])."</font></strong></td>
							<td class=\"journal-content\"><strong><font color=\"red\">".$result[0]["credit"]."</font></strong></td>
						   </tr>";
					print "</table>";
						
	}else{
		print "<p class = 'infos'>Aucun Enregistrement</p>";
	}
}
                
				
/*
	Cette fonction renvoi un tableau associatif  renfermant les informations sur le 
	solde du compte courant
*/
function afficheSolde($compte){
	$tab =  array();
	$con = Database::connect2db();
	$periode = $_SESSION["periode"];
	$query =  $con->query("SELECT SUM(ACTION) AS solde FROM operation WHERE PERIODE = '$periode' AND IDCOMPTE = '$compte' ");		
	if($query->rowCount() > 0){
		$result = $query->fetch();
		$tab["solde"] = abs($result["solde"]);
		if($result["solde"] > 0 || $result["solde"] == 0 ){
			$tab["inc"] = "CR";
			$tab["message"]	= "Compte Crediteur";		
		}else{
				$tab["inc"] = "";
				$tab["message"]	= "Compte Debiteur";	
			 }
	}
	
	return $tab;
}

				
/*
CETTE FONCTION RENVOI LE NET A PAYER PAR ELEVE
	
*/
function Net($frais, $compte){
	$periode = $_SESSION["periode"];
	$con = Database::connect2db();
	$sql = "SELECT SUM( IF( p1.ACTION > 0, p1.ACTION, 0 ) ) AS credit
			FROM operation p1
			WHERE p1.IDCOMPTE = '$compte' 
			AND p1.PERIODE = '$periode'";
	$query = $con->query($sql);
	$result = $query->fetchAll();
		return ($frais - $result[0]['credit']); 

}

/*
	Importation de la classe Eleve
*/
	require_once("../eleves/eleve_inc.php");

/*

	Cette fonction permet l' affichage des informations du payement sur le correspondant : eleve , professeur , staff
*/

function afficheInfoPayement($correspondant,$compte,$classe = ""){
	$table = tableConcerned($correspondant);
	if (!strcmp($table["correspondant"],"eleve")){
		$result = $table["details"];
	/*	
		$con = Database::connect2db();
		$periode = $_SESSION["periode"];
		$query =  $con->query("SELECT i.* FROM eleve e 
		JOIN inscription i ON e.MATEL = i.MATEL 
		JOIN annee_academique a ON i.PERIODE = a.ANNEEACADEMIQUE 
		WHERE e.MATEL = '".$result['MATEL']."' AND i.PERIODE = '$periode' ");
	*/
	$eleve = new Eleve($result["MATEL"],true,$classe);
	print "<fieldset style=\"position:relative; border:none; border-top:solid 1px black;text-align:left;\">";
	print "<legend style=\"color:#444; font-weight:bold; text-transform:capitalize; border:1px solid #EEE;\">Information Payement Pour Cette Classe</legend>";
	if($eleve->isInscrit){
			if($eleve->getFraisAPayer())
			print "<table cellspacing =\"2px\" bgcolor=\"#DDD\">
					<tr bgcolor=\"#ECFFF1\">
					<td width=\"160\" class=\"journal_ent\">Libelle</td>
					<td width=\"80\" class=\"journal_ent\">Date Debut</td>
					<td width=\"80\" class=\"journal_ent\">Date Fin</td>
					<td width=\"80\" class=\"journal_ent\">Montant</td></tr>";
			for($i = 0; $i < count($eleve->fraisAPayer); $i++){
				$date1 =date("j-m-Y",strtotime($eleve->fraisAPayer[$i]->datedebut));
				$date2 =date("j-m-Y",strtotime($eleve->fraisAPayer[$i]->datefin));
				print alterneLigne($i);
				print "<td style=\"padding-left:1%;\">".$eleve->fraisAPayer[$i]->libelle."</td>
					   <td style=\"padding-left:1.3%;\">$date1</td>
					   <td style=\"padding-left:1.3%;\">$date2</td>
					   <td style=\"padding-left:1.3%; color:blue; font-weight:bold;\">".$eleve->fraisAPayer[$i]->montant."</td>";
				print "</tr>";
			}
	
			print "<tr bgcolor=\"#FFFFB5\">
					<td colspan=\"3\" style=\"text-align:center;color:red;font-weight:bold;\">TOTAL FRAIS</td>
					<td style=\"padding-left:1.3%;color:red;font-weight:bold;\">".$eleve->getTotalFraisAPayer()."</td>
				   </tr>";
		
			if($eleve->getReductionObtenue()){	
				for($i = 0; $i < count($eleve->reductionObtenue); $i++){
				print alterneLigne($i);
				print "<td style=\"padding-left:1%;\">".$eleve->reductionObtenue[$i]->libelle."</td>
					   <td align=\"center\" style=\"padding-left:1.3%;\">--</td>
					   <td align=\"center\" style=\"padding-left:1.3%;\">--</td>
					   <td style=\"padding-left:1.3%; color:blue; font-weight:bold;\">".$eleve->reductionObtenue[$i]->getMontant()."</td>";
				print "</tr>";
			}
					
				print "<tr bgcolor=\"#FFFFB5\">
						<td colspan=\"3\" style=\"text-align:center;color:red;font-weight:bold;\">TOTAL REDUCTION</td>
						<td style=\"padding-left:1.3%;color:red;font-weight:bold;\">".$eleve->getTotalReductionObtenue()."</td>
					   </tr>";
			}
			
			print "<tr bgcolor=\"#FFFFB5\">
					<td colspan=\"3\" style=\"text-align:center;color:red;font-weight:bold;\">TOTAL A PAYER</td>
					<td style=\"padding-left:1.3%;color:red;font-weight:bold;\">";
			$fraistotal = $eleve->getTotalFraisAPayer();
			if($eleve->getReductionObtenue())
				$fraistotal = $fraistotal - $eleve->getTotalReductionObtenue();
				
			print $fraistotal."</td>
				   </tr>";
			$net = Net($fraistotal,$compte); 
			print "<tr>
					<td colspan=\"3\" style=\"text-align:center;font-weight:bold;\" >RESTE A PAYER</td>
					<td id=\"reste_payement\" style=\"padding-left:1.3%;font-weight:bold; text-decoration:overline;\">$net</td>
				   </tr>";
			if($net < 0 || $net == 0){
				print "<tr bgcolor=\"#EAEAEA\"><td colspan=\"4\" style=\"text-align:center;color:blue;\" >";
				print "Le Solde Annuel De Votre Compte A Un Credit De : <strong>".abs($net)."</strong> Fcfa</td><tr/>";
			}
			print "</table>";
		}else
			echo "<p class = 'infos'>REMPLIR LES INFORMATIONS DE PAYEMENT POUR LA CLASSE ".$eleve->classe->libelle."</p>";
	}else{
			print "<p class = 'infos'>AUCUNE INFORMATION POUR CETTE CLASSE Remplir Les Droits De Scolarisation Pour Cette Classe Ref : table Classe_frais</p>";	
	}
	print "</fieldset>";
}


/*
	Cette fonction permet l' affichage des informations sur le correspondant : eleve , professeur , staff
*/
function afficheConcerned($correspondant,$classe = ""){
	$table = tableConcerned($correspondant);
	if (!strcmp($table["correspondant"], "eleve")){
		$result = $table["details"];
		$el = new Eleve($result['MATEL'],true,$classe);?>
        <table cellpadding="1" cellspacing="0" ><tr>
            <td width="15%">
                <?php echo getImage($el->image, "", "150", "100"); ?>
            </td>
            <td width="45%" valign="top">
                <label style="font-weight:bold">Matricule : </label><?php echo $result['MATEL']; ?><br/>
                <label style="font-weight:bold">Nom : </label><?php echo $el->nom ?><br/>
                <label style="font-weight:bold">Pr&eacute;nom : </label><?php echo $el->prenom; ?><br/>
                <label style="font-weight:bold">Date Naiss. : </label><?php echo $el->datenaiss; ?><br/>
                <label style="font-weight:bold">Classe : </label><?php  echo $el->classe->libelle; ?><br/>
                <label style="font-weight:bold">Redoublant : </label><?php echo $el->redouble(); ?><br/>
            </td>
        </tr>
        <tr id="choix_classe" style="display:none;"></tr>
		</table>
        <?php 
	}else
		print "<p class = 'infos'>Gestion du compte professeur non encore impl&eacute;ment&eacute;e</p>";
}
?>
<script type="text/javascript">

function debitCredit(){
	var montant = 0;
	tableau = {
			  "debit" : document.getElementById("debit").value,
			  "credit" : document.getElementById("credit").value
			  };

	for(var cle in tableau){
		if(tableau[cle] != "" && cle == "credit")
			montant = tableau[cle];
		if(tableau[cle] != "" && cle == "debit")
			montant = -tableau[cle];
	}
		//return montant;
		document.getElementById("montant").value = montant;
}



function validation(periode,auteur){
	debitCredit();
	montant = document.getElementById("montant").value;
	if(!isNaN(montant)){
		if(montant != 0){
			libelle = document.getElementById("libelle").value;
			reg = /[a-z,0-9]/gi;
			test = reg.test(libelle);
			if(test){
				document.forms['frm'].action ="./operation2.php";
				document.forms['frm'].submit();
			}else
				alert("REMPLIR LE CHAMP LIBELLE DE L' ONGLET OPERATION COURANTE");	
		}else
			alert("PAS D' OPERATION AVEC 0 COMME VALEUR");
	}else
		alert("LES CHAMPS DE L' ONGLET OPERATION COURANTE DOIVENT ETRE LES NOMBRES !!!");	
}


function classe_load(){
	if(document.getElementsByName("idclasse")[0].value != "")
		document.getElementById("choix_classe").style.display = "table-row";
	else
		document.getElementById("choix_classe").style.display = "none";
}


function operation2(req){
	if(req.readyState == 4){
		if(req.status == 200){
			taille = req.responseXML.getElementsByTagName("ETAT").length;
			for (i=0;i<taille;i++){
					if(req.responseXML.getElementsByTagName("ETAT")[i].hasAttribute("id")){
						at = req.responseXML.getElementsByTagName("ETAT")[i].getAttribute("id");
						if(document.getElementById(at) != null)
							document.getElementById(at).innerHTML = req.responseXML.getElementsByTagName("ETAT")[i].innerHTML;
					}else{
						  len = req.responseXML.getElementsByTagName("CHAMP").length;
						  for(j=0;j<len;j++){
						   	if(req.responseXML.getElementsByTagName("CHAMP")[j].hasAttribute("id")){
								at = req.responseXML.getElementsByTagName("CHAMP")[j].getAttribute("id");
								document.getElementById(at).innerHTML = req.responseXML.getElementsByTagName("CHAMP")[j].innerHTML;
							}
								
						  }
						     					
							
						 }
						
			} 			
			document.getElementById("debit").value = "";
			document.getElementById("credit").value = "";
			document.getElementById("libelle").value = "";
			document.getElementById("choix_classe").style.display = "none";
			document.getElementById("chargement").innerHTML = "";			
		}else{
			alert('Erreur server '+req.status+'-----'+req.statusText);
		}
	}
}

function redouble(){
	if(document.getElementById("non").checked)
		return document.getElementById("non").value;
	else
		return document.getElementById("oui").value;
}

function operation(compte,correspondant,date){
	montant = debitCredit();
	if(!isNaN(montant)){
		if(montant != 0){
			libelle = document.getElementById("libelle").value;
			reg = /[a-z,0-9]/gi;
			test = reg.test(libelle);
			if(test){
				if(document.getElementsByName("idclasse")[0] == null){
					parametre = "compte="+compte+"&correspondant="+correspondant+
						 	"&date="+date+"&libelle="+libelle+"&montant="+montant+
							"&class="+document.getElementById("classe").innerHTML;
				}else
					if(document.getElementsByName("idclasse")[0].value != "")
						parametre = "compte="+compte+"&correspondant="+correspondant+
								"&date="+date+"&libelle="+libelle+
								"&classe="+document.getElementsByName("idclasse")[0].value+
								"&montant="+montant+"&redouble="+redouble();
					else{
							alert("CHOISIR UNE CLASSE POUR EFFECTUER L'OPERATION ");
							return;
						}
					var xhr = getXMLHttpRequest(); 
					xhr.open("post","./operation2.php",true);
					document.getElementById("chargement").innerHTML = '<img src ="../images/loader.gif" />'; 
					xhr.onreadystatechange = function(){ operation2(xhr); };
					xhr.overrideMimeType('text/xml');
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					xhr.send(parametre);
			}else
				alert("REMPLIR LE CHAMP LIBELLE DE L' ONGLET OPERATION COURANTE");	
		}else
			alert("PAS D' OPERATION AVEC 0 COMME VALEUR");
	}else
		alert("LES CHAMPS DE L' ONGLET OPERATION COURANTE DOIVENT ETRE LES NOMBRES !!!");
}
</script>