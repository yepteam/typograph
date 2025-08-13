<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

use Yepteam\Typograph\Rules\Formatting\HtmlEntities;
use Yepteam\Typograph\Typograph;

require __DIR__ . '/vendor/autoload.php';

$default = '';
$tokens = [];

$entity_format_options = HtmlEntities::$formats;

$entity_format = $_POST['format'] ?? 'named';

// Обработка POST-запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original = $_POST['text'] ?? $default;

    if (!in_array($entity_format, $entity_format_options, true)) {
        $entity_format = 'named';
    }

    $typograph = new Typograph(['entities' => (string)$entity_format]);

    $text = $typograph->format($original);

    $tokens = $typograph->getTokens();

    if (mb_strlen($text) > 4096) {
        $tokens = [];
    }

    // Сохраняем данные в сессии
    session_start();
    $_SESSION['original'] = $original;
    $_SESSION['text'] = $text;
    $_SESSION['tokens'] = $tokens;
    $_SESSION['format'] = $entity_format;

    // Перенаправляем на эту же страницу методом GET
    header('Location: ./');
    exit;
}

// Получаем данные из сессии (если есть)
session_start();
session_regenerate_id(true);
$original = $_SESSION['original'] ?? $default;
$text = $_SESSION['text'] ?? '';
$tokens = $_SESSION['tokens'] ?? [];
$entity_format = $_SESSION['format'] ?? 'named';

// Очищаем сессию
unset($_SESSION['original'], $_SESSION['text'], $_SESSION['tokens'], $_SESSION['encoding']);
?>
<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Типограф</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="h-full bg-gray-50">
<div class="container mx-auto px-4 h-full flex flex-col">
    <div class="py-6">
        <div class="flex-1 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="flex flex-col h-full">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                    <span class="inline-block">- Это "Типограф"?</span>
                </h1>
            </div>
            <!-- Right Column -->
            <div class="flex flex-col h-full">
                <p class="text-2xl md:text-3xl font-bold text-gray-800">
                    <span class="inline-block">— Да, это «Типограф»!</span>
                </p>
            </div>
        </div>
    </div>

    <form method="post" action="" class="flex-1 flex flex-col pb-8">
        <div class="flex-1 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column - Input -->
            <div class="flex flex-col h-full">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">
                    <label class="h-full">
                    <textarea name="text"
                              class="flex-1 p-4 w-full h-full resize-none border border-gray-200 rounded-t-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-gray-800 placeholder-gray-400"
                              placeholder="Введите текст для обработки…"><?= htmlspecialchars($original) ?></textarea>
                    </label>
                    <div class="border-t border-gray-200 p-4 bg-gray-50">
                        <div class="flex flex-wrap gap-4 items-center justify-between">
                            <div class="flex items-center gap-4">
                                <?php foreach ($entity_format_options as $entity_format_option): ?>
                                    <div class="flex items-center">
                                        <input id="encoding_<?= $entity_format_option ?>" name="format" type="radio"
                                               value="<?= $entity_format_option ?>"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                <?= $entity_format === $entity_format_option ? 'checked' : '' ?>>
                                        <label for="encoding_<?= $entity_format_option ?>"
                                               class="ml-2 text-sm text-gray-700">
                                            <?= $entity_format_option ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-magic mr-2"></i> Обработать
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Output -->
            <div class="flex flex-col h-full">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button type="button"
                                    class="tab-button active py-4 px-4 text-sm font-medium text-center border-b-2 border-blue-500 text-blue-600"
                                    data-target="result">
                                Результат
                            </button>
                            <button type="button"
                                    class="tab-button py-4 px-4 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                                    data-target="tokens">
                                Токенизация
                            </button>
                        </nav>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <div id="result" class="tab-content h-full active">
                            <label class="h-full">
                            <textarea
                                    class="w-full h-full p-4 resize-none border border-gray-200 rounded-b-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white text-gray-800"
                                    readonly><?= htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                            </label>
                        </div>
                        <div id="tokens" class="tab-content h-full hidden">
                            <label class="h-full">
                            <textarea
                                    class="w-full h-full p-4 resize-none border border-gray-200 rounded-b-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white text-gray-800"
                                    readonly><?= htmlspecialchars(json_encode($tokens, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></textarea>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Tab switching functionality
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', () => {
            // Update button states
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });

            button.classList.add('active', 'border-blue-500', 'text-blue-600');
            button.classList.remove('border-transparent', 'text-gray-500');

            // Update content visibility
            const target = button.getAttribute('data-target');
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            document.getElementById(target).classList.remove('hidden');
            document.getElementById(target).classList.add('active');
        });
    });
</script>
</body>
</html>