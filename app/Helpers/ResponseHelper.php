<?php

namespace App\Helpers;

/**
 * ResponseHelper
 */
class ResponseHelper
{

    /**
     * success
     *
     * @param  mixed $data
     * @param  mixed $message
     * @param  mixed $statusCode
     * @return mixed
     */
    public static function success($data = [], $message = 'Request successful', $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => now(),
        ], $statusCode);
    }

    /**
     * error
     *
     * @param  mixed $message
     * @param  mixed $errors
     * @param  mixed $statusCode
     * @return mixed
     */
    public static function error($message = 'An error occurred', $errors = [], $statusCode = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now(),
        ], $statusCode);
    }

    /**
     * validationError
     *
     * @param  mixed $errors
     * @param  mixed $message
     * @param  mixed $statusCode
     * @return mixed
     */
    public static function validationError($errors, $message = 'Validation failed', $statusCode = 422)
    {
        return response()->json([
            'status' => 'fail',
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now(),
        ], $statusCode);
    }

    /**
     * paginated
     *
     * @param  mixed $data
     * @param  mixed $pagination
     * @param  mixed $message
     * @param  mixed $statusCode
     * @return mixed
     */
    public static function paginated($data, $pagination, $message = 'Request successful', $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination,
            'timestamp' => now(),
        ], $statusCode);
    }
}
