<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
/**
 * @file
 * @brief input detail of card category , variable to set
 *    -  $nom_mod name of the category
 *    - $fd_description ITextarea for the description
 *    - $f_class_base base account
 */
?>
<div class="content" style="margin-left: 2rem;">
    <div class="form-group">
        <label for="nom_mod"><?=_('Categorie de fiche')?></label>
        <input id="nom_mod_id" type="text" class="input_text form-control" style="width: 50rem" name="nom_mod" value="<?=$nom_mod?>">
    </div>
<div class="form-group">
    <label for="fd_description"><?=_("Description")?></label>
		<?php echo $fd_description->input(); ?>
</div>
    <div class="form-group">
        <p class="text-muted">
            <?php echo _("Choisissez poste comptable qui sera la base des postes comptables pour les fiches de cette catégorie")?>
        </p>

        <label for="class_base"><?=_("Poste comptable de base")?></label>
        <?php echo $f_class_base?>

    </div>
    <div class="form-group">
        <INPUT TYPE="CHECKBOX" NAME="create" <?php if ($this->create_account) echo 'CHECKED';?> ><?php echo _("Création automatique du poste comptable")?>
        <p class="text-muted">
            <?php echo _("Cochez cette case si chaque fiche de cette catégorie doit avoir un poste comptable propre, il sera calculé automatiquement si vous n'en donnez pas un. ")?>
        </p>
        <p class="text-muted">
            <?php echo _("Si vous ne cochez pas et que le poste comptable de base n'est pas vide alors toutes les fiches auront le poste comptable de base comme poste comptable par défaut")?>
        </p>
    </div>
</div>

