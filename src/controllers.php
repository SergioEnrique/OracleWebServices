<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Zend\Soap\Client;

$app->register(new Silex\Provider\SerializerServiceProvider());

$app->before(function () use ($app) {
    $app['base_url'] = $app['request_stack']->getCurrentRequest()->getScheme().'://'.$app['request_stack']->getCurrentRequest()->getHttpHost().$app['request_stack']->getCurrentRequest()->getBaseUrl();
    ini_set("soap.wsdl_cache_enabled", "0");
    $app['zendSerializer'] = Zend\Serializer\Serializer::factory('phpserialize');
});

$app->get('/client/services/{service}/operations/{operation}/', function (Request $request, $service, $operation) use ($app) {

    $username = "RORTIZ"; // Producción
    //$username = "RORTIZ"; // Desarrollo
    $password = "Medix2015";

    // Convertir la informacion (content del archivo) a un array para mandarlo al cliente soap
    $params = json_decode($request->getContent(), true);

    $urls = array(
        'salespartyservice' => "https://caxj.crm.us2.oraclecloud.com/crmCommonSalesParties/SalesPartyService?WSDL",
        'contactservice' => 'https://cbfa.crm.us2.oraclecloud.com/crmCommonSalesParties/ContactService?WSDL',
        'globalweather' => "http://www.webservicex.com/globalweather.asmx?WSDL",
    );

    $options = array(
        "login" => $username,
        "password" => $password,
        "soap_version" => SOAP_1_1,
        "streamContext" => stream_context_create(
        array(
          'http' => array(
            'protocol_version' => '1.0'
          )
        )
        ),
        # WSDL_CACHE_NONE | WSDL_CACHE_MEMORY : Guarda el resulta en cache por default
        "cacheWsdl" => WSDL_CACHE_NONE
    );

    $client = new Client($urls[$service], $options);

    switch ($operation) {
        case 'GetWeather':
            $response = $client->GetWeather($params);
            break;
        case 'createContact':
            $response = $client->createContact($params);
            break;
        case 'createPersonParty':
            $response = $client->createPersonParty($params);
            break;
        default:
            $response = array("Operacion no existente en el controlador");
            break;
    }

    return new Response($app['zendSerializer']->serialize($response), 200, array(
        "Content-Type" => "application/json"
    ));

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