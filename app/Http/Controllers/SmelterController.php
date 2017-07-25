<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Redis;