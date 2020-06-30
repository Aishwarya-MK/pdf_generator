<?php

namespace App\Controller;

use App\Entity\Template;
use App\Entity\User;
use App\Services\UtilsGeneralHelper;
use App\Services\UtilsPdfHelper;
use Dompdf\Dompdf;
use Dompdf\Options;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(){
        return new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));
    }

    /**
     * @Route("/test", name="test", methods={"GET"})
     */
    public function test()
    {
        $message = "Success";
        return UtilsGeneralHelper::getReturnMessage( Response::HTTP_ACCEPTED, $message);
    }

    /**
     * @Route("/api/test", name="test_jwt", methods={"GET"})
     */
    public function testJwt()
    {
        $message = "Success";
        return UtilsGeneralHelper::getReturnMessage( Response::HTTP_ACCEPTED, $message);
    }

    /**
     * @Route("/api/generate_pdf", name="generate_pdf", methods={"post"})
     * create a pdf file from template and stored in pdf folder
     */
    public function generatePDF(Request $Request)
    {
        $directory = $this->getParameter('project_directory'). DIRECTORY_SEPARATOR.'public';
        $directory .= DIRECTORY_SEPARATOR.Template::PDFSTORAGE.DIRECTORY_SEPARATOR;
        $url = $Request->getSchemeAndHttpHost();
        $data= $Request->request->all();
        try{
            $validMessage = UtilsPdfHelper::pdfRequestValidation($data);
            if($validMessage != null)
                return UtilsGeneralHelper::getErrorMessage(Response::HTTP_PARTIAL_CONTENT, $validMessage);
            $template = $this->getDoctrine()->getRepository(Template::class)->findOneBy(['id' => $data["template"]]);
            if(empty($template))
                return UtilsGeneralHelper::getErrorMessage(Response::HTTP_PARTIAL_CONTENT, "Template not found");
            $response = UtilsPdfHelper::pdfRequestProcess($template,$data["modifiers"],$directory,$url);
            if($response != null)
                return UtilsGeneralHelper::getReturnMessage( Response::HTTP_ACCEPTED, $response);
            return UtilsGeneralHelper::getReturnMessage( Response::HTTP_ACCEPTED, "Something went wrong");
        }
        catch (\Exception $e) {
            return UtilsGeneralHelper::getErrorMessage(Response::HTTP_NOT_FOUND, $e->getMessage());
        }
    }

    /**
     * @Route("/api/pdf_list", name="pdf_template_list")
     * get active template list
     */
    public function getTemplateList()
    {
        try{
            $templateList= array();
            $em = $this->getDoctrine()->getManager();
            $templateList= $em->getRepository(Template::class)->getActiveTemplateList(Template::IS_ACTIVE);
            return UtilsGeneralHelper::getReturnMessage( Response::HTTP_ACCEPTED, $templateList);
        }
        catch (\Exception $e) {
            return UtilsGeneralHelper::getErrorMessage(Response::HTTP_NOT_FOUND, $e->getMessage());
        }
    }

    /**
     * @Route("/api/preview_pdf/{id}", name="preview_pdf")
     * get pdf preview url
     */
    public function getPreviewPDFTemplate($id, Request $request)
    {
        try{
            $em = $this->getDoctrine()->getManager();
            $url = $request->getSchemeAndHttpHost();
            $template= $em->getRepository(Template::class)->findOneBy(['id'=> $id]);
            if(empty($template))
                return UtilsGeneralHelper::getErrorMessage(Response::HTTP_PARTIAL_CONTENT, "Template not found");
            $fileName = trim($template->getName())."-".$template->getId().".pdf";
            $url = urlencode($url.'/pdf/preview'.'/'.$fileName);
            return UtilsGeneralHelper::getReturnMessage( Response::HTTP_ACCEPTED, $url);
        }
        catch (\Exception $e) {
            return UtilsGeneralHelper::getErrorMessage(Response::HTTP_NOT_FOUND, $e->getMessage());
        }
    }
}
