<?php
session_start();
require_once 'config/database.php';
require_once 'models/Model.php';
require_once 'models/Produto.php';
require_once 'models/Pedido.php';
require_once 'models/Cupom.php';
require_once 'utils/Utils.php';

// Inicializar carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Roteamento básico
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Header
include 'views/header.php';

// Conteúdo principal
switch ($page) {
    case 'produtos':
        include 'views/produtos.php';
        break;
    case 'carrinho':
        include 'views/carrinho.php';
        break;
    case 'checkout':
        include 'views/checkout.php';
        break;
    case 'admin':
        include 'views/admin.php';
        break;
    default:
        include 'views/home.php';
        break;
}

// Footer
include 'views/footer.php';
?> 