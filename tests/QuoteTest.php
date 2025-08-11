<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class QuoteTest extends TestCase
{
    public function testLevel1()
    {
        $typograph = new Typograph([
            'entities' => 'raw',
            'nbsp' => [],
        ]);

        $original = '"Электроника"';
        $expected = '«Электроника»';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Часы "Электроника"';
        $expected = 'Часы «Электроника»';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Часы "Электроника" в корпусе из нержавеющей стали';
        $expected = 'Часы «Электроника» в корпусе из нержавеющей стали';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'автор-“классик“';
        $expected = 'автор-«классик»';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testLevel2()
    {
        $typograph = new Typograph([
            'entities' => 'raw',
            'dash' => [],
            'nbsp' => [],
        ]);

        $original = '«Часы "Электроника ЧН-54" в корпусе из нержавеющей стали»';
        $expected = '«Часы „Электроника ЧН-54“ в корпусе из нержавеющей стали»';
        $this->assertSame($expected, $typograph->format($original));

        $original = '""Белград" Отель"';
        $expected = '«„Белград“ Отель»';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testAfterNumber()
    {
        $typograph = new Typograph([
            'entities' => 'raw',
            'nbsp' => [],
        ]);

        $original = '"Концерт 5"';
        $this->assertSame('«Концерт 5»', $typograph->format($original));

        $original = '"Концерт № 5"';
        $this->assertSame('«Концерт № 5»', $typograph->format($original));

        $original = '("Концерт № 5")';
        $this->assertSame('(«Концерт № 5»)', $typograph->format($original));

        $original = '(«Концерт № 5″ на музыку Прокофьева)';
        $this->assertSame('(«Концерт № 5» на музыку Прокофьева)', $typograph->format($original));
    }

    public function testNoSpace()
    {
        $typograph = new Typograph([
            'entities' => 'raw',
            'nbsp' => [],
        ]);

        $original = '"Приход"/"Расход"';
        $expected = '«Приход»/«Расход»';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testCustomQuotes()
    {
        $typograph = new Typograph([
            'quotes' => [
                ['‹ ', ' ›'],
                ['›', '‹'],
            ],
            'dash' => [],
            'entities' => 'raw',
        ]);

        $original = '"Часы "Электроника ЧН-54" в корпусе из нержавеющей стали"';
        $expected = '‹ Часы ›Электроника ЧН-54‹ в корпусе из нержавеющей стали ›';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testDeep()
    {
        $typograph = new Typograph([
            'quotes' => [
                ['«', '»'],
                ['„', '“'],
                ['‘', '’'],
                ['“', '”'],
            ],
            'entities' => 'raw',
            'nbsp' => []
        ]);

        $original = 'Он вздохнул и сказал: "Мне вчера пришло письмо со словами: "Вот что написал автор: "Этот символ — "золотой ключ" — нельзя терять", а потом добавил кое-что ещё". Я даже не знал, как на это реагировать".';
        $expected = 'Он вздохнул и сказал: «Мне вчера пришло письмо со словами: „Вот что написал автор: ‘Этот символ — “золотой ключ” — нельзя терять’, а потом добавил кое-что ещё“. Я даже не знал, как на это реагировать».';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testOdd()
    {
        $typograph = new Typograph([
            'entities' => 'raw',
            'nbsp' => [],
        ]);

        $original = '«ООО «Рога и копыта»';
        $expected = '«ООО «Рога и копыта»';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testMultiline()
    {
        $typograph = new Typograph([
            'entities' => 'raw',
            'nbsp' => [],
        ]);

        $original = '(глава "Записок" Мертвого дома)
(Из книги "Истина и метод")';
        $expected = '(глава «Записок» Мертвого дома)
(Из книги «Истина и метод»)';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testApos()
    {
        $typograph = new Typograph([
            'entities' => 'raw',
            'nbsp' => [],
        ]);

        $original = trim(<<<EOF
"Hello 'world'"  
'Hello "world"'  
"Hello 'world'"  
'Hello "world"'
EOF
        );
        $expected = trim(<<<EOF
«Hello ’world’»
’Hello «world»’
«Hello ’world’»
’Hello «world»’
EOF
        );

        $this->assertSame($expected, $typograph->format($original));

        // https://habr.com/ru/articles/57351/
        $original = "Д'Артаньян, Сара О'Коннор";
        $expected = "Д’Артаньян, Сара О’Коннор";
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testPrime()
    {
        $typograph = new Typograph([
            'entities' => 'named',
            'nbsp' => [],
        ]);

        $original = 'Труба 3/4"';
        $expected = 'Труба 3/4&Prime;';
        $this->assertSame($expected, $typograph->format($original));

        $original = "59° 57' 00\"";
        $expected = '59&deg; 57&prime; 00&Prime;';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testReversed()
    {
        $typograph = new Typograph([
            'entities' => 'raw',
            'nbsp' => [],
        ]);

        $original = '»Есть люди, которых интересует именно «Лебединое озеро», — сказал он с улыбкой.';
        $expected = '«Есть люди, которых интересует именно «Лебединое озеро», — сказал он с улыбкой.';
        $this->assertSame($expected, $typograph->format($original));

        $original = '"Есть люди, которых интересует именно "Лебединое озеро", — сказал он.';
        $expected = '«Есть люди, которых интересует именно «Лебединое озеро», — сказал он.';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testQuotesInHtml()
    {
        $typograph = new Typograph([
            'entities' => 'raw',
            'nbsp' => [],
            'dash' => [],
        ]);

        $original = '<p>test - "test"</p><p>"test" - test</p>';
        $expected = '<p>test - «test»</p><p>«test» - test</p>';
        $this->assertSame($expected, $typograph->format($original));
    }

}