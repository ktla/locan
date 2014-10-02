<?php 
require_once("../includes/commun_inc.php");
if(isset($_GET['action'])){
	switch($_GET['action']){
		case "reglement" : reglement(); break;
		case "logo" : logo(); break;
	}
}
function reglement(){
	$etab = new etablissement();
	if(!empty($etab->reglement)){
		if(file_exists($etab->reglement))
			unlink($etab->reglement);
		else{
			print "<script>alert(decodeURIComponent('Fichier du réglement inexistant'));</script>";
			//return;
		}
		mysql_query("UPDATE etablissement SET REGLEMENT = ''") or die(mysql_error());
		print "<input type = 'file' name = 'reglement' />";
	}
}
function logo(){
	$etab = new etablissement();
	if(!empty($etab->logo)){
		if(file_exists($etab->logo))
			unlink($etab->logo);
		else{
			print "<script>alert(decodeURIComponent('Fichier image logo inexistant'));</script>";
			//return;
		}
		mysql_query("UPDATE etablissement SET LOGO = ''") or die(mysql_error());
		print "<input type = 'file' name = 'image' accept=\"image/*\" />";
	}
}
?>