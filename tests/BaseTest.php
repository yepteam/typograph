<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class BaseTest extends TestCase
{
    public function testEmptyString()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $this->assertSame('', $typograph->format(''));
    }

    public function testEmptySpaces()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $this->assertSame('', $typograph->format(' '));
        $this->assertSame('', $typograph->format('   '));
    }

    public function testExtraSpaces()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'nbsp' => [],
        ]);

        $this->assertSame(
            'В этом тексте лишние пробелы и табуляции',
            $typograph->format("В    этом    тексте  лишние   пробелы  и    \t табуляции")
        );
    }

    public function testLeadingTabs()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'nbsp' => []
        ]);

        $this->assertSame(
            "\t\t\tТабуляции в начале строки не трогаем",
            $typograph->format("\t\t\tТабуляции в начале строки не\t трогаем")
        );
    }

    public function testSerializedStr()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $str = 's:11:"Привет, мир";';
        $this->assertSame($str, $typograph->format($str));
    }

    public function testSerializedValuesAreNotFormatted()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $object = new stdClass();
        $object->title = 'Он сказал "привет" - и ушёл...';

        $values = [
            serialize(['text' => 'Он сказал "привет" - и ушёл', 'n' => 1.5, 'ok' => true, 'none' => null]),
            serialize(['nested' => ['a' => 'x - y; z', 'b' => [1, 2, 3]]]),
            serialize($object),
            serialize("строка с\nпереводом строки - и \"кавычками\""),
            serialize(false),
            serialize(42),
            serialize(-0.5),
        ];

        foreach ($values as $str) {
            $this->assertSame($str, $typograph->format($str));
        }
    }

    public function testTextLikeSerializedPrefixIsFormatted()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_RAW,
            'nbsp' => [],
        ]);

        $this->assertSame('a: «Цитата» — это; тест', $typograph->format('a: "Цитата" - это; тест'));
        $this->assertSame('s: он сказал «привет» — и ушёл; потом', $typograph->format('s: он сказал "привет" - и ушёл; потом'));
        $this->assertSame('O: заметка; «текст»', $typograph->format('O: заметка; "текст"'));
        $this->assertSame('i:5; «текст»', $typograph->format('i:5; "текст"'));
    }

    public function testDebugTrue()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'debug' => true,
        ]);

        $typograph->format('И так далее');
        $tokens = $typograph->getTokens();

        $nbsp_rules = json_encode($tokens[1]['rules'] ?? '');
        $this->assertStringContainsString("ShortWord", $nbsp_rules);
    }

    public function testDebugFalse()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'debug' => false,
        ]);

        $typograph->format('И так далее');
        $tokens = $typograph->getTokens();

        $nbsp_rules = json_encode($tokens[1]['rules'] ?? '');
        $this->assertStringNotContainsString("ShortWord", $nbsp_rules);
    }

    /**
     * Имена правил, записанные в отладочную информацию токенов
     */
    private static function collectLoggedRules(array $tokens): string
    {
        $rules = [];
        foreach ($tokens as $token) {
            foreach (array_keys($token['rules'] ?? []) as $rule) {
                $rules[] = $rule;
            }
        }

        return implode("\n", $rules);
    }

    public function testDebugLogsTimesRule()
    {
        $typograph = new Typograph(['entities' => Typograph::ENTITIES_NAMED, 'debug' => true]);

        $typograph->format('2 x 2');
        $this->assertStringContainsString('ReplaceTimes', self::collectLoggedRules($typograph->getTokens()));

        $typograph->format('10x20');
        $this->assertStringContainsString('ReplaceTimes', self::collectLoggedRules($typograph->getTokens()));
    }

    public function testDebugLogsNonBreakingHyphenInWordRule()
    {
        $typograph = new Typograph(['entities' => Typograph::ENTITIES_NAMED, 'debug' => true]);

        $typograph->format('из-за');
        $this->assertStringContainsString('NonBreakingHyphen', self::collectLoggedRules($typograph->getTokens()));
    }

    public function testDebugLogsNumberRule()
    {
        $typograph = new Typograph(['entities' => Typograph::ENTITIES_NAMED, 'debug' => true]);

        $typograph->format('Дом № 5');
        $this->assertStringContainsString('Nbsp\Number', self::collectLoggedRules($typograph->getTokens()));
    }

    public function testNoDebugKeysWithoutDebug()
    {
        $typograph = new Typograph(['entities' => Typograph::ENTITIES_NAMED, 'debug' => false]);

        $texts = [
            'Он сказал "привет "мир"" и ушёл',
            '2 x 2 и 10x20',
            'из-за 5-й раз',
            'Дом № 5, 10 000 руб., XIV век',
        ];

        foreach ($texts as $text) {
            $typograph->format($text);
            foreach ($typograph->getTokens() as $token) {
                $extraKeys = array_diff(array_keys($token), ['type', 'value', 'name']);
                $this->assertSame([], array_values($extraKeys), "text: $text");
            }
        }
    }

    public function testStateIsResetForEmptyText()
    {
        $typograph = new Typograph(['entities' => Typograph::ENTITIES_NAMED]);

        $typograph->format('Он сказал "привет"');
        $this->assertSame('', $typograph->format(''));

        $metrics = $typograph->getMetrics();
        $this->assertSame(0, $metrics['originalContentLength']);
        $this->assertSame(0, $metrics['resultContentLength']);
        $this->assertSame([], $typograph->getTokens());
    }

    public function testTokensAreResetForUnformattedText()
    {
        $typograph = new Typograph(['entities' => Typograph::ENTITIES_NAMED]);

        // Сериализованное значение и невалидный UTF-8 возвращаются без обработки
        foreach (['s:5:"hello";', "Привет \xFF мир"] as $text) {
            $typograph->format('Он сказал "привет"');
            $this->assertSame($text, $typograph->format($text));

            $metrics = $typograph->getMetrics();
            $this->assertSame($metrics['originalContentLength'], $metrics['resultContentLength']);
            $this->assertSame([], $typograph->getTokens());
        }
    }

}
