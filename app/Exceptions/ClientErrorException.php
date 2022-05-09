<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Definitions\HttpCode;

class ClientErrorException extends Exception
{
    protected $errorCode = 'client-error';

    protected $code = HttpCode::NOT_FOUND;

    protected $debugContext;

    public function __construct(string $message, array $debugContext = null)
    {
        $this->debugContext = $debugContext;

        parent::__construct($message, $this->code, null);
    }

    public function debugContext()
    {
        return $this->debugContext;
    }

    /**
     * Report the exception
     */
    public function report()
    {
        Log::error("[ClientErrorException] {$this->getMessage()}", [
            'context' => $this->debugContext,
            ['exception' => $this]
        ]);
    }


    /**
     * Render the exception as an HTTP response
     *
     * @return JsonResponse
     * @throws ServerErrorException
     */
    public function render()
    {
        $error = [
            'error' => $this->errorCode,
            'message' => $this->getMessage()
        ];

        $debug = [
            'context' => $this->debugContext,
            'stack' => $this->getTrace(),
        ];


        // if(Helper::configRequired('app.debug') === true) {
            // $error['debug'] = $debug;
        // }

        return response()->json($error, $this->getCode());
    }
}
