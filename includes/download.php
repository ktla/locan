<?php
	//Téléchargement d’un document identifié par $_GET['iddownload']
	// On envoie un en-tête forçant le transfert (download)
	header("Content-type:application/force-download");
	header("Content-Disposition:attachment;filename=".basename($_GET['url']));
	// Après l’en-tête on transmet le contenu du fichier lui-même
	readfile($_GET['url']);
?>