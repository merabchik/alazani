<?php
namespace App\Library\Http;

class Response {
    const RESPONSE_CODE_OK = 200;
    const RESPONSE_CODE_BADREQUEST = 400;
    const RESPONSE_CODE_NOTFOUND = 404;
    const RESPONSE_CODE_INTERNALERROR = 500;
    const JSON_CONTENT_TYPE = "Content-type: application/json; charset=utf-8";
    private $ContentType;

    public function setContentType(string $pContentType): Response
    {
        $this->ContentType = $pContentType;
        return $this;
    }

    public function getContentType(): string {
        return $this->ContentType;
    }

    public function run(mixed $result, int $pHttpResponseCode = self::RESPONSE_CODE_OK, string $pContentType = self::JSON_CONTENT_TYPE,  bool $pContentToJson = true): string
    {
        http_response_code($pHttpResponseCode);
        if ($pContentToJson) {
            header(self::JSON_CONTENT_TYPE);
            return json_encode($result);
        } else {
            header($pContentType);
            return $result;
        }
    }

    public static function JSON(array $result): void {
        header(self::JSON_CONTENT_TYPE);
        echo json_encode($result);
    }
}