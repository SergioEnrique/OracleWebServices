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


// Comienza la app
$app->get('/client', function () use ($app) {
    $url = "https://caxj.crm.us2.oraclecloud.com/crmCommonSalesParties/SalesPartyService?WSDL";

    $request = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Header>
        <wsse:Security soap:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <wsse:UsernameToken>
                <wsse:Username>jgonzalez</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">Medix2015</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soap:Header>
    <soap:Body xmlns:ns1="http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/applicationModule/types/">
        <ns1:createPerson>
            <ns1:personParty xmlns:ns2="http://xmlns.oracle.com/apps/cdm/foundation/parties/personService/">
                <ns2:CreatedByModule>AMS</ns2:CreatedByModule>
                <ns2:PersonProfile>
                    <ns2:PersonFirstName>Andy</ns2:PersonFirstName>
                    <ns2:PersonMiddleName>Randy</ns2:PersonMiddleName>
                    <ns2:PersonLastName>Wilson</ns2:PersonLastName>
                    <ns2:CreatedByModule>AMS</ns2:CreatedByModule>
                </ns2:PersonProfile>
                <ns2:PartyUsageAssignment xmlns:ns3="http://xmlns.oracle.com/apps/cdm/foundation/parties/partyService/">
                    <ns3:PartyUsageCode>CUSTOMER</ns3:PartyUsageCode>
                    <ns3:CreatedByModule>AMS</ns3:CreatedByModule>
                    <par:OwnerTableId>?</par:OwnerTableId>
                </ns2:PartyUsageAssignment>
                <ns2:AdditionalPartyId xmlns:ns4="http://xmlns.oracle.com/apps/cdm/foundation/parties/partyService/">
                    <ns4:PartyIdentifierType>ID_NUMBER</ns4:PartyIdentifierType>
                    <ns4:PartyIdentifierValue>ID12345678</ns4:PartyIdentifierValue>
                    <ns4:CreatedByModule>AMS</ns4:CreatedByModule>
                    <ns4:IssuingAuthorityName>APRTO</ns4:IssuingAuthorityName>
                </ns2:AdditionalPartyId>
            </ns1:personParty>
        </ns1:createPerson>
    </soap:Body>
</soap:Envelope>';

    $data = '';    

    $opts = array (
        'http' => array (
            'method' => 'GET',
            'header'=> "Content-type: text/xml\r\n"
                . "Content-Length: " . strlen($data) . "\r\n",
            'content' => $data
            )
        );

    $context  = stream_context_create($opts);

    $response = file_get_contents($url, false, $context);

    return new Response($response, 200, array(
        "Content-Type" => "text/xml"
    ));

/*
    $options = array(
        'login' => 'jgonzalez',
        'password' => 'Medix2015',
    );

    $client = new Client($url, $options);

    $client->setParameterPost('request', $request);
    $response = $client->request('POST');

    return new Response($response);
*/
});

// Errores
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
