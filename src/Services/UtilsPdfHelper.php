<?php
/**
 * Created by PhpStorm.
 * User: techjini
 * Date: 08-06-2020
 * Time: 12:26
 */

namespace App\Services;


use App\Entity\Template;
use Dompdf\Dompdf;
use Dompdf\Options;
use phpDocumentor\Reflection\Types\Self_;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

class UtilsPdfHelper
{
    /*
     *validating the request
     */
    public static  function pdfRequestValidation($data){
        $message ="";
        if(!$data) {
            $message = "please pass the parameters to generatePdf";
        }
        if( !isset($data["template"])){
            $message.= "PDF Template name is missing";
        }
        if( !isset($data["modifiers"])){
            $message.= "PDF modifier json string is missing";
        }
        return $message;
    }

    /*
     * creating the pdf in public folder
     * return web file path
     */
    public static  function  pdfRequestProcess($template,$modifiers,$directory,$url){

        $pdfFileName = $template->getName().rand(100,10000).".pdf";
        $directory = $directory."pdf".date("Y_m_d");

        if($template instanceof Template){
            $pdfData= self::getPdfData($template->getContent(),$modifiers);
            // Configure Dompdf according to your needs
            $pdfOptions = new Options();
            $pdfOptions->set('isHtml5ParserEnabled', true);
            $dompdf = new Dompdf($pdfOptions);
            $dompdf->loadHtml($pdfData);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();// important function
            file_put_contents($directory."\\".$pdfFileName,$dompdf->output());//important function
            return urlencode($url.'/pdf/pdf'.date("Y_m_d").'/'.$pdfFileName);

        }
        return null;
    }

    public function getPdfData($content,$modifiers){
        $modifiers = json_decode($modifiers);
        $modifiers = (array) $modifiers;
        foreach ($modifiers as $string=>$replaceValue ){
            $value = isset($replaceValue)?$replaceValue:"";
            $content = str_replace($string,$value,$content);
        }
        return $content;
    }


}