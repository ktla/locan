	******************************************************************
	**																**
	**		PROCEDURE D'INSTALLATION								**
	**		DU PROGRAMME INTRANET LOCAN								**
	**																**
	******************************************************************

1-Decompresser le fichier locan.zip
	- Selon votre configuration 32bits ou 64bits, Telecharger le programme wampserver (www.wampserver.com) ou xampplite (www.apachefriends.org/fr/xampp.html) sur internet
2-Installer le programme wampserver2.2d-x64.exe; wampserver2.2d-x32.exe ou xampplite-win32-1.7.3.exe.
	selon votre configuration, installer soit le 32bits ou le 64bits de wamp ou xampplite.
	Le repertoire d'installation doit etre c:\wamp  (pour wampserver) ou c:\xampplite (pour xampplite)
	
3-Copier le dossier locan decompressé sous le repertoire c:\wamp\www pour wampserver ou c:\xampplite\htdocs pour xampplite.
	Le chemin d'accès doit etre c:\wamp\www\locan ou c:\xampplite\htdocs\locan
	Verifier que les sous dossiers se trouvent immediatement sous la radicale locan et non c:\wamp\www\locan\locan

4-Executer (start WampServer/Xamp Control panel) le programme wampserver ou xampplite installé.
	-l'icone de notification doit etre vert pour wamp et marqué "serveur en ligne"
	-Pour les utilisateurs de xampplite, cliquer sur start Apache et Start MySQL. ils doivent marquer running
	-Sinon redemarrer la machine et arreter skype (quitter skype de la barre des taches)

5-Cliquer sur l'icon de notification pour wampserver et selectionner phpMyAdmin. pour les utilisateur xampplite cliquer sur le button admin de MySQL.
	-Une page web s'ouvre.  Pour xampplite, dans le menu de gauche cliquer sur phpMyAdmin
	-Cliquer sur l'onglet Base de données/database.
	-Entrer le nom de la base de donnéee : locan et cliquer sur creer/create
	-Choisir locan sur le menu de gauche pour les utilisateurs de wamp sur cette page web. pour les utilisateurs de xampplite, passer à l'étape suivante.
	-cliquer sur l'onglet importer/import.
	-Cliquer sur browse/parcourir et selectionner le fichier .sql situer c:\wamp\www\locan\sql\locan.sql ou c:\xampplite\htdocs\locan\sql\locan.sql
	-Cliquer sur go/valider

6-Compte administrateur:
	-Nom utilisateur/login : admin
	-Mot de passe: admin
7-Acceder au logiciel en lancant un navigateur et saisir localhost/locan sur la barre d'addresse.
	-Lancer wampServer chaque fois qu'il faut lancer le logiciel.
	
8-POUR LES UTILISATEURS AVANCES ET ATTENTION A CETTE MANIPULATION
	-definisser un mot de passe au Systeme de Gestion de la Base de Donnees.
	-Editer le fichier c:\wamp\www\locan\configurations\config.xml (Modifier la ligne <password>votre_nouveau_mot_de_pass_ici</password>).
	-Sauvegarder le fichier et lancer wamp ou xampplite.
	-Si vous avez installe le serveur sur une machine distante, entrer l'adresse de la machine dans la ligne(<host>adresse_IP_machine_distance</host>) a la place de localhost.
