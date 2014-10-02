// JavaScript Document

function selectionImgClick(){ // actionner le onchange de l' input image
	var tab = document.getElementsByName("frm");
	action = tab.item(0).action;
	tab.item(0).target="hiddeniframe";
	tab.item(0).action="./chargementImg.php";
	tab.item(0).submit();
	tab.item(0).target="";
	tab.item(0).action = action;
}

function selectionReglementClick(){ // actionner le onchange de l' input reglement
	var tab = document.getElementsByName("frm");
	action = tab.item(0).action;
	tab.item(0).target="hiddeniframe";
	tab.item(0).action="./chargementReglement.php";
	tab.item(0).submit();
	tab.item(0).target="";
	tab.item(0).action = action;
}

function selectionBdClick(){ // actionner le onchange de l' input reglement
	var tab = document.getElementsByName("frm");
	action = tab.item(0).action;
	tab.item(0).target="hiddeniframe";
	tab.item(0).action="./chargementBd.php";
	tab.item(0).submit();
	tab.item(0).target="";
	tab.item(0).action = action;
}
/*
	Function permettant le chargement de l'image
*/
var loadImage = function(lien){
	balise = '<img src="'+lien+'" width="100px" height="70px" />';
	document.getElementById("chargerlogo").innerHTML = balise; 
	document.getElementById("erreurImage").innerHTML = "";
}

/*
	Function permettant le chargement du reglement
*/
var loadReglement = function(id,mes){
	switch (parseInt(id)){
	case 0 :balise = '<p style="float:left;" >'+mes+'</p><img title="Supprimer reglement" height="10px" width="10px" style="margin-left:5px; margin-top:13px; cursor:pointer;" src="../images/icons/cancel.png" onclick="clearReglement();"/>';
	break;
	default : balise = '<p style="float:left; color:red;" >'+mes+'</p><img title="Supprimer reglement" height="10px" width="10px" style="margin-left:5px; margin-top:13px; cursor:pointer;" src="../images/icons/cancel.png" onclick="clearReglement();"/>';
	document.getElementById("bd").value="";
	}
	document.getElementById("reglement_print").innerHTML = balise; 
}

/*
	Function permettant le chargement la BD
*/
var loadBd = function(id,mes){
	switch (parseInt(id)){
	case 0 :balise = '<p style="float:left;" >'+mes+'</p><img title="Supprimer BD" height="10px" width="10px" style="margin-left:5px; margin-top:13px; cursor:pointer;" src="../images/icons/cancel.png" onclick="clearBd();"/>';
	break;
	default : balise = '<p style="float:left; color:red;" >'+mes+'</p><img title="Supprimer BD" height="10px" width="10px" style="margin-left:5px; margin-top:13px; cursor:pointer;" src="../images/icons/cancel.png" onclick="clearBd();"/>';
	document.getElementById("bd").value="";
	}
	document.getElementById("bd_print").innerHTML = balise; 
}

var CA = {}; // Controle Ajax permettant de verifier l' image uploade
CA.UploadAjax = function(){};
CA.UploadAjax.callBack = function (message){
	document.getElementById('logo').value="";
	tab = message.split(';');
	if(tab[0]== 0)	
		loadImage(tab[1]);
	else
		document.getElementById("erreurImage").innerHTML = tab[1];
}

var CD = {}; // Controle Ajax permettant de verifier le reglement uploade
CD.UploadAjax = function(){};
CD.UploadAjax.callBack = function (message){
	tab = message.split(';');
	loadReglement(tab[0],tab[1]);
}

var CS = {}; // Controle Ajax permettant de verifier la BD uploade
CS.UploadAjax = function(){};
CS.UploadAjax.callBack = function (message){
	tab = message.split(';');
	loadBd(tab[0],tab[1]);
}

function clearImage(){
	balise = '<strong style="position:relative; top:35%;">Pas de Logo</strong>';
	$.ajax({
		type:'POST',
		url:'./clearImage.php',
		datatype:'text',
		error: function() {alert('Erreur serveur');}
	});
	document.getElementById("chargerlogo").innerHTML = balise; 
	document.getElementById("erreurImage").innerHTML = "";


}
function clearReglement(){
	document.getElementById("reglement_print").innerHTML =""; 
	document.getElementById("reglement").value = "";
}

function clearBd(){
	document.getElementById("bd_print").innerHTML =""; 
	document.getElementById("bd").value = "";
}


function print_error(msg){
		document.getElementById("error").innerHTML = msg;	
}

function step2(elt){
	reg=/[a-z,0-9]/gi;
	reg1=/[a-z,0-9]/gi;
	reg2=/[a-z,0-9]/gi; 
	reg3=/[a-z,0-9]/gi;
	reg4=/[a-z,0-9]/gi;
	reg5=/[a-z,0-9]/gi;
	reg6=/[a-z,0-9]/gi;
	test1 = reg.test(document.getElementById('identifiant').value);
	test2 = reg1.test(document.getElementById('libelle').value);
	test3 = reg2.test(document.getElementById('adresse').value);
	test4 = reg3.test(document.getElementById('principal').value);
	test5 = reg4.test(document.getElementById("bd").value);
	test6 = reg5.test(document.getElementById('user').value);
	test7 = reg6.test(document.getElementById('pwd').value);
	if (test1 && test2 && test3 && test4 && test5 && test6 && test7)
		elt.type="submit";	
	else
		print_error("Remplir bien tous les champs obligatoires !");
}