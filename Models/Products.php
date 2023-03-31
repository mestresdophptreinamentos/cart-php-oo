<?php

namespace Models;

use Database\Connection;
use PDO;

class Products
{

    /** @var PDO */
    private $Conn;

    /** @var PDOStatement */
    private $Read;

    /** @var  */
    public $table = 'cart_products';

    /* Obtém conexão do banco de dados Singleton
     * Método constructor.
     */
    public function __construct() {
        $this->Conn = Connection::getInstance();
    }

    /**
     * Método privado Responsável por fazer a leitura dos produtos (Query)
     * @param $Indexes
     * @param $Status
     * @return void
     */
    private function ReadQuery($Indexes, $Status) {
        //Monta a consulta
        $this->Read = $this->Conn->prepare("SELECT {$Indexes} FROM {$this->table} WHERE product_status = :product_status");
        $this->Read -> bindValue(':product_status', $Status);
        $this->Read -> execute();
        $this->Read -> setFetchMode(PDO::FETCH_ASSOC);
    }

    /**
     * Metodo público responsável por fazer o retorno dos dados.
     * @param $Indexes
     * @param $Status
     * @return mixed
     */
    public function QueryResult($Indexes, $Status) {
        //Chama o método ReadQuery.
        $this->ReadQuery($Indexes, $Status);

        //Retorna os dados dentro de um array
        return $this->Read->fetchAll();
    }

}