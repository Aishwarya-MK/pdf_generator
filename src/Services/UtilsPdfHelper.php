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
use mysql_xdevapi\Exception;
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

        $directory = $directory."pdf".date("Y_m_d");
        $modifiers = json_decode($modifiers);
        $modifiers = (array)$modifiers;
        $name = $modifiers["STUDENT_NAME"]?str_replace(" ","",$modifiers["STUDENT_NAME"]): $template->getName();
        $applicationId = $modifiers["APPLICATION_NO"]? $modifiers["APPLICATION_NO"]: rand(100,10000);
        $pdfFileName =  $name."_".$applicationId.".pdf";//student name_application no
        if (!is_dir($directory))
            mkdir($directory);
        if($template instanceof Template){
            $pdfData= self::getPdfData($template->getContent(),$modifiers);

            $orientation = Template::PDF_PORTRAIT_VALUE;
            if($template->getType() == Template::PDF_LANDSCAPE)
                $orientation = Template::PDF_LANDSCAPE_VALUE;
            // Configure Dompdf according to your needs
            $pdfOptions = new Options();
            $pdfOptions->set('isHtml5ParserEnabled', true);
            $dompdf = new Dompdf($pdfOptions);
            $dompdf->loadHtml($pdfData);
            $dompdf->setPaper('A4', $orientation);
            $dompdf->render();// important function
            file_put_contents($directory."\\".$pdfFileName,$dompdf->output());//important function
            return urlencode($url.'/pdf/pdf'.date("Y_m_d").'/'.$pdfFileName);
        }
        return null;
    }

    public function getPdfData($content, $modifiers){
        $instalmentData = self::getInstallmentDetails($modifiers);
        $content = str_replace('{{INSTALLMENT_DETAILS}}', $instalmentData, $content);
        unset($modifiers['INSTALLMENT_OBJECT']);
        foreach ($modifiers as $string => $replaceValue) {
            $string ="{{".trim($string)."}}";
            $value = isset($replaceValue) ? $replaceValue : "";
            $content = str_replace($string, $value, $content);
        }
        return $content;
    }

    public static function previewPdf($content,$type){

        $orientation = Template::PDF_PORTRAIT_VALUE;
        if($type == Template::PDF_LANDSCAPE)
            $orientation = Template::PDF_LANDSCAPE_VALUE;
        // Configure Dompdf according to your needs
        $instalmentData = self::previewInstalment();
        $content = str_replace('{{INSTALLMENT_DETAILS}}', $instalmentData, $content);
        $pdfOptions = new Options();
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($content);
        $dompdf->setPaper('A4', $orientation);//landscape other option
        $dompdf->render();// important function
        $dompdf->stream("previewpdf.pdf", [
            "Attachment" => false
        ]);
    }

    public function previewInstalment(){
        return '<tr>
            <td align="center" style="padding: 5px; border-top: 1px solid black; border-left: 1px solid black; border-right: 1px solid black;">I</td>
            <td style="padding: 5px; border-top: 1px solid black; border-right: 1px solid black;">
                Admission Fee - Immediate
            </td>
            <td align="right" style="padding: 5px; border-top: 1px solid black; border-right: 1px solid black;">
                {{INSTALLMENT[0].TUTION_FEE}}
            </td>
            <td align="right" style="padding: 5px; border-top: 1px solid black; border-right: 1px solid black;">
                {{INSTALLMENT[0].TOTAL_FEE}}
            </td>
        </tr>
         <tr>
            <td align="center" style="padding: 5px; border-top: 1px solid black; border-left: 1px solid black; border-right: 1px solid black;">II</td>
            <td style="padding: 5px; border-top: 1px solid black; border-right: 1px solid black;">
                Before {{INSTALLMENT[1].DUE_DATE}}
            </td>
            <td align="right" style="padding: 5px; border-top: 1px solid black; border-right: 1px solid black;">
                {{INSTALLMENT[1].TUTION_FEE}}
            </td>
            <td align="right" style="padding: 5px; border-top: 1px solid black; border-right: 1px solid black;">
                {{INSTALLMENT[1].TOTAL_FEE}}
            </td>
        </tr>';
    }

    public function getInstallmentDetails($requestData){
        $installmentDetails="";
        if((array_key_exists('NUMBER_OF_INSTALMENTS',$requestData)) &&($requestData['NUMBER_OF_INSTALMENTS'] >0)){
            $installments=  $requestData['INSTALLMENT_OBJECT'];
            $i=1;
            foreach ($installments as $installment){
                $installment =(array) $installment;
                $installmentDetails .= '<tr>';
                $installmentDetails .= '<td align="center" style="padding: 5px; border-top: 1px solid black; border-left: 1px solid black; border-right: 1px solid black;">'
                                      .self::numberToRoman($i).'</td>';
                $installmentDetails .= '<td style="padding: 5px; border-top: 1px solid black; border-right: 1px solid black;">';
                if($i == 1)
                    $installmentDetails .= 'Admission Fee - Immediate';
                else
                    $installmentDetails .= 'Before '. (isset($installment['DUE_DATE'])?$installment['DUE_DATE']:'-');
                $installmentDetails .= '</td>';
                $installmentDetails .= '<td align="right" style="padding: 5px; border-top: 1px solid black; border-right: 1px solid black;">'.
                                        (isset($installment['TUTION_FEE'])?$installment['TUTION_FEE']:'xxxxx-').'</td>';
                $installmentDetails .= '<td align="right" style="padding: 5px; border-top: 1px solid black; border-right: 1px solid black;">'.
                                        (isset($installment['TOTAL_FEE'])?$installment['TOTAL_FEE']:'-').'</td>';
                $installmentDetails .= '</tr>';
                $i++;
            }
        }
        return $installmentDetails;
    }

    public function numberToRoman($number){
        // Be sure to convert the given parameter into an integer
        $n = intval($number);
        $result = '';
        $lookup = array(
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
            'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
            'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
        );
        foreach ($lookup as $roman => $value)
        {
            $matches = intval($n / $value);
            $result .= str_repeat($roman, $matches);
            $n = $n % $value;
        }
        return $result;
    }

    public static function deleteDirctory($dirPath){
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object !="..") {
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                        self::deleteDirctory($dirPath . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dirPath);
            return 1;
        }
        return 0;
    }

}