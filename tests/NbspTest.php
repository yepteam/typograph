<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class NbspTest extends TestCase
{
    public function testRemoveWrongNbsp()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = 'И так&nbsp;далее';
        $expected = 'И&nbsp;так далее';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testBeforeMdash()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        // https://www.artlebedev.ru/kovodstvo/sections/62/
        $original = 'Крикну — а в ответ тишина';
        $expected = 'Крикну&nbsp;&mdash; а';
        $this->assertStringStartsWith($expected, $typograph->format($original));

        $original = '(слева) — справа';
        $expected = '(слева)&nbsp;&mdash; справа';
        $this->assertSame($expected, $typograph->format($original));

        $original = '«слева» — справа';
        $expected = '&laquo;слева&raquo;&nbsp;&mdash; справа';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'слева $ — справа';
        $expected = 'слева $&nbsp;&mdash; справа';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'слева* — справа';
        $expected = 'слева*&nbsp;&mdash; справа';
        $this->assertSame($expected, $typograph->format($original));

        $original = '*слева* — справа';
        $expected = '*слева*&nbsp;&mdash; справа';
        $this->assertSame($expected, $typograph->format($original));

        $original = '1 — справа';
        $expected = '1&nbsp;&mdash; справа';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'слева, - справа';
        $expected = 'слева,&nbsp;';
        $this->assertStringStartsWith($expected, $typograph->format($original));

        $original = '[слева] - справа';
        $expected = '[слева]&nbsp;';
        $this->assertStringStartsWith($expected, $typograph->format($original));

        $original = '[left] - right';
        $expected = '[left]&nbsp;';
        $this->assertStringStartsWith($expected, $typograph->format($original));
    }

    public function testNotBeforeMdash()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '* — справа';
        $expected = '* &mdash; справа';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'слева * — справа';
        $expected = 'слева * &mdash; справа';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'слева ** &mdash; справа';
        $expected = 'слева ** &mdash; справа';
        $this->assertSame($expected, $typograph->format($original));

        $original = '\'слева\' — справа';
        $unexpected = '&nbsp;';
        $this->assertStringNotContainsString($unexpected, $typograph->format($original));
    }

    public function testAfterMdash()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = 'Вы всё ещё кипятите? — Тогда мы идём к вам!';
        $expected = 'кипятите? &mdash;&nbsp;Тогда';
        $this->assertStringContainsString($expected, $typograph->format($original));
    }

    public function testNotAfterMdash()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = 'см. 3.2 — 3.31.';
        $expected = '&mdash; 3.31.';
        $this->assertStringEndsWith($expected, $typograph->format($original));
    }

    public function testBeforeShortWord()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '15 мм';
        $expected = '15&nbsp;мм';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Содержание подраздела А…';
        $expected = 'Содержание подраздела&nbsp;А';
        $this->assertStringStartsWith($expected, $typograph->format($original));

        $original = 'и т. д.';
        $expected = 'и&nbsp;т.&nbsp;д.';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'и др. приметы';
        $expected = 'и&nbsp;др. приметы';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'также см. изображение';
        $expected = 'также&nbsp;см. изображение';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Батарейка типа АА';
        $expected = 'Батарейка типа&nbsp;АА';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'сказал он, обернувшись';
        $expected = 'сказал&nbsp;он, обернувшись';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Что ж мне делать?';
        $expected = 'Что&nbsp;ж мне делать?';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Проверяем, человек ли вы. Это может занять несколько секунд.';
        $expected = 'Проверяем, человек&nbsp;ли вы. Это может занять несколько секунд.';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testAfterShortWord()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        // https://www.artlebedev.ru/kovodstvo/sections/62/
        $original = 'Коляныч пошел за пивом';
        $expected = 'Коляныч пошел за&nbsp;пивом';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Что за чудесная погода';
        $expected = 'Что за&nbsp;чудесная погода';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'А вот раньше лучше было';
        $expected = 'А&nbsp;вот раньше лучше было';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Я ни за что не соглашусь';
        $expected = 'Я&nbsp;ни&nbsp;за&nbsp;что не&nbsp;соглашусь';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Всё не так уж плохо';
        $expected = 'Всё не&nbsp;так уж&nbsp;плохо';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Кожа да кости';
        $expected = 'Кожа да&nbsp;кости';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Батарейка АА заряжена';
        $expected = 'Батарейка АА&nbsp;заряжена';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'А когда обед?';
        $expected = 'А&nbsp;когда обед?';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'бесполезности. А ещё некоторые';
        $expected = 'бесполезности. А&nbsp;ещё некоторые';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'предупредил: «Не пытайтесь повторить это дома».';
        $expected = 'предупредил: &laquo;Не&nbsp;пытайтесь повторить это дома&raquo;.';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Опасно (не пытайтесь повторить это дома).';
        $expected = 'Опасно (не&nbsp;пытайтесь повторить это дома).';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Опасно [не пытайтесь повторить это дома].';
        $expected = 'Опасно [не&nbsp;пытайтесь повторить это дома].';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Опасно {не пытайтесь повторить это дома}.';
        $expected = 'Опасно {не&nbsp;пытайтесь повторить это дома}.';
        $this->assertSame($expected, $typograph->format($original));

        $original1 = 'Опасно <не пытайтесь повторить это дома>.';
        $original2 = 'Опасно &lt;не пытайтесь повторить это дома&gt;.';
        $expected = 'Опасно &lt;не&nbsp;пытайтесь повторить это дома&gt;.';
        $this->assertSame($expected, $typograph->format($original1));
        $this->assertSame($expected, $typograph->format($original2));

        $original = 'Опасно //не пытайтесь повторить это дома.';
        $expected = 'Опасно //не&nbsp;пытайтесь повторить это дома.';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Опасно **не пытайтесь повторить это дома**.';
        $expected = 'Опасно **не&nbsp;пытайтесь повторить это дома**.';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Опасно \'не пытайтесь повторить это дома\'.';
        $expected = 'Опасно &rsquo;не&nbsp;пытайтесь повторить это дома&rsquo;.';
        $this->assertSame($expected, $typograph->format($original));

        $original = "<p>Исполнители:</p>
<p><strong>В программе:</strong></p>";
        $expected = "<p>Исполнители:</p>
<p><strong>В&nbsp;программе:</strong></p>";
        $this->assertSame($expected, $typograph->format($original));

        $original = 'и (иногда) успешно';
        $expected = 'и&nbsp;(иногда) успешно';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'и (не всегда) успешно';
        $expected = 'и&nbsp;(не&nbsp;всегда) успешно';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Картина была представлена в Санкт-Петербурге, а затем в Москве.';
        $expected = 'Картина была представлена в&nbsp;Санкт-Петербурге, а&nbsp;затем в&nbsp;Москве.';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNotAfterShortWord()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'dash' => [],
        ]);

        $original = 'читайте с. 272-294';
        $expected = 'читайте&nbsp;с. 272-294';
        $this->assertSame($expected, $typograph->format($original));

        $original = '1820-е годы';
        $expected = '1820-е годы';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'и все же не могу';
        $expected = 'же не';
        $this->assertStringContainsString($expected, $typograph->format($original));

        $original = 'и <span></span>';
        $expected = $original;
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testBeforeNumber()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
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

        $original = 'гл. 4, стр. 5, рис. 7, илл. 9, ст. 6, п. 9';
        $expected = 'гл.&nbsp;4, стр.&nbsp;5, рис.&nbsp;7, илл.&nbsp;9, ст.&nbsp;6, п.&nbsp;9';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'кредитный договор № 123456 от 29.08.2025';
        $expected = 'кредитный договор &#8470;&nbsp;123456 от&nbsp;29.08.2025';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNotBeforeNumber()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '+ 2';
        $expected = '+ 2';
        $this->assertSame($expected, $typograph->format($original));

        $original = '= 4';
        $expected = '= 4';
        $this->assertSame($expected, $typograph->format($original));

        $original = '* 1';
        $expected = '* 1';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testAfterNumber()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'dash' => [],
        ]);

        $original = '1 декабря 2015';
        $expected = '1&nbsp;декабря 2015';
        $this->assertSame($expected, $typograph->format($original));

        $original = '8 201 794';
        $expected = '8&nbsp;201&nbsp;794';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'написана в 1895 году, опубликована';
        $expected = 'написана в&nbsp;1895&nbsp;году, опубликована';
        $this->assertSame($expected, $typograph->format($original));

        $original = '40 тыс. руб.';
        $expected = '40&nbsp;тыс. руб.';
        $this->assertSame($expected, $typograph->format($original));

        $original = '100 000 руб.';
        $expected = '100&nbsp;000&nbsp;руб.';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'нужно успеть к 1 мая';
        $expected = 'нужно успеть к&nbsp;1&nbsp;мая';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'нужно успеть к 16 мая';
        $expected = 'нужно успеть к&nbsp;16&nbsp;мая';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'предложение действует до 31 марта';
        $expected = 'предложение действует до&nbsp;31&nbsp;марта';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'нужно успеть к 1 мая, обязательно';
        $expected = 'нужно успеть к&nbsp;1&nbsp;мая, обязательно';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'нужно успеть к 16 мая, обязательно';
        $expected = 'нужно успеть к&nbsp;16&nbsp;мая, обязательно';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'это было в 300 году, нашей эры';
        $expected = 'это было в&nbsp;300&nbsp;году, нашей эры';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'нужно успеть к 2050 году, обязательно';
        $expected = 'нужно успеть к&nbsp;2050&nbsp;году, обязательно';
        $this->assertSame($expected, $typograph->format($original));

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
        $this->assertSame($expected, $typograph->format($original));

        $original = '(4 картины)';
        $expected = '(4&nbsp;картины)';
        $this->assertSame($expected, $typograph->format($original));

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

        $original = '300 dpi 150 lpi';
        $expected = '300&nbsp;dpi 150&nbsp;lpi';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testPhoneNumber()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'dash' => [],
        ]);

        // libphonenumber\PhoneNumberFormat::INTERNATIONAL
        $original = '+7 902 345-67-89';
        $expected = '+7&nbsp;902 345-67-89';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNotAfterNumber()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'dash' => [],
        ]);

        $original = 'представлена в 2006 году';
        $expected = 'представлена в&nbsp;2006 году';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Собеседования состоятся 14-24 сентября';
        $expected = 'Собеседования состоятся 14-24 сентября';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'это было в 300 году';
        $expected = 'это было в&nbsp;300 году';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'это было в 300 году' . PHP_EOL . 'Тогда';
        $expected = 'это было в&nbsp;300 году' . PHP_EOL . 'Тогда';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'нужно успеть к 2050 году';
        $expected = 'нужно успеть к&nbsp;2050 году';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'нужно успеть к 2050 году' . PHP_EOL . 'Иначе';
        $expected = 'нужно успеть к&nbsp;2050 году' . PHP_EOL . 'Иначе';
        $this->assertSame($expected, $typograph->format($original));

        $original = '2 +';
        $expected = '2 +';
        $this->assertSame($expected, $typograph->format($original));

        $original = '1-2 метра';
        $expected = '1-2 метра';
        $this->assertSame($expected, $typograph->format($original));

        $original = '1/3 метра';
        $expected = '1/3 метра';
        $this->assertSame($expected, $typograph->format($original));

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
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = 'Съешь же ещё этих мягких французских булок';
        $expected = 'Съешь&nbsp;же ещё этих мягких французских булок';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Я бы подумал';
        $expected = 'Я&nbsp;бы подумал';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Было б чем гордиться';
        $expected = 'Было&nbsp;б чем гордиться';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'А был ли мальчик?';
        $expected = 'был&nbsp;ли мальчик?';
        $this->assertStringEndsWith($expected, $typograph->format($original));
    }

    public function testBeforeYear()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '1941–1945 гг.';
        $expected = '1945&nbsp;гг.';
        $this->assertStringEndsWith($expected, $typograph->format($original));
    }

    public function testNotBeforeShortWord()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
            'dash' => [],
        ]);

        $original = 'Было дело, да';
        $expected = 'Было дело, да';
        $this->assertSame($expected, $typograph->format($original));

        // Война и мир
        $original = 'вы уж не друг мой, вы уж не мой верный раб';
        $expected = 'вы&nbsp;уж не&nbsp;друг мой, вы&nbsp;уж не&nbsp;мой верный раб';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Тарифы зон А и Б';
        $expected = 'Тарифы зон&nbsp;А и&nbsp;Б';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Тарифы зон А - Б - В';
        $expected = 'Тарифы зон А&nbsp;- Б&nbsp;- В';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Тарифы зон А или Б';
        $expected = 'Тарифы зон&nbsp;А или&nbsp;Б';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testInitial()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '(подраздел Ф. М. Достоевского)';
        $expected = '(подраздел Ф.&nbsp;М.&nbsp;Достоевского)';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Москву И. И. Иванов увидел впервые';
        $expected = 'Москву И.&nbsp;И. Иванов увидел впервые';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'клиент И. И. Иванов зарегистрировался';
        $expected = 'клиент И.&nbsp;И.&nbsp;Иванов зарегистрировался';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'В начале предложения И. А. Бунин писал';
        $expected = 'предложения И.&nbsp;А.&nbsp;Бунин писал';
        $this->assertStringEndsWith($expected, $typograph->format($original));

        $original = 'Обернуто скобками (П. П. Шмидт)';
        $expected = 'Обернуто скобками (П.&nbsp;П.&nbsp;Шмидт)';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Двойная фамилия: М. А. Волошин-Петров';
        $expected = 'Двойная фамилия: М.&nbsp;А.&nbsp;Волошин-Петров';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Инициалы после фамилии: Достоевский Ф. М.';
        $expected = 'Инициалы после фамилии: Достоевский&nbsp;Ф.&nbsp;М.';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'сначала Иванов И. И. рассказывал';
        $expected = 'сначала Иванов&nbsp;И.&nbsp;И. рассказывал';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'позже Иванов И. И. Петровича позабыл';
        $expected = 'позже Иванов И.&nbsp;И. Петровича позабыл';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'сказал Иванов И. И. Оказывается';
        $expected = 'сказал Иванов И.&nbsp;И. Оказывается';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Лет 10 Иванов И. И. заблуждался';
        $expected = 'Лет&nbsp;10 Иванов&nbsp;И.&nbsp;И. заблуждался';
        $this->assertSame($expected, $typograph->format($original));
    }
    public function testInitialAfterNumberEndingWithZero()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        // Число перед инициалами обрабатывается одинаково, независимо от последней цифры
        foreach (['5', '10', '2020'] as $number) {
            $this->assertSame(
                "Дом {$number}&nbsp;А. Пушкина",
                $typograph->format("Дом {$number} А. Пушкина"),
                "number: {$number}"
            );

            $this->assertSame(
                "{$number}&nbsp;А.&nbsp;С. Пушкин",
                $typograph->format("{$number} А. С. Пушкин"),
                "number: {$number}"
            );
        }
    }

    public function testTags()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED
        ]);

        $original = 'Согласен с <strong>условиями</strong> пользовательского соглашения';
        $expected = 'Согласен с&nbsp;<strong>условиями</strong> пользовательского соглашения';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Согласен с<strong> условиями</strong> пользовательского соглашения';
        $expected = 'Согласен с<strong>&nbsp;условиями</strong> пользовательского соглашения';
        $this->assertSame($expected, $typograph->format($original));

        $original = '<p>15 мм</p>';
        $expected = '<p>15&nbsp;мм</p>';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testBeforeSingleCharacter()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED
        ]);

        $original = 'миллионов ₽.';
        $expected = 'миллионов&nbsp;₽.';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNoNbspNearTags()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED
        ]);

        $original = '<p>Текст в</p> <p>Новой строке</p>';
        $expected = '<p>Текст в</p> <p>Новой строке</p>';
        $this->assertSame($expected, $typograph->format($original));

        $original = '<div>Текст в</div> <div>Новой строке</div>';
        $expected = '<div>Текст в</div> <div>Новой строке</div>';
        $this->assertSame($expected, $typograph->format($original));

        $original = '<div>Текст </div><div>— Новая строка</div>';
        $expected = '<div>Текст </div><div>&mdash;&nbsp;Новая строка</div>';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Текст в<br> Новой строке';
        $expected = 'Текст в<br> Новой строке';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Текст в <br>Новой строке';
        $expected = 'Текст в <br>Новой строке';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNoNbspNearEmoji()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = 'Привет 😀';
        $expected = 'Привет 😀';
        $this->assertSame($expected, $typograph->format($original));

        // \u{231A}: ⌚ default emoji presentation character (Emoji_Presentation)
        $original = 'Привет ⌚';
        $expected = 'Привет ⌚';
        $this->assertSame($expected, $typograph->format($original));

        // \u{2194}\u{FE0F}: ↔️ default text presentation character rendered as emoji
        $original = 'Привет ↔️';
        $expected = 'Привет &harr;️';
        $this->assertSame($expected, $typograph->format($original));

        // \u{1F469}: 👩 emoji modifier base (Emoji_Modifier_Base)
        $original = 'Привет 👩';
        $expected = 'Привет 👩';
        $this->assertSame($expected, $typograph->format($original));

        // \u{1F469}\u{1F3FF}: 👩🏿 emoji modifier base followed by a modifier
        $original = 'Привет 👩🏿';
        $expected = 'Привет 👩🏿';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNbspNearAmp()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = 'FAMILY &amp; CO';
        $expected = 'FAMILY &amp;&nbsp;CO';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Mr. & Mrs.';
        $expected = 'Mr.&nbsp;&amp;&nbsp;Mrs.';
        $this->assertSame($expected, $typograph->format($original));

        $original = 'Dolce & Gabbana';
        $expected = 'Dolce &amp;&nbsp;Gabbana';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNoNbspNearEntities()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        // Nbsp/Number

        $original = '1 &lt; 2';
        $expected = '1 &lt; 2';
        $this->assertSame($expected, $typograph->format($original));

        $original = '1 &times; 2';
        $expected = '1 &times; 2';
        $this->assertSame($expected, $typograph->format($original));

        // Nbsp/ShortWord

        $original = 'A &lt; B';
        $expected = 'A &lt; B';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNoNbspNearSpecialCharacters()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = '↑ ↓ ± ≈ ≠ 😊';
        $expected = '&uarr; &darr; &plusmn; &asymp; &ne; 😊';
        $this->assertSame($expected, $typograph->format($original));
    }

    public function testNotInRange()
    {
        $typograph = new Typograph([
            'entities' => Typograph::ENTITIES_NAMED,
        ]);

        $original = 'По данным ЮНЕП, ежегодно в озёра, реки и моря поступает от 19 до 23 млн тонн пластиковых отходов.';
        $expected = 'от&nbsp;19 до&nbsp;23&nbsp;млн';
        $this->assertStringContainsString($expected, $typograph->format($original));
    }

}
