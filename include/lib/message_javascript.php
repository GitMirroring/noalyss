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
 */
?>
<script>
var content=new Array();
content[0]="<?php echo escape_xml(_("Cherchez en saisissant le quickcode, le poste comptable ou une partie du nom de la fiche ou de l'adresse"))?>";
content[1]="<?php echo escape_xml(_("(optionnel) La description est un commentaire libre qui sert à identifier cette opération"))?>";
content[2]="<?php echo escape_xml(_("Selectionnez le journal où l'opération doit être sauvée"))?>";
content[3]="<?php echo escape_xml(_("Les périodes comptables servent comme un second contrôle pour la date de l'opération. Modifiez dans vos préférence pour avoir une autre période par défaut. Pour ne plus avoir à changer la période aller dans COMPANY, et mettez 'Afficher la période comptable' à non"))?>";
content[4]="<?php echo escape_xml(_("(optionnel) L'échéance est la date limite de paiement"))?>";
content[5]="<?php echo escape_xml(_("(optionnel)Le numéro d'extrait permet de retrouver plus facilement l'extrait de banque"))?>";
content[6]="<?php echo escape_xml(_("Indiquez ici le prix hors tva si vous êtes affilié à la tva et que vous  pouvez la déduire , sinon indiquez ici le total tva incluse et utilisez un taux tva de 0%"))?>";
content[7]="<?php echo escape_xml(_("(optionnel) Ces champs servent à contrôler que les montants correspondent à l'extrait"))?>";
content[8]="<?php echo escape_xml(_("(optionnel) Ce montant correspond au total tva, si vous le laissez à vide, il sera calculé automatiquement en fonction du taux"))?>";
content[9]="<?php echo escape_xml(_("Tapez le numéro de poste ou une partie du poste ou du libellé puis sur recherche, Si vous avez donné un quickcode, le poste comptable ne sera pas utilisé"))?>";
content[10]="<?php echo escape_xml(_("ATTENTION changer le poste comptable d'une fiche <b>ne modifiera pas toutes les opérations</b> où cette fiche est utilisée"))?>";
content[11]="<?php echo escape_xml(_("ATTENTION si le poste comptable est  vide, il sera créé automatiquement"))?>";
content[12]="<?php echo escape_xml(_("Document généré uniquement si le mode de paiement est utilisé"))?>";
content[13]="<?php echo escape_xml(_("Vous pouvez utiliser le % pour indiquer le poste parent"))?>";
content[14]="<?php echo escape_xml(_("Attention, le poste comptable doit exister, il ne sera pas vérifié"))?>";
content[15]="<?php echo escape_xml(_("Laissez à 0 pour ne rien changer"))?>";
content[16]="<?php echo escape_xml(_("Vous devez donner la date par opération"))?>";
content[17]="<?php echo escape_xml(_("Cliquez sur le titre d'une colonne pour trier"))?>";
content[18]="<?php echo escape_xml(_("Donnez une partie du nom, prénom, de la description, du poste comptable, du n° de TVA,quick code ... "))?>";
content[19]="<?php echo escape_xml(_("Donnez une partie du nom, de la description,  du n° de TVA du poste comptable ou du quick code"))?>";
content[20]="<?php echo escape_xml(_("Les menus ne peuvent dépendre que dans d'un menu principal ou d'un menu, si cette liste est vide, ajouter des modules ou menu principal sans donner de dépendance"))?>";
content[21]="<?php echo escape_xml(_("Donnez un nombre entre 0 & 100"))?>";
content[22]="<?php echo escape_xml(_("Donnez une partie du nom du dossier,du nom, du prénom ou du login pour filtrer"))?>";
content[23]="<?php echo escape_xml(_("Donnez une partie du nom du dossier ou de la description pour filtrer"))?>";
content[24]="<?php echo escape_xml(_("Donnez une partie du poste comptable ou du libellé pour filtrer"))?>";
content[25]="<?php echo escape_xml(_("Donnez une partie du libellé, la date, le montant ou le numéro d'opération pour filtrer, cela n'efface pas ce qui a déjà été sélectionné"))?>";
content[26]="<?php echo escape_xml(_("Donnez une partie du quickcode, nom, description... pour filtrer"))?>";
content[27]="<?php echo escape_xml(_("Attention, <b>SI</b> la fiche a changé de poste comptable, c'est seulement le dernier qui est affiché"))?>";
content[28]="<?php echo escape_xml(_("Attention Différence entre TVA calculée et donnée"))?>";
content[29]="<?php echo escape_xml(_("Si vous ne donnez pas de nom, ce sera le nom du fichier qui sera utilisé"))?>";
content[30]="<?php echo escape_xml(_("Peut contenir une information telle que le message structuré sur le virement"))?>";
content[31]="<?php echo escape_xml(_("Peut contenir un numéro de bon de commande"))?>";
// Because we are using HTML tag , we cannot escape and we must be careful with quote and double-quote, the best
// is to avoid them
content[32]="<?php echo _("<h3>        Remarque  : choix possibles    </h3>    <ul>        <li> Détail opérations ne donne pas le même résultat si on regarde tous les journaux ou un journal de type ACH ou VEN</li>        <li> Liste opérations ne donne pas le même résultat si on regarde tous les journaux ou un journal de type ACH ou VEN</li>        <li> Journaux VEN ou ACH en mode détail opérations donne les détails des factures, y compris les montants, TVA et quantité par article</li>        <li> Journaux VEN ou ACH en mode liste opérations donne pour chaque opération, le total de la TVA, ND, ...</li>    </ul>")?>";
content[33]="<?php echo escape_xml(_("le type vaut :<ul>	<li> ME pour Menu</li>	<li> PR pour les impressions </li>	<li> PL pour les plugins</li>	<li> SP pour des valeurs spéciales</li>	</ul>"))?>";
content[34]="<?php echo escape_xml(_("Cliquez sur le code AD pour ouvrir le menu dans un nouvel onglet"))?>";
content[35]="<?php echo escape_xml(_("Cliquez sur le chemin pour ouvrir le menu"))?>";
content[36]="<?php echo escape_xml(_("En utilisant les dates d échéance ou de paiement, seuls les journaux de type ACH et VEN seront utilisés ,vous excluez d office les autres journaux"))?>";
content[37]="<?php echo escape_xml(_("Les dates sont en format DD.MM.YYYY"))?>";
content[38]="<?php echo escape_xml(_("La numérotation est propre à chaque journal. Laissez à 0 pour ne pas changer le numéro"))?>";
content[39]="<?php echo escape_xml(_("Le préfixe des pièces doit être différent pour chaque journal, on peut aussi utiliser l'année"))?>";
content[40]="<?php echo escape_xml(_("Laissez à 0 pour ne pas changer le numéro"))?>";
content[41]="<?php echo escape_xml(_("Mettez le pourcentage <br> à zéro pour effacer la ligne"))?>";
content[42]="<?php echo escape_xml(_("Selectionnez le plan qui vous intéresse avant de cliquer sur Recherche"))?>";
content[43]="<?php echo escape_xml(_("Autoliquidation : Utilisé en même temps au crédit et au débit"))?>";
content[44]="<?php echo escape_xml(_("Ne donnez pas ce poste comptable si ce code n'est pas utilisé à l'achat"))?>";
content[45]="<?php echo escape_xml(_("Ne donnez pas ce poste comptable si ce code n'est pas utilisé  à la vente"))?>";
content[46]="<?php echo escape_xml(_("Un instant svp"))?>";
content[47]="<?php echo escape_xml(_("Vous confirmez ?"))?>";
content[48]="<?php echo escape_xml(_("Echec "))?>";
content[49]="<?php echo escape_xml(_("Echec donnée manquante ou incorrecte"))?>";
content[50]="<?php echo escape_xml(_("Vous confirmez effacement ?"))?>";
content[51]="<?php echo escape_xml(_("Vous confirmez mise à jour ?"))?>";
content[52]="<?php echo escape_xml(_("Si vous changez de page vous perdez les reconciliations, continuez ?")) ?>";
content[53]="<?php echo escape_xml(_("Echec réponse"))?>";
content[54]="<?php echo escape_xml(_("Désolé, les montants pour la comptabilité analytique sont incorrects"))?>";
content[55]="<?php echo escape_xml(_("Maximum 15 lignes"))?>";
content[56]="<?php echo escape_xml(_("Maximum"))?>";
content[57]="<?php echo escape_xml(_("calculé"))?>";
content[58]="<?php echo escape_xml(_("Balance incorrecte D/C")); ?>";
content[59]="<?php echo escape_xml(_("Action non autorisée")); ?>";
content[60]="<?php echo escape_xml(_("Donnée manquante ou déjà supprimée")) ?>";
content[61]="<?php echo escape_xml(_('Désolé, événement en cours de création à sauver')); ?>";
content[62]="<?php echo escape_xml(_("Cacher"))?>";
content[63]="<?php echo escape_xml(_("Afficher"))?>";
content[64]="<?php echo escape_xml(_("Traitement en cours"))?>";
content[65]="<?php echo escape_xml(_("Un instant"))?>";
content[66]="<?php echo escape_xml(_("Calculatrice"))?>";
content[67]="<?php echo escape_xml(_('Taper une formule (ex 20*5.1) puis enter'))?>";
content[68]="<?php echo escape_xml(_("Calculatrice simplifiée: écrivez simplement les opérations que vous voulez puis la touche retour. exemple : 1+2+3*(1/5)"));?>";
content[69]="<?php echo escape_xml(_("Aucune donnée"))?>";
content[70]="<?php echo escape_xml(_("Votre demande est en cours de traitement"))?>";
content[71]="<?php echo _('Limite le type de fiche si vous choisissez la fiche à la saisie, uniquement avec journaux OD');?>";
content[72]="<?php echo _("Pour les journaux FIN, ce sera la fiche du journal");?>";
content[73]="<?php echo _("Mettre à oui pour un journal dédié uniquement aux notes de crédit ou de débit, il affichera un avertissement si le montant n'est pas en négatif");?>";
content[74]="<?php echo _('TVA due ou récupérable quand l\'opération est payée ou exécutée')?>";
content[75]="<?php echo _('Journaux Achat ou vente en mode simple, TVA ou détaillé')?>";
content[76]="<?php echo _('Il est conseillé d\'avoir un quickcode de moins de 9 car.')?>";
content[77]="<?php echo _("Permet de chercher dans le suivi pour les contacts multiques")?>";
</script>
