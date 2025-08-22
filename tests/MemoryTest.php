<?php declare(strict_types=1);

ini_set('memory_limit', '256M');

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

final class MemoryTest extends TestCase
{
    public function test64()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        // Строка 64 символа
        $original = 'Часы наручные "Электроника 77А" в корпусе из нержавеющей стали.' . PHP_EOL;
        $expected = 'Часы наручные &laquo;Электроника 77А&raquo; в&nbsp;корпусе из&nbsp;нержавеющей стали.' . PHP_EOL;

        $count = 64; // повторения

        $longText = str_repeat($original, $count);
        $longExpected = trim(str_repeat($expected, $count));

        // Проверяем результат
        $this->assertSame($longExpected, $typograph->format($longText));
        $this->assertLessThan(0.08, $typograph->getMetrics()['totalTime']);
        $this->assertLessThan(1.2 * 1024 * 1024, $typograph->getMetrics()['memoryUsage']);
    }

    public function test1024()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        // Строка 64 символа
        $original = 'Часы наручные "Электроника 77А" в корпусе из нержавеющей стали.' . PHP_EOL;
        $expected = 'Часы наручные &laquo;Электроника 77А&raquo; в&nbsp;корпусе из&nbsp;нержавеющей стали.' . PHP_EOL;

        $count = 1024; // повторения

        $longText = str_repeat($original, $count);
        $longExpected = trim(str_repeat($expected, $count));

        // Проверяем результат
        $this->assertSame($longExpected, $typograph->format($longText));
        $this->assertLessThan(0.8, $typograph->getMetrics()['totalTime']);
        $this->assertLessThan(12.0 * 1024 * 1024, $typograph->getMetrics()['memoryUsage']);
    }

    public function testTags1024()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        // Строка 64 символа
        $original = 'Часы <strong>"Электроника 77А"</strong> в корпусе из стали.' . PHP_EOL;
        $expected = 'Часы <strong>&laquo;Электроника 77А&raquo;</strong> в&nbsp;корпусе из&nbsp;стали.' . PHP_EOL;

        $count = 1024; // повторения

        $longText = str_repeat($original, $count);
        $longExpected = trim(str_repeat($expected, $count));

        // Проверяем результат
        $this->assertSame($longExpected, $typograph->format($longText));
        $this->assertLessThan(0.7, $typograph->getMetrics()['totalTime']);
        $this->assertLessThan(12.0 * 1024 * 1024, $typograph->getMetrics()['memoryUsage']);
    }
}