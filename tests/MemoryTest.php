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
    /**
     * Использование памяти: ~26M
     * Время выполнения: ~00:00.040
     */
    public function test4096()
    {
        $typograph = new Typograph();
        
        // Строка 64 символа
        $original = 'Часы наручные "Электроника 77А" в корпусе из нержавеющей стали.' . PHP_EOL;
        $expected = 'Часы наручные &laquo;Электроника 77А&raquo; в&nbsp;корпусе из&nbsp;нержавеющей стали.' . PHP_EOL;

        $count = 64; // повторения

        $longText = str_repeat($original, $count);
        $longExpected = trim(str_repeat($expected, $count));

        // Проверяем результат
        $this->assertEquals($longExpected, $typograph->format($longText));
    }

    /**
     * Использование памяти: ~38M
     * Время выполнения: ~00:00.330
     */
    public function test65536()
    {
        $typograph = new Typograph();
        
        // Строка 64 символа
        $original = 'Часы наручные "Электроника 77А" в корпусе из нержавеющей стали.' . PHP_EOL;
        $expected = 'Часы наручные &laquo;Электроника 77А&raquo; в&nbsp;корпусе из&nbsp;нержавеющей стали.' . PHP_EOL;

        $count = 1024; // повторения

        $longText = str_repeat($original, $count);
        $longExpected = trim(str_repeat($expected, $count));

        // Проверяем результат
        $this->assertEquals($longExpected, $typograph->format($longText));
    }
}