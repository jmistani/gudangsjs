<?php
/**
 * Beutify API response using this helper
 * @author yogs22
 * Date 11/11/19
 */

namespace App\Helpers;

class ResponseHelper
{
    public function cleanResponse($data)
    {

    }

    /**
     * @param $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function successResponse($data, $message = "Data successfully retrieved", $code = 200)
    {
        $response = [
            'url' => url()->full(),
            'method' => request()->getMethod(),
            'code' => $code,
            'message' => $message,
            'payload' => $data,
        ];


        return response($response, $code);
    }

    public function pdfFileResponse($data, $message = "", $code=200)
    {
        return response()->json(
            [
                'pdf' => $data['pdf'],
                'filename' => $data['filename'],
            ],
            200);
    }

    /**
     * @param \String $message
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */    
    public function failedResponseMessage(String $message)
    {
      $response = [
          'url' => url()->full(),
          'method' => request()->getMethod(),
          'code' => 400,
          'file' => null,
          'line' => null,
          'message' => $message
      ];

      return response($response, 500);
  }


    public function failedResponseException(\Exception $exception)
    {
        $response = [
            'url' => url()->full(),
            'method' => request()->getMethod(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'message' => $exception->getMessage()
        ];

        return response($response, 500);
    }

    /**
     * @param $message
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function notFoundResponse($message)
    {
        $response = [
            'url' => url()->full(),
            'method' => request()->getMethod(),
            'code' => 404,
            'message' => $message
        ];

        return response($response, 404);
    }
    /**
     * @param $message
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function unprocessableResponse($message, $code = 400)
    {
        $response = [
            'url' => url()->full(),
            'method' => request()->getMethod(),
            'code' => $code,
            'message' => $message
        ];

        return response($response, $code);
    }
}
