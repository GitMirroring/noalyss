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
/**
 * @brief Export DOCUMENT from Analytic accountancy, can transform into PDF
 * and add a stamp on each pages
 * 
 * It depends on PDFTK and CONVERT_GIF_PDF
 */
class Document_Export
{
    /**
     *@brief create 2 temporary folders, store_pdf and store_convert, initialize
     * an array feedback containing messages
     * 
     */
    function __construct()
    {
        // Create 2 temporary folders   1. convert to PDF + stamp
        //                              2. store result
        $this->feedback = array();
        $this->store_convert = tempnam($_ENV['TMP'], 'convert_');
        $this->store_pdf = tempnam($_ENV['TMP'], 'pdf_');
        unlink($this->store_convert);
        unlink($this->store_pdf);
        $this->progress=NULL;
        umask(0);
        if ( mkdir($this->store_convert) == FALSE )            
            throw new Exception(sprintf("Create %s failed",$this->store_onvert));
        if ( mkdir($this->store_pdf)== FALSE )            
            throw new Exception(sprintf("Create %s failed",$this->store_pdf));
    }
    /**
     * @brief concatenate all PDF into a single one and save it into the
     * store_pdf folder.
     * If an error occurs then it is added to feedback
     */
    function concatenate_pdf()
    {
        try
        {
            $this->check_file();
            $stmt=PDFTK." ".$this->store_pdf.'/*pdf  output '.$this->store_pdf.'/result.pdf';
            $status=0;
            echo $stmt;
            passthru($stmt, $status);

            if ($status<>0)
            {
                $cnt_feedback=count($this->feedback);
                $this->feedback[$cnt_feedback]['file']='result.pdf';
                $this->feedback[$cnt_feedback]['message']=' cannot concatenate PDF';
                $this->feedback[$cnt_feedback]['error']=$status;
            }
        }
        catch (Exception $exc)
        {
            $cnt_feedback=count($this->feedback);
            $this->feedback[$cnt_feedback]['file']=' ';
            $this->feedback[$cnt_feedback]['message']=$exc->getMessage();
            $this->feedback[$cnt_feedback]['error']=0;
        }
    }

    /**
     * Make a zip file
     */
    function make_zip()
    {
        $zip=new Zip_Extended();
        $res=$zip->open("{$this->store_pdf}/result.zip",ZipArchive::CREATE);
        if ($res !== true) {
            error_log("DE89 cannot create zip file");
            throw new Exception ( __FILE__.":".__LINE__."cannot recreate zip");
        }
        chdir($this->store_pdf);
        $zip->addGlob("*.pdf");
        $zip->close();

    }
    /**
     * copy the file
     * @param $p_source
     * @param $target
     * @throws Exception
     */

    function move_file($p_source, $p_target)
    {
        $this->check_file();
        $i=1;
        $target=$p_target;
        // do not overwrite a document already present
        while (file_exists($target)) {
            $target = sprintf("%d-%s",$i,$p_target);
            $i++;
        }
        copy($p_source, $this->store_pdf . '/' . $target);
    }
    /**
     * @brief send the resulting PDF to the browser
     */
    function send_pdf()
    {
        header('Content-Type: application/x-download');
        header('Content-Disposition: attachment; filename="result.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo file_get_contents($this->store_pdf . '/result.pdf');
    }
    /**
     * @brief send the resulting PDF to the browser
     */
    function send_zip()
    {
        header('Content-Type: application/x-download');
        header('Content-Disposition: attachment; filename="result.zip"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo file_get_contents($this->store_pdf . '/result.zip');
    }
    /**
     * @brief remove folder and its content
     */
    function clean_folder()
    {
        $files=  scandir($this->store_convert);
        $nb_file=count($files);
        for ($i=0;$i < $nb_file;$i++) {
            if (is_file($this->store_convert."/".$files[$i])) unlink($this->store_convert."/".$files[$i]);
        }
        rmdir($this->store_convert);
        $files=  scandir($this->store_pdf);
        $nb_file=count($files);
        for ($i=0;$i < $nb_file;$i++) {
            if (is_file($this->store_pdf."/".$files[$i])) unlink($this->store_pdf."/".$files[$i]);
        }
        rmdir($this->store_pdf);
        
    }

    /**
     * @brief export all the pieces in PDF and transform them into a PDF with
     * a stamp. If an error occurs then $this->feedback won't be empty
     * @param $p_array contents all the jr_id 
     * @param Progress_Bar $progress is the progress bar
     * @param int $p_separate 1 everything in a single PDF or a ZIP with all PDF
     * @param int $reconcilied_operation 1 with receipt of reconcilied operation 2 without them
     * 
     */
    function     export_all($p_array, Progress_Bar $progress,$p_separate=1,$reconcilied_document=2)
    {
        $this->progress=$this->progress;

        $this->check_file();
        if (count($p_array)==0)
            return;
        ob_start();
        $cnt_feedback=0;
        global $cn;
        
        $p_array=$this->reorder_array($p_array);
        $order=0;
        // follow progress
        $step=round(16/count($p_array), 2);

        foreach ($p_array as $value)
        {
            $progress->increment($step);

            $output_receipt=$this->export_receipt($value, $progress, $step);

            if ($output_receipt==NULL)
            {
                continue;
            }
            $output=$output_receipt['output'];
            $file_pdf=$output_receipt['filepdf'];

            // export also the receipt of reconcilied operation
            $a_reconcilied_operation=[];
            if ( $reconcilied_document == 1 ) {
                $a_reconcilied_operation=$cn->get_array("select jr_id,jra_concerned 
                     from jrn_rapt where jra_concerned=$1 or jr_id=$1", [$value]);
            }
            
            // for each reconcilied operation , export the receipt and concantenate
            foreach ($a_reconcilied_operation as $reconcilied_operation)
            {
                $op=($reconcilied_operation['jr_id']==$value)?$reconcilied_operation['jra_concerned']:$reconcilied_operation['jr_id'];

                $output_rec=$this->export_receipt($op, $progress, $step);
                if ($output_rec==NULL)
                {
                    continue;
                }
                // concatenate detail operation with the output
                $output3=$this->store_convert.'/tmp_operation_'.$file_pdf;

                $stmt=PDFTK." ".$output." ".$output_rec['output'].
                        ' output '.$output3;

                passthru($stmt, $status);
                if ($status<>0)
                {
                    $cnt_feedback=count($this->feedback);
                    $this->feedback[$cnt_feedback]['file']=$output3;
                    $this->feedback[$cnt_feedback]['message']=_('Echec  ');
                    $this->feedback[$cnt_feedback]['error']=$status;
                    $cnt_feedback++;
                    continue;
                }
                unlink($output_rec['output']);
                rename($output3, $output);
            }
            $progress->increment($step);

            // create the pdf with the detail of operation
            $detail_operation=new PDF_Operation($cn, $value);
            $detail_operation->export_pdf(array("acc", "anc"));

            // output 2
            $output2=$this->store_convert.'/operation_'.$file_pdf;

            // concatenate detail operation with the output
            $stmt=PDFTK." ".$detail_operation->get_pdf_filename()." ".$output.
                    ' output '.$output2;

            $progress->increment($step);
            passthru($stmt, $status);
            if ($status<>0)
            {
                $cnt_feedback=count($this->feedback);
                $this->feedback[$cnt_feedback]['file']=$output2;
                $this->feedback[$cnt_feedback]['message']=_('Echec Ajout detail ');
                $this->feedback[$cnt_feedback]['error']=$status;
                $cnt_feedback++;
                continue;
            }
            // remove doc with detail
            $detail_operation->unlink();

            // overwrite old with new PDF
            rename($output2, $output);

            // Move the PDF into another temp directory 
            $this->move_file($output, $file_pdf);
            $order++;
        }

        $progress->set_value(93);

        if ($p_separate==1)
        {
            // concatenate all pdf into one
            $this->concatenate_pdf();

            ob_clean();
            $this->send_pdf();
        }
        else
        {
            // Put all PDF In a zip file
            $this->make_zip();
            ob_clean();
            $this->send_zip();
        }

        $progress->set_value(100);
        // remove files from "conversion folder"
        //  $this->clean_folder();
    }

    /**
    * @brief check that the files are installed
    * throw a exception if one is missing
    */
    function check_file()
    {
        try 
        {
            if (CONVERT_GIF_PDF == 'NOT') throw new Exception(_("CONVERT_GIF_PDF n'est pas installé"));
            if (PDFTK          == 'NOT')  throw new Exception(_("TKPDF n'est pas installé"));
            if ( FIX_BROKEN_PDF == 'YES') {
                if (PS2PDF == 'NOT')    throw new Exception(_('PS2PDF non installé'));
                if (PDF2PS == 'NOT')    throw new Exception(_('PDF2PS non installé'));
            }
        } catch (Exception $ex) 
        {
            throw ($ex);
        }
    }

    /**
     * @brief export a file (
     * @param type $p_jrn_id
     * @param $progress
     * @return string
     */
    function export_receipt($p_jrn_id, Progress_Bar $progress,$step)
    {
        global $cn;
       $cnt_feedback=count($this->feedback);
        
        // For each file save it into the temp folder,
        $file=$cn->get_array('select jr_pj,jr_pj_name,jr_pj_number,jr_pj_type from jrn '
                .' where jr_id=$1', array($p_jrn_id));
         
         
        if ($file[0]['jr_pj']=='')
        {
            return null;
        }


        $filename=clean_filename($file[0]['jr_pj_name']);
        $receipt=clean_filename($file[0]['jr_pj_number']);
        $receipt=noalyss_str_replace('.','-',$receipt);
        $filename=$receipt.'-'.$filename;

        $cn->start();
        $cn->lo_export($file[0]['jr_pj'], $this->store_convert.'/'.$filename);
        $cn->commit();
        
        // Convert this file into PDF 
        if ($file[0]['jr_pj_type']!='application/pdf')
        {
            $status=0;
            $arg=" ".escapeshellarg($this->store_convert.DIRECTORY_SEPARATOR.$filename);
            echo "arg = [".$arg."]";
            passthru(OFFICE." ".$arg, $status);
            if ($status<>0)
            {
                $this->feedback[$cnt_feedback]['file']=$filename;
                $this->feedback[$cnt_feedback]['message']=' cannot convert to PDF';
                $this->feedback[$cnt_feedback]['error']=$status;
                return null;
            }
        }
        // Create a image with the stamp + formula
        $img=imagecreatefromgif(NOALYSS_INCLUDE.'/template/template.gif');
        $font=imagecolorallocatealpha($img, 100, 100, 100, 110);
        imagettftext($img, 40, 25, 500, 1000, $font,
                NOALYSS_INCLUDE.'/tfpdf/font/unifont/DejaVuSans.ttf'
                , _("Copie certifiée conforme à l'original"));
        imagettftext($img, 40, 25, 550, 1100, $font,
                NOALYSS_INCLUDE.'/tfpdf/font/unifont/DejaVuSans.ttf'
                , $file[0]['jr_pj_number']);
        imagettftext($img, 40, 25, 600, 1200, $font,
                NOALYSS_INCLUDE.'/tfpdf/font/unifont/DejaVuSans.ttf'
                , $file[0]['jr_pj_name']);
        imagegif($img, $this->store_convert.'/'.'stamp.gif');

        // transform gif file to pdf with convert tool
        $stmt=CONVERT_GIF_PDF." ".escapeshellarg($this->store_convert.'/'.'stamp.gif')." "
                .escapeshellarg($this->store_convert.'/stamp.pdf');
        passthru($stmt, $status);
        if ($status<>0)
        {
            $this->feedback[$cnt_feedback]['file']='stamp.pdf';
            $this->feedback[$cnt_feedback]['message']=' cannot convert to PDF';
            $this->feedback[$cnt_feedback]['error']=$status;
            return null;
        }


        $progress->increment($step);
        // 
        // remove extension
        $ext=strrpos($filename, ".");
        $file_pdf=substr($filename, 0, $ext);
        $file_pdf.=".pdf";

        //-----------------------------------
        // Fix broken PDF , actually pdftk can not handle all the PDF
        if (FIX_BROKEN_PDF=='YES'&&PDF2PS!='NOT'&&PS2PDF!='NOT')
        {

            $stmpt=PDF2PS." ".escapeshellarg($this->store_convert.'/'.$file_pdf).
                    " ".escapeshellarg($this->store_convert.'/'.$file_pdf.'.ps');

            passthru($stmpt, $status);

            if ($status<>0)
            {
                $this->feedback[$cnt_feedback]['file']=$this->store_convert.'/'.$file_pdf;
                $this->feedback[$cnt_feedback]['message']=' cannot force to PDF';
                $this->feedback[$cnt_feedback]['error']=$status;
                $cnt_feedback++;
                return null;
            }
            $stmpt=PS2PDF." ".escapeshellarg($this->store_convert.'/'.$file_pdf.'.ps').
                    " ".escapeshellarg($this->store_convert.'/'.$file_pdf.'.2');

            passthru($stmpt, $status);

            if ($status<>0)
            {
                $this->feedback[$cnt_feedback]['file']=$this->store_convert.'/'.$file_pdf;
                $this->feedback[$cnt_feedback]['message']=' cannot force to PDF';
                $this->feedback[$cnt_feedback]['error']=$status;
                $cnt_feedback++;
                return null;
            }
            rename($this->store_convert.'/'.$file_pdf.'.2', $this->store_convert.'/'.$file_pdf);
        }
        $progress->increment($step);
        // output
        $output=$this->store_convert.'/stamp_'.$file_pdf;

        // Concatenate stamp + file
        $stmt=PDFTK." ".escapeshellarg($this->store_convert.'/'.$file_pdf)
                .' stamp '.$this->store_convert.
                '/stamp.pdf output '.$output;

        passthru($stmt, $status);
        if ($status<>0)
        {

            $this->feedback[$cnt_feedback]['file']=$file_pdf;
            $this->feedback[$cnt_feedback]['message']=_(' ne peut pas convertir en PDF');
            $this->feedback[$cnt_feedback]['error']=$status;
            return null;
        }
        return array("output"=>$output,"filepdf"=>$file_pdf);
    }
    /**
     * @brief Order the array with the date
     * @param array $p_array array of jrn.jr_id
     */
    function reorder_array($p_array)
    {
        global $cn;
        if (empty($p_array)) {return array();}
        
        $list_jrn_id=join(',', $p_array);
        
        $array=$cn->get_array("select jr_id ,jr_date from jrn where jr_id in ($list_jrn_id) order by jr_date");
        $array=array_column($array, 'jr_id');
        return $array;
    }
}
