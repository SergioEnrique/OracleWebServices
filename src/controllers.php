<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Zend\Soap\Client;

class StupidWrapperForOracleServer extends SoapClient {
    protected function callCurl($url, $data, $action) {
        $handle   = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml", 'SOAPAction: "' . $action . '"'));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($handle, CURLOPT_SSLVERSION, 2);
        $response = curl_exec($handle);
        if (empty($response)) {
            throw new SoapFault('CURL error: '.curl_error($handle),curl_errno($handle));
        }
        curl_close($handle);
        return $response;
    }

    public function __doRequest($request,$location,$action,$version,$one_way = 0) {
        return $this->callCurl($location, $request, $action);
    }
}

$app->before(function () use ($app) {
    $app['base_url'] = $app['request_stack']->getCurrentRequest()->getScheme().'://'.$app['request_stack']->getCurrentRequest()->getHttpHost().$app['request_stack']->getCurrentRequest()->getBaseUrl();
    ini_set("soap.wsdl_cache_enabled", "0");
    $app['serializer'] = Zend\Serializer\Serializer::factory('phpserialize');
});

$app->get('/client', function () use ($app) {

    $oracleWebServiceWSDL = "https://caxj.crm.us2.oraclecloud.com/crmCommonSalesParties/SalesPartyService?WSDL";
    //$oracleWebServiceWSDL = "https://webtjener09.kred.no/TestWebservice/OppdragServiceSoapHttpPort?WSDL";

//    file_put_contents(dirname(__FILE__) .'/Server.wsdl', get_wsdl()); //get_wsdl uses the same wrapper as above
//    $client = new StupidWrapperForOracleServer(dirname(__FILE__) .'/Server.wsdl',array('trace'=>1,'cache_wsdl'=>0));

    $options = array(
        'trace' => 1,
        'use' => SOAP_LITERAL,
        'style' => SOAP_DOCUMENT,
    );

    $client = new SoapClient($oracleWebServiceWSDL, $options);

    $params = new \SoapVar("<Echo><Acquirer><Id>MyId</Id><UserId>MyUserId</UserId><Password>MyPassword</Password></Acquirer></Echo>", XSD_ANYXML);
    $result = $client->MethodNameIsIgnored($params);

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
