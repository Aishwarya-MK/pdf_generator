<?php

namespace App\Controller;

use App\Entity\Template;
use App\Services\UtilsGeneralHelper;
use App\Services\UtilsPdfHelper;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class TemplateAdminController extends CRUDController
{

    public  function  createAction()
    {
        $request = $this->getRequest();
        $templateKey = 'edit';
        $this->admin->checkAccess('create');
        $class = new \ReflectionClass($this->admin->hasActiveSubClass() ? $this->admin->getActiveSubClass() : $this->admin->getClass());

        if ($class->isAbstract()) {
            return $this->renderWithExtraParams(
                '@SonataAdmin/CRUD/select_subclass.html.twig',
                [
                    'base_template' => $this->getBaseTemplate(),
                    'admin' => $this->admin,
                    'action' => 'create',
                ],
                null
            );
        }

        $newObject = $this->admin->getNewInstance();
        $preResponse = $this->preCreate($request, $newObject);
        if (null !== $preResponse) {
            return $preResponse;
        }
        $this->admin->setSubject($newObject);
        $form = $this->admin->getForm();
        $form->setData($newObject);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $isFormValid = $form->isValid();

            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                $submittedObject = $form->getData();
                $this->admin->setSubject($submittedObject);
                $this->admin->checkAccess('create', $submittedObject);

                if($submittedObject instanceof Template) {
                    $submittedObject->setCreatedAt(new \DateTime("now"));
                    $submittedObject->setUpdatedAt(new \DateTime("now"));
                    $template = $this->getDoctrine()->getRepository(Template::class)
                        ->findOneBy(['name' => $submittedObject->getName()]);
                    if ($template) {
                        $this->addFlash('sonata_flash_error', "Template name is already exist cannot use again");
                    } else {
                        $newObject = $this->admin->create($submittedObject);
                        $directory = $this->getParameter('project_directory'). DIRECTORY_SEPARATOR.'public';
                        $directory .= DIRECTORY_SEPARATOR.Template::PDFSTORAGE.DIRECTORY_SEPARATOR;
                        $url = $request->getSchemeAndHttpHost();
                        UtilsPdfHelper::previewPdfRequestProcess($newObject,$directory,$url);
                        $this->addFlash('sonata_flash_success', "template created successfully");
                        return $this->redirectTo($newObject);
                    }
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if ($this->isXmlHttpRequest() && null !== ($response = $this->handleXmlHttpRequestErrorResponse($request, $form))) {
                    return $response;
                }

                $this->addFlash('sonata_flash_error',"Template not able to create");
            } elseif ($this->isPreviewRequested()) {
                $previewObj = $form->getData();
                if($previewObj instanceof Template)
                    UtilsPdfHelper::previewPdf($previewObj->getContent(),$previewObj->getType());
                else
                    $this->addFlash('sonata_flash_error', "Something went wrong to create PDF");
            }
        }
        $formView = $form->createView();
        $this->setFormTheme($formView, $this->admin->getFormTheme());
        $template = $this->admin->getTemplate($templateKey);
        return $this->renderWithExtraParams($template, [
            'action' => 'create',
            'form' => $formView,
            'object' => $newObject,
            'objectId' => null,
        ], null);
    }

    /**
     * @param null $deprecatedId
     * @return JsonResponse|RedirectResponse|Response|null
     * @throws \Exception
     *
     */
    public function editAction($deprecatedId = null) // NEXT_MAJOR: Remove the unused $id parameter
    {

        if (isset(\func_get_args()[0])) {
            @trigger_error(
                sprintf(
                    'Support for the "id" route param as argument 1 at `%s()` is deprecated since sonata-project/admin-bundle 3.62 and will be removed in 4.0, use `AdminInterface::getIdParameter()` instead.',
                    __METHOD__
                ),
                E_USER_DEPRECATED
            );
        }
        // the key used to lookup the template
        $templateKey = 'edit';
        $request = $this->getRequest();
        $id = $request->get($this->admin->getIdParameter());
        $existingObject = $this->admin->getObject($id);

        if (!$existingObject) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        $this->checkParentChildAssociation($request, $existingObject);
        $this->admin->checkAccess('edit', $existingObject);
        $preResponse = $this->preEdit($request, $existingObject);
        if (null !== $preResponse) {
            return $preResponse;
        }
        $this->admin->setSubject($existingObject);
        $objectId = $this->admin->getNormalizedIdentifier($existingObject);
        $form = $this->admin->getForm();
        $form->setData($existingObject);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $isFormValid = $form->isValid();
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                $submittedObject = $form->getData();
                $this->admin->setSubject($submittedObject);
                if($submittedObject instanceof Template) {
                    $submittedObject->setUpdatedAt(new \DateTime("now"));
                    $template = $this->getDoctrine()->getRepository(Template::class)
                        ->findOneBy(['name' => $submittedObject->getName()]);
                    if (isset($template)&&($template->getId() != $submittedObject->getId())) {
                        $this->addFlash('sonata_flash_error', "This Template name is already used");
                    }else{
                        try {
                            $existingObject = $this->admin->update($submittedObject);
                            $directory = $this->getParameter('project_directory'). DIRECTORY_SEPARATOR.'public';
                            $directory .= DIRECTORY_SEPARATOR.Template::PDFSTORAGE.DIRECTORY_SEPARATOR;
                            $url = $request->getSchemeAndHttpHost();
                            UtilsPdfHelper::previewPdfRequestProcess($existingObject,$directory,$url);
                            if ($this->isXmlHttpRequest())
                                return $this->handleXmlHttpRequestSuccessResponse($request, $existingObject);
                            $this->addFlash('sonata_flash_success', "template updated succes");
                            return $this->redirectTo($existingObject);
                        } catch (ModelManagerException $e) {
                            $this->handleModelManagerException($e);

                            $isFormValid = false;
                        } catch (LockException $e) {
                            $this->addFlash('sonata_flash_error', "can not edit the  template");
                        }
                    }
                }
            }
            if (!$isFormValid) {
                if ($this->isXmlHttpRequest() && null !== ($response = $this->handleXmlHttpRequestErrorResponse($request, $form))) {
                    return $response;
                }
                $this->addFlash('sonata_flash_error',"Can not update the template");
            } elseif ($this->isPreviewRequested()) {
                $previewObj = $form->getData();
                if($previewObj instanceof Template)
                    UtilsPdfHelper::previewPdf($previewObj->getContent(), $previewObj->getType());
                else
                    $this->addFlash('sonata_flash_error', "Something went wrong to create PDF");
            }
        }
        $formView = $form->createView();
        $this->setFormTheme($formView, $this->admin->getFormTheme());
        $template = $this->admin->getTemplate($templateKey);
        return $this->renderWithExtraParams($template, [
            'action' => 'edit',
            'form' => $formView,
            'object' => $existingObject,
            'objectId' => $objectId,
        ], null);
    }

    private function checkParentChildAssociation(Request $request, $object): void
    {
        if (!$this->admin->isChild()) {
            return;
        }
        if (!$this->admin->getParentAssociationMapping()) {
            return;
        }
        $parentAdmin = $this->admin->getParent();
        $parentId = $request->get($parentAdmin->getIdParameter());
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyPath = new PropertyPath($this->admin->getParentAssociationMapping());

        if ($parentAdmin->getObject($parentId) !== $propertyAccessor->getValue($object, $propertyPath)) {
            // NEXT_MAJOR: make this exception
            @trigger_error(
                "Accessing a child that isn't connected to a given parent is"
                ." deprecated since sonata-project/admin-bundle 3.34 and won't be allowed in 4.0.",
                E_USER_DEPRECATED
            );
        }
    }
    private function setFormTheme(FormView $formView, ?array $theme = null): void
    {
        $twig = $this->get('twig');
        $twig->getRuntime(FormRenderer::class)->setTheme($formView, $theme);
    }


    private function handleXmlHttpRequestErrorResponse(Request $request, FormInterface $form): ?JsonResponse
    {
        if (!\in_array('application/json', $request->getAcceptableContentTypes(), true)) {
            @trigger_error('In next major version response will return 406 NOT ACCEPTABLE without `Accept: application/json`', E_USER_DEPRECATED);
            return null;
        }
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return $this->renderJson([
            'result' => 'error',
            'errors' => $errors,
        ], Response::HTTP_BAD_REQUEST);
    }

    private function handleXmlHttpRequestSuccessResponse(Request $request, $object): JsonResponse
    {
        if (!\in_array('application/json', $request->getAcceptableContentTypes(), true)) {
            @trigger_error('In next major version response will return 406 NOT ACCEPTABLE without `Accept: application/json`', E_USER_DEPRECATED);
        }
        return $this->renderJson([
            'result' => 'ok',
            'objectId' => $this->admin->getNormalizedIdentifier($object),
            'objectName' => "",
        ], Response::HTTP_OK);
    }

}
