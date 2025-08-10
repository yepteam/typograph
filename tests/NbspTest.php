<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class NbspTest extends TestCase
{
    public function testBeforeMdash()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        // https://www.artlebedev.ru/kovodstvo/sections/62/
        $original = 'Крикну — а в ответ тишина';
        $expected = 'Крикну&nbsp;&mdash; а';
        $this->assertStringStartsWith($expected, $typograph->format($original));

        $original = '(слева) — справа';
        $expected = '(слева)&nbsp;&mdash; справа';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '«слева» — справа';
        $expected = '&laquo;слева&raquo;&nbsp;&mdash; справа';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'слева $ — справа';
        $expected = 'слева $&nbsp;&mdash; справа';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'слева* — справа';
        $expected = 'слева*&nbsp;&mdash; справа';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '*слева* — справа';
        $expected = '*слева*&nbsp;&mdash; справа';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '1 — справа';
        $expected = '1&nbsp;&mdash; справа';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'слева, - справа';
        $expected = 'слева,&nbsp;';
        $this->assertStringStartsWith($expected, $typograph->format($original));
    }

    public function testNotBeforeMdash()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = '* — справа';
        $expected = '* &mdash; справа';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'слева * — справа';
        $expected = 'слева * &mdash; справа';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'слева ** &mdash; справа';
        $expected = 'слева ** &mdash; справа';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '\'слева\' — справа';
        $unexpected = '&nbsp;';
        $this->assertStringNotContainsString($unexpected, $typograph->format($original));
    }

    public function testAfterMdash()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = 'Вы всё ещё кипятите? — Тогда мы идём к вам!';
        $expected = 'кипятите? &mdash;&nbsp;Тогда';
        $this->assertStringContainsString($expected, $typograph->format($original));
    }

    public function testNotAfterMdash()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = 'см. 3.2 — 3.31.';
        $expected = '&mdash; 3.31.';
        $this->assertStringEndsWith($expected, $typograph->format($original));
    }

    public function testBeforeShortWord()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = 'Содержание подраздела А…';
        $expected = 'Содержание подраздела&nbsp;А';
        $this->assertStringStartsWith($expected, $typograph->format($original));

        $original = 'и т. д.';
        $expected = 'и&nbsp;т.&nbsp;д.';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'и др. приметы';
        $expected = 'и&nbsp;др. приметы';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'также см. изображение';
        $expected = 'также&nbsp;см. изображение';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Батарейка типа АА';
        $expected = 'Батарейка типа&nbsp;АА';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'сказал он, обернувшись';
        $expected = 'сказал&nbsp;он, обернувшись';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Что ж мне делать?';
        $expected = 'Что&nbsp;ж мне делать?';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testAfterShortWord()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        // https://www.artlebedev.ru/kovodstvo/sections/62/
        $original = 'Коляныч пошел за пивом';
        $expected = 'Коляныч пошел за&nbsp;пивом';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Что за чудесная погода';
        $expected = 'Что за&nbsp;чудесная погода';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'А вот раньше лучше было';
        $expected = 'А&nbsp;вот раньше лучше было';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Я ни за что не соглашусь';
        $expected = 'Я&nbsp;ни&nbsp;за&nbsp;что не&nbsp;соглашусь';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Всё не так уж плохо';
        $expected = 'Всё не&nbsp;так уж&nbsp;плохо';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Кожа да кости';
        $expected = 'Кожа да&nbsp;кости';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Батарейка АА заряжена';
        $expected = 'Батарейка АА&nbsp;заряжена';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'А когда обед?';
        $expected = 'А&nbsp;когда обед?';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testNotAfterShortWord()
    {
        $typograph = new Typograph([
            'entities' => 'named',
            'dash' => [],
        ]);

        $original = 'читайте с. 272-294';
        $expected = 'читайте&nbsp;с. 272-294';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '1820-е годы';
        $expected = '1820-е годы';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'и все же не могу';
        $expected = 'же не';
        $this->assertStringContainsString($expected, $typograph->format($original));
    }

    public function testBeforeNumber()
    {
        $typograph = new Typograph([
            'entities' => 'named',
        ]);

        $original = 'Начинается текст подраздела 1…';
        $expected = 'Начинается текст подраздела&nbsp;1';
        $this->assertStringStartsWith($expected, $typograph->format($original));

        $original = 'Яблоки 80 Арбуз 90';
        $expected = 'Яблоки&nbsp;80 Арбуз&nbsp;90';
        $this->assertStringStartsWith($expected, $typograph->format($original));

        $original = 'Палата № 6';
        $expected = '&nbsp;6';
        $this->assertStringEndsWith($expected, $typograph->format($original));

        $original = '€ 60-80';
        $expected = '&nbsp;60';
        $this->assertStringContainsString($expected, $typograph->format($original));
    }

    public function testNotBeforeNumber()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = '+ 2';
        $expected = '+ 2';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '= 4';
        $expected = '= 4';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '* 1';
        $expected = '* 1';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testAfterNumber()
    {
        $typograph = new Typograph([
            'entities' => 'named',
            'dash' => [],
        ]);

        $original = '8 201 794';
        $expected = '8&nbsp;201&nbsp;794';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'написана в 1895 году, опубликована';
        $expected = 'написана в&nbsp;1895&nbsp;году, опубликована';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '100 000 руб.';
        $expected = '100&nbsp;000&nbsp;руб.';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'нужно успеть к 1 мая';
        $expected = 'нужно успеть к&nbsp;1&nbsp;мая';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'нужно успеть к 16 мая';
        $expected = 'нужно успеть к&nbsp;16&nbsp;мая';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'нужно успеть к 1 мая, обязательно';
        $expected = 'нужно успеть к&nbsp;1&nbsp;мая, обязательно';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'нужно успеть к 16 мая, обязательно';
        $expected = 'нужно успеть к&nbsp;16&nbsp;мая, обязательно';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'это было в 300 году, нашей эры';
        $expected = 'это было в&nbsp;300&nbsp;году, нашей эры';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'нужно успеть к 2050 году, обязательно';
        $expected = 'нужно успеть к&nbsp;2050&nbsp;году, обязательно';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '60-80 % всей массы груза';
        $expected = '80&nbsp;%';
        $this->assertStringContainsString($expected, $typograph->format($original));

        $original = '60-80 ₽';
        $expected = '80&nbsp;';
        $this->assertStringContainsString($expected, $typograph->format($original));

        $original = '30 °С';
        $expected = '30&nbsp;';
        $this->assertStringContainsString($expected, $typograph->format($original));

        $original = '200 ГВт';
        $expected = '200&nbsp;ГВт';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '(4 картины)';
        $expected = '(4&nbsp;картины)';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'упомянута 9 раз («Лучшая опера»)';
        $expected = 'упомянута 9&nbsp;раз (';
        $this->assertStringStartsWith($expected, $typograph->format($original));

        $original = 'упомянута 3 раза';
        $expected = 'упомянута 3&nbsp;раза';
        $this->assertStringStartsWith($expected, $typograph->format($original));

        $original = "упомянута 3 раза" . PHP_EOL . "сегодня";
        $expected = "упомянута 3&nbsp;раза" . PHP_EOL . "сегодня";
        $this->assertSame($expected, $typograph->format($original));

        $original = "упомянута 1 раз" . PHP_EOL . "сегодня";
        $expected = "упомянута 1&nbsp;раз" . PHP_EOL . "сегодня";
        $this->assertSame($expected, $typograph->format($original));

        $original = 'упомянута 3 раза (в одном эпизоде)';
        $expected = 'упомянута 3&nbsp;раза (';
        $this->assertStringStartsWith($expected, $typograph->format($original));
    }

    public function testNotAfterNumber()
    {
        $typograph = new Typograph([
            'entities' => 'named',
            'dash' => [],
        ]);

        $original = 'представлена в 2006 году';
        $expected = 'представлена в&nbsp;2006 году';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Собеседования состоятся 14-24 сентября';
        $expected = 'Собеседования состоятся 14-24 сентября';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'это было в 300 году';
        $expected = 'это было в&nbsp;300 году';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'это было в 300 году' . PHP_EOL . 'Тогда';
        $expected = 'это было в&nbsp;300 году' . PHP_EOL . 'Тогда';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'нужно успеть к 2050 году';
        $expected = 'нужно успеть к&nbsp;2050 году';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'нужно успеть к 2050 году' . PHP_EOL . 'Иначе';
        $expected = 'нужно успеть к&nbsp;2050 году' . PHP_EOL . 'Иначе';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '2 +';
        $expected = '2 +';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '1-2 метра';
        $expected = '1-2 метра';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '1/3 метра';
        $expected = '1/3 метра';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '+5...+10 °С';
        $expected = '10 ';
        $this->assertStringContainsString($expected, $typograph->format($original));

        $original = 'за 200 миллионов рублей.';
        $expected = '200 миллионов';
        $this->assertStringContainsString($expected, $typograph->format($original));
    }

    public function testBeforeParticleWord()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = 'Съешь же ещё этих мягких французских булок';
        $expected = 'Съешь&nbsp;же ещё этих мягких французских булок';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Я бы подумал';
        $expected = 'Я&nbsp;бы подумал';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Было б чем гордиться';
        $expected = 'Было&nbsp;б чем гордиться';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'А был ли мальчик?';
        $expected = 'был&nbsp;ли мальчик?';
        $this->assertStringEndsWith($expected, $typograph->format($original));
    }

    public function testBeforeYear()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = '1941–1945 гг.';
        $expected = '1945&nbsp;гг.';
        $this->assertStringEndsWith($expected, $typograph->format($original));
    }

    public function testNotBeforeShortWord()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = 'Было дело, да';
        $expected = 'Было дело, да';
        $this->assertEquals($expected, $typograph->format($original));

        // Война и мир
        $original = 'вы уж не друг мой, вы уж не мой верный раб';
        $expected = 'вы&nbsp;уж не&nbsp;друг мой, вы&nbsp;уж не&nbsp;мой верный раб';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testInitial()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = '(подраздел Ф. М. Достоевского)';
        $expected = '(подраздел Ф.&nbsp;М.&nbsp;Достоевского)';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Москву И. И. Иванов увидел впервые';
        $expected = 'Москву И.&nbsp;И. Иванов увидел впервые';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'клиент И. И. Иванов зарегистрировался';
        $expected = 'клиент И.&nbsp;И.&nbsp;Иванов зарегистрировался';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'В начале предложения И. А. Бунин писал';
        $expected = 'предложения И.&nbsp;А.&nbsp;Бунин писал';
        $this->assertStringEndsWith($expected, $typograph->format($original));

        $original = 'Обернуто скобками (П. П. Шмидт)';
        $expected = 'Обернуто скобками (П.&nbsp;П.&nbsp;Шмидт)';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Двойная фамилия: М. А. Волошин-Петров';
        $expected = 'Двойная фамилия: М.&nbsp;А.&nbsp;Волошин-Петров';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Инициалы после фамилии: Достоевский Ф. М.';
        $expected = 'Инициалы после фамилии: Достоевский&nbsp;Ф.&nbsp;М.';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'сначала Иванов И. И. рассказывал';
        $expected = 'сначала Иванов&nbsp;И.&nbsp;И. рассказывал';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'позже Иванов И. И. Петровича позабыл';
        $expected = 'позже Иванов И.&nbsp;И. Петровича позабыл';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'сказал Иванов И. И. Оказывается';
        $expected = 'сказал Иванов И.&nbsp;И. Оказывается';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Лет 10 Иванов И. И. заблуждался';
        $expected = 'Лет&nbsp;10 Иванов&nbsp;И.&nbsp;И. заблуждался';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testTags()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = 'Согласен с <strong>условиями</strong> пользовательского соглашения';
        $expected = 'Согласен с&nbsp;<strong>условиями</strong> пользовательского соглашения';
        $this->assertStringStartsWith($expected, $typograph->format($original));

        $original = 'Согласен с<strong> условиями</strong> пользовательского соглашения';
        $expected = 'Согласен с<strong>&nbsp;условиями</strong> пользовательского соглашения';
        $this->assertStringStartsWith($expected, $typograph->format($original));
    }

    public function testBeforeSingleCharacter()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = 'миллионов ₽.';
        $expected = 'миллионов&nbsp;₽.';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testNoNbspNearTags()
    {
        $typograph = new Typograph([
            'entities' => 'named'
        ]);

        $original = '<p>Текст в</p> <p>Новой строке</p>';
        $expected = '<p>Текст в</p> <p>Новой строке</p>';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '<div>Текст в</div> <div>Новой строке</div>';
        $expected = '<div>Текст в</div> <div>Новой строке</div>';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '<div>Текст </div><div>— Новая строка</div>';
        $expected = '<div>Текст </div><div>&mdash;&nbsp;Новая строка</div>';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Текст в<br> Новой строке';
        $expected = 'Текст в<br> Новой строке';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Текст в <br>Новой строке';
        $expected = 'Текст в <br>Новой строке';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testNoNbspNearEmoji()
    {
        $typograph = new Typograph([
            'entities' => 'named',
        ]);

        $original = 'Привет 😀';
        $expected = 'Привет 😀';
        $this->assertEquals($expected, $typograph->format($original));

        // \u{231A}: ⌚ default emoji presentation character (Emoji_Presentation)
        $original = 'Привет ⌚';
        $expected = 'Привет ⌚';
        $this->assertEquals($expected, $typograph->format($original));

        // \u{2194}\u{FE0F}: ↔️ default text presentation character rendered as emoji
        $original = 'Привет ↔️';
        $expected = 'Привет &harr;️';
        $this->assertEquals($expected, $typograph->format($original));

        // \u{1F469}: 👩 emoji modifier base (Emoji_Modifier_Base)
        $original = 'Привет 👩';
        $expected = 'Привет 👩';
        $this->assertEquals($expected, $typograph->format($original));

        // \u{1F469}\u{1F3FF}: 👩🏿 emoji modifier base followed by a modifier
        $original = 'Привет 👩🏿';
        $expected = 'Привет 👩🏿';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testNbspNearAmp()
    {
        $typograph = new Typograph([
            'entities' => 'named',
        ]);

        $original = 'FAMILY &amp; CO';
        $expected = 'FAMILY &amp;&nbsp;CO';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Mr. & Mrs.';
        $expected = 'Mr.&nbsp;&amp;&nbsp;Mrs.';
        $this->assertEquals($expected, $typograph->format($original));

        $original = 'Dolce & Gabbana';
        $expected = 'Dolce &amp;&nbsp;Gabbana';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testNoNbspNearEntities()
    {
        $typograph = new Typograph([
            'entities' => 'named',
        ]);

        // Nbsp/Number

        $original = '1 &lt; 2';
        $expected = '1 &lt; 2';
        $this->assertEquals($expected, $typograph->format($original));

        $original = '1 &times; 2';
        $expected = '1 &times; 2';
        $this->assertEquals($expected, $typograph->format($original));

        // Nbsp/ShortWord

        $original = 'A &lt; B';
        $expected = 'A &lt; B';
        $this->assertEquals($expected, $typograph->format($original));
    }

    public function testNoNbspNearSpecialCharacters()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $original = '↑ ↓ ± ≈ ≠ 😊';
        $expected = '&uarr; &darr; &plusmn; &asymp; &ne; 😊';
        $this->assertEquals($expected, $typograph->format($original));
    }

}
