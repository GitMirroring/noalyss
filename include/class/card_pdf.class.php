<?php
/**
 * @file
 * @brief
 *
 */
/**
 * @class
 * @brief Class Card_PDF
 */
class Card_PDF extends \PDF
{
    private $card;
    function __construct($p_card_id)
    {
        global $cn;
        $this->card=new \Fiche ($cn,$p_card_id);
        $this->card->load();
        parent::__construct($cn, "P");
        $this->setDossierInfo($this->card->strAttribut(ATTR_DEF_QUICKCODE));
    }

    /**
     * @return mixed
     */
    public function getCardId()
    {
        return $this->card->get_id();
    }

    /**
     * @param mixed $card_id
     */
    public function setCardId($card_id)
    {
        $this->card->set_id($card_id);
        $this->card->load();
    }

    public function export()
    {
        $nb_attribut = count($this->card->attribut);
//        var_dump(
//            $this->card->attribut
//        );
        if ($nb_attribut == 0) {
            throw new \Exception(_("card_pdf.044 , card_inexistante"));
        }
        $this->setTitle($this->card->get_quick_code()." ".strtoupper($this->card->strAttribut(1))
            ." ".$this->card->strAttribut(32), true);
        $this->SetAuthor('NOALYSS');
        $this->AliasNbPages();
        $this->AddPage();

        $this->setFont("DejaVu", '', 7);
        $this->SetDrawColor(214, 209, 209);
        for ($i=0;$i<$nb_attribut;$i++)
        {
            $border="B";
            $this->write_cell(60,5,$this->card->attribut[$i]->ad_text,$border);
            $this->write_cell(100,5,$this->card->attribut[$i]->av_text,$border);
            $this->line_new(5);
        }
      

        $filename=$this->card->strAttribut(1)."-".$this->card->strAttribut(32).".pdf";
        $filename=sanitize_filename($filename);
        $this->Output($filename,"D");
    }

}