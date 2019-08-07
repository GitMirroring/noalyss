<?php
/*
 *   This file is part of NOALYSS.
 *
 *   NOALYSS is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   NOALYSS is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Copyright Author Dany De Bontridder danydb@aevalys.eu

/*!\file
 * \brief API for creating PDF, unicode, based on tfpdf
 *@see TFPDF
 */

require_once NOALYSS_INCLUDE.'/tfpdf/tfpdf.php';
class Cellule {
    var $width;
    var $height;
    var $text;
    var $new_line;
    var $border;
    var $align;
    var $fill;
    var $link;
    var $type;
    function __construct($w,$h,$txt,$border,$ln,$align,$fill,$link,$type)
    {
        $this->width=$w ;
        $this->height=$h ;
        $this->text=$txt;
        $this->border=$border;
        $this->new_line=$ln;
        $this->align=$align;
        $this->fill=$fill;
        $this->link=$link;
        $this->type=$type;
        return $this;
    }
}
class PDF_Core extends TFPDF
{



    private $cells=array();

    public function __construct ( $orientation = 'P', $unit = 'mm', $format = 'A4')
    {
		$this->bigger=0;

        parent::__construct($orientation, $unit, $format);
        $this->AddFont('DejaVu','','DejaVuSans.ttf',true);
        $this->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
        $this->AddFont('DejaVu','BI','DejaVuSans-BoldOblique.ttf',true);
        $this->AddFont('DejaVuCond','','DejaVuSansCondensed.ttf',true);
        $this->AddFont('DejaVuCond','B','DejaVuSansCondensed-Bold.ttf',true);
        $this->AddFont('DejaVuCond','I','DejaVuSansCondensed-Oblique.ttf',true);



        $this->cells=array();
    }


    /**
     * Count the number of rows a p_text will take for a multicell
     * @param $p_text String
     * @param $p_colSize size of the column in User Unit
     */
    private function count_nb_row($p_text,$p_colSize) 
    {
        // If colSize is bigger than the size of the string then it takes 1 line
        if ( $this->GetStringWidth($p_text) <= $p_colSize) return 1;
        $nRow=0;
        $aWords=explode(' ',$p_text);
        $nb_words=count($aWords);
        $string="";
        
        for ($i=0;$i < $nb_words;$i++){
            // Concatenate String with current word + a space 
            $string.=$aWords[$i];
            
            // if there is a word after add a space
            if ( $i+1 < $nb_words) $string.=" ";
            
            // Compute new size and compare to the colSize
            if ( $this->GetStringWidth($string) >= $p_colSize) {
            // If the size of the string if bigger than we add a row, the current
            // word is the first word of the next line
                $nRow++;
                $string=$aWords[$i];
            }
        }
        $nRow++;
        return $nRow;
        
        
        
    }
    /**
     * Check if a page must be added due a MultiCell 
     * @return boolean
     */
    private function check_page_add()
    {
        // break on page
        $size=count($this->cells);
        for ($i=0;$i < $size ; $i++)
        {
            if ($this->cells[$i]->type == 'M' )
            {
                /**
                 * On doit calculer si le texte dépasse la texte et compter le
                 * nombre de lignes que le texte prendrait. Ensuite il faut
                 * faire un saut de page (renvoit true) si dépasse
                 */
                
                $sizetext=$this->GetStringWidth($this->cells[$i]->text);
                
                // if text bigger than column width then check

                $y=$this->GetY();
                $nb_row=$this->count_nb_row($this->cells[$i]->text, $this->cells[$i]->width);
                $height=$this->cells[$i]->height*$nb_row;

                // If the text is bigger than a sheet of paper then return false
                if ($height >= $this->h) return false;

                if ( $y + $height > ($this->h - $this->bMargin -7  ))
                    return true;

            }
        }
        return false;
    }
    private function print_row()
    {
        static $e=0;
        $e++;
        if ( $this->check_page_add() == true ) $this->AddPage ();
        $this->bigger=0;
        $size=count($this->cells);
        $cell=$this->cells;
        if ($size == 0 )return;
        for ($i=0;$i < $size ; $i++)
        {
            $a=$cell[$i];
            $a->text= str_replace("\\", "", $a->text);
            switch ($a->type)
            {
                case "M":
                $x_m=$this->GetX();
		$y_m=$this->GetY();
		parent::MultiCell(
                                    $a->width, 
                                    $a->height, 
                                    $a->text, 
                                    $a->border, 
                                    $a->align, 
                                    $a->fill
                        );
		$x_m=$x_m+$a->width;
		$tmp=$this->GetY()-$y_m;
		if ( $tmp > $this->bigger) $this->bigger=$tmp;
		$this->SetXY($x_m,$y_m);
                break;
                
                case "C":
                    
                     parent::Cell(   $a->width, 
                                    $a->height, 
                                    $a->text, 
                                    $a->border, 
                                    $a->new_line, 
                                    $a->align, 
                                    $a->fill, 
                                    $a->link);
                    break;

                default:
                    break;
            }
        }
        $this->cells=array();
    }
    private function add_cell(Cellule $Ce)
    {
        $size=count($this->cells);
        $this->cells[$size]=$Ce;
        
    }
    function write_cell ($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $this->add_cell(new Cellule($w,$h,$txt,$border,$ln,$align,$fill,$link,'C'));
        
    }
    function LongLine($w,$h,$txt,$border=0,$align='',$fill=false)
    {
        $this->add_cell(new Cellule($w,$h,$txt,$border,0,$align,$fill,'','M'));

    }
    /**
     * Print all the cell stored and call Ln (new line)
     * @param int $p_step
     */

    function line_new($p_step=null){
            $this->print_row();
           if ( $this->bigger==0) 
                parent::Ln($p_step);
            else 
                parent::Ln($this->bigger);
            $this->bigger=0;
    }
       /**
     * If the step is even then return 1 and set the backgroup color to blue , otherwise
     * returns 0, and set the background color to white
     * It is use to compute alternated  colored row , it the parameter fill in write_cell and 
     * cell
     * @see PDF:write_cell
     * @see TPDF:cell
     * 
     */
    function is_fill($p_step)
    {
        if ($p_step % 2 == 0) {
            $pdf->SetFillColor(220, 221, 255);
            $fill = 1;
        } else {
            $pdf->SetFillColor(0, 0, 0);
            $fill = 0;
        }
        return $p_step;
    }



}

