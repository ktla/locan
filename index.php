<?php
if(!file_exists('./configurations/config.xml'))
	@header('Location: configurations/index.php');
else
	@header("location:accueil/index.php");
?>