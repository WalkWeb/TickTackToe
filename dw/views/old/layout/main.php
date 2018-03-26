<html>
<head>
    <title><?= $this->title ?></title>
    <meta name="Description" content="<?= $this->description ?>">
    <meta name="Keywords" content="<?= $this->keywords ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="/templates/<?= $this->templates ?>css/style.css?v=1.0">
    <link rel="stylesheet" type="text/css" href="/templates/<?= $this->templates ?>css/game.css?v=1.0">
</head>
<body>
<div class="menu">
    <ul class="navigation">
        <li><a href="/" title="">Игра</a></li>
        <li><a href="/framework" title="">О фреймворке</a></li>
    </ul>
</div>
<div class="main">
    <div id="main_content">
        <?= $content ?>
    </div>
</div>
<div class="statictics">
    <div class="content">
        <p>DW Framework</p>
        <?php
        // Если это режим разработчика - выводим время и расходуемую память
        if (SITE && DEV) {
            echo \dw\core\Runtime::end();
        }
        ?>
    </div>
</div>
<script src="/js/jquery.js"></script>
<script src="/js/game.js?v=1.3"></script>
</body>
</html>