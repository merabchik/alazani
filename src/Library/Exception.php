<?php 
namespace App\Library;

class Exception extends Throwable {
    private $errorMessages = [
        0 => "Base URL is not set",
        1 => "HTTP Method is not set",
        2 => "Params is not set in Reuqest"
    ];
    private int $errorCode;
    private string $message;

    public function __construct(int $pErrorCode){
        $message = $this->getErrorMessage($pErrorCode);
        return $this->getErrorMessage($pErrorCode);
    }

    public function init(int $pErrorCode): array {
        $message = $this->getErrorMessage($pErrorCode);
        $code = $this->getCode();
        $result = [
            "code" => $code,
            "message" => $message
        ];
        return $result;
    }

    final public function getMessage(): string {
        return $this->message;
    }

    final public function setMessage(string $pMessage): Exception {
        $this->message = $pMessage;
        return $this;
    }

    final public function getCode(): int {
        return $this->errorCode;
    }

    final public function setCode($pErrorCode): Exception {
        $this->errorCode = $pErrorCode;
        return $this;
    }

    private function getErrorMessage(int $pErrorIndex): string {
        return $this->errorMessages[$pErrorIndex];
    }
}