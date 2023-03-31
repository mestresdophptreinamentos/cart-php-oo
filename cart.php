<?php
    ob_start();

    require_once 'Config/Config.php';
    require_once 'Config/Autoload.php';
    require_once 'Database/Connection.php';
    require_once 'Models/Cart.php';

    $Conn = \Database\Connection::getInstance();
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
            <section class="container_cart">

                <p class="navigator">
                    <a href="<?= strip_tags($url) ?>/index.php" title="Voltar para a loja" class="radius">Loja</a>
                    <a href="<?= strip_tags($url) ?>/cart.php" title="Ir para o carrinho" class="radius">Carrinho</a>
                </p>

                <h1> Minha lista de pedido:</h1>

                <div class="cartLoad">

                    <?php
                        $Cart = new \Models\Cart();
                        $Temp = $Cart->QueryResult();
                    ?>

                    <table class="table">

                        <?php
                            $Product = $Cart->Product();
                            $counter = count($Product['name']);

                            if (!$Temp) {
                                echo '<tr><td colspan="5"> <p class="none">Nenhum produto adicionado no seu carrinho!</p></td></tr>';
                            }

                            if ($Temp) {
                                for ($i = 0; $i < $counter; $i++) {
                                    $Value = strip_tags($Temp['value']);
                        ?>
                        <tr>
                            <td class="td_minus td_img">
                                <img src="<?= strip_tags($url) ?>/<?= strip_tags($Product['images'][$i]); ?>"
                                     title="Imagem do produto:  <?= strip_tags($Product['name'][$i]); ?>"
                                     alt="Imagem do produto:  <?= strip_tags($Product['name'][$i]); ?>">
                            </td>

                            <td class="td_plus">
                                <h2> Produto: <?= strip_tags($Product['name'][$i]); ?></h2>
                            </td>

                            <td class="td_minus">
                                <h2>R$ <?= strip_tags(number_format($Temp['price'][$i], 2,',','.')); ?></h2>
                            </td>

                            <td class="td_minus">
                                <form method="post" id="form_cart">
                                    <input type="number" id="input_cart" name="input_cart"
                                           value="<?= strip_tags($Temp['quantity'][$i]); ?>"
                                           data-id="<?= strip_tags($Temp['id'][$i]); ?>"
                                           data-price="<?= strip_tags($Temp['price'][$i]); ?>"
                                           data-product="<?= strip_tags($Product['id'][$i]); ?>"
                                           class="radius quantity">
                                </form>
                            </td>

                            <td class="td_sub">
                                <p class="cart_actions text-center">
                                    <a href="#" class="btn_delete radius delete_cart"
                                       title="Remova esse produto do seu pedido" data-id="<?= strip_tags($Temp['id'][$i]); ?>"
                                        data-quantity="<?= strip_tags($Temp['quantity'][$i]); ?>"
                                        data-product="<?= strip_tags($Product['id'][$i]); ?>">
                                        Excluir
                                    </a>
                                </p>
                            </td>
                        </tr>
                        <?php } } ?>

                        <tr>
                            <td colspan="5">
                                <p class="cart_value">Valor Total: R$ <?= number_format($Value, 2,',','.'); ?>
                                    </p>
                            </td>
                        </tr>

                    </table>
                </div>
            </section>
        </main>

        <script src="<?= strip_tags($url) ?>/jquery.js"></script>
        <script src="<?= strip_tags($url) ?>/Ajax/ajax.js"></script>
    </body>
</html>

<?php
ob_end_flush();
?>