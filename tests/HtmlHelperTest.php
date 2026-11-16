<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Helpers\HtmlHelper;

require_once __DIR__ . '/../vendor/autoload.php';

final class HtmlHelperTest extends TestCase
{
    public function testNewlinesInOpeningTagsAreReplacedAndRestored()
    {
        $original = "<script\nsrc=\"a.js\">x\ny</script>\n<pre\r\nclass=\"p\">a\nb</pre>";

        $replaced = HtmlHelper::replaceNewlinesInTags($original, 'MARK');
        $this->assertSame("<script MARK src=\"a.js\">x\ny</script>\n<pre MARK class=\"p\">a\nb</pre>", $replaced);

        $this->assertSame(
            "<script\nsrc=\"a.js\">x\ny</script>\n<pre\nclass=\"p\">a\nb</pre>",
            HtmlHelper::restoreNewlinesInTags($replaced, "\n", 'MARK')
        );
    }

    public function testLongUnclosedPreIsNotLost()
    {
        // Длинный текст после незакрытого тега не должен теряться
        $original = '<pre class="x">' . str_repeat("a b\n", 300000);

        // Сравниваем длину и хэш, а не сами строки: diff мегабайтных строк строится очень долго
        foreach ([
            'replace' => HtmlHelper::replaceNewlinesInTags($original),
            'restore' => HtmlHelper::restoreNewlinesInTags($original),
        ] as $method => $result) {
            $this->assertSame(strlen($original), strlen($result), $method);
            $this->assertSame(md5($original), md5($result), $method);
        }
    }
}
