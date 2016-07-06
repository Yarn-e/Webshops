<?php

include('../../dashboard/assets/inc/ss_config.php');
$userIdentifier = '28008';  // ontvanger van het bericht
$senderIdentifier = ''; // verzender van het bericht , mag leeg zijn
$title = "testbericht";
$body = "dit is een test via php";


try {
    libxml_disable_entity_loader(false);
    $client = new SoapClient('https://' . $platform . '/Webservices/V3?wsdl', ['cache_wsdl' => WSDL_CACHE_NONE]);
    $result = $client->sendMsg($webservicesPwd, $userIdentifier, $title, $body);
    if($result !== 0) {

        // HAAL ALLE FOUTCODES EN HUN OMSCHRIJVINGEN OP
        $errorCodes = $client->returnErrorCodes();

        // OMSCHRIJVING VOOR DEZE FOUTCODE
        $errorMessage = $errorCodes->{$result};

        // FOUTMELDING
        throw new \Exception($errorMessage);
    }

    // RESULTAAT IS CORRECT: MELDING TONEN
    echo "Boodschap is succesvol verzonden";
}
catch(\Exception $e)
{
    //AFHANDELING FOUTMELDINGEN
    header("HTTP/1.0 400 Error");
    echo "ERROR : " . $e->getMessage();
}
?>