<?php

$this->execPieceByName('ff_InitLib');
$fromname = 'ILSO Pruebas';
$from = 'docser2@gmail.com';
$subject = 'Clima actual';

$to = ff_getSubmit('email');
$pais = ff_getSubmit('pais');
$ciudad = ff_getSubmit('ciudad');

$url = "https://oraclewebservice.herokuapp.com/web/index.php/client/services/globalweather/operations/GetWeather/params/%7B%22CountryName%22:%22".$pais."%22,%22CityName%22:%22".$ciudad."%22%7D/";
$body = file_get_contents($url);

$this->sendMail($from, $fromname, $to, $subject, $body);