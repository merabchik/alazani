<?php 
namespace App\Model;

class Product {
    private $id;
    private $name;

    public function __construct($id = 0)
    {
        
    }

    public function getId(): int{
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $pName): void {
        $this->name = $pName;
    }
}