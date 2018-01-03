<?php

namespace App\Handlers;

use Exception;
use Cache;
use Log;
use BLogger;
use Response;
class SwooleHandler 
{
    private $devRepo;
    private $hubRepo;
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
        public function __construct()
    {
        parent::__construct();
    }
    public function onStart($serv){

    }

}
