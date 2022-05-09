<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Definitions\HttpCode;

class ServerErrorException extends Exception
{
    protected $errorCode = 'server-error';

    protected $code = HttpCode::INTERNAL_SERVER_ERROR;

    protected $debugMessage;

    protected $debugContext;

    public function __construct(string $debugMessage = '', array $debugContext = null)
    {
        $this->debugMessage = $debugMessage;
        $this->debugContext = $debugContext;

        $message = "Something went wrong!";

        // show the raw message if not in production environment
        if(app()->environment() !== 'production') {
            $message = $this->debugMessage;
        }

        parent::__construct($message, HttpCode::INTERNAL_SERVER_ERROR, null);
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
        Log::error("[ServerErrorException] {$this->debugMessage}", [
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
            'message' => $this->debugMessage,
            'context' => $this->debugContext,
            'stack' => $this->getTrace()
        ];

        if(config('app.debug') === true) {
            $error['debug'] = $debug;
        }

        return response()->json($error, $this->getCode());
    }
}
