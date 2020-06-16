<?php
/**
 * Created by PhpStorm.
 * User: techjini
 * Date: 09-06-2020
 * Time: 21:01
 */

namespace App\Admin;


use phpDocumentor\Reflection\Types\Boolean;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TemplateAdmin extends AbstractAdmin
{
    public $supportsPreviewMode = true;

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class,
                array("label"=>"Template Name", 'attr' => ['maxlength' => 30, 'style' => 'width:50%']))
            ->add('modifiers', TextareaType::class,
                array("label"=>"Modifiers", "required"=>false, 'attr' => ['placeholder'=> 'ex: NAME,EMAIL']))
            ->add('content', TextareaType::class,
                array("label"=>"PdF Content", "required"=>false, 'attr' => ['class' => 'tinymce','placeholder'=> 'HTML format']))
            ->add('isActive', CheckboxType::class,
            array("label"=>"Enable","required"=>false,'attr' => ['style' => 'margin-left: 55px;']))
            ->add('type', ChoiceType::class,
                array("label"=>"Pdf orientation type",  "required"=>false,'attr' => ['style' => 'width:130px'],
                    'choices' => ["portrait"=>false,"landscape"=>true ],'placeholder' => false))
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name');
        $datagridMapper->add('modifiers');
        $datagridMapper->add('isActive');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('name');
        $listMapper->addIdentifier('modifiers');
        $listMapper->addIdentifier('isActive');
        $listMapper->addIdentifier('createdAt');
    }

    //removed delete functionalities from the admin panel
    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection->remove('delete');
    }



}