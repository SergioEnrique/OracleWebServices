<?php

$this->execPieceByName('ff_InitLib');

$params = array(
    "personParty" => array(
        "CreatedByModule" => "AMS",
        "PersonProfile" => array(
            "PersonFirstName" => ff_getSubmit('nombre'),
            //"PersonMiddleName" => ff_getSubmit(''),
            "PersonLastName" => ff_getSubmit(''),
            "PersonSecondLastName" => ff_getSubmit(''),
            //"PersonTitle" => ff_getSubmit(''),
            "PersonAcademicTitle" => ff_getSubmit(''),
            "Country" => ff_getSubmit(''),
            "City" => ff_getSubmit(''),
            "State" => ff_getSubmit(''),
            "EmailAddress" => ff_getSubmit('email'),
            "Comments" => "Teléfono: ".ff_getSubmit('telefono')." Especialidad: ".ff_getSubmit('')." Cédula: ".ff_getSubmit(''),
            "CreatedByModule" => "AMS"
        ),
        "PartyUsageAssignment" => array(
            "PartyUsageCode" => "CUSTOMER",
            "CreatedByModule" => "AMS",
        )
    )
);

$params = json_encode($params);

$opts = array('http' =>
    array(
        'method'  => 'GET',
        'header'  => 'Content-type: application/json',
        'content' => $params
    )
);

$context  = stream_context_create($opts);

$url = "https://oraclewebservice.herokuapp.com/web/index.php/client/services/salespartyservice/operations/createPersonParty/";
$result = file_get_contents($url, false, $context);