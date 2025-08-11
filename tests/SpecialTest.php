<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class SpecialTest extends TestCase
{
    public function testReplaceCopyright()
    {
        $typograph = new Typograph(['entities' => 'named', 'nbsp' => []]);
        $typographRaw = new Typograph(['entities' => 'raw', 'nbsp' => []]);

        $c_letters = [
            'c', // Latin Lower
            'с', // Cyrillic Lower
            'C', // Latin Upper
            'С', // Cyrillic Upper
        ];

        foreach ($c_letters as $letter) {
            $original = sprintf('(%s)', $letter);
            $this->assertSame('&copy;', $typograph->format($original));
            $this->assertSame('©', $typographRaw->format($original));

            $original = sprintf('Copyright (%s) 2025', $letter);
            $this->assertSame('Copyright &copy; 2025', $typograph->format($original));
            $this->assertSame('Copyright © 2025', $typographRaw->format($original));
        }
    }

    public function testReplacePlusMinus()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $typographRaw = new Typograph(['entities' => 'raw']);

        // Базовый тест
        $this->assertSame('&plusmn;', $typograph->format('+-'));
        $this->assertSame('±', $typographRaw->format('+-'));

        // В начале строки и числом справа
        $this->assertSame('&plusmn;2', $typograph->format('+-2'));
        $this->assertSame('±2', $typographRaw->format('+-2'));

        // С числом слева и справа
        $this->assertSame('100&plusmn;2', $typograph->format('100+-2'));
        $this->assertSame('100±2', $typographRaw->format('100+-2'));

        // Отрицательный тест
        $this->assertStringNotContainsString('&plusmn;', $typograph->format('C++-API'));
        $this->assertStringNotContainsString('±', $typographRaw->format('C++-API'));
    }

    public function testReplaceTimes()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $typographRaw = new Typograph(['entities' => 'raw']);

        $x_letters = [
            'x', // Latin Lower
            'х', // Cyrillic Lower
        ];

        foreach ($x_letters as $letter) {
            $original = sprintf('2%s2', $letter);
            $this->assertSame('2&times;2', $typograph->format($original));
            $this->assertSame('2×2', $typographRaw->format($original));

            $original = sprintf('25%s100', $letter);
            $this->assertSame('25&times;100', $typograph->format($original));
            $this->assertSame('25×100', $typographRaw->format($original));

            $original = sprintf('640%s480', $letter);
            $this->assertSame('640&times;480', $typograph->format($original));
            $this->assertSame('640×480', $typographRaw->format($original));

            $original = sprintf('2 %s 2', $letter);
            $this->assertSame('2&times;2', $typograph->format($original));
            $this->assertSame('2×2', $typographRaw->format($original));

            $original = sprintf('10.5%s5.5', $letter);
            $this->assertSame('10.5&times;5.5', $typograph->format($original));
            $this->assertSame('10.5×5.5', $typographRaw->format($original));

            $original = sprintf('10,5%s5,5', $letter);
            $this->assertSame('10,5&times;5,5', $typograph->format($original));
            $this->assertSame('10,5×5,5', $typographRaw->format($original));

            $original = sprintf('20 %s 30 %s 40', $letter, $letter);
            $this->assertSame('20&times;30&times;40', $typograph->format($original));
            $this->assertSame('20×30×40', $typographRaw->format($original));
        }

        // Отрицательный тест: заглавные буквы не применимы
        foreach ($x_letters as $letter) {
            $letter = mb_strtoupper($letter);

            $original = sprintf('2%s2', $letter);
            $this->assertNotSame('2&times;2', $typograph->format($original));
            $this->assertNotSame('2×2', $typographRaw->format($original));

            $original = sprintf('25%s100', $letter);
            $this->assertNotSame('25&times;100', $typograph->format($original));
            $this->assertNotSame('25×100', $typographRaw->format($original));

            $original = sprintf('640%s480', $letter);
            $this->assertNotSame('640&times;480', $typograph->format($original));
            $this->assertNotSame('640×480', $typographRaw->format($original));

            $original = sprintf('2 %s 2', $letter);
            $this->assertNotSame('2&times;2', $typograph->format($original));
            $this->assertNotSame('2×2', $typographRaw->format($original));

            $original = sprintf('10.5%s5.5', $letter);
            $this->assertNotSame('10.5&times;5.5', $typograph->format($original));
            $this->assertNotSame('10.5×5.5', $typographRaw->format($original));

            $original = sprintf('10,5%s5,5', $letter);
            $this->assertNotSame('10,5&times;5,5', $typograph->format($original));
            $this->assertNotSame('10,5×5,5', $typographRaw->format($original));

            $original = sprintf('20 %s 30 %s 40', $letter, $letter);
            $this->assertNotSame('20&times;30&times;40', $typograph->format($original));
            $this->assertNotSame('20×30×40', $typographRaw->format($original));
        }
    }


}
