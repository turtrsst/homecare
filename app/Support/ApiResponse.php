<?php

namespace App\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Envelope respons API yang konsisten:
 *   sukses : { success: true,  message, data, meta? }
 *   error  : { success: false, message, errors? }
 */
final class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Data berhasil diperoleh',
        int $status = Response::HTTP_OK,
        ?array $meta = null,
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    public static function paginated($paginator, string $message = 'Data berhasil diperoleh'): JsonResponse
    {
        return self::success(
            $paginator->items(),
            $message,
            Response::HTTP_OK,
            [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        );
    }

    public static function error(
        string $message = 'Data tidak valid',
        int $status = Response::HTTP_UNPROCESSABLE_ENTITY,
        ?array $errors = null,
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /** Ubah exception menjadi envelope API yang aman (tanpa bocoran detail internal). */
    public static function renderException(Throwable $e, Request $request): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return self::error('Data tidak valid', Response::HTTP_UNPROCESSABLE_ENTITY, $e->errors());
        }

        if ($e instanceof AuthenticationException) {
            return self::error('Sesi berakhir atau token tidak valid. Silakan masuk kembali.', Response::HTTP_UNAUTHORIZED);
        }

        if ($e instanceof AuthorizationException) {
            return self::error('Anda tidak memiliki akses ke data ini.', Response::HTTP_FORBIDDEN);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return self::error('Data tidak ditemukan.', Response::HTTP_NOT_FOUND);
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $message = match (true) {
                $status === 429 => 'Terlalu banyak permintaan. Silakan coba beberapa saat lagi.',
                $status >= 500 => 'Maaf, permintaan belum dapat diproses. Silakan coba kembali.',
                default => $e->getMessage() ?: 'Permintaan tidak dapat diproses.',
            };

            return self::error($message, $status);
        }

        // Jangan pernah membocorkan SQLSTATE dsb. ke klien.
        report($e);

        $debug = config('app.debug') && $request->boolean('debug');

        return self::error(
            'Maaf, permintaan belum dapat diproses. Silakan coba kembali.',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $debug ? ['exception' => $e->getMessage()] : null,
        );
    }

    /** Timestamp terformat untuk payload API. */
    public static function timestamp(?Carbon $time = null): ?string
    {
        return ($time ?? now())->toIso8601String();
    }
}
