<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Exceção de domínio para erros de regra de negócio.
 * Deve ser usada em Actions para substituir \Exception genérica.
 */
class BusinessException extends Exception
{
    /**
     * @param  string  $message  Mensagem amigável para o usuário.
     * @param  int  $code  Código HTTP ou interno da exceção.
     * @param  Exception|null  $previous  Exceção original, se houver.
     */
    public function __construct(string $message = 'Ocorreu um erro ao processar a operação. Tente novamente.', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     * Returns a user-friendly response with the business error message.
     *
     * @param  Request  $request
     * @return Response|JsonResponse|false
     */
    public function render(mixed $request): mixed
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $this->getMessage(),
            ], 422);
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $this->getMessage());
    }
}
