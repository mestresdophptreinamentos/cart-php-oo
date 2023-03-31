<?php

namespace Models;

use Database\Connection;
use PDO;

class Buy
{
    /** @var PDO */
    private $Conn;

    /** @var PDOStatement */
    private $Read;

    /** @var  */
    public $message = null;

    /** @var  */
    public $table = 'cart_products';

    /** @var  */
    public $temp = 'cart_temp';

    /* Obtém conexão do banco de dados Singleton
     * Método constructor.
     */
    public function __construct() {
        $this->Conn = Connection::getInstance();
    }

    /** Método privado responsável para criar nova sessão
     * @return void
     */
    private function Session(){
        return $_SESSION['order'] = (empty ($_SESSION['order']) ? rand(100, 100000) . time() : $_SESSION['order']);
    }

    /**
     * Método privado Responsável por fazer a leitura do pedido (Query)
     * @param $Product
     * @param $Session
     * @return array|false
     */
    private function CheckTemp($Product, $Session) {
        //Monta a consulta
        $this->Read = $this->Conn->prepare("SELECT * FROM {$this->temp} 
            WHERE product_id = :product_id AND temp_order = :temp_order");
        $this->Read -> bindValue(':product_id', $Product);
        $this->Read -> bindValue(':temp_order', $Session);
        $this->Read -> execute();
        $this->Read -> setFetchMode(PDO::FETCH_ASSOC);

        //Retorna os dados dentro de um array
        return $this->Read->fetchAll();
    }

    /**
     * Método privado Responsável por fazer a leitura do produto (Query)
     * @param $Product
     * @return array|false
     */
    private function CheckStock($Product) {
        //Monta a consulta
        $this->Read = $this->Conn->prepare("SELECT * FROM {$this->table} WHERE product_id = :product_id");
        $this->Read -> bindValue(':product_id', $Product);
        $this->Read -> execute();
        $this->Read -> setFetchMode(PDO::FETCH_ASSOC);

        //Retorna os dados dentro de um array
        return $this->Read->fetchAll();
    }

    /**
     * Método privado responsável por update do estoque da tabela products.
     * @param $Product
     * @param $Stock
     * @return void
     */
    private function UpdateTable($Product, $Stock){
        //Monta o update
        $this->Read = $this->Conn->prepare("UPDATE {$this->table} SET product_stock = :product_stock WHERE product_id = :product_id");
        $this->Read -> bindValue(':product_stock', $Stock);
        $this->Read -> bindValue(':product_id', $Product);
        $this->Read -> execute();
    }

    /**
     * Método privado responsável por atualizar a quantidade de produto no pedido
     * @param $Quantity
     * @param $Price
     * @param $Value
     * @param $Product
     * @param $Session
     * @return void
     */
    private function UpdateTemp($Quantity, $Price, $Value, $Product, $Session){
        //Monta o update
        $this->Update = $this->Conn->prepare("UPDATE {$this->temp} SET temp_quantity = :temp_quantity,
                 product_price = :product_price, temp_value = :temp_value
            WHERE product_id = :product_id AND temp_order = :temp_order");
        $this->Update->bindValue(':temp_quantity', $Quantity);
        $this->Update -> bindValue(':product_price', $Price);
        $this->Update -> bindValue(':temp_value', $Value);
        $this->Update->bindValue(':product_id', $Product);
        $this->Update->bindValue(':temp_order', $Session);

        $this->Update->execute();
    }
    /**
     * Método privado responsável por capturar dados da tabela temporaria, e prepara-la para o update
     * @param $Product
     * @param $Quantity
     * @param $Price
     * @param $Session
     * @return void
     */
    private function DataTemp($Product, $Quantity, $Price, $Session)
    {
        $Datas = $this->CheckTemp($Product, $Session);

        //Criar um método para verificar se o estoque está zerado ou não

        if ($Datas) {
            foreach ($Datas as $Data) { }
            $Qtd = strip_tags($Data['temp_quantity']);
            $Reserved = $Qtd + $Quantity;

            //Recalcula o valor do produto * a quantidade
            $Value = $Price * $Reserved;

            $this->UpdateTemp($Reserved, $Price, $Value, $Product, $Session);
        }
    }

    /**
     * Método privado responsável por retirar do estoque a quantidade daquele produto.
     * @param $Product
     * @param $Quantity
     * @return bool
     */
    private function Stock($Product, $Quantity) {
        $Datas = $this->CheckStock($Product);

        foreach ($Datas as $Data) { }
        $Qtd = strip_tags($Data['product_stock']);
        $Reserved = $Qtd - $Quantity;

        $Price = strip_tags($Data['product_price']);

        if ($Reserved <= 0) {
            return false;
        }

       $this->UpdateTable($Product, $Reserved);
        return true;
     }

    /**
     * Método privado responsável por adicionar novo produto ao pedido
     * @param $Product
     * @param $Quantity
     * @param $Price
     * @param $Value
     * @param $Session
     * @return void
     */
    private function CreateTemp($Product, $Quantity, $Price, $Value, $Session) {

        //Monta o insert
        $this->Create = $this->Conn->prepare("INSERT INTO {$this->temp} 
            (temp_order, product_id, temp_quantity, product_price, temp_value, temp_status) VALUES
              (:temp_order, :product_id, :temp_quantity, :product_price, :temp_value, :temp_status)");
        $this->Create -> bindValue(':temp_order', $Session);
        $this->Create -> bindValue(':product_id', $Product);
        $this->Create -> bindValue(':temp_quantity', $Quantity);
        $this->Create -> bindValue(':product_price', $Price);
        $this->Create -> bindValue(':temp_value', $Value);
        $this->Create -> bindValue(':temp_status', 1);
        $this->Create -> execute();
    }

    /**
     * Método privado responsável por checar se será feito um insert ou update na compra do produto.
     * @param $Product
     * @param $Quantity
     * @param $Price
     * @param $Session
     * @param $Check
     * @return bool|void
     */
    private function Result ($Product, $Quantity, $Price, $Value, $Session, $Check){
        //Se existir um produto para este pedido, o bloco de código abaixo é acionado
        if ($Check) {
            $this->DataTemp($Product, $Quantity, $Price, $Session);

            $message = ["status" => 'success', "message" => "Quantidade do produto foi alterada!", "redirect" => ""];
            echo json_encode($message);
            return true;
        }

        //Se não existir um produto para este pedido, o bloco de código abaixo é acionado
        if (!$Check) {
            $this->CreateTemp($Product, $Quantity, $Price, $Value, $Session);

            $message = ["status" => 'success', "message" => "Produto acrescentado ao carrinho!", "redirect" => ""];
            echo json_encode($message);
            return true;
        }
    }

    /**
     * Método público responsável por montar o processo de compra do produto.
     * @param $Product
     * @param $Quantity
     * @param $Price
     * @return bool|void
     */
    public function BuyOrders($Product, $Quantity, $Price) {

        $Session = $this->Session();
        $Check = $this->CheckTemp($Product, $Session);

        //Faz a reserva do estoque do produto
        $Stock = $this->Stock($Product, $Quantity);

        //Captura o preço
        $Value = number_format($Price * $Quantity, 2,'.','');

        //Não tiver estoque suficiente, mostra a mensagem abaixo.
        if (!$Stock) {
            $message = ["status" => 'warning', "message" => "Não há estoque suficiente para essa quantidade!", "redirect" => ""];
            echo json_encode($message);
            return true;
        }

        $this->Result($Product, $Quantity, $Price, $Value, $Session, $Check);

        //Se der algum erro no code, retorna falso.
        $message = ["status" => 'error', "message" => "Ocorreu um problema!", "redirect" => ""];
        echo json_encode($message);
    }
}