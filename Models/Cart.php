<?php

namespace Models;

use Database\Connection;
use PDO;

class Cart
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

    /**
     * Método privado responsável por fazer a leitura do pedido (Query)
     * @param $Indexes
     * @param $Session
     * @return void
     */
    private function ReadQuery($Indexes, $Session) {
        //Monta a consulta
        $this->Read = $this->Conn->prepare("SELECT {$Indexes} FROM {$this->temp} WHERE temp_order = :temp_order");
        $this->Read -> bindValue(':temp_order', $Session);
        $this->Read -> execute();
        $this->Read -> setFetchMode(PDO::FETCH_ASSOC);
    }

    /**
     * Método privado responsável processar os dados da consulta.
     * @param $Datas
     * @return array
     */
    private function DataQuery ($Datas){
        //Se tiver produtos no pedido
        foreach ($Datas as $Data):
            $Quantity[] = strip_tags($Data['temp_quantity']);
            $Price[] = strip_tags($Data['product_price']);
            $Id[] = strip_tags($Data['temp_id']);
            $Value += $Data['temp_value'];
        endforeach;

        //Retorna os dados para a aplicação
        return ['quantity' => $Quantity, 'price' => $Price, 'id' => $Id, 'value' => $Value];
    }

    /**
     * Método privado repsonsável por processar os dados da consulta da tabela produtos
     * @param $Datas
     * @return array
     */
    private function DataProduct ($Datas){
        foreach ($Datas as $Data):
            $Product = strip_tags($Data['product_id']);

            //Monta a consulta
            $this->Read = $this->Conn->prepare("SELECT * FROM {$this->table} WHERE product_id = :product_id");
            $this->Read -> bindValue(':product_id', $Product);
            $this->Read -> execute();
            $this->Read -> setFetchMode(PDO::FETCH_ASSOC);

            //Retorna os dados dentro de um array
            $Products = $this->Read->fetchAll();

            foreach ($Products as $Product) { }
            $Image[] = strip_tags($Product['product_cover']);
            $Quantity[] = strip_tags($Product['product_stock']);
            $Name[] = strip_tags($Product['product_name']);
            $Id[] = strip_tags($Product['product_id']);

        endforeach;

        //Retorna os dados dentro de um array
        return ['images' => $Image, 'name' => $Name, 'quantity' => $Quantity, 'id' => $Id];
    }

    /**
     * Metodo público responsável por fazer o retorno dos dados da tabela pedido.
     * @param $Indexes
     * @return array|false
     */
    public function QueryResult($Indexes = '*') {
        $Session = strip_tags($_SESSION['order']);

        //Chama o método ReadQuery.
        $this->ReadQuery($Indexes, $Session);

        //Retorna os dados dentro de um array
        $Datas = $this->Read->fetchAll();

        //Se não tiver nenhum produto no pedido
        if (!$Datas) {
            return false;
        }

        return $this->DataQuery($Datas);
    }

    /**
     * Método público responsável por trazer informações do produto (imagem e nome)
     * @return array
     */
    public function Product(){
        $Session = strip_tags($_SESSION['order']);

        //Chama o método ReadQuery.
        $this->ReadQuery('*', $Session);

        //Retorna os dados dentro de um array
        $Datas = $this->Read->fetchAll();

       return $this->DataProduct($Datas);

    }

    /**
     * Método privado responsável por realizar o update na tabela temporaria de pedido.
     * @param $Quantity
     * @param $Price
     * @param $OrderId
     * @return void
     */
    private function UpdateTemp($Quantity, $Price, $OrderId){
        //Monta a consulta
        $this->Update = $this->Conn->prepare("UPDATE {$this->temp} SET temp_quantity = :temp_quantity, temp_value = :temp_value 
            WHERE temp_id = :temp_id");
        $this->Update -> bindValue(':temp_quantity', $Quantity);
        $this->Update -> bindValue(':temp_value', $Price);
        $this->Update -> bindValue(':temp_id', $OrderId);
        $this->Update -> execute();
    }

    /**
     * Método privado responsável por update na tabela produtos
     * @param $Stock
     * @param $Product
     * @return void
     */
    private function UpdateTable($Stock, $Product){
        //Monta a consulta
        $this->Update = $this->Conn->prepare("UPDATE {$this->table} SET product_stock = :product_stock 
            WHERE product_id = :product_id");
        $this->Update -> bindValue(':product_stock', $Stock);
        $this->Update -> bindValue(':product_id', $Product);
        $this->Update -> execute();
    }

    /**
     * Método privado responsável por realizar consulta na tabela temporária de pedidos
     * @param $OrderId
     * @return array|false
     */
    private function ReadTemp($OrderId){
        //Monta a consulta
        $this->Read = $this->Conn->prepare("SELECT * FROM {$this->temp} WHERE temp_id = :temp_id");
        $this->Read -> bindValue(':temp_id', $OrderId);
        $this->Read -> execute();
        $this->Read -> setFetchMode(PDO::FETCH_ASSOC);

        //Retorna os dados dentro de um array
        return $this->Read->fetchAll();
    }

    /**
     * Método privado responsável por consultar tabela produtos.
     * @param $Product
     * @return array|false
     */
    private function ReadProduct($Product){
        //Monta a consulta
        $this->Read = $this->Conn->prepare("SELECT * FROM {$this->table} WHERE product_id = :product_id");
        $this->Read -> bindValue(':product_id', $Product);
        $this->Read -> execute();
        $this->Read -> setFetchMode(PDO::FETCH_ASSOC);

        //Retorna os dados dentro de um array
        return $this->Read->fetchAll();
    }

    /**
     * Método privado responsável por deletar o produto do pedido
     * @param $OrderId
     * @return bool
     */
    private function DeleteProduct($OrderId){
        //Monta a exclusão
        $this->Delete = $this->Conn->prepare("DELETE FROM {$this->temp} WHERE temp_id = :temp_id");
        $this->Delete -> bindValue(':temp_id', $OrderId);
        $this->Delete -> execute();

        return true;
    }

    /**
     * Método público responsável por alterar a quantidade do produto no pedido.
     * @param $OrderId
     * @param $Quantity
     * @param $Value
     * @param $Product
     * @return bool
     */
    public function Quantity($OrderId, $Quantity, $Value, $Product){
        $Temps = $this-> ReadTemp($OrderId);

        foreach ($Temps as $Temp) { }
        $Qtd = strip_tags($Temp['temp_quantity']);
        $StockTemp = $Quantity - $Qtd;

        //Monta o preço
        $Price = $Value * $Quantity;

        $this->UpdateTemp($Quantity, $Price, $OrderId);
        $Products = $this->ReadProduct($Product);

        foreach ($Products as $Data) { }
        $Qtd = strip_tags($Data['product_stock']);
        $Stock = $Qtd - $StockTemp;

        $this->UpdateTable($Stock, $Product);

        if ($this->Update) {
            $message = ["status" => "success", "message" => "Quantidade do produto alterado.", "redirect" => ''];
            echo json_encode($message);

            return true;
        }

        $message = ["status" => "warning", "message" => "Ocorreu um problema tente novamente.", "redirect" => ''];
        echo json_encode($message);

        return false;
    }

    /**
     * Método público responsável por excluir o produto da lista de pedido.
     * @param $OrderId
     * @param $Quantity
     * @param $Product
     * @return bool
     */
    public function Delete($OrderId, $Quantity, $Product){

        $Products = $this->ReadProduct($Product);

        foreach ($Products as $Data) { }
        $Qtd = strip_tags($Data['product_stock']);
        $Stock = $Qtd + $Quantity;

        //Devolve a quantidade reservada.
        $this->UpdateTable($Stock, $Product);
        $Delete = $this->DeleteProduct($OrderId);

        if ($Delete) {
            $message = ["status" => "success", "message" => "Produto excluído do seu carrinho.", "redirect" => ''];
            echo json_encode($message);

            return true;
        }

        $message = ["status" => "warning", "message" => "Ocorreu um problema, tente novamente.", "redirect" => ''];
        echo json_encode($message);

        return false;
    }
}