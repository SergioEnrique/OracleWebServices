<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Zend\Soap\Client;

$app->before(function () use ($app) {
    $app['base_url'] = $app['request_stack']->getCurrentRequest()->getScheme().'://'.$app['request_stack']->getCurrentRequest()->getHttpHost().$app['request_stack']->getCurrentRequest()->getBaseUrl();
    ini_set("soap.wsdl_cache_enabled", "0");
    $app['serializer'] = Zend\Serializer\Serializer::factory('phpserialize');
});

$app->get('/client', function () use ($app) {

    $url = "https://caxj.crm.us2.oraclecloud.com/crmCommonSalesParties/SalesPartyService?WSDL";

    $options = array(
        'login' => 'jgonzalez',
        'password' => 'Medix2015',
        'local_cert' => dirname(__FILE__).'/../web/certificate.crt',
    );

    $client = new Client($url, $options);


    $result = $client->MethodNameIsIgnored();

    return new Response($result, 200, array(
        "Content-Type" => "application/xml"
    ));

    //$personParty = $client->getSalesAccount();

    return new Response($app['serializer']->serialize($personParty), 200, array(
        "Content-Type" => "application/json"
    ));

    /*

    $options = array(
                //'trace' => 1,
                //'location' => 'https://caxj.crm.us2.oraclecloud.com/crmCommonSalesParties/SalesPartyService',
            );

    $client = new StupidWrapperForOracleServer($oracleWebServiceWSDL, $options);
    $client->setHttpLogin('prueba');
    $client->setHttpPassword('aN1m4La');

    $personParty = $client->getSalesAccount();

    return new Response($app['serializer']->serialize($personParty), 200, array(
        "Content-Type" => "application/json"
    ));

    /*
    return new Response($app['serializer']->serialize($clima->GetWeatherResult), 200, array(
        "Content-Type" => "application/json"
    ));

    return new Response($clima->GetWeatherResult, 200, array(
        "Content-Type" => "application/xml"
    ));

    return $client->createPersonPartyAsyncResponse();
    */
});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
