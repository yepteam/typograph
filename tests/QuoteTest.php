<?php declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class QuoteTest extends TestCase {
    public function testLevel1() {
        $original = '"Электроника"';
        $expected = '&laquo;Электроника&raquo;';
        $this->assertSame( $expected, Typograph::format( $original ) );

        $original = 'Часы "Электроника"';
        $expected = 'Часы &laquo;Электроника&raquo;';
        $this->assertSame( $expected, Typograph::format( $original ) );

        $original = 'Часы "Электроника" в корпусе из нержавеющей стали';
        $expected = 'Часы &laquo;Электроника&raquo; в';
        $this->assertStringStartsWith( $expected, Typograph::format( $original ) );

        $original = '"Правило 42"';
        $this->assertStringStartsWith( '&laquo;', Typograph::format( $original ) );
        $this->assertStringEndsWith( '&raquo;', Typograph::format( $original ) );

        $original = '"Правило № 42"';
        $this->assertStringStartsWith( '&laquo;', Typograph::format( $original ) );
        $this->assertStringEndsWith( '&raquo;', Typograph::format( $original ) );
    }

    public function testLevel2() {
        $original = '«Часы "Электроника ЧН-54" в корпусе из нержавеющей стали»';
        $expected = '&laquo;Часы &bdquo;Электроника ЧН-54&ldquo; в&nbsp;корпусе из&nbsp;нержавеющей стали&raquo;';
        $this->assertSame( $expected, Typograph::format( $original ) );
    }

    public function testOdd() {
        $original = '«ООО «Рога копыта»';
        $expected = '&laquo;ООО &laquo;Рога копыта&raquo;';
        $this->assertSame( $expected, Typograph::format( $original ) );
    }

    public function testMultiline() {
        $original = '(глава «Записок» Мертвого дома)
(Из книги «Истина и метод»)';
        $expected = '(глава &laquo;Записок&raquo; Мертвого дома)
(Из&nbsp;книги &laquo;Истина и&nbsp;метод&raquo;)';
        $this->assertSame( $expected, Typograph::format( $original ) );
    }

    public function testApos() {
        $original = trim( <<<EOF
"Hello 'world'"  
'Hello "world"'  
"Hello 'world'"  
'Hello "world"'
EOF
        );
        $expected = trim( <<<EOF
&laquo;Hello &rsquo;world&rsquo;&raquo;
&rsquo;Hello &laquo;world&raquo;&rsquo;
&laquo;Hello &rsquo;world&rsquo;&raquo;
&rsquo;Hello &laquo;world&raquo;&rsquo;
EOF
        );

        $this->assertSame( $expected, Typograph::format( $original ) );
    }

    public function testPrime() {
        $original = 'Труба 3/4"';
        $expected = 'Труба 3/4&Prime;';
        $this->assertSame( $expected, Typograph::format( $original ) );
    }

}