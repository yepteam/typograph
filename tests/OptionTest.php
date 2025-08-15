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
        $defaultOptions['entities'] = 'named';
        $this->assertEquals($options, $defaultOptions);
    }

    public function testTrue()
    {
        $typograph = new Typograph(true);
        $options = $typograph->getOptions();
        $defaultOptions = $typograph->getDefaultOptions();
        $defaultOptions['entities'] = 'raw';
        $this->assertEquals($options, $defaultOptions);
    }

    public function testDisabledOptions()
    {
        $typograph = new Typograph([ 'dash' => false ]);
        $options = $typograph->getOptions();
        $defaultOptions = $typograph->getDefaultOptions();
        $defaultOptions['dash'] = [];
        $this->assertEquals($options, $defaultOptions);

        $typograph = new Typograph([ 'nbsp' => false ]);
        $options = $typograph->getOptions();
        $defaultOptions = $typograph->getDefaultOptions();
        $defaultOptions['nbsp'] = [];
        $this->assertEquals($options, $defaultOptions);

        $typograph = new Typograph([ 'special' => false ]);
        $options = $typograph->getOptions();
        $defaultOptions = $typograph->getDefaultOptions();
        $defaultOptions['special'] = [];
        $this->assertEquals($options, $defaultOptions);
    }

    public function testPartiallyChangedOptions()
    {
        $typograph = new Typograph([ 'dash' => [
            'hyphen-to-mdash' => false
        ] ]);
        $options = $typograph->getOptions();
        $defaultOptions = $typograph->getDefaultOptions();
        $defaultOptions['dash']['hyphen-to-mdash'] = false;
        $this->assertEquals($options, $defaultOptions);
    }

}
