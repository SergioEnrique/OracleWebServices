<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SoapService
{
    /**
     * Returns a hello world
     *
     * @param string $name
     * @return string the hello world
     */
    public function hello($name)
    {
        return "Hello $name!";
    }
}

$app->before(function () use ($app) {
    $app['base_url'] = $app['request_stack']->getCurrentRequest()->getScheme().'://'.$app['request_stack']->getCurrentRequest()->getHttpHost().$app['request_stack']->getCurrentRequest()->getBaseUrl();
    ini_set("soap.wsdl_cache_enabled", "0");
});

$app->match('/', function () use ($app) {
    $server = new Zend\Soap\Server($app['base_url'].'/wsdl');
    $server->setObject(new SoapService());
    $server->setReturnResponse(true);
    $response = $server->handle($app['request_stack']->getCurrentRequest()->getContent());
    return $response;
});

$app->get('/wsdl', function () use ($app) {
    $autodiscover = new Zend\Soap\AutoDiscover();
    $autodiscover->setClass('SoapService');
    $autodiscover->setUri($app['base_url']);
    $wsdl = $autodiscover->toXml();
    return new Response($wsdl, 200, array('Content-Type' => 'application/xml'));
});

$app->get('/client', function () use ($app) {
    //$oracleWebServiceWSDL = "https://caxj.crm.us2.oraclecloud.com/crmCommonSalesParties/SalesPartyService?WSDL";
    $client = new Zend\Soap\Client($app['base_url'].'/wsdl');
    return $client->hello("igor");
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
