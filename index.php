<?php
    ob_start();

    require_once 'Config/Config.php';
    require_once 'Config/Autoload.php';
    require_once 'Database/Connection.php';
    require_once 'Models/Products.php';

?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title>Carrinho de Compras - PHP OO</title>
        <meta name="base" content="<?= strip_tags($url) ?>">
        <link href="<?= strip_tags($url) ?>/style.css" rel="stylesheet">
    </head>

    <body>

        <div class="result"></div>
        <main>
            <?php
                $Product = new \Models\Products();
                $Datas = $Product->QueryResult('*', 1);
            ?>
            <section class="cart_products">
                <p class="navigator">
                    <a href="<?= strip_tags($url) ?>/index.php" title="Voltar para a loja" class="radius">Loja</a>
                    <a href="<?= strip_tags($url) ?>/cart.php" title="Ir para o carrinho" class="radius">Carrinho</a>
                </p>

                <?php foreach($Datas as $Data): ?>
                <div class="card3">
                    <img src="<?= strip_tags($url) ?>/<?= strip_tags($Data['product_cover']); ?>"
                         title="Imagem da <?= strip_tags($Data['product_name']); ?>"
                         alt="Imagem da <?= strip_tags($Data['product_name']); ?>">
                    <h1><?= strip_tags($Data['product_name']); ?></h1>
                    <h2>R$ <?= strip_tags(number_format($Data['product_price'], 2,',','.')); ?></h2>

                    <div class="cart_form">
                        <input type="number" name="cart_quantity" value="1" class="cart_quantity radius">
                    </div>

                    <p class="cart_actions">
                        <a href="#" title="Compre esta camiseta agora!" class="btn_edit radius buyProduct"
                           data-id="<?= strip_tags($Data['product_id']); ?>"  data-value="1"
                           data-price="<?= strip_tags(number_format($Data['product_price'], 2,',', '.')) ?>">
                            Comprar Agora
                        </a>
                    </p>
                </div>

                <?php endforeach; ?>
            </section>
        </main>

        <script src="<?= strip_tags($url) ?>/jquery.js"></script>
        <script src="<?= strip_tags($url) ?>/Ajax/ajax.js"></script>
    </body>
</html>

<?php
    ob_end_flush();
?>