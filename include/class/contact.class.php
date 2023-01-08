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
//!\brief class for the contact, contact are derived from fiche
require_once NOALYSS_INCLUDE.'/constant.php';
require_once NOALYSS_INCLUDE.'/lib/user_common.php';
/*! \file
 * \brief Contact are a card which are own by a another card (customer, supplier...)
 */
/*!
 * \brief Class contact (customer, supplier...)
 */

class contact extends Fiche
{
    private $filter;

    /*!\brief constructor */
    function __construct($p_cn,$p_id=0)
    {
        $this->fiche_def_ref=FICHE_TYPE_CONTACT;
        parent::__construct($p_cn,$p_id) ;
        $this->filter=[];
    }

    /**
     * @brief Build the SQL query thanks the parameter
     * @param array $array if empty , we use $this->filter , otherwise $array will override it
     * @return string
     */
    function build_sql($array)
    {
        if ( empty($array) ) $array=$this->filter;

        $sql_query='
        SELECT f_id,
               contact_fname, 
               contact_name, 
               contact_qcode, 
               contact_company, 
               contact_mobile, 
               contact_phone, 
               contact_email, 
               contact_fax
        FROM public.v_contact
        ';
        $where=' where ';$and='';
        if ( isset ($array['company'])) {
            $sql_query.=$where.sprintf(" contact_company ilike '%%%s%%'",
                    sql_string($array['company']));
            $where='';$and=' and ';
        }
        if ( isset($array['search'])) {
            $sql_query.=$where.$and.sprintf(" f_id in (select distinct f_id from fiche_detail where ad_value ilike '%%%s%%')",
                    sql_string($array['search']));
            $where='';$and=' and ';

        }
        if ( isset($array['category'])) {
            $sql_query.=$where.$and.sprintf(" fd_id = %s",
                    sql_string($array['category']));
            $where='';$and=' and ';
        }

        return $sql_query;
    }

    function filter_category($pn_category) {
        unset($this->filter['category']);
        if ( !empty($pn_category)
            && isNumber($pn_category)==1
            && $pn_category != -1){
            $this->filter['category']=$pn_category;
        }
    }
    function filter_company($p_company=null) {
        unset($this->filter['company']);
        if ( ! empty($p_company) && $p_company != "-1")  {
            $this->filter['company']= strtoupper($p_company);
        }
        return $this;
    }
    function filter_search($p_search="") {
        unset($this->filter['search']);
        if ( ! empty($p_search) ){
            $this->filter['search']=$p_search;
        }
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /*!
     * @brief display a summary of the contact card,
     *
     * @param  string p_search : filter on card name, if empty , contact->filter will be used
     * @param  string  p_action :  not used
     * @param  string p_sql : extra SQL command not used
     * @param  string  p_nothing (filter) not used
     *
     * @returns string to display
     */
    function Summary($p_search="",$p_action="",$p_sql="",$p_nothing=false)
    {
        $http=new HttpInput();
        if ( !empty ($p_search) ) { $this->filter_search($p_search);}

        $sql=$this->build_sql($this->filter);
        \Noalyss\Dbg::echo_var(1,"Contact::summary ($sql)");
        // Creation of the nav bar
        // Get the max numberRow
        $all_contact=$this->cn->get_value("select count(*) from ($sql) as m");
	    if ( $all_contact == 0 ) return "";

        // Get offset and page variable
        $offset=$http->request('offset','number',0);
        $page=$http->request('page','number',1);

        $bar=navigation_bar($offset,$all_contact,$_SESSION[SESSION_KEY.'g_pagesize'],$page);


        // Get The result Array
        $step_contact=$this->fetch($sql);


        $contact=$this;
        require NOALYSS_TEMPLATE.'/contact-summary.php';

    }

    /**
     * @brief Fetch all rows from view, ordered by  by contact_name, with offset and limit
     * @see Contact::build_sql()
     * @param $ps_string
     * @return array
     */
   private function fetch($ps_sql) {
        $http=new HttpInput();
        // Get offset and page variable
        $offset=$http->request('offset','number',0);
        $nb_pagesize=$_SESSION[SESSION_KEY.'g_pagesize'];
        $limit = '';
        $ps_sql.=' order by contact_name ';
        if ( $nb_pagesize <0 ) {$limit='';} else {
            $limit=sprintf(' limit %s offset %s',$nb_pagesize,$offset);
        }
        $ps_sql .=  $limit ;
        if (DEBUGNOALYSS > 1) {
            var_export("Contact::fetch ($ps_sql)");
        }
        return $this->cn->get_array($ps_sql);
    }
}
