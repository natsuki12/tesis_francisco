<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Sistema SENIAT' ?></title>

    <!-- CSS del Header Guest -->
    <link rel="stylesheet" href="<?= asset('css/partials/guest/header_guest.css') ?>">

    <!-- CSS Global (Variables y Tipografía) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/global/toast.css') ?>">

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
    </style>

    <!-- CSS extra de la vista -->
    <?php if (isset($extraCss))
        echo $extraCss; ?>
</head>

<body>

    <?php include __DIR__ . '/../partials/guest/header_guest.php'; ?>

    <main>
        <?= $content ?? '' ?>
    </main>

    <!-- Global text sanitization -->
    <script src="<?= asset('js/global/sanitize.js') ?>"></script>

</body>

</html>