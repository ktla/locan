<?php
require_once("../includes/commun_inc.php");
if(isset($_GET['id'])){
	$query = "UPDATE eleve SET ACTIF = IF(ACTIF = 1, '0', '1') WHERE MATEL = \"".parse($_GET['id'])."\"";
	if(mysql_query($query)){
		/*$q = "UPDATE users SET ACTIF = (SELECT ACTIF FROM eleve WHERE MATEL = \"".parse($_GET['id'])."\") WHERE LOGIN = \"".parse($_GET['id'])."\"";
		mysql_query($q) or die(mysql_error());*/
		header("location:eleve.php");
	}else
		die(mysql_error());
}
?>