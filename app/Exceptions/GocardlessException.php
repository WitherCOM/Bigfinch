<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Nette\Utils\Json;

class GocardlessException extends Exception
{
    public function __construct(Response $response)
    {
        if ($response->paymentRequired())
        {
            parent::__construct("Gocardless is out of free credits!");
        }
        else if($response->status() === 429)
        {
            parent::__construct("You are rate limited by Gocardless!");
        }
        else
        {
            $status_code = $response->status();
            $content = Json::encode($response->json());
            parent::__construct("Gocardless http error! - $status_code\n$content");
        }
    }
}
