<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Sistema SENIAT' ?></title>
    
    <!-- CSS del Header Guest -->
    <link rel="stylesheet" href="<?= asset('css/partials/guest/header_guest.css') ?>">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
    </style>
    
    <!-- CSS extra de la vista -->
    <?php if (isset($extraCss)) echo $extraCss; ?>
</head>
<body>

    <?php include __DIR__ . '/../partials/guest/header_guest.php'; ?>

    <main>
        <?= $content ?? '' ?>
    </main>

</body>
</html>
