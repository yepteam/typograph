<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class HtmlTagTest extends TestCase
{
    public function testTag()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '<b>Текст</b>';
        $expected = '<b>Текст</b>';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testTagEntitiesRaw()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_RAW,
        ]);

        // Стандартный HTML-тег остаётся нетронутым
        $original = '<b>Жирный</b>';
        $expected = '<b>Жирный</b>';
        $this->assertSame($expected, $typograph->format($original));

        // Кастомный HTML5-тег остаётся нетронутым
        $original = '<my-component>Текст</my-component>';
        $expected = '<my-component>Текст</my-component>';
        $this->assertSame($expected, $typograph->format($original));

        // Тег c namespace остаётся нетронутым
        $original = '<isbn:number>1568491379</isbn:number>';
        $expected = '<isbn:number>1568491379</isbn:number>';
        $this->assertSame($expected, $typograph->format($original));

        // Уже закодированный "тег" не превращаем обратно
        $original = '&lt;script&gt;alert(1)&lt;/script&gt;';
        $expected = '&lt;script&gt;alert(1)&lt;/script&gt;';
        $this->assertSame($expected, $typograph->format($original));

        // Частично закодированные конструкции — тоже безопасно
        $original = '&lt;TOKEN&gt; <';
        $expected = '&lt;TOKEN&gt; <';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testEntitiesNamed()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        // Стандартный HTML-тег сохраняется
        $original = '<i>Курсив</i>';
        $expected = '<i>Курсив</i>';
        $this->assertSame($expected, $typograph->format($original));

        // Кастомный HTML5-тег сохраняется
        $original = '<custom-widget>Data</custom-widget>';
        $expected = '<custom-widget>Data</custom-widget>';
        $this->assertSame($expected, $typograph->format($original));

        // Тег c namespace сохраняется
        $original = '<isbn:number>1568491379</isbn:number>';
        $expected = '<isbn:number>1568491379</isbn:number>';
        $this->assertSame($expected, $typograph->format($original));

        // Уже закодированный "тег" не раскодируем (XSS-безопасность)
        $original = '&lt;img src=x onerror=alert(1)&gt;';
        $expected = '&lt;img src=x onerror=alert(1)&gt;';
        $this->assertSame($expected, $typograph->format($original));

        // Сырые символы & < > кодируются
        $original = '& < >';
        $expected = '&amp; &lt; &gt;';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testTagMultiline()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        /** @noinspection GrazieInspection */
        $original = '<button 
    class="btn btn-primary">
    Отправить   в  корзину
</button>';
        $expected = '<button 
    class="btn btn-primary">
Отправить в&nbsp;корзину
</button>';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testTagScript()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '<script>console.log("Hello World!");</script>';
        $expected = '<script>console.log("Hello World!");</script>';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testTagStyle()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '<style>body { font-family: "sans-serif" }</style>';
        $expected = '<style>body { font-family: "sans-serif" }</style>';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testTagPre()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '<pre>
    Ничего&nbsp;
    не  менять
</pre>';
        $expected = '<pre>
    Ничего&nbsp;
    не  менять
</pre>';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testTagWithAttribute()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = "<form data-default='{\"br\":\"<br>\"}'>Текст в форме</form>";
        $expected = "<form data-default='{\"br\":\"<br>\"}'>Текст в&nbsp;форме</form>";
        $this->assertSame($expected, $typograph->format($original));

        $original = '<button data-click="handleClick">Текст в кнопке</button>';
        $expected = '<button data-click="handleClick">Текст в&nbsp;кнопке</button>';
        $this->assertSame($expected, $typograph->format($original));

        $original = '<button @click="handleClick()">Текст в кнопке</button>';
        $expected = '<button @click="handleClick()">Текст в&nbsp;кнопке</button>';
        $this->assertSame($expected, $typograph->format($original));

        $original = '<button x-on:click="handleClick()">Текст в кнопке</button>';
        $expected = '<button x-on:click="handleClick()">Текст в&nbsp;кнопке</button>';
        $this->assertSame($expected, $typograph->format($original));

        $original = '<button v-on:click.prevent="handleClick()">Текст в кнопке</button>';
        $expected = '<button v-on:click.prevent="handleClick()">Текст в&nbsp;кнопке</button>';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testDoctype()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '<!DOCTYPE html>';
        $expected = '<!DOCTYPE html>';
        $this->assertSame($expected, $typograph->format($original));

        $original = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
        $expected = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
        $this->assertSame($expected, $typograph->format($original));

        $original = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" 
        "http://www.w3.org/TR/html4/strict.dtd">';
        $expected = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" 
        "http://www.w3.org/TR/html4/strict.dtd">';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testComment()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '<!-- Комментарий в коде -->';
        $expected = '<!-- Комментарий в коде -->';
        $this->assertSame($expected, $typograph->format($original));

        $original = '<!-- <tag/> -->';
        $expected = '<!-- <tag/> -->';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testOnlyHtml()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $html = '<div><p></p></div>';
        $this->assertSame($html, $typograph->format($html));
    }

    public function testXmlDeclaration()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'dash' => [],
            'quotes' => false,
        ]);

        $typographRaw = new Typograph([
            'entities' => Typograph::ENTITIES_RAW,
            'dash' => [],
            'quotes' => false,
        ]);

        $original = '<?xml version="1.0" encoding="UTF-8"?>';
        $this->assertSame($original, $typograph->format($original));
        $this->assertSame($original, $typographRaw->format($original));

        $original = '&lt;xml version="1.0" encoding="UTF-8"?&gt;';
        $this->assertSame($original, $typographRaw->format($original));
    }

    /**
     * @noinspection JSUnresolvedReference, CommaExpressionJS, JSValidateTypes
     */
    public function testScript()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
    (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
    (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

    ym(12345678, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true,
        ecommerce:"dataLayer"
    });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/12345678" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->';
        $this->assertSame($original, $typograph->format($original));
    }

    public function testPhpTag()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        // Однострочный PHP-тег
        $original = '<?php echo "Hello"; ?>';
        $expected = '<?php echo "Hello"; ?>';
        $this->assertSame($expected, $typograph->format($original));

        // Короткий вывод
        $original = '<?= $variable ?>';
        $expected = '<?= $variable ?>';
        $this->assertSame($expected, $typograph->format($original));

        // Многострочный PHP-тег
        $original = '<?php

get_template_part( \'templates/spoiler\', null, [
    \'caption\' => \'Подробнее\'
] );

?>';
        $expected = '<?php

get_template_part( \'templates/spoiler\', null, [
    \'caption\' => \'Подробнее\'
] );

?>';
        $this->assertSame($expected, $typograph->format($original));

        // PHP-тег внутри HTML — текст вокруг типографируется, PHP не трогается
        $original = '<div>Текст в блоке <?php echo $var; ?> и ещё текст</div>';
        $expected = '<div>Текст в&nbsp;блоке <?php echo $var; ?> и&nbsp;ещё текст</div>';
        $this->assertSame($expected, $typograph->format($original));

        // PHP-тег с WordPress-шаблоном
        $original = '<header>
<?php get_template_part( \'templates/header\' ); ?>
</header>
<main>Контент в блоке</main>';
        $expected = '<header>
<?php get_template_part( \'templates/header\' ); ?>
</header>
<main>Контент в&nbsp;блоке</main>';
        $this->assertSame($expected, $typograph->format($original));
    }

}
