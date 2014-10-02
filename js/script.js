// JavaScript Document
function printError(id){
	var obj = document.getElementById("erreur");
	id = parseInt(id);
	obj.style.visibility = "visible";
	var sms = "";
	switch(id){
		case 0 : sms = "Mot de passe Incorrect\n" ;break;
		case -1 : sms = "Utilisateur du systeme inconnu\n"; break;
		case 2 : sms = "Veuillez entrer au moins un parametres\n"; break;
		case 3 : sms = "Veuillez entrer Tous les parametres  \n"; break;
		case 4 : sms = "Veuillez entrer Tous les champs  obligatoires.\n"; break;
		case 5 : sms = "Ce champ est obligatoire.\n"; break;
		default : sms = "Code d'erreur Inconnu\n";
	}
	obj.firstChild.nodeValue = sms;
}
//restaure l'URL initiale d'une page en supprimant la chaine de requete de l'URL actuelle.
function initurl(){
	var completeurl = document.location.href;
	//alert(document.location.href);
	var goodurl = 	completeurl;

	if (completeurl.substring(0,completeurl.indexOf('?')) != ''){
		goodurl = completeurl.substring(0,completeurl.indexOf('?'));
		//document.location.replace(goodurl);
	}
	else if (completeurl.substring(0,completeurl.indexOf('#')) != ''){
		goodurl = completeurl.substring(0,completeurl.indexOf('?'));
		//document.location.replace(goodurl);
	}
	//alert(goodurl);
	return goodurl;
}
function suppression(id){
	if(window.confirm(decodeURIComponent("Attention \n Vous êtes sur le point d'effectuer une suppression!!!"))){
		document.location = id;
	}
}

function checkall(){
	var obj = document.getElementsByName('chk[]');
	var chk = document.getElementById("chkall");
	for(i = 0; i < obj.length; i++)
		obj.item(i).checked = chk.checked;
}
function editbutton(id){
	document.location = id;
}

function checkAtLeastOne(sms){
	var obj = document.getElementsByName("chk[]");
	var trouver = false;
	var i = 0;
	while(i < obj.length && !trouver){
		if(obj.item(i).checked == true)
			trouver = true;
		i++;
	}
	if(!trouver)
		alert(decodeURIComponent("Cocher au moins un élément"));
	else{
		if(window.confirm(sms)){
			document.forms['frmgrid'].action = initurl() + "?del=all&action=delete";
			document.forms['frmgrid'].submit();
		}
	}
}

function deletecheck(){
	var obj = document.getElementsByName("chk[]");
	var trouver = false;
	var i = 0;
	while(i < obj.length && !trouver){
		if(obj.item(i).checked == true)
			trouver = true;
		i++;
	}
	if(!trouver)
		alert(decodeURIComponent("Cocher au moins un élément"));
	else{
		if(window.confirm(decodeURIComponent("Attention \n Vous êtes sur le point de supprimer le(s) élt(s) cochés!!!"))){
			document.forms['frmgrid'].action = initurl() + "?action=deleteall";
			document.forms['frmgrid'].submit();
		}
	}
}
function rechercher(){
	if(document.getElementById('rech').value == "")
		alert(decodeURIComponent("Entrer un élément a rechercher"));
	else{
		document.location = initurl() + "?action=rechercher&val=" + document.getElementById('rech').value;
	}
}
function rediriger(url){
	document.location = url;
}
function home(){
	document.location = '../accueil/index.php';
}
function preciserOption(zonecible, zoneselect, sms, name){
	var cible = document.getElementById(zonecible);
	var zselect = document.getElementsByName(zoneselect).item(0);
	remove_content(cible);
	//cible.style.marginTop = "-10px";
	if(zselect.value != "" && zselect.value == "other"){
		alert(decodeURIComponent(sms));
		var input = document.createElement("input");
		input.setAttribute("name", name);
		input.setAttribute("type", "text");
		
		//cible.style.marginTop = "";
		cible.appendChild(input);
	}
}
/*Calcul le montant dans le champ ajout classe frais officiels */
function getMontant(){
	var obj = document.getElementsByName('montant[]');
	montant = 0;
	for(i = 0; i < obj.length; i++){
		alert('');
		montant += parseInt(obj.item(i).firstChild.value);
	}
	document.getElementById('total').innerHTML = montant;
}