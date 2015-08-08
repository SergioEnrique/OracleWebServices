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

$url = "https://oraclewebservice.herokuapp.com/web/index.php/client/services/salespartyservice/environments/dev/operations/createPersonParty/";
$result = file_get_contents($url, false, $context);


/* Servicio que agrega un contacto en vez de un cliente:

$params = array(
    "contact" => array( 
        "FirstName" => ff_getSubmit('nombre'),
        "LastName" => ff_getSubmit('apellido_paterno'),
        "SecondLastName" => ff_getSubmit('apellido_materno'),
        "EmailAddress" => ff_getSubmit('email'),
        "HomePhoneNumber" => (int)ff_getSubmit('telefono'),
        "AcademicTitle" => ff_getSubmit('titulo_academico'),
        "PrimaryAddress" => array(
            "City" => ff_getSubmit('ciudad'),
            "Country" => ff_getSubmit('pais'),
            "State" => ff_getSubmit('estado')
        )
        "Comments" => " Especialidad: ".ff_getSubmit('especialidad')." Cédula: ".ff_getSubmit('cedula')
    )
);

$url = "https://oraclewebservice.herokuapp.com/web/index.php/client/services/contactservice/environments/dev/operations/createContact/";

