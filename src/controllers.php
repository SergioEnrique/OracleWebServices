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

$app->get('/client/services/{service}/params/{params}/', function ($service, $params) use ($app) {

    $params = json_decode($params);

    $urls = array(
        'SalesPartyService' => "https://caxj.crm.us2.oraclecloud.com/crmCommonSalesParties/SalesPartyService?WSDL",
        'globalweather' => "http://www.webservicex.com/globalweather.asmx?WSDL",
    );

    $options = array(

    );

    $client = new Client($urls[$service], $options);

    $clima = $client->GetWeather($params)->GetWeatherResult;

    return new Response($clima, 200, array(
        "Content-Type" => "application/xml"
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
