<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app->before(function () use ($app) {
    $app['base_url'] = $app['request_stack']->getCurrentRequest()->getScheme().'://'.$app['request_stack']->getCurrentRequest()->getHttpHost().$app['request_stack']->getCurrentRequest()->getBaseUrl();
    ini_set("soap.wsdl_cache_enabled", "0");
    $app['serializer'] = Zend\Serializer\Serializer::factory('phpserialize');
});

$app->get('/client', function () use ($app) {
    //$oracleWebServiceWSDL = "https://caxj.crm.us2.oraclecloud.com/crmCommonSalesParties/SalesPartyService?WSDL";
    $oracleWebServiceWSDL = "http://www.webservicex.com/globalweather.asmx?WSDL";

    $options = array('CountryName' => 'Mexico', 'CityName' => 'Puebla');

    $client = new Zend\Soap\Client($oracleWebServiceWSDL);
    $clima = $client->GetWeather($options);

    //return new Response(get_class_vars($clima));
    return new Response($clima->GetWeatherResult, 200, array(
        "Content-Type" => "application/xml"
    ));
    /*
    return new Response($app['serializer']->serialize($clima->GetWeatherResult), 200, array(
        "Content-Type" => "application/json"
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
