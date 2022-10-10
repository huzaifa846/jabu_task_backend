<?php

namespace App\Traits;


trait TaskMethods
{
    public static function taskRules()
    {
        return [
            "title" => "required|max:255",
            "description" => "required|max:255",
            "type" => "required|string",
            "interval_type" => "required|string",
            "repeat_count" =>  "required_if:intervalType,repetition",
            "cycles" => "array"
        ];
    }
}
?>
