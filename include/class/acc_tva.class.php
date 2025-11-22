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
 * \brief this class is used for the table tva_rate
 */

/*!\brief Acc_Tva is used for to map the table tva_rate
 * parameter are
- private static $cn;	database connection
- private static $variable=array("id"=>"tva_id",
		 "label"=>"tva_label",
		 "rate"=>"tva_rate",
		 "comment"=>"tva_comment",
		 "account"=>"tva_poste"
                  tva_peppol_code
                 'vx_code' 
 * );

*/
class Acc_Tva
{
    private static $variable=array("id"=>"tva_id",
                                   "label"=>"tva_label",
                                   "rate"=>"tva_rate",
                                   "comment"=>"tva_comment",
                                   "account"=>"tva_poste",
                                    "both_side"=>'tva_both_side',
                                    'tva_reverse_account'=>'tva_reverse_account',
                                    'tva_code'=>'tva_code',
                                    "tva_peppol_code"=>"tva_peppol_code",
                                    'vx_code'=>'vx_code'
        
                                );
    public $tva_id,
        $tva_label,
        $tva_rate,
        $tva_comment,
        $tva_poste,
        $tva_both_side,
        $tva_code,
        $tva_reverse_account,
        $tva_peppol_code,
        $vx_code
            ;

    private $cn; //!< Database connection

    private Tva_Rate_SQL $tva_rate_sql;

    function __construct (Database $cn,$p_tva_id=-1)
    {
        $this->cn=$cn;
        $this->tva_rate_sql=new Tva_Rate_SQL($cn,$p_tva_id);
        $this->tva_id=$p_tva_id;
        $this->tva_label=&$this->tva_rate_sql->tva_label;
        $this->tva_rate=&$this->tva_rate_sql->tva_rate;
        $this->tva_comment=&$this->tva_rate_sql->tva_comment;
        $this->tva_poste=&$this->tva_rate_sql->tva_poste;
        $this->tva_both_side=&$this->tva_rate_sql->tva_both_side;
        $this->tva_code=&$this->tva_rate_sql->tva_code;
        $this->tva_reverse_account=&$this->tva_rate_sql->tva_reverse_account;
        $this->tva_peppol_code=&$this->tva_rate_sql->tva_peppol_code;
        $this->vx_code=&$this->tva_rate_sql->vx_code;

    }
    /**
     * @return Tva_Rate_SQL
     */
    public function getTvaRateSql(): Tva_Rate_SQL
    {
        return $this->tva_rate_sql;
    }

    /**
     * @param Tva_Rate_SQL $tva_rate_sql
     */
    public function setTvaRateSql(Tva_Rate_SQL $tva_rate_sql)
    {
        $this->tva_rate_sql = $tva_rate_sql;
        return $this;
    }

    public function get_parameter($p_string)
    {
        if ( array_key_exists($p_string,self::$variable) )
        {
            $idx=self::$variable[$p_string];
            return $this->$idx;
        }

        throw new Exception("ACC98"."Attribut inexistant $p_string",EXC_INVALID);
    }
    public function set_parameter($p_string,$p_value)
    {
        if ( array_key_exists($p_string,self::$variable) )
        {
            $idx=self::$variable[$p_string];
            $this->$idx=$p_value;
            }
        else
            throw new Exception("ACC108"."Attribut inexistant $p_string",EXC_INVALID);


    }

    /**
     *@brief Load the VAT, return 0 if the TVA_ID exists otherwise -1
     *@note if the label is not found then we get an message error, so the best is probably
     *to initialize the VAT object with default value
     *
     */
    public function load():int
    {
        $this->tva_rate_sql->setp("tva_id",$this->tva_id);

        if ( !  $this->tva_rate_sql->load() ) return -1;
        return 0;
    }
    /*!\brief get the account of the side (debit or credit)
     *\param $p_side is d or C
     *\return the account to use
     *\note call first load if tva_poste is empty
     */
    public function get_side($p_side)
    {
        if ( strlen($this->tva_poste??"") == 0 ) $this->load();
        list($deb,$cred)=explode(",",$this->tva_poste);
        switch ($p_side)
        {
        case 'd':
                return $deb;
            break;
        case 'c':
            return $cred;
            break;
        default:
            throw (new Exception (__FILE__.':'.__LINE__." param est d ou c, on a recu [ $p_side ]"));
        }
    }

    /**
     * @brief retrieve TVA rate thanks the code that could be the tva_id or tva_code. Check first if p_code is a
     * TVA_CODE and if not, check if it is a TVA_ID
     * @param $db Database connection
     * @param $p_code either tva_id or tva_code
     * @return Acc_Tva or Acc_TVA with tva_id=-1
     */
    static function build($db,$p_code):Acc_Tva {
        if (empty($p_code)) return new Acc_Tva($db,-1);
        $tva_id = $db->get_value("select tva_id from public.tva_rate where tva_code=upper(trim($1))",[$p_code]);
        if ( $db->size() == 1) {
            return new Acc_Tva($db,$tva_id);
        }
        if (isNumber($p_code) == 0) return new Acc_Tva($db,-1);
        $exist = $db->get_value("select count(*) from public.tva_rate where tva_id=$1",[$p_code]);
        if ( $exist == 1) {
            return new Acc_Tva($db,$p_code);
        }
        return new Acc_Tva($db,-1);

    }
}
