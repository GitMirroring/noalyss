<?php
/*
 *   This file is part of NOALYSS.
 *
 *   NOALYSS isfree software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   NOALYSS isdistributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// Copyright (2014) Author Dany De Bontridder <dany@alchimerys.be>


/**
 * @file
 * @brief Contains all the javascript messages, this file must be included before
 * the javascript file. The message cannot contains a double quote even when
 * it is escaped !
 * Avoid use of HTML Tag
 */
?>

var content=new Array();
content[0]="<?php echo htmlspecialchars(_("Cherchez en saisissant le quickcode, le poste comptable ou une partie du nom de la fiche ou de l'adresse"),ENT_QUOTES)?>";
content[1]="<?php echo htmlspecialchars(_("(optionnel) La description est un commentaire libre qui sert à identifier cette opération"),ENT_QUOTES)?>";
content[2]="<?php echo htmlspecialchars(_("Selectionnez le journal où l'opération doit être sauvée"),ENT_QUOTES)?>";
content[3]="<?php echo htmlspecialchars(_("Les périodes comptables servent comme un second contrôle pour la date de l'opération. Modifiez dans vos préférence pour avoir une autre période par défaut. Pour ne plus avoir à changer la période aller dans COMPANY, et mettez 'Afficher la période comptable' à non"),ENT_QUOTES)?>";
content[4]="<?php echo htmlspecialchars(_("(optionnel) L'échéance est la date limite de paiement"),ENT_QUOTES)?>";
content[5]="<?php echo htmlspecialchars(_("(optionnel)Le numéro d'extrait permet de retrouver plus facilement l'extrait de banque"),ENT_QUOTES)?>";
content[6]="<?php echo htmlspecialchars(_("Indiquez ici le prix hors tva si vous êtes affilié à la tva et que vous  pouvez la déduire , sinon indiquez ici le total tva incluse et utilisez un taux tva de 0%"))?>";
content[7]="<?php echo htmlspecialchars(_("(optionnel) Ces champs servent à contrôler que les montants correspondent à l'extrait"),ENT_QUOTES)?>";
content[8]="<?php echo htmlspecialchars(_("(optionnel) Ce montant correspond au total tva, si vous le laissez à vide, il sera calculé automatiquement en fonction du taux"),ENT_QUOTES)?>";
content[9]="<?php echo htmlspecialchars(_("Tapez le numéro de poste ou une partie du poste ou du libellé puis sur recherche, Si vous avez donné un quickcode, le poste comptable ne sera pas utilisé"),ENT_QUOTES)?>";
content[10]="<?php echo htmlspecialchars(_("ATTENTION changer le poste comptable d'une fiche <b>ne modifiera pas toutes les opérations</b> où cette fiche est utilisée"),ENT_QUOTES)?>";
content[11]="<?php echo htmlspecialchars(_("ATTENTION si le poste comptable est  vide, il sera créé automatiquement"),ENT_QUOTES)?>";
content[12]="<?php echo htmlspecialchars(_("Document généré uniquement si le mode de paiement est utilisé"),ENT_QUOTES)?>";
content[13]="<?php echo htmlspecialchars(_("Vous pouvez utiliser le % pour indiquer le poste parent"),ENT_QUOTES)?>";
content[14]="<?php echo htmlspecialchars(_("Attention, le poste comptable doit exister, il ne sera pas vérifié"),ENT_QUOTES)?>";
content[15]="<?php echo htmlspecialchars(_("Laissez à 0 pour ne rien changer"),ENT_QUOTES)?>";
content[16]="<?php echo htmlspecialchars(_("Vous devez donner la date par opération"),ENT_QUOTES)?>";
content[17]="<?php echo htmlspecialchars(_("Cliquez sur le titre d'une colonne pour trier"),ENT_QUOTES)?>";
content[18]="<?php echo htmlspecialchars(_("Donnez une partie du nom, prénom, de la description, du poste comptable, du n° de TVA,quick code ... "),ENT_QUOTES)?>";
content[19]="<?php echo htmlspecialchars(_("Donnez une partie du nom, de la description,  du n° de TVA du poste comptable ou du quick code"),ENT_QUOTES)?>";
content[20]="<?php echo htmlspecialchars(_("Les menus ne peuvent dépendre que dans d'un menu principal ou d'un menu, si cette liste est vide, ajouter des modules ou menu principal sans donner de dépendance"),ENT_QUOTES)?>";
content[21]="<?php echo htmlspecialchars(_("Donnez un nombre entre 0 & 100"),ENT_QUOTES)?>";
content[22]="<?php echo htmlspecialchars(_("Donnez une partie du nom du dossier,du nom, du prénom ou du login pour filtrer"),ENT_QUOTES)?>";
content[23]="<?php echo htmlspecialchars(_("Donnez une partie du nom du dossier ou de la description pour filtrer"),ENT_QUOTES)?>";
content[24]="<?php echo htmlspecialchars(_("Donnez une partie du poste comptable ou du libellé pour filtrer"),ENT_QUOTES)?>";
content[25]="<?php echo htmlspecialchars(_("Donnez une partie du libellé, la date, le montant ou le numéro d'opération pour filtrer, cela n'efface pas ce qui a déjà été sélectionné"),ENT_QUOTES)?>";
content[26]="<?php echo htmlspecialchars(_("Donnez une partie du quickcode, nom, description... pour filtrer"),ENT_QUOTES)?>";
content[27]="<?php echo htmlspecialchars(_("Attention, <b>SI</b> la fiche a changé de poste comptable, c'est seulement le dernier qui est affiché"),ENT_QUOTES)?>";
content[28]="<?php echo htmlspecialchars(_("Attention Différence entre TVA calculée et donnée"),ENT_QUOTES)?>";
content[29]="<?php echo htmlspecialchars(_("Si vous ne donnez pas de nom, ce sera le nom du fichier qui sera utilisé"),ENT_QUOTES)?>";
content[30]="<?php echo htmlspecialchars(_("Peut contenir une information telle que le message structuré sur le virement"),ENT_QUOTES)?>";
content[31]="<?php echo htmlspecialchars(_("Peut contenir un numéro de bon de commande"),ENT_QUOTES)?>";

content[32]="<?php echo htmlspecialchars(_('Le résultat peut changer en fonction du type de journal'),ENT_QUOTES);?>";
	
content[33]="<?php echo htmlspecialchars(_("le type vaut : ME pour Menu, PR pour les impressions,PL pour les plugins,SP pour des valeurs spéciales"),ENT_QUOTES)?>";
content[34]="<?php echo htmlspecialchars(_("Cliquez sur le code AD pour ouvrir le menu dans un nouvel onglet"),ENT_QUOTES)?>";
content[35]="<?php echo htmlspecialchars(_("Cliquez sur le chemin pour ouvrir le menu"),ENT_QUOTES)?>";
content[36]="<?php echo htmlspecialchars(_("En utilisant les dates d échéance ou de paiement, seuls les journaux de type ACH et VEN seront utilisés ,vous excluez d office les autres journaux"),ENT_QUOTES)?>";
content[37]="<?php echo htmlspecialchars(_("Les dates sont en format DD.MM.YYYY"),ENT_QUOTES)?>";
content[38]="<?php echo htmlspecialchars(_("La numérotation est propre à chaque journal. Laissez à 0 pour ne pas changer le numéro"),ENT_QUOTES)?>";
content[39]="<?php echo htmlspecialchars(_("Le préfixe des pièces doit être différent pour chaque journal, on peut aussi utiliser l'année"),ENT_QUOTES)?>";
content[40]="<?php echo htmlspecialchars(_("Laissez à 0 pour ne pas changer le numéro"),ENT_QUOTES)?>";
content[41]="<?php echo htmlspecialchars(_("Mettez le pourcentage <br> à zéro pour effacer la ligne"),ENT_QUOTES)?>";
content[42]="<?php echo htmlspecialchars(_("Selectionnez le plan qui vous intéresse avant de cliquer sur Recherche"),ENT_QUOTES)?>";
content[43]="<?php echo htmlspecialchars(_("Autoliquidation : Utilisé en même temps au crédit et au débit"),ENT_QUOTES)?>";
content[44]="<?php echo htmlspecialchars(_("Ne donnez pas ce poste comptable si ce code n'est pas utilisé à l'achat"),ENT_QUOTES)?>";
content[45]="<?php echo htmlspecialchars(_("Ne donnez pas ce poste comptable si ce code n'est pas utilisé  à la vente"),ENT_QUOTES)?>";
content[46]="<?php echo htmlspecialchars(_("Un instant svp"),ENT_QUOTES)?>";
content[47]="<?php echo htmlspecialchars(_("Vous confirmez ?"),ENT_QUOTES)?>";
content[48]="<?php echo htmlspecialchars(_("Echec "),ENT_QUOTES)?>";
content[49]="<?php echo htmlspecialchars(_("Echec donnée manquante ou incorrecte"),ENT_QUOTES)?>";
content[50]="<?php echo htmlspecialchars(_("Vous confirmez effacement ?"),ENT_QUOTES)?>";
content[51]="<?php echo htmlspecialchars(_("Vous confirmez mise à jour ?"),ENT_QUOTES)?>";
content[52]="<?php echo htmlspecialchars(_("Si vous changez de page vous perdez les reconciliations, continuez ?"),ENT_QUOTES) ?>";
content[53]="<?php echo htmlspecialchars(_("Echec réponse"),ENT_QUOTES)?>";
content[54]="<?php echo htmlspecialchars(_("Désolé, les montants pour la comptabilité analytique sont incorrects"),ENT_QUOTES)?>";
content[55]="<?php echo htmlspecialchars(_("Maximum 15 lignes"),ENT_QUOTES)?>";
content[56]="<?php echo htmlspecialchars(_("Maximum"),ENT_QUOTES)?>";
content[57]="<?php echo htmlspecialchars(_("calculé"),ENT_QUOTES)?>";
content[58]="<?php echo htmlspecialchars(_("Balance incorrecte D/C")),ENT_QUOTES; ?>";
content[59]="<?php echo htmlspecialchars(_("Action non autorisée")),ENT_QUOTES; ?>";
content[60]="<?php echo htmlspecialchars(_("Donnée manquante ou déjà supprimée"),ENT_QUOTES) ?>";
content[61]="<?php echo htmlspecialchars(_('Désolé, événement en cours de création à sauver')),ENT_QUOTES; ?>";
content[62]="<?php echo htmlspecialchars(_("Cacher"),ENT_QUOTES)?>";
content[63]="<?php echo htmlspecialchars(_("Afficher"),ENT_QUOTES)?>";
content[64]="<?php echo htmlspecialchars(_("Traitement en cours"),ENT_QUOTES)?>";
content[65]="<?php echo htmlspecialchars(_("Un instant"),ENT_QUOTES)?>";
content[66]="<?php echo htmlspecialchars(_("Calculatrice"),ENT_QUOTES)?>";
content[67]="<?php echo htmlspecialchars(_('Taper une formule (ex 20*5.1) puis enter'),ENT_QUOTES)?>";
content[68]="<?php echo htmlspecialchars(_("Calculatrice simplifiée: écrivez simplement les opérations que vous voulez puis la touche retour. exemple : 1+2+3*(1/5)"),ENT_QUOTES);?>";
content[69]="<?php echo htmlspecialchars(_("Aucune donnée"),ENT_QUOTES)?>";
content[70]="<?php echo htmlspecialchars(_("Votre demande est en cours de traitement"),ENT_QUOTES)?>";
content[71]="<?php echo htmlspecialchars(_('Limite le type de fiche si vous choisissez la fiche à la saisie, uniquement avec journaux OD'),ENT_QUOTES);?>";
content[72]="<?php echo htmlspecialchars(_("Pour les journaux FIN, ce sera la fiche du journal"),ENT_QUOTES);?>";
content[73]="<?php echo htmlspecialchars(_("Mettre à oui pour un journal dédié uniquement aux notes de crédit ou de débit, il affichera un avertissement si le montant n'est pas en négatif"),ENT_QUOTES);?>";
content[74]="<?php echo htmlspecialchars(_('TVA due ou récupérable quand l\'opération est payée ou exécutée'),ENT_QUOTES)?>";
content[75]="<?php echo htmlspecialchars(_('Journaux Achat ou vente en mode simple, TVA ou détaillé'),ENT_QUOTES)?>";
content[76]="<?php echo htmlspecialchars(_('Conseil : quickcode de moins de 9 car. commençant pas les 2 ou 3 premiers chiffres du poste comptable'),ENT_QUOTES)?>";
content[77]="<?php echo htmlspecialchars(_("Permet de chercher dans le suivi pour les contacts multiples"),ENT_QUOTES)?>";
<?php $file_too_large=sprintf("Fichier trop grand , taille max = %s mb total = %s",(round(MAX_FILE_SIZE/1024/1024,2)),ini_get("post_max_size"));?>
content[78]="<?php echo htmlspecialchars($file_too_large,ENT_QUOTES)?>";

content[79]="<?php echo htmlspecialchars(_("Les postes comptables sont entre [] , les fiches entre {} et les postes analytiques entre {{ }}"))?>";
content[80]="<?php echo htmlspecialchars(_("Oui pour charger les fichiers javascripts et CSS standards"),ENT_QUOTES)?>";    
content[81]="<?php echo htmlspecialchars(_("Recommendé d'avoir un poste propre"),ENT_QUOTES)?>";
content[82]="<?php echo htmlspecialchars(_("valeur en % "),ENT_QUOTES)?>";
content[83]="<?php echo htmlspecialchars(_("Données invalides "),ENT_QUOTES)?>";
content[84]="<?php echo htmlspecialchars(_("En Belgique, l'exercice commence par un report des comptes de 0 à 5, mais pas en France, ce solde est calculé depuis le tout premier exercice"),ENT_QUOTES)?>";
content[85]="<?php echo htmlspecialchars(_("Solde créditeur au lieu de débiteur"),ENT_QUOTES)?>";
content[86]="<?php echo htmlspecialchars(_("Solde débiteur au lieu de créditeur"),ENT_QUOTES)?>";
content[87]="<?php echo htmlspecialchars(_("Uniquement pour les choix, séparer les valeurs possibles par un |"),ENT_QUOTES)?>";
content[88]="<?php echo htmlspecialchars(_("Par défault, le poste d'autoliquidation est celui qui est au débit pour les ventes et au crédit pour les achats"),ENT_QUOTES)?>";
