<?php
/**
 * Created by PhpStorm.
 * User: techjini
 * Date: 07-06-2020
 * Time: 12:12
 */

namespace App\Services;


use Symfony\Component\HttpFoundation\Response;

class UtilsGeneralHelper
{
    public static function getReturnMessage($statusCode, $message)
    {
        $response = new Response();
        $response->setContent(json_encode([
            'data' => $message,
        ]));
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public static function getErrorMessage($statusCode, $message)
    {
        dump("ghjk");
        $response = new Response();
        $response->setContent($message);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public static function getValidationMessage($errors)
    {
        $messages = null;
        foreach ($errors as $violation) {
            $messages.= $violation->getMessage()."<br>";
        }
        return $messages;
    }


}