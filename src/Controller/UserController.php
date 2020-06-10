<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST","GET"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder,ValidatorInterface $validator)
    {
        $em = $this->getDoctrine()->getManager();
        $username = $request->get('username');
        $password = $request->get('password');
        $token = $request->get('access');
        if ($token != "9er4ymwuhFRkxr0jVG0E")
            return UtilsGeneralHelper::getErrorMessage(Response::HTTP_BAD_REQUEST, "Bad Request, Kindly Contact the Admin");
        $user = new User($username);
        $user->setPassword($encoder->encodePassword($user, $password));
        //dump($user);die;
        $errors =$validator->validate($user);
        if(count($errors) > 0){
            return UtilsGeneralHelper::getErrorMessage(Response::HTTP_PARTIAL_CONTENT, UtilsGeneralHelper::getValidationMessage($errors));
        }
        $em->persist($user);
        $em->flush();
        return UtilsGeneralHelper::getErrorMessage(Response::HTTP_OK, "User $username successfully created");
    }
}
