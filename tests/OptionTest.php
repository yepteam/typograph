<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class OptionTest extends TestCase
{
    public function testDefaultOptions()
    {
        $typograph = new Typograph();

        $options = $typograph->getOptions();
        $defaultOptions = $typograph->getDefaultOptions();
        $this->assertEquals($options, $defaultOptions);
    }

    public function testFalse()
    {
        $typograph = new Typograph(false);

        $options = $typograph->getOptions();
        $defaultOptions = $typograph->getDefaultOptions();
        $defaultOptions['entities'] = Typograph::ENTITIES_NAMED;
        $this->assertEquals($options, $defaultOptions);
    }

    public function testTrue()
    {
        $typograph = new Typograph(true);
        $options = $typograph->getOptions();
        $defaultOptions = $typograph->getDefaultOptions();
        $defaultOptions['entities'] = Typograph::ENTITIES_RAW;
        $this->assertEquals($options, $defaultOptions);
    }

    public function testDisabledOptions()
    {
        $typograph = new Typograph([
            'dash' => false,
        ]);

        $options = $typograph->getOptions();
        $defaultOptions = $typograph->getDefaultOptions();
        $defaultOptions['dash'] = [];
        $this->assertEquals($options, $defaultOptions);

        $typograph = new Typograph([
            'nbsp' => false,
        ]);

        $options = $typograph->getOptions();
        $defaultOptions = $typograph->getDefaultOptions();
        $defaultOptions['nbsp'] = [];
        $this->assertEquals($options, $defaultOptions);

        $typograph = new Typograph([
            'special' => false,
        ]);

        $options = $typograph->getOptions();
        $defaultOptions = $typograph->getDefaultOptions();
        $defaultOptions['special'] = [];
        $this->assertEquals($options, $defaultOptions);
    }

    public function testPartiallyChangedOptions()
    {
        $typograph = new Typograph([
            'dash' => [
                'hyphen-to-mdash' => false
            ]
        ]);

        $options = $typograph->getOptions();
        $defaultOptions = $typograph->getDefaultOptions();
        $defaultOptions['dash']['hyphen-to-mdash'] = false;
        $this->assertEquals($options, $defaultOptions);
    }

    public function testEllipsisOption()
    {
        $original = 'Итак... Ну… ладно';

        $expected = [
            Typograph::ELLIPSIS_HELLIP => 'Итак… Ну… ладно',
            Typograph::ELLIPSIS_DOTS => 'Итак... Ну... ладно',
            Typograph::ELLIPSIS_NONE => 'Итак... Ну… ладно',
        ];

        foreach ($expected as $ellipsis => $result) {
            $typograph = new Typograph([
                'entities' => Typograph::ENTITIES_RAW,
                'nbsp' => [],
                'ellipsis' => $ellipsis,
            ]);

            $this->assertSame($result, $typograph->format($original), "ellipsis: $ellipsis");
        }
    }

}
