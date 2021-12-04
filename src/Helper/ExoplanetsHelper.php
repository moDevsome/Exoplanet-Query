<?php

namespace App\Helper;

class ExoplanetsHelper
{

    public static function getFieldChoices(string $workingDirectory) : array {

        return json_decode(file_get_contents($workingDirectory.'Exoplanets_localDb_FieldChoices.json'), TRUE);

    }

}
