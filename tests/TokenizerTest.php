<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Tokenizer;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class TokenizerTest extends TestCase
{
    private function plain(): Typograph
    {
        return new Typograph([
            'entities' => Typograph::ENTITIES_RAW,
            'nbsp' => [],
        ]);
    }

    // ─────────────────────────────────────────────
    // Невалидный UTF-8
    // ─────────────────────────────────────────────

    public function testInvalidUtf8IsReturnedUnchanged()
    {
        $original = "Текст \xff с битым байтом - и тире";
        $this->assertSame($original, $this->plain()->format($original));
    }

    public function testTokenizerDoesNotHangOnInvalidUtf8()
    {
        $tokens = (new Tokenizer())->tokenize("ab \xff cd");
        $this->assertSame("ab \xff cd", (new Tokenizer())->toString($tokens));
    }

    // ─────────────────────────────────────────────
    // Буквальная последовательность \n
    // ─────────────────────────────────────────────

    public function testLiteralBackslashNIsNotLineBreak()
    {
        $original = 'Путь C:\new\folder и printf("a\n")';
        $result = $this->plain()->format($original);

        $this->assertStringNotContainsString("\n", $result);
        $this->assertStringContainsString('C:\new\folder', $result);
        $this->assertStringContainsString('a\n', $result);
    }

    // ─────────────────────────────────────────────
    // Метки специальных тегов (script, style, pre)
    // ─────────────────────────────────────────────

    public function testSpecialTagDoesNotLeakIntoNextCall()
    {
        $typograph = $this->plain();
        $typograph->format('<pre>SECRET</pre>');

        $original = 'Текст [SPECIAL_TAG:pre:0] конец';
        $this->assertSame($original, $typograph->format($original));
    }

    public function testPlaceholderLikeTextIsPreserved()
    {
        $original = '<b>x</b> [SPECIAL_TAG:pre:5] y';
        $this->assertSame($original, $this->plain()->format($original));
    }

    public function testSpecialTagsAreStillRestored()
    {
        $original = "<pre>a  --  \"b\"</pre> и <script>if (a -- b) {}</script> текст";
        $this->assertSame($original, $this->plain()->format($original));
    }

    public function testSpecialTagGluedToUrlIsRestored()
    {
        $original = 'Сайт www.site.ru<pre>a -- "b"</pre> текст';
        $this->assertSame($original, $this->plain()->format($original));
    }

    public function testSpecialTagInsideShortcodeIsRestored()
    {
        $original = '[block html="<pre>a -- b</pre>"] текст';
        $this->assertSame($original, $this->plain()->format($original));
    }

    // ─────────────────────────────────────────────
    // Распознавание токенов в середине строки
    // ─────────────────────────────────────────────

    public function testTokensInTheMiddleOfLine()
    {
        $tokens = (new Tokenizer())->tokenize(
            'Сайт https://example.com, почта mail@example.com, автор А. С. Пушкин, 123abc'
        );

        $byValue = [];
        foreach ($tokens as $token) {
            $byValue[$token['value']] = $token['type'];
        }

        $this->assertSame('url', $byValue['https://example.com,'] ?? $byValue['https://example.com'] ?? null);
        $this->assertSame('email', $byValue['mail@example.com'] ?? null);
        $this->assertSame('initial', $byValue['А.'] ?? null);
        $this->assertSame('initial', $byValue['С.'] ?? null);
        $this->assertSame('number', $byValue['123'] ?? null);
        $this->assertSame('word', $byValue['abc'] ?? null);
    }

    // ─────────────────────────────────────────────
    // Производительность на длинной строке
    // ─────────────────────────────────────────────

    public function testLongSingleLineIsProcessedInLinearTime()
    {
        $paragraph = '<p>В 2024 году компания "Ромашка" - лидер рынка - выпустила 10 x 20 продуктов, т. е. на 5% больше...</p>';
        $text = str_repeat($paragraph, 320); // ~33 тыс. символов в одной строке

        $typograph = new Typograph();
        $typograph->format($text);

        $this->assertLessThan(1.0, $typograph->getMetrics()['tokenizationTime']);
    }

    // ─────────────────────────────────────────────
    // Переводы строк не зависят от платформы
    // ─────────────────────────────────────────────

    public function testLfLineEndingsArePreserved()
    {
        $this->assertSame("строка 1\nстрока 2", $this->plain()->format("строка 1\nстрока 2"));
    }

    public function testCrLfLineEndingsArePreserved()
    {
        $this->assertSame("строка 1\r\nстрока 2", $this->plain()->format("строка 1\r\nстрока 2"));
    }

    public function testNewlineInsideTagIsPreserved()
    {
        $original = "<a\nhref=\"#\">ссылка</a>\nтекст";
        $this->assertSame($original, $this->plain()->format($original));
    }
}
