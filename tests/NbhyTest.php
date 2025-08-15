<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class NbhyTest extends TestCase
{

    public function testAfterNumber()
    {
        $typograph = new Typograph([
            'entities' => 'numeric',
            'dash' => [
                'hyphen-to-nbhy' => 2
            ],
            'nbsp' => [],
        ]);

        $original = 'конец 60-х годов';
        $expected = 'конец 60&#8209;х годов';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Ту-154';
        $expected = 'Ту&#8209;154';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Номер 5‑2024';
        $expected = 'Номер 5&#8209;2024';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'стр. 45-50';
        $expected = 'стр. 45&#8209;50';
        $this->assertSame($expected, $typograph->format($original));

        $original = '5‑километровая дистанция';
        $expected = '5&#8209;километровая дистанция';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'XX-XXI века';
        $expected = 'XX&#8209;XXI века';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testBeforeNumber()
    {
        $typograph = new Typograph([
            'entities' => 'numeric',
            'dash' => [
                'hyphen-to-nbhy' => 2
            ],
            'nbsp' => [],
        ]);

        $original = 'COVID-19';
        $expected = 'COVID&#8209;19';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Олимпиада-80';
        $expected = 'Олимпиада&#8209;80';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testPhoneNumber()
    {
        $typograph = new Typograph([
            'entities' => 'numeric',
            'dash' => [
                'hyphen-to-nbhy' => 2
            ],
            'nbsp' => [],
        ]);

        $original = '+7 (987) 654-32-10';
        $expected = '+7 (987) 654&#8209;32&#8209;10';
        $this->assertSame($expected, $typograph->format($original));

        $original = '8-800-250-50-50';
        $expected = '8&#8209;800-250&#8209;50&#8209;50';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testIsbn()
    {
        $typograph = new Typograph([
            'entities' => 'numeric',
            'dash' => [
                'hyphen-to-nbhy' => 2
            ],
            'nbsp' => [],
        ]);

        $original = 'ISBN 978-5-389-12345-6';
        $expected = 'ISBN 978&#8209;5&#8209;389-12345&#8209;6';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testShortWord()
    {
        $typograph = new Typograph([
            'entities' => 'numeric',
            'dash' => [
                'hyphen-to-nbhy' => 2
            ],
            'nbsp' => [],
        ]);

        $original = 'Wi-Fi';
        $expected = 'Wi&#8209;Fi';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testParticle()
    {
        $typograph = new Typograph([
            'entities' => 'numeric',
            'dash' => [
                'hyphen-to-nbhy' => 2
            ],
            'nbsp' => [],
        ]);

        $original = 'Что-то пошло не так';
        $expected = 'Что&#8209;то пошло не так';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'T-образный';
        $expected = 'T&#8209;образный';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNegative()
    {
        $typograph = new Typograph([
            'entities' => 'numeric',
            'dash' => [
                'hyphen-to-nbhy' => 2
            ],
            'nbsp' => [],
        ]);

        $original = 'Салтыков-Щедрин';
        $expected = 'Салтыков-Щедрин';
        $this->assertSame($expected, $typograph->format($original));

        $typograph = new Typograph([
            'entities' => 'numeric',
            'dash' => [
                'hyphen-to-nbhy' => 2
            ],
            'nbsp' => [],
        ]);

        $original = 'Ростов-на-Дону';
        $expected = 'Ростов-на-Дону';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'фото- и видео';
        $expected = 'фото- и видео';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'образовательно-развлекательный';
        $expected = 'образовательно-развлекательный';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNegativeUrl()
    {
        $typograph = new Typograph([
            'entities' => 'numeric',
            'dash' => [
                'hyphen-to-nbhy' => 2
            ],
            'nbsp' => [],
        ]);

        $original = 'https://example.com/an-example-of-page-slug';
        $expected = 'https://example.com/an-example-of-page-slug';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNegativeListMarker()
    {
        $typograph = new Typograph([
            'entities' => 'numeric',
            'dash' => [
                'hyphen-to-mdash' => false,
                'hyphen-to-nbhy' => true
            ],
            'nbsp' => [],
        ]);

        $original = '- Первый элемент списка';
        $expected = '- Первый элемент списка';
        $this->assertSame($expected, $typograph->format($original));
    }

}
