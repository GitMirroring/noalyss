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

/*! \file
 * \brief Class Document corresponds to the table document
 */
/*! \brief Class Document corresponds to the table document
 */

class Document
{

    var $db;          /*!< $db Database connexion */
    var $d_id;        /*!< $d_id Document id */
    var $ag_id;       /*!< $ag_id action_gestion.ag_id (pk) */
    var $d_mimetype;  /*!< $d_mimetype  */
    var $d_filename;  /*!< $d_filename */
    var $d_lob;       /*!< $d_lob the oid of the lob */
    var $d_description;       /*!< Description of the file */
    var $d_number;    /*!< $d_number number of the document */
    var $md_id;       /*!< $md_id document's template */
    private $counter; /*!< counter for the items ( goods ) */

    /* Constructor
     * \param $p_cn Database connection
     */

    function __construct($p_cn, $p_d_id=0)
    {
        $this->db=$p_cn;
        $this->d_id=$p_d_id;

        // counter for MARCH_NEXT
        $this->counter=0;
    }

    /**
     * @brief insert a minimal document and set the d_id
     */
    function blank()
    {
        $this->d_id=$this->db->get_next_seq("document_d_id_seq");
        // affect a number
        $this->d_number=$this->db->get_next_seq("seq_doc_type_".$this->md_type);
        $sql='insert into document(d_id,ag_id,d_number) values($1,$2,$3)';

        $this->db->exec_sql($sql,
                array
            ($this->d_id,
            $this->ag_id,
            $this->d_number));
    }

    /**
     * @brief Insert the receipt number into the filename , each generated file
     * will have the name of the template (model) + receipt number)
     * @param type $pj the receipt number
     * @param type $filename the name of the file
     * @return string
     */
    function compute_filename($pj, $filename)
    {
        $pos_prefix=strrpos($filename, ".");
        if ($pos_prefix==0)
            $pos_prefix=strlen($filename);
        $filename_no=substr($filename, 0, $pos_prefix);
        $filename_suff=substr($filename, $pos_prefix, strlen($filename));
        
        foreach (array('/', '*', '<', '>', ';', ',', '\\', '.', ':', '(', ')', ' ', '[', ']',"'") as $i)
        {
            $pj=str_replace($i, "-", $pj);
            $filename_no=str_replace($i,"-",$filename_no);
        }
        
       
        $new_filename=strtolower($filename_no."-".$pj.$filename_suff);
        $pj=str_replace("---","-",$pj);
        $pj=str_replace("--","-",$pj);
        $new_filename=str_replace("---","-",$new_filename);
        $new_filename=str_replace("--","-",$new_filename);
        return $new_filename;
    }

    /*!
     * \brief Generate the document, Call $this-\>replace to replace
     *        tag by value
     * @param p_array contains the data normally it is the $_POST
     * @param $p_filename contains the new filename
     * \return an string : the url where the generated doc can be found, the name
     * of the file and his mimetype
     */

    function generate($p_array, $p_filename="")
    {
        try {
            // create a temp directory in /tmp to unpack file and to parse it
            $dirname=tempnam($_ENV['TMP'], 'doc_');


            unlink($dirname);
            mkdir($dirname);
            // Retrieve the lob and save it into $dirname
            $this->db->start();
            $dm_info="select md_name,md_type,md_lob,md_filename,md_mimetype
                     from document_modele where md_id=$1";
            $Res=$this->db->exec_sql($dm_info, [$this->md_id]);

            $row=Database::fetch_array($Res, 0);
            $this->d_lob=$row['md_lob'];
            $this->d_filename=$row['md_filename'];
            $this->d_mimetype=$row['md_mimetype'];
            $this->d_name=$row['md_name'];


            chdir($dirname);
            $filename=$row['md_filename'];
            $exp=$this->db->lo_export($row['md_lob'], $dirname.DIRECTORY_SEPARATOR.$filename);
            if ($exp===false)
            {
                record_log(sprintf('DOCUMENT.GENERATE.D1 ,  export failed %s %s',$dirname, $filename));
                throw new Exception(sprintf(_("Export a échoué pour %s"), $filename));
            }

            $type="n";
            // if the doc is a OOo, we need to unzip it first
            // and the name of the file to change is always content.xml
            if (strpos($row['md_mimetype'], 'vnd.oasis')!=0)
            {
                ob_start();
                $zip=new Zip_Extended;
                if ($zip->open($filename)===TRUE)
                {
                    $zip->extractTo($dirname.DIRECTORY_SEPARATOR);
                    $zip->close();
                }
                else
                {
                    record_log(sprintf('DOCUMENT.GENERATE.D2  unzip failed %s', $filename));
                    throw new Exception(sprintf(_("Décompression a échoué %s", $filename)));
                }

                // Remove the file we do  not need anymore
                unlink($filename);
                ob_end_clean();
                $file_to_parse="content.xml";
                $type="OOo";
            }
            else
            {
                $file_to_parse=$filename;
            }
            // affect a number
            $this->d_number=$this->db->get_next_seq("seq_doc_type_".$row['md_type']);

            // parse the document - return the doc number ?
            $this->parseDocument($dirname, $file_to_parse, $type, $p_array);

            $this->db->commit();
            // if the doc is a OOo, we need to re-zip it
            if (strpos($row['md_mimetype'], 'vnd.oasis')!=0)
            {
                ob_start();
                $zip=new Zip_Extended;
                $res=$zip->open($filename, ZipArchive::CREATE);
                if ($res!==TRUE)
                {
                    record_log(sprintf('DOCUMENT.GENERATE.D3  zip failed %s', $filename));
                    throw new Exception(_('Echec compression'));
                }
                $zip->add_recurse_folder($dirname.DIRECTORY_SEPARATOR);
                $zip->close();

                ob_end_clean();

                $file_to_parse=$filename;
            }
            if ($p_filename!="")
            {
                $this->d_filename=$this->compute_filename($p_filename, $this->d_filename);
            }
            $this->saveGenerated($dirname.DIRECTORY_SEPARATOR.$file_to_parse);
            // Invoice
            $href=http_build_query(array('gDossier'=>Dossier::id(), "d_id"=>$this->d_id, 'act'=>'RAW:document'));
            $ret='<A class="mtitle" HREF="export.php?'.$href.'">'._('Document').'</A>';

            return $ret;
        } catch (Exception $e) {
            record_log($e->getMessage());
            record_log($e->getTraceAsString());
            return span(_("Génération du document a échoué"),'class="notice"');
        }
    }

    /*! parseDocument
     * \brief This function parse a document and replace all
     *        the predefined tags by a value. This functions
     *        generate diffent documents (invoice, order, letter)
     *        with the info from the database
     *
     * \param $p_dir directory name
     * \param $p_file filename
     * \param $p_type For the OOo document the tag are &lt and &gt instead of < and >
     * \param $p_array variable from $_POST
     */

    function parseDocument($p_dir, $p_file, $p_type, $p_array)
    {

        /*!\note replace in the doc the tags by their values.
         *  - MY_*   table parameter
         *  - ART_VEN* table quant_sold for invoice
         *  - CUST_* table quant_sold and fiche for invoice
         *  - e_* for the invoice in the $_POST
         */
        // open the document
        $infile_name=$p_dir.DIRECTORY_SEPARATOR.$p_file;
        $h=fopen($infile_name, "r");

        // check if tmpdir exist otherwise create it
        $temp_dir=$_ENV['TMP'];
        if (is_dir($temp_dir)==false)
        {
            if (mkdir($temp_dir)==false)
            {
                $msg=sprintf(_("Ne peut pas créer le répertoire %s", $temp_dir));
                report_log("D221".$msg);
                throw new Exception($msg);
            }
        }
        // Compute output_name
        $output_name=tempnam($temp_dir, "gen_doc_");
        $output_file=fopen($output_name, "w+");
        // check if the opening is sucessfull
        if ($h===false)
        {
            $msg=sprintf(_("Ne peut pas ouvrir [%s] [%s]"), $p_dir, $p_file);
            report_log("D232".$msg);
            throw new Exception($msg);
        }
        if ($output_file==false)
        {
            $msg=sprintf(_("Ne peut pas ouvrir [%s] [%s]"), $p_dir, $output_name);
            record_log($msg);
            throw new Exception($msg);
        }
        // compute the regex
        if ($p_type=='OOo')
        {
            $regex="/=*&lt;&lt;[A-Z]+_*[A-Z]*_*[A-Z]*_*[A-Z]*_*[0-9]*&gt;&gt;/i";
            $lt="&lt;";
            $gt="&gt;";
        }
        else
        {
            $regex="/=*<<[A-Z]+_*[A-Z]*_*[A-Z]*_*[A-Z]*_*[0-9]*>>/i";
            $lt="<";
            $gt=">";
        }
        //read the file
        while (!feof($h))
        {
            // replace the tag
            $buffer=fgets($h);
            // search in the buffer the magic << and >>
            // while preg_match_all finds something to replace
            while (preg_match_all($regex, $buffer, $f)>0)
            {

                foreach ($f as $apattern)
                {

                    foreach ($apattern as $pattern)
                    {


                        $to_remove=$pattern;
                        // we remove the < and > from the pattern
                        $tag=str_replace($lt, '', $pattern);
                        $tag=str_replace($gt, '', $tag);


                        // if the pattern if found we replace it
                        $value=$this->replace($tag, $p_array);
                        if (strpos($value, 'ERROR')!=false)
                            $value="";
                        /*
                         * Change type of cell to numeric
                         *  allow numeric cel in ODT for the formatting and formula
                         */

                        $buffer=\Document::replace_value($buffer, $pattern, $value, 1, $p_type);
                    }
                }
            }
            // write into the output_file
            fwrite($output_file, $buffer);
        }
        fclose($h);
        fclose($output_file);
        if (($ret=copy($output_name, $infile_name))==FALSE)
        {
            $msg="D299 ".sprintf(_('Ne peut pas sauver [%s] vers [%s] code erreur = [%s]'), $output_name, $infile_name,
                            $ret);
            record_log($msg);
            throw new Exception($msg);
        }
    }

    /*! saveGenerated
     * \brief Save the generated Document
     * \param $p_file is the generated file
     *
     *
     * \return 0 if no error otherwise 1
     */

    function saveGenerated($p_file)
    {
        // We save the generated file
        $doc=new Document($this->db);
        $this->db->start();
        $this->d_lob=$this->db->lo_import($p_file);
        if ($this->d_lob==false)
        {
            echo "ne peut pas importer [$p_file]";
            return 1;
        }

        $sql="insert into document(ag_id,d_lob,d_number,d_filename,d_mimetype)
             values ($1,$2,$3,$4,$5)";

        $this->db->exec_sql($sql,
                array($this->ag_id,
            $this->d_lob,
            $this->d_number,
            $this->d_filename,
            $this->d_mimetype));
        $this->d_id=$this->db->get_current_seq("document_d_id_seq");
        // Clean the file
        unlink($p_file);
        $this->db->commit();
        return 0;
    }
    
    /**
     * @brief Download all documents in a ZIP files. The parameters is an array of Document, see 
     * DOcument::get_all
     * 
     * @param array of Document $aDocument
     * 
     * @see Document::get_all()
     */
    function download($aDocument)
    {
        
        if (empty($aDocument)||is_array($aDocument)==false)
        {
            throw new Exception("Document.download expects an array");
        }
        // make a temp folder
        $dirname=tempnam($_ENV['TMP'], 'document_dwnall');
        unlink($dirname);
        mkdir($dirname);

        // download each file into that folder
        $nb_document=count($aDocument);
        $nCopy=0;
        
        // start a transaction to be able to export LOB
        $this->db->start();
        for ($i=0; $i<$nb_document; $i++)
        {
            // check that aDocument elt is a document object
            if ( ! $aDocument[$i] instanceof  Document ) {
                throw new Exception("Document.download.2 element is not a document object");
            }
            $filename=$dirname.DIRECTORY_SEPARATOR.$aDocument[$i]->d_filename;
            // if file exists then add a number
            if (file_exists($filename))
            {

                while (true)
                {
                    $nCopy++;
                    $filename=$dirname.DIRECTORY_SEPARATOR.$nCopy."-".$aDocument[$i]->d_filename;
                    if (!file_exists($filename))
                    {
                        $nCopy=0;
                        break;
                    }
                } // end while true
            } // end if fileexist
            // export file
            $this->db->lo_export($aDocument[$i]->d_lob,$filename);
        } // end for $i 
        // make a large PDF and send it
        $zip=new Zip_Extended();
        $name="document-".date ("Ymd-His").".zip";
        if ( $zip->open($_ENV['TMP'].DIRECTORY_SEPARATOR.$name , ZipArchive::CREATE) != true)
        {
              die("Cannot create zip file");
        }
        $zip->add_recurse_folder($dirname . "/");
        $zip->close();
        // send it to stdout
        ini_set('zlib.output_compression', 'off');
        header("Pragma: public");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
        header("Cache-Control: must-revalidate");
        header('Content-type: zip/application');
        header('Content-Disposition: attachment;filename="'.$name.'"', FALSE);
        header("Accept-Ranges: bytes");
        $file=fopen($_ENV['TMP'].DIRECTORY_SEPARATOR.$name, 'r');
        while (!feof($file))
        {
            echo fread($file, 8192);
        }
        fclose($file);

        $this->db->commit();
        
    }

    /**
     * @brief upload a file into document
     *  all the needed data are in $_FILES we don't increment the seq
     * @param $p_file : array containing by default $_FILES
     *
     */
    function upload($p_ag_id)
    {
        // nothing to save
        if (sizeof($_FILES)==0)
            return;

        /* for several files  */
        /* $_FILES is now an array */
        // Start Transaction
        $this->db->start();
        $name=$_FILES['file_upload']['name'];
        for ($i=0; $i<sizeof($name); $i++)
        {
            $new_name=tempnam($_ENV['TMP'], 'doc_');
            // check if a file is submitted
            if (strlen($_FILES['file_upload']['tmp_name'][$i])!=0)
            {
                // upload the file and move it to temp directory
                if (move_uploaded_file($_FILES['file_upload']['tmp_name'][$i], $new_name))
                {
                    $oid=$this->db->lo_import($new_name);
                    // check if the lob is in the database
                    if ($oid==false)
                    {
                        $this->db->rollback();
                        return 1;
                    }
                }
                // the upload in the database is successfull
                $this->d_lob=$oid;
                $this->d_filename=$_FILES['file_upload']['name'][$i];
                $this->d_mimetype=$_FILES['file_upload']['type'][$i];
                $this->d_description=strip_tags($_POST['input_desc'][$i]);
                // insert into  the table
                $sql="insert into document (ag_id, d_lob,d_filename,d_mimetype,d_number,d_description)"
                        . " values ($1,$2,$3,$4,$5,$6)";
                $this->db->exec_sql($sql,
                        array($p_ag_id, $this->d_lob, $this->d_filename, $this->d_mimetype, 1, $this->d_description));
            }
        } /* end for */
        $this->db->commit();
    }

    /**
     * Copy a existing OID (LOB) into the table document
     * @note  use of global variable $cn which is the db connx to the current folder
     * @param type $p_ag_id Follow_Up::ag_id
     * @param type $p_lob oid of existing document
     * @param type $p_filename filename of existing document
     * @param type $p_mimetype mimetype of existing document
     * @param type $p_description Description of existing document (default empty)
     */
    static function insert_existing_document($p_ag_id, $p_lob, $p_filename, $p_mimetype, $p_description="")
    {
        global $cn;
        // insert into  the table
        $sql="insert into document (ag_id, d_lob,d_filename,d_mimetype,d_number,d_description) "
                . "values ($1,$2,$3,$4,$5,$6)";
        $cn->exec_sql($sql, array($p_ag_id, $p_lob, $p_filename, $p_mimetype, 1, $p_description));
    }

    /*! 
     * \brief create and compute a string for reference the doc <A ...>
     *
     * \return a string
     */

    function anchor()
    {
        if ($this->d_id==0)
            return '';
        $image='<IMG SRC="image/insert_table.gif" title="'.$this->d_filename.'" border="0">';
        $r="";
        $href=http_build_query(array('gDossier'=>Dossier::id(), "d_id"=>$this->d_id, 'act'=>'RAW:document'));

        $r='<A class="mtitle" HREF="export.php?'.$href.'">'.$image.'</A>';
        return $r;
    }

    /** Get
     * \brief send the document
     */
    function send()
    {
        // retrieve the template and generate document
        $this->db->start();
        $ret=$this->db->exec_sql(
                "select d_id,d_lob,d_filename,d_mimetype from document where d_id=$1", [$this->d_id]);
        if (Database::num_row($ret)==0)
        {
            return;
        }
        $row=Database::fetch_array($ret, 0);
        //the document  is saved into file $tmp
        $tmp=tempnam($_ENV['TMP'], 'document_');
        $this->db->lo_export($row['d_lob'], $tmp);
        $this->d_mimetype=$row['d_mimetype'];
        $this->d_filename=$row['d_filename'];

        // send it to stdout
        ini_set('zlib.output_compression', 'Off');
        header("Pragma: public");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
        header("Cache-Control: must-revalidate");
        header('Content-type: '.$this->d_mimetype);
        header('Content-Disposition: attachment;filename="'.$this->d_filename.'"', FALSE);
        header("Accept-Ranges: bytes");
        $file=fopen($tmp, 'r');
        while (!feof($file))
        {
            echo fread($file, 8192);
        }
        fclose($file);

        unlink($tmp);

        $this->db->commit();
    }

    /*!\brief get all the document of a given action
     * \param $ag_id the ag_id from action::ag_id (primary key)
     * \return an array of objects document or an empty array if nothing found
     */

    function get_all($ag_id)
    {
        $res=$this->db->get_array('select d_id, ag_id, d_lob, d_number, d_filename,'.
                ' d_mimetype,d_description from document where ag_id=$1', array($ag_id));
        $a=array();
        for ($i=0; $i<sizeof($res); $i++)
        {
            $doc=new Document($this->db);
            $doc->d_id=$res[$i]['d_id'];
            $doc->ag_id=$res[$i]['ag_id'];
            $doc->d_lob=$res[$i]['d_lob'];
            $doc->d_number=$res[$i]['d_number'];
            $doc->d_filename=$res[$i]['d_filename'];
            $doc->d_mimetype=$res[$i]['d_mimetype'];
            $doc->d_description=$res[$i]['d_description'];
            $a[$i]=clone $doc;
        }
        return $a;
    }
    

    /*!\brief Get  complete all the data member thx info from the database
     */

    function get()
    {
        $sql="select * from document where d_id=$1";
        $ret=$this->db->exec_sql($sql,[$this->d_id]);
        if (Database::num_row($ret)==0)
        {
            return;
        }
        $row=Database::fetch_array($ret, 0);
        $this->ag_id=$row['ag_id'];
        $this->d_mimetype=$row['d_mimetype'];
        $this->d_filename=$row['d_filename'];
        $this->d_lob=$row['d_lob'];
        $this->d_number=$row['d_number'];
        $this->d_description=$row['d_description'];
    }

    /*!
     * \brief replace the TAG by the real value, this value can be into
     * the database or in $_POST
     * The possible tags are
     *  - [CUST_NAME] customer's name
     *  - [CUST_ADDR_1] customer's address line 1
     *  - [CUST_CP] customer's ZIP code
     *  - [CUST_CO] customer's country
     *  - [CUST_CITY] customer's city
     *  - [CUST_VAT] customer's VAT
     *  - [MARCH_NEXT]   end this item and increment the counter $i
     *  - [DATE_LIMIT]
     *  - [VEN_ART_NAME]
     *  - [VEN_ART_PRICE]
     *  - [VEN_ART_QUANT]
     *  - [VEN_ART_TVA_CODE]
     *  - [VEN_ART_STOCK_CODE]
     *  - [VEN_HTVA]
     *  - [VEN_TVAC]
     *  - [VEN_TVA]
     *  - [TOTAL_VEN_HTVA]
     *  - [DATE_CALC]
     *  - [DATE]
     *  - [DATE_LIMIT]
     *  - [DATE_LIMIT_CALC]
     *  - [NUMBER]
     *  - [MY_NAME]
     *  - [MY_CP]
     *  - [MY_COMMUNE]
     *  - [MY_TVA]
     *  - [MY_STREET]
     *  - [MY_NUMBER]
     *  - [TVA_CODE]
     *  - [TVA_RATE]
     *  - [BON_COMMANDE]
     *  - [OTHER_INFO]
     *  - [CUST_NUM]
     *  - [CUST_BANQUE_NAME]
     *  - [CUST_BANQUE_NO]
     *  - [USER]
     *  - [REFERENCE]
     *  - [BENEF_NAME]
     *  - [BENEF_BANQUE_NAME]
     *  - [BENEF_BANQUE_NO]
     *  - [BENEF_ADDR_1]
     *  - [BENEF_CP]
     *  - [BENEF_CO]
     *  - [BENEF_CITY]
     *  - [BENEF_VAT]
     *  - [ACOMPTE]
     *  - [TITLE]
     *  - [DESCRIPTION]
     *  - [COMM_PAYMENT]
     *  - [LABELOP]
     *  - [COMMENT]
     *  - [DESCRIPTION]
     *  - [DOCUMENT_ID]
     *
     * \param $p_tag TAG
     * \param $p_array data from $_POST
     * \return String which must replace the tag
     */

    function replace($p_tag, $p_array)
    {
        global $g_parameter;
        $p_tag=strtoupper($p_tag);
        $p_tag=str_replace('=', '', $p_tag);
        $r="Tag inconnu";
        static $aComment=NULL;
        static $counter_comment=1; /* <! counter for the comment , skip the first one which is the descrition */

        static $aRelatedAction=NULL;
        static $counter_related_action=0; /* <! counter for the related action */

        static $aRelatedOperation=NULL;
        static $counter_related_operation=0; /* <! counter for the related operation */

        static $aFileAttached=NULL;
        static $counter_file=0; /* <! counter for the file */

        static $aOtherCard=NULL;
        static $counter_other_card=0; /* <! counter for the other card */

        static $aTag=NULL;
        static $counter_tag=0; /* <! counter for the tags */

        static $aParameterExtra=NULL; // Extra parameter for the company
        switch ($p_tag)
        {
            case 'DATE':
                $r=(isset($p_array['ag_timestamp']))?$p_array['ag_timestamp']:$p_array['e_date'];
                break;
            case 'DATE_CALC':
                $r=' ';
                // Date are in $p_array['ag_date']
                // or $p_array['e_date']
                if (isset($p_array['ag_timestamp']))
                {
                    $date=format_date($p_array['ag_timestamp'], 'DD.MM.YYYY', 'YYYY-MM-DD');
                    $r=$date;
                }
                if (isset($p_array['e_date']))
                {
                    $date=format_date($p_array['e_date'], 'DD.MM.YYYY', 'YYYY-MM-DD');
                    $r=$date;
                }
                return $r;
                break;
            //
            //  the company priv

            case 'MY_NAME':
                $r=$g_parameter->MY_NAME;
                break;
            case 'MY_CP':
                $r=$g_parameter->MY_CP;
                break;
            case 'MY_COMMUNE':
                $r=$g_parameter->MY_COMMUNE;
                break;
            case 'MY_TVA':
                $r=$g_parameter->MY_TVA;
                break;
            case 'MY_STREET':
                $r=$g_parameter->MY_STREET;
                break;
            case 'MY_NUMBER':
                $r=$g_parameter->MY_NUMBER;
                break;
            case 'MY_TEL':
                $r=$g_parameter->MY_TEL;
                break;
            case 'MY_FAX':
                $r=$g_parameter->MY_FAX;
                break;
            case 'MY_PAYS':
                $r=$g_parameter->MY_PAYS;
                break;
            
            

            // customer
            /* \note The CUST_* are retrieved thx the $p_array['tiers']
             * which contains the quick_code
             */
            case 'SOLDE':
                $tiers=new Fiche($this->db);
                $qcode=isset($p_array['qcode_dest'])?$p_array['qcode_dest']:$p_array['e_client'];
                $tiers->get_by_qcode($qcode, false);
                $p=$tiers->strAttribut(ATTR_DEF_ACCOUNT);
                $poste=new Acc_Account_Ledger($this->db, $p);
                $r=$poste->get_solde(' true');
                break;
            case 'CUST_NAME':
                $tiers=new Fiche($this->db);
                $qcode=isset($p_array['qcode_dest'])?$p_array['qcode_dest']:$p_array['e_client'];
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_NAME);
                break;
            case 'CUST_ADDR_1':
                $tiers=new Fiche($this->db);
                $qcode=isset($p_array['qcode_dest'])?$p_array['qcode_dest']:$p_array['e_client'];
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_ADRESS);

                break;
            case 'CUST_CP':
                $tiers=new Fiche($this->db);

                $qcode=isset($p_array['qcode_dest'])?$p_array['qcode_dest']:$p_array['e_client'];
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_CP);

                break;
            case 'CUST_CITY':
                $tiers=new Fiche($this->db);

                $qcode=isset($p_array['qcode_dest'])?$p_array['qcode_dest']:$p_array['e_client'];
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_CITY);

                break;

            case 'CUST_CO':
                $tiers=new Fiche($this->db);

                $qcode=isset($p_array['qcode_dest'])?$p_array['qcode_dest']:$p_array['e_client'];
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_PAYS);

                break;
            // Marchandise in $p_array['e_march*']
            // \see user_form_achat.php or user_form_ven.php
            case 'CUST_VAT':
                $tiers=new Fiche($this->db);

                $qcode=isset($p_array['qcode_dest'])?$p_array['qcode_dest']:$p_array['e_client'];
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_NUMTVA);
                break;
            case 'CUST_NUM':
                $tiers=new Fiche($this->db);
                $qcode=isset($p_array['qcode_dest'])?$p_array['qcode_dest']:$p_array['e_client'];
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_NUMBER_CUSTOMER);
                break;
            case 'CUST_BANQUE_NO':
                $tiers=new Fiche($this->db);
                $qcode=isset($p_array['qcode_dest'])?$p_array['qcode_dest']:$p_array['e_client'];
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_BQ_NO);
                break;
            case 'CUST_BANQUE_NAME':
                $tiers=new Fiche($this->db);
                $qcode=isset($p_array['qcode_dest'])?$p_array['qcode_dest']:$p_array['e_client'];
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_BQ_NAME);
                break;
            /* -------------------------------------------------------------------------------- */
            /* BENEFIT (fee notes */
            case 'BENEF_NAME':
                $tiers=new Fiche($this->db);
                $qcode=isset($p_array['qcode_benef'])?$p_array['qcode_benef']:'';
                if ($qcode=='')
                {
                    $r='';
                    break;
                }
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_NAME);
                break;
            case 'BENEF_ADDR_1':
                $tiers=new Fiche($this->db);
                $qcode=isset($p_array['qcode_benef'])?$p_array['qcode_benef']:'';
                if ($qcode=='')
                {
                    $r='';
                    break;
                }
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_ADRESS);

                break;
            case 'BENEF_CP':
                $tiers=new Fiche($this->db);

                $qcode=isset($p_array['qcode_benef'])?$p_array['qcode_benef']:'';
                if ($qcode=='')
                {
                    $r='';
                    break;
                }
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_CP);

                break;
            case 'BENEF_CITY':
                $tiers=new Fiche($this->db);

                $qcode=isset($p_array['qcode_benef'])?$p_array['qcode_benef']:'';
                if ($qcode=='')
                {
                    $r='';
                    break;
                }
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_CITY);

                break;

            case 'BENEF_CO':
                $tiers=new Fiche($this->db);

                $qcode=isset($p_array['qcode_benef'])?$p_array['qcode_benef']:'';
                if ($qcode=='')
                {
                    $r='';
                    break;
                }
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_PAYS);

                break;
            // Marchandise in $p_array['e_march*']
            // \see user_form_achat.php or user_form_ven.php
            case 'BENEF_VAT':
                $tiers=new Fiche($this->db);

                $qcode=isset($p_array['qcode_benef'])?$p_array['qcode_benef']:'';
                if ($qcode=='')
                {
                    $r='';
                    break;
                }
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_NUMTVA);
                break;
            case 'BENEF_NUM':
                $tiers=new Fiche($this->db);
                $qcode=isset($p_array['qcode_benef'])?$p_array['qcode_benef']:'';
                if ($qcode=='')
                {
                    $r='';
                    break;
                }
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_NUMBER_CUSTOMER);
                break;
            case 'BENEF_BANQUE_NO':
                $tiers=new Fiche($this->db);
                $qcode=isset($p_array['qcode_benef'])?$p_array['qcode_benef']:'';
                if ($qcode=='')
                {
                    $r='';
                    break;
                }
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_BQ_NO);
                break;
            case 'BENEF_BANQUE_NAME':
                $tiers=new Fiche($this->db);
                $qcode=isset($p_array['qcode_benef'])?$p_array['qcode_benef']:'';
                if ($qcode=='')
                {
                    $r='';
                    break;
                }
                $tiers->get_by_qcode($qcode, false);
                $r=$tiers->strAttribut(ATTR_DEF_BQ_NAME);
                break;

            // Marchandise in $p_array['e_march*']
            // \see user_form_achat.php or user_form_ven.php
            case 'NUMBER':
                $r=$this->d_number;
                break;
            case "DOCUMENT_ID":
                if (isset($p_array['ag_id']))
                    return $p_array['ag_id'];
                return "";
                break;

            case 'USER' :
                return $_SESSION[SESSION_KEY.'use_name'].', '.$_SESSION[SESSION_KEY.'use_first_name'];

                break;
            case 'REFERENCE':
                $act=new Follow_Up($this->db);
                $act->ag_id=$this->ag_id;
                $act->get();
                $r=$act->ag_ref;
                break;

            /*
             *  - [VEN_ART_NAME]
             *  - [VEN_ART_PRICE]
             *  - [VEN_ART_QUANT]
             *  - [VEN_ART_TVA_CODE]
             *  - [VEN_ART_STOCK_CODE]
             *  - [VEN_HTVA]
             *  - [VEN_TVAC]
             *  - [VEN_TVA]
             *  - [TOTAL_VEN_HTVA]
             *  - [DATE_LIMIT]
             *  - [DATE_PAID]
             */
            case 'DATE_LIMIT_CALC':
                if (isset($p_array["e_ech"]))
                    return format_date($p_array["e_ech"], 'DD.MM.YYYY', 'YYYY-MM-DD');
                if (isset($p_array["ech"]))
                    return format_date($p_array["ech"], 'DD.MM.YYYY', 'YYYY-MM-DD');
                if (isset($p_array["ag_remind_date"]))
                    return format_date($p_array["ag_remind_date"], 'DD.MM.YYYY', 'YYYY-MM-DD');
                return "";
                break;
            case 'DATE_LIMIT':
                if (isset($p_array["ech"]))
                    return $p_array["ech"];
                if (isset($p_array["e_ech"]))
                    return $p_array["e_ech"];
                if (isset($p_array["ag_remind_date"]))
                    return $p_array["ag_remind_date"];
                return "";
                break;
            case 'DATE_PAID':
                if ( isset ($p_array['jr_date_paid']) ) { return $p_array['jr_date_paid'];}
                break;
            case 'MARCH_NEXT':
                $this->counter++;
                $r='';
                break;

            case 'VEN_ART_NAME':
                // check if the march exists
                if (!isset($p_array["e_march".$this->counter]))
                    return "";
                // check that something is sold
                if ($p_array['e_march'.$this->counter.'_price']!=0&&$p_array['e_quant'.$this->counter]!=0)
                {
                    $f=new Fiche($this->db);
                    $f->get_by_qcode($p_array["e_march".$this->counter], false);
                    $r=$f->strAttribut(ATTR_DEF_NAME);
                }
                else
                    $r="";
                break;
            case 'VEN_ART_LABEL':
                $id='e_march'.$this->counter."_label";
                // check if the march exists

                if (!isset($p_array[$id])||(isset($p_array[$id])&&strlen(trim($p_array[$id]))==0))
                {
                    $id='e_march'.$this->counter;
                    // check if the march exists
                    if (!isset($p_array[$id]))
                        $r="";
                    else
                    {
                        // check that something is sold
                        if ($p_array['e_march'.$this->counter.'_price']!=0&&$p_array['e_quant'.$this->counter]!=0)
                        {
                            $f=new Fiche($this->db);
                            $f->get_by_qcode($p_array[$id], false);
                            $r=$f->strAttribut(ATTR_DEF_NAME);
                        }
                        else
                            $r="";
                    }
                }
                else
                    $r=$p_array[$id];
                break;
            case 'VEN_ART_STOCK_CODE':
                $id='e_march'.$this->counter;
                // check if the march exists
                if (!isset($p_array[$id]))
                    $r="";
                else
                {
                    // check that something is sold
                    if ($p_array['e_march'.$this->counter.'_price']!=0&&$p_array['e_quant'.$this->counter]!=0)
                    {
                        $f=new Fiche($this->db);
                        $f->get_by_qcode($p_array[$id], false);
                        $r=$f->strAttribut(ATTR_DEF_STOCK);
                        $r=($r==NOTFOUND)?'':$r;
                    }
                }
                break;
            case 'VEN_QCODE':
                $id='e_march'.$this->counter;
                if (!isset($p_array[$id]))
                    return "";
                return $p_array[$id];
                break;
            case 'VEN_ART_PRICE':
                $id='e_march'.$this->counter.'_price';
                if (!isset($p_array[$id]))
                    return "";
                if ($p_array[$id]==0)
                    return "";
                $r=$p_array[$id];
                break;

            case 'TVA_RATE':
            case 'VEN_ART_TVA_RATE':
                $id='e_march'.$this->counter.'_tva_id';
                if (!isset($p_array[$id]))
                    return "";
                if ($p_array[$id]==-1||$p_array[$id]=='')
                    return "";
                $march_id='e_march'.$this->counter.'_price';
                if (!isset($p_array[$march_id]))
                    return '';
                $tva=new Acc_Tva($this->db);
                $tva->set_parameter("id", $p_array[$id]);
                if ($tva->load()==-1)
                    return '';
                return $tva->get_parameter("rate");
                break;

            case 'TVA_CODE':
            case 'VEN_ART_TVA_CODE':
                $id='e_march'.$this->counter.'_tva_id';
                if (!isset($p_array[$id]))
                    return "";
                if ($p_array[$id]==-1)
                    return "";
                $qt='e_quant'.$this->counter;
                $price='e_march'.$this->counter.'_price';
                if ($p_array[$price]==0||$p_array[$qt]==0||strlen(trim($p_array[$price]))==0||strlen(trim($p_array[$qt]))==0)
                    return "";

                $r=$p_array[$id];
                break;

            case 'TVA_LABEL':
                $id='e_march'.$this->counter.'_tva_id';
                if (!isset($p_array[$id]))
                    return "";
                $march_id='e_march'.$this->counter.'_price';
                if (!isset($p_array[$march_id]))
                    return '';
                if ($p_array[$march_id]==0)
                    return '';
                $tva=new Acc_Tva($this->db, $p_array[$id]);
                if ($tva->load()==-1)
                    return "";
                $r=$tva->get_parameter('label');

                break;

            /* total VAT for one sold */
            case 'TVA_AMOUNT':
            case 'VEN_TVA':
                $qt='e_quant'.$this->counter;
                $price='e_march'.$this->counter.'_price';
                $tva='e_march'.$this->counter.'_tva_id';
                /* if we do not use vat this var. is not set */
                if (!isset($p_array[$tva]))
                    return '';
                if (!isset($p_array ['e_march'.$this->counter]))
                    return "";
                if (!isset($p_array[$tva]))
                    return "";
                // check that something is sold
                if ($p_array[$price]==0||$p_array[$qt]==0||strlen(trim($p_array[$price]))==0||strlen(trim($p_array[$qt]))==0)
                    return "";
                $r=$p_array['e_march'.$this->counter.'_tva_amount'];
                break;
            /* TVA automatically computed */
            case 'VEN_ART_TVA':

                $qt='e_quant'.$this->counter;
                $price='e_march'.$this->counter.'_price';
                $tva='e_march'.$this->counter.'_tva_id';
                if (!isset($p_array['e_march'.$this->counter]))
                    return "";
                if (!isset($p_array[$tva]))
                    return "";
                // check that something is sold
                if ($p_array[$price]==0||$p_array[$qt]==0||strlen(trim($p_array[$price]))==0||strlen(trim($p_array[$qt]))==0)
                    return "";
                $oTva=new Acc_Tva($this->db, $p_array[$tva]);
                if ($oTva->load()==-1)
                    return "";
                $r=round($p_array[$price], 2)*$oTva->get_parameter('rate');
                $r=round($r, 2);
                break;

            case 'VEN_ART_TVAC':
                $qt='e_quant'.$this->counter;
                $price='e_march'.$this->counter.'_price';
                if (!isset($p_array['e_march'.$this->counter]))
                    return "";
                if (!isset($p_array['e_march'.$this->counter.'_tva_id']))
                    return "";
                // check that something is sold
                if ($p_array[$price]==0||$p_array[$qt]==0||strlen(trim($p_array[$price]))==0||strlen(trim($p_array[$qt]))==0)
                    return "";
                if (!isset($p_array['e_march'.$this->counter.'_tva_id']))
                    return '';
                $tva=new Acc_Tva($this->db, $p_array['e_march'.$this->counter.'_tva_id']);
                if ($tva->load()==-1)
                {
                    $r=round($p_array[$price], 2);
                }
                else
                {
                    $r=round($p_array[$price]*$tva->get_parameter('rate')+$p_array[$price], 2);
                }

                break;

            case 'VEN_ART_QUANT':
                $id='e_quant'.$this->counter;
                if (!isset($p_array[$id]))
                    return "";
                // check that something is sold
                if ($p_array['e_march'.$this->counter.'_price']==0||$p_array['e_quant'.$this->counter]==0||strlen(trim($p_array['e_march'.$this->counter.'_price']))==0||strlen(trim($p_array['e_quant'.$this->counter]))==0)
                    return "";
                $r=$p_array[$id];
                break;

            case 'VEN_HTVA':
                $id='e_march'.$this->counter.'_price';
                $quant='e_quant'.$this->counter;
                if (!isset($p_array[$id]))
                    return "";

                // check that something is sold
                if ($p_array['e_march'.$this->counter.'_price']==0||$p_array['e_quant'.$this->counter]==0||strlen(trim($p_array['e_march'.$this->counter.'_price']))==0||strlen(trim($p_array['e_quant'.$this->counter]))==0)
                    return "";
                bcscale(4);
                $r=bcmul($p_array[$id], $p_array[$quant]);
                $r=round($r, 2);
                break;

            case 'VEN_TVAC':
                $id='e_march'.$this->counter.'_tva_amount';
                $price='e_march'.$this->counter.'_price';
                $quant='e_quant'.$this->counter;
                if (!isset($p_array['e_march'.$this->counter.'_price'])||!isset($p_array['e_quant'.$this->counter]))
                {
                    return "";
                }
                // check that something is sold
                if ($p_array['e_march'.$this->counter.'_price']==0||$p_array['e_quant'.$this->counter]==0)
                {
                    return "";
                }
                bcscale(4);
                // if TVA not exist
                if (!isset($p_array[$id]))
                    $r=bcmul($p_array[$price], $p_array[$quant]);
                else
                {
                    $r=bcmul($p_array[$price], $p_array[$quant]);
                    $r=bcadd($r, $p_array[$id]);
                }
                $r=round($r, 2);
                return $r;
                break;

            case 'TOTAL_VEN_HTVA':
                bcscale(4);
                $sum=0.0;
                if (!isset($p_array["nb_item"]))
                    return "";
                for ($i=0; $i<$p_array["nb_item"]; $i++)
                {
                    $sell='e_march'.$i.'_price';
                    $qt='e_quant'.$i;

                    if (!isset($p_array[$sell]))
                        break;

                    if (strlen(trim($p_array[$sell]))==0||
                            strlen(trim($p_array[$qt]))==0||
                            $p_array[$qt]==0||$p_array[$sell]==0)
                        continue;
                    $tmp1=bcmul($p_array[$sell], $p_array[$qt]);
                    $sum=bcadd($sum, $tmp1);
                }
                $r=round($sum, 2);
                break;
            case 'TOTAL_VEN_TVAC':
                if (!isset($p_array["nb_item"]))
                    return "";
                $sum=0.0;
                bcscale(4);
                for ($i=0; $i<$p_array["nb_item"]; $i++)
                {
                    $tva='e_march'.$i.'_tva_amount';
                    $tva_amount=0;
                    /* if we do not use vat this var. is not set */
                    if (isset($p_array[$tva]))
                    {
                        $tva_amount=$p_array[$tva];
                    }
                    $sell=$p_array['e_march'.$i.'_price'];
                    $qt=$p_array['e_quant'.$i];
                    $tot=bcmul($sell, $qt);
                    $tot=bcadd($tot, $tva_amount);
                    $sum=bcadd($sum, $tot);
                }
                $r=round($sum, 2);

                break;
            case 'TOTAL_TVA':
                if (!isset($p_array["nb_item"]))
                    return "";
                $sum=0.0;
                for ($i=0; $i<$p_array["nb_item"]; $i++)
                {
                    $tva='e_march'.$i.'_tva_amount';
                    if (!isset($p_array[$tva]))
                        $tva_amount=0.0;
                    else
                    {
                        $tva_amount=$p_array[$tva];
                        $tva_amount=($tva_amount=="")?0:$tva_amount;
                    }
                    $sum+=$tva_amount;
                    $sum=round($sum, 2);
                }
                $r=$sum;

                break;
            case 'BON_COMMANDE':
                if (isset($p_array['bon_comm']))
                    return $p_array['bon_comm'];
                else
                    return "";
                break;
            case 'PJ':
                if (isset($p_array['e_pj']))
                    return $p_array['e_pj'];
                else
                    return "";

            case 'OTHER_INFO':
                if (isset($p_array['other_info']))
                    return $p_array['other_info'];
                else
                    return "";
                break;
            case 'LABELOP':
                if (isset($p_array['e_comm']))
                    return $p_array['e_comm'];
                break;
            case 'ACOMPTE':
                if (isset($p_array['acompte']))
                    return $p_array['acompte'];
                return "0";
                break;
            case 'STOCK_NAME':
                if (!isset($p_array['repo']))
                    return "";
                $ret=$this->db->get_value('select r_name from public.stock_repository where r_id=$1',
                        array($p_array['repo']));
                return $ret;
            case 'STOCK_ADRESS':
                if (!isset($p_array['repo']))
                    return "";
                $ret=$this->db->get_value('select r_adress from public.stock_repository where r_id=$1',
                        array($p_array['repo']));
                return $ret;
            case 'STOCK_COUNTRY':
                if (!isset($p_array['repo']))
                    return "";
                $ret=$this->db->get_value('select r_country from public.stock_repository where r_id=$1',
                        array($p_array['repo']));
                return $ret;
            case 'STOCK_CITY':
                if (!isset($p_array['repo']))
                    return "";
                $ret=$this->db->get_value('select r_city from public.stock_repository where r_id=$1',
                        array($p_array['repo']));
                return $ret;
            case 'STOCK_PHONE':
                if (!isset($p_array['repo']))
                    return "";
                $ret=$this->db->get_value('select r_phone from public.stock_repository where r_id=$1',
                        array($p_array['repo']));
                return $ret;
            // Follow up
            //Title
            case 'TITLE':
                if (isset($p_array['ag_title']))
                    return $p_array['ag_title'];
                return "";
                break;
            // Description is the first comment
            case 'DESCRIPTION':
                if (isset($p_array['ag_id']))
                {
                    // retrieve first comment
                    $description=$this->db->get_value("select agc_comment "
                            ."  from action_gestion_comment "
                            ."where ag_id=$1 order by AGC_ID asc limit 1"
                            , [$p_array['ag_id']]);
                    return $description;
            	}
            if ( isset($p_array['e_comm'])) {return $p_array['e_comm'] ; }
            
            return "";
            break;
      

            // Comments, use a counter to move to the next comment, only for Follow-Up
            //
            case 'COMMENT':
                if (isset($p_array['ag_id']))
                {
                    // Static value, if null the retrieve all of them
                    if ($aComment==NULL)
                    {
                        // retrieve comments
                        $aComment=$this->db->get_array("select AGC_ID,agc_comment ,"
                                ." to_char(agc_date,'DD-MM-YY HH24:MI') as str_date ,"
                                ." tech_user "
                                ."  from action_gestion_comment "
                                ."where ag_id=$1 order by 1"
                                , [$p_array['ag_id']]);
                    }
                    $nb_comment=count($aComment);
                    $description="";
                    if (count($aComment)>$counter_comment)
                    {
                        $description.=sprintf(_('le %s , %s écrit %s'), $aComment[$counter_comment]['str_date'],
                                $aComment[$counter_comment]['tech_user'], $aComment[$counter_comment]['agc_comment']);
                        $counter_comment++;
                    }
                    return $description;
                }
                return "";
                break;
            // Related Action, use a counter to move to the next related action, only for Follow-Up
            //
            case 'RELATED_ACTION':
                if (isset($p_array['ag_id']))
                {
                    // Static value, if null the retrieve all of them
                    if ($aRelatedAction==NULL)
                    {
                        // retrieve parent
                        $followup=new Follow_Up($this->db, $p_array["ag_id"]);
                        $aRelatedAction=array();
                        $aParent=$followup->get_parent();
                        if ($aParent==-1)
                            return "";
                        $nb_parent=count($aParent);
                        $sql_related_action="
                 select ag_id,
                    f_id_dest,
                    (select ad_value from fiche_detail fd1 where fd1.ad_id=23 and fd1.f_id=f_id_dest) as qcode,
                    (select ad_value from fiche_detail fd1 where fd1.ad_id=1 and fd1.f_id=f_id_dest) as card_name,
                    (select ad_value from fiche_detail fd1 where fd1.ad_id=32 and fd1.f_id=f_id_dest) as card_fname,
                    ag_title,
                    to_char(ag_timestamp,'DD.MM.YYYY') as strdate,
                    ag_ref
                    from action_gestion ag where ag_id=$1 ";

                        for ($x=0; $x<$nb_parent; $x++)
                        {
                            $aRelatedAction[]=$this->db->get_row($sql_related_action, [$aParent[$x]['aga_least']]);
                            $aChild=$followup->get_children($aParent[$x]['aga_least']);
                            $nb_child=count($aChild);
                            for ($y=0; $y<$nb_child; $y++)
                            {
                                $aRelatedAction[]=$this->db->get_row($sql_related_action,
                                        [$aChild[$y]['aga_greatest']]);
                            }
                        }
                    }
                    $description="";
                    if (count($aRelatedAction)>$counter_related_action)
                    {
                        $description=sprintf("docid %s %s %s %s %s %s %s",
                                $aRelatedAction[$counter_related_action]['ag_id'],
                                $aRelatedAction[$counter_related_action]['ag_ref'],
                                $aRelatedAction[$counter_related_action]['strdate'],
                                $aRelatedAction[$counter_related_action]['qcode'],
                                $aRelatedAction[$counter_related_action]['card_fname'],
                                $aRelatedAction[$counter_related_action]['card_name'],
                                $aRelatedAction[$counter_related_action]['ag_title']
                        );

                        $counter_related_action++;
                    }
                    return $description;
                }
                return "";
                break;

            // Concerned operation, use a counter to move to the next one, only for Follow-Up
            //
            case 'CONCERNED_OPERATION':
                if (isset($p_array['ag_id']))
                {
                    // Static value, if null the retrieve all of them
                    if ($aRelatedOperation==NULL)
                    {
                        // retrieve comments
                        $aRelatedOperation=$this->db->get_array("select ago_id,
                        j.jr_id,
                        j.jr_internal,
                        j.jr_comment,
                        j.jr_pj_number,
                        to_char(j.jr_date,'DD.MM.YY') as str_date
			from jrn as j 
                        join action_gestion_operation as ago on (j.jr_id=ago.jr_id)
			where ag_id=$1 order by jr_date,jr_id"
                                , [$p_array['ag_id']]);
                    }
                    $description="";
                    if (count($aRelatedOperation)>$counter_related_operation)
                    {
                        $description.=sprintf('%s %s %s %s ',
                                $aRelatedOperation[$counter_related_operation]['str_date'],
                                $aRelatedOperation[$counter_related_operation]['jr_internal'],
                                $aRelatedOperation[$counter_related_operation]['jr_comment'],
                                $aRelatedOperation[$counter_related_operation]['jr_pj_number']
                        );
                        $counter_related_operation++;
                    }
                    return $description;
                }
                return "";
                break;
            // Other card, use a counter to move to the next one, only for Follow-Up
            //
            case 'OTHER_CARDS':
                if (isset($p_array['ag_id']))
                {
                    // Static value, if null the retrieve all of them
                    if ($aOtherCard==NULL)
                    {
                        // retrieve comments
                        $aOtherCard=$this->db->get_array("
                        select 
                        (select ad_value from fiche_detail where f_id = ap.f_id and ad_id = 1) as cname,
                        (select ad_value from fiche_detail where f_id = ap.f_id and ad_id = 32) as cfname,
                        (select ad_value from fiche_detail where f_id = ap.f_id and ad_id = 23) as qcode,
                        (select ad_value from fiche_detail where f_id = ap.f_id and ad_id = 18 ) as email,
                        (select ad_value from fiche_detail where f_id = ap.f_id and ad_id = 27 ) as mobile,
                        (select ad_value from fiche_detail where f_id = ap.f_id and ad_id = 17 ) as phone
                         from action_person ap  
                         where ag_id=$1
                            "
                                , [$p_array['ag_id']]);
                    }
                    $description="";
                    if (count($aOtherCard)>$counter_other_card)
                    {
                        $description.=sprintf('%s %s %s %s %s %s ', $aOtherCard[$counter_other_card]['cname'],
                                $aOtherCard[$counter_other_card]['cfname'], $aOtherCard[$counter_other_card]['qcode'],
                                $aOtherCard[$counter_other_card]['email'], $aOtherCard[$counter_other_card]['phone'],
                                $aOtherCard[$counter_other_card]['mobile']);
                        $counter_other_card++;
                    }
                    return $description;
                }
                return "";
                break;
            // Attachment , use a counter to move to the next one, only for Follow-Up
            //
            case 'ATTACHED_FILES':
                if (isset($p_array['ag_id']))
                {
                    // Static value, if null the retrieve all of them
                    if ($aFileAttached==NULL)
                    {
                        // retrieve comments
                        $aFileAttached=$this->db->get_array("
                        select d_filename,d_description from document d  where ag_id=$1
                            "
                                , [$p_array['ag_id']]);
                    }
                    $nb_comment=count($aFileAttached);
                    $description="";
                    if (count($aFileAttached)>$counter_file)
                    {
                        $description.=sprintf("%s %s ", $aFileAttached[$counter_file]['d_filename'],
                                $aFileAttached [$counter_file]['d_description']);
                        $counter_file++;
                    }
                    return $description;
                }
                return "";
                break;
            // Tag , use a counter to move to the next one
            case 'TAGS':
                if (isset($p_array['ag_id']))
                {
                    // Static value, if null the retrieve all of them
                    if ($aTag==NULL)
                    {
                        // retrieve comments
                        $aTag=$this->db->get_array("
                       select t_tag from action_tags at2 join tags t using(t_id)  where ag_id=$1 order by upper(t_tag)
                            "
                                , [$p_array['ag_id']]);
                    }
                    $description="";
                    if (count($aTag)>$counter_tag)
                    {
                        $description.=sprintf("%s ", $aTag [$counter_tag]['t_tag']);
                        $counter_tag++;
                    }
                    return $description;
                }
                
                return "";
                break;
            case 'COMM_PAYMENT':
                if (isset($p_array["e_comm_paiement"]))
                {
                    return $p_array["e_comm_paiement"];
                }
                else
                {
                    return "";
                }

            // priority of the follow up  document
            case 'PRIORITY':
                if (isset($p_array['ag_priority']))
                {
                    $aPriority=array(1=>_("Haute"), 2=>_("Normale"), 3=>_("Basse"));
                    return $aPriority[$p_array["ag_priority"]];
                }
                return "";
            // Priority of the follow up  document
            case 'GROUPMGT':
                if (isset($p_array['ag_dest']))
                {
                    $profile=$this->db->get_value("select p_name from profile where p_id=$1", array($p_array['ag_dest']));
                    return $profile;
                }
                return "";
            // Hour in the follow up document
            case 'HOUR':
                if (isset($p_array['ag_hour']))
                {
                    return $p_array["ag_hour"];
                }
                return "";
            // State in the follow up document
            case 'STATUS':
                if (isset($p_array['ag_state']))
                {
                    $status=$this->db->get_value("
                            select s_value from document_state where s_id=$1", array($p_array['ag_state']));
                    return $status;
                }
                return "";
            // type of document
            case 'DOCUMENT_TYPE':
                $ret="";
                if (isset($p_array['ag_id']))
                {
                    $ret=$this->db->get_value("select dt_value 
                                             from action_gestion 
                                             join document_type dt  on (ag_type=dt.dt_id) 
                            where ag_id=$1", array($p_array["ag_id"]));
                }
                return $ret;
           
        } // end switch 
        /*
         * retrieve the value of ATTR for e_march
         */
        if (preg_match('/^ATTR/', $p_tag)==1)
        {
            $r="";
            // Retrieve f_id
            if (isset($p_array['e_march'.$this->counter]))
            {
                $id=$p_array['e_march'.$this->counter];
                $r=$this->replace_special_tag($id, $p_tag);
                return $r;
            }
        }
        /*
         * retrieve the value of ATTR for e_march
         */
        if (preg_match('/^BENEFATTR/', $p_tag)==1)
        {
            $r="";
            $qcode=isset($p_array['qcode_benef'])?$p_array['qcode_benef']:'';
            // Retrieve f_id
            $r=$this->replace_special_tag($qcode, $p_tag);
                return $r;
        }
        if (preg_match('/^CUSTATTR/', $p_tag)==1)
        {
            $r="";
            if (isset($p_array['qcode_dest'])||isset($p_array['e_client']))
            {
                $qcode=(isset($p_array['qcode_dest']))?$p_array['qcode_dest']:$p_array['e_client'];
                $r=$this->replace_special_tag($qcode, $p_tag);
            }
                return $r;
        }
        
        // check also if the tag does exist in parameter_extra table
        $pe_value=$this->db->get_value("select pe_value from parameter_extra where pe_code=$1",[$p_tag]);
        if ( $this->db->count() > 0 ) { return $pe_value;}
        
        return $r;
    }

    /*!\brief remove a row from the table document, the lob object is not deleted
     *        because can be linked elsewhere
     */

    function remove()
    {
        $d_lob=$this->db->get_value('select d_lob from document where d_id=$1', array($this->d_id));
        $sql='delete from document where d_id='.$this->d_id;
        $this->db->exec_sql($sql);
        if ($d_lob!=0)
            $this->db->lo_unlink($d_lob);
    }

    /*!\brief Move a document from the table document into the concerned row
     *        the document is not copied : it is only a link
     *
     * \param $p_internal internal code
     *
     */

    function moveDocumentPj($p_internal)
    {
        $sql="update jrn set jr_pj=$1,jr_pj_name=$2,jr_pj_type=$3 where jr_internal=$4";

        $this->db->exec_sql($sql, array($this->d_lob, $this->d_filename, $this->d_mimetype, $p_internal));
        // clean the table document
        $sql='delete from document where d_id='.$this->d_id;
        $this->db->exec_sql($sql);
    }

    /**
     * replace a special tag *TAGxxxx with the value from fiche_detail, the xxxx
     * is the ad_value
     * @param $p_qcode qcode of the card
     * @param $p_tag tag to parse
     * @return  the ad_value contained in fiche_detail or for the type "select" the
     *          label
     */
    function replace_special_tag($p_qcode, $p_tag)
    {
        // check if the march exists
        if ($p_qcode=="")             return "";

        $f=new Fiche($this->db);
        $found=$f->get_by_qcode($p_qcode, false);
        // if not found exit
        if ($found==1)             return "";

        // get the ad_id
        $attr=preg_replace("/^.*ATTR/", "", $p_tag);

        if (isNumber($attr)==0)             return "";
        
        $ad_type=$this->db->get_value("select ad_type from attr_def where ad_id=$1", array($attr));

        // get ad_value
        $ad_value=$this->db->get_value("select ad_value from fiche_detail where f_id=$1 and ad_id=$2",
                array($f->id, $attr));

        // if ad_id is type select execute select and get value
        if ($ad_type=="select")
        {
            $sql=$this->db->get_value("select ad_extra from attr_def where ad_id=$1", array($attr));
            $array=$this->db->make_array($sql);
            for ($a=0; $a<count($array); $a++)
            {
                if ($array[$a]['value']==$ad_value)
                    return $array[$a]['label'];
            }
        }
        // if ad_id is not type select get value
        return $ad_value;
    }

    function update_description($p_desc)
    {
        $this->db->exec_sql('update document set d_description = $1 where d_id=$2', array($p_desc, $this->d_id));
    }

    /**
     * replace a pattern with a value in the buffer , handle the change for OOo type file and amount
     * 
     * @param string $p_buffer
     * @param string $_pattern
     * @param mixed $p_value
     */
    static function replace_value($p_buffer, $p_pattern, $p_value, $p_limit=-1, $p_type='OOo')
    {
        $check=$p_pattern;
        $check=str_replace(['&lt;', '&gt;', '<', '>', '='], "", $check);
        if (preg_replace('/[^[:alnum:]^_]/', '', $check)!=$check)
        {
            throw new Exception(sprintf(_("chaine à remplacer [%s] contient un caractère interdit"), $p_pattern));
        }
        $count=0;
        if (is_numeric($p_value)&&$p_type=='OOo')
        {
            /* -- works only with OOo Calc -- */
            $searched='/office:value-type="string"><text:p>'.$p_pattern.'/i';
            $replaced='office:value-type="float" office:value="'.$p_value.'"><text:p>'.$p_value;
            $p_buffer=preg_replace($searched, $replaced, $p_buffer, $p_limit, $count);
            if ($count==0)
            {
                /* -- work with libreOffice > 5 -- */
                $searched='/office:value-type="string" calcext:value-type="string"><text:p>(<text:s\/>)*'.$p_pattern.'/i';
                $replaced='office:value-type="float" office:value="'.$p_value.'"  calcext:value-type="float"><text:p>'.$p_value;
                $p_buffer=preg_replace($searched, $replaced, $p_buffer, $p_limit, $count);
            }
        }
        if ($count==0)
        {
            if ($p_type=='OOo')
            {
                $p_value=str_replace('&', '&amp;', $p_value);
                $p_value=str_replace('<', '&lt;', $p_value);
                $p_value=str_replace('>', '&gt;', $p_value);
                $p_value=str_replace('"', '&quot;', $p_value);
                $p_value=str_replace("'", '&apos;', $p_value);
            }
            $p_buffer=preg_replace('/'.$p_pattern.'/i', $p_value, $p_buffer, $p_limit);
        }
        return $p_buffer;
    }
    
    /**
     * @brief export the file to the file system and complet $this->d_mimetype, d_filename and 
     * @param string $p_destination_file path
     * @return bool false for failure and true for success
     */
    function export_file($p_destination_file)
    {
        if ($this->d_id==0) {
            return;
        }
         $this->db->start();
        $ret=$this->db->exec_sql(
                "select d_id,d_lob,d_filename,d_mimetype from document where d_id=$1", [$this->d_id]);
        if (Database::num_row($ret)==0)
        {
            return;
        }
        $row=Database::fetch_array($ret, 0);
        //the document  is saved into file $tmp
        $tmp=$p_destination_file;
        if ( $this->db->lo_export($row['d_lob'], $tmp) == true) { 
            $this->d_mimetype=$row['d_mimetype'];
            $this->d_filename=$row['d_filename'];
            $this->db->commit();
            return true;
        } else {
            $this->db->commit();
            return false;
            
        }

    }
    /**
     * @brief transform the current Document to a PDF, returns the full path of the PDF from the TMP folder
     * @return string full path to the PDF file
     */
    function transform2pdf()
    {
        if (GENERATE_PDF == 'NO' ) {
            \record_log(__FILE__."DOC37 : PDF not available");
            throw new \Exception("Cannot not transform to PDF");
        }
            // Extract from public.document
        $dirname=tempnam($_ENV['TMP'],"document");
        unlink($dirname);
        umask(0);
        mkdir($dirname);
        $destination_file=$dirname."/".$this->d_filename;
        $this->export_file($destination_file);
        
        passthru(OFFICE . escapeshellarg($destination_file), $status);
        if ($status != 0) {
            \record_log(__FILE__."DOC45 : Error  cannot transform into PDF");
            throw new \Exception("DOC45 Cannot not transform to PDF");
        }
        // remove extension
        $ext = strrpos($this->d_filename, ".");
        $pdf_file = substr($this->d_filename, 0, $ext);
        $pdf_file .=".pdf";
        return $dirname."/".$pdf_file;
    }
}
