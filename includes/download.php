<?php
	//T�l�chargement d�un document identifi� par $_GET['iddownload']
	// On envoie un en-t�te for�ant le transfert (download)
	header("Content-type:application/force-download");
	header("Content-Disposition:attachment;filename=".basename($_GET['url']));
	// Apr�s l�en-t�te on transmet le contenu du fichier lui-m�me
	readfile($_GET['url']);
?>