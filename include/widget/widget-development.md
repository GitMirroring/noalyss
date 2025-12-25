# Développement de "widget"

le code du widget , est toujours le même que celui du sous-répertoire dans NOALYSS_INCLUDE/widget, que le nom du fichier
principal et aussi, le nom de la CLASS. Donc ce nom ne peut pas contenir d'autres caractères que des lettres 
et des soulignés. Toujours en minuscule.

Il faut aussi l'ajouter dans la table WIDGET_DASHBOARD
    * wd_id clef primaire
    * wd_code code du widget 
    * wd_description description du widget , ce qu'il fait
    * wd_parameter int , égal à 0, s'il n'y a pas de paramètre, à 1 s'il existe un paramètre (voir Paramètre)

## Installation

Création d'un fichier appelé "install.php" dans le sous-répertoire qui sera exécuté si 
le widget n'est pas encore dans la base de données

Exemple

    global $cn;
    
    $cn->exec_sql("insert into widget_dashboard (wd_code,wd_description,wd_parameter,wd_name) values ($1,$2,$3,$4)",
    array (
    'event'
    ,'Affiche les 10 actions en retards, celles à venir , les 10 prochaines factures client ou fournisseurs, ou celles en retard '
    ,1
    ,'10 actions ou factures'
    )
    );


## Ajax 

Tous les appels ajax appellent "html/ajax_misc.php" , la requête doit contenir : 

    * la variable "op" : "widget"
    * la variable "w" : chaine qui est le nom du widget (nom du sous-répertoire de include/widget) 

le fichier ajax.php du sous-répertoire NOALYSS_INCLUDE/widget/(w)/ajax.php sera appelé

## Application

La classe s'appele toujours "nom-du-widget.php" , elle est dérivée de widget et doit avoir les fonctions 

* display  : affichage du widget
* input : affichage de la description et permet son activation (visible dans la box )
* input-parameter : si des paramètres doivent être sauvés, les paramètres sont par utilisateur et par widget activés,
* display_parameter

### Paramètres

Certains widgets peuvent être paramétrés, et il est possible d'ajouter plusieurs fois le même widget

Exemple mini-report : choix du mini-report à afficher, 

Pour cela, il faut ajouter certaines fonctions :

1. function input_parameter() : création d'un FORM dont le DOMID sera le code widget (wd_code) suivi de "_param"

exemple pour mini_report

    function input_parameter() {
   
        $select=new \ISelect('simple_report');
        $select->value=$this->db->make_array("select fr_id, fr_label from form_definition order by 2");
        $this->make_form($select->input());
    }

2. display_parameter : affichage du paramètre 

Les paramètres sont toujours sauvés "brut" , comme une chaîne URL, il faut donc pouvoir l'afficher, en la travaillant
avec parse_str, pour cela il faut appeler Widget->get_parameter(), le résultat doit être dans un SPAN avec la classe
"widget_param".

Exemple pour mini_report

    function display_parameter() {
        $aParam=$this->get_parameter();
        $name = $this->db->get_value("select fr_label from form_definition where fr_id=$1",[$aParam['simple_report']]);
        echo " ";
        echo span(_("Rapport") ." ".h($name),' class="widget_param"');


    }


### function  display

Toujours commencer par un DIV (id=code_widget+uw_id), Widget::open_div et Widget::close_div

Ce DIV doit avoir comme classe **box** et **widget-box** , le **widget-box** permet de numéroter les boites.

Exemple :

```php
    // création de la boite 
      $this->open_div();

    // création du titre avec les boutons "refresh et agrandissement"
      $this->title(_("Planifications"));

    // Code pour le widget
    /// .....

     // fermeture
      $this->close_div();
```
Voir aussi "Paramètres"


# Problèmes à résoudre

## Taille

Certains widget ont besoin de plus de place 
Utilisation de vw et vh , attention agenda utilise des tailles fixes !

## Position
