<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class NbspTest extends TestCase
{
    public function testBeforeMdash()
    {
        // https://www.artlebedev.ru/kovodstvo/sections/62/
        $original = 'Крикну — а в ответ тишина';
        $expected = 'Крикну&nbsp;&mdash; а';
        $this->assertStringStartsWith($expected, Typograph::format($original));

        $original = '(слева) — справа';
        $expected = '(слева)&nbsp;&mdash; справа';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '«слева» — справа';
        $expected = '&laquo;слева&raquo;&nbsp;&mdash; справа';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'слева $ — справа';
        $expected = 'слева $&nbsp;&mdash; справа';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'слева* — справа';
        $expected = 'слева*&nbsp;&mdash; справа';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '*слева* — справа';
        $expected = '*слева*&nbsp;&mdash; справа';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '1 — справа';
        $expected = '1&nbsp;&mdash; справа';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'слева, - справа';
        $expected = 'слева,&nbsp;';
        $this->assertStringStartsWith($expected, Typograph::format($original));
    }

    public function testNotBeforeMdash()
    {
        $original = '* — справа';
        $expected = '* &mdash; справа';
        $this->assertStringStartsWith($expected, Typograph::format($original));

        $original = 'слева * — справа';
        $expected = 'слева * &mdash; справа';
        $this->assertStringStartsWith($expected, Typograph::format($original));

        $original = 'слева ** &mdash; справа';
        $expected = 'слева ** &mdash; справа';
        $this->assertStringStartsWith($expected, Typograph::format($original));

        $original = '\'слева\' — справа';
        $unexpected = '&nbsp;';
        $this->assertStringNotContainsString($unexpected, Typograph::format($original));
    }

    public function testAfterMdash()
    {
        $original = 'Вы всё ещё кипятите? — Тогда мы идём к вам!';
        $expected = 'кипятите? &mdash;&nbsp;Тогда';
        $this->assertStringContainsString($expected, Typograph::format($original));
    }

    public function testNotAfterMdash()
    {
        $original = 'см. 3.2 — 3.31.';
        $expected = '&mdash; 3.31.';
        $this->assertStringEndsWith($expected, Typograph::format($original));
    }

    public function testBeforeShortWord()
    {
        $original = 'Содержание подраздела А…';
        $expected = 'Содержание подраздела&nbsp;А';
        $this->assertStringStartsWith($expected, Typograph::format($original));

        $original = 'и т. д.';
        $expected = 'и&nbsp;т.&nbsp;д.';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'и др. приметы';
        $expected = 'и&nbsp;др. приметы';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'также см. изображение';
        $expected = 'также&nbsp;см. изображение';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Батарейка типа АА';
        $expected = 'Батарейка типа&nbsp;АА';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'сказал он, обернувшись';
        $expected = 'сказал&nbsp;он, обернувшись';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Что ж мне делать?';
        $expected = 'Что&nbsp;ж мне делать?';
        $this->assertEquals($expected, Typograph::format($original));
    }

    public function testAfterShortWord()
    {
        // https://www.artlebedev.ru/kovodstvo/sections/62/
        $original = 'Коляныч пошел за пивом';
        $expected = 'Коляныч пошел за&nbsp;пивом';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Что за чудесная погода';
        $expected = 'Что за&nbsp;чудесная погода';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'А вот раньше лучше было';
        $expected = 'А&nbsp;вот раньше лучше было';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Я ни за что не соглашусь';
        $expected = 'Я&nbsp;ни&nbsp;за&nbsp;что не&nbsp;соглашусь';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Всё не так уж плохо';
        $expected = 'Всё не&nbsp;так уж&nbsp;плохо';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Кожа да кости';
        $expected = 'Кожа да&nbsp;кости';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Батарейка АА заряжена';
        $expected = 'Батарейка АА&nbsp;заряжена';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'А когда обед?';
        $expected = 'А&nbsp;когда обед?';
        $this->assertEquals($expected, Typograph::format($original));
    }

    public function testNotAfterShortWord()
    {
        $original = 'читайте с. 272-294';
        $expected = 'читайте&nbsp;с. 272-294';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '1820-е годы';
        $expected = '1820-е годы';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'и все же не могу';
        $expected = 'же не';
        $this->assertStringContainsString($expected, Typograph::format($original));
    }

    public function testBeforeNumber()
    {
        $original = 'Начинается текст подраздела 1…';
        $expected = 'Начинается текст подраздела&nbsp;1';
        $this->assertStringStartsWith($expected, Typograph::format($original));

        $original = 'Яблоки 80 Арбуз 90';
        $expected = 'Яблоки&nbsp;80 Арбуз&nbsp;90';
        $this->assertStringStartsWith($expected, Typograph::format($original));

        $original = 'Палата № 6';
        $expected = 'Палата &#8470;&nbsp;6';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '€ 60-80';
        $expected = '&nbsp;60';
        $this->assertStringContainsString($expected, Typograph::format($original));
    }

    public function testNotBeforeNumber()
    {
        $original = '+ 2';
        $expected = '+ 2';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '= 4';
        $expected = '= 4';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '* 1';
        $expected = '* 1';
        $this->assertEquals($expected, Typograph::format($original));
    }

    public function testAfterNumber()
    {
        $original = '8 201 794';
        $expected = '8&nbsp;201&nbsp;794';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'написана в 1895 году, опубликована';
        $expected = 'написана в&nbsp;1895&nbsp;году, опубликована';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '100 000 руб.';
        $expected = '100&nbsp;000&nbsp;руб.';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'нужно успеть к 1 мая';
        $expected = 'нужно успеть к&nbsp;1&nbsp;мая';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'нужно успеть к 16 мая';
        $expected = 'нужно успеть к&nbsp;16&nbsp;мая';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'нужно успеть к 1 мая, обязательно';
        $expected = 'нужно успеть к&nbsp;1&nbsp;мая, обязательно';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'нужно успеть к 16 мая, обязательно';
        $expected = 'нужно успеть к&nbsp;16&nbsp;мая, обязательно';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'это было в 300 году, нашей эры';
        $expected = 'это было в&nbsp;300&nbsp;году, нашей эры';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'нужно успеть к 2050 году, обязательно';
        $expected = 'нужно успеть к&nbsp;2050&nbsp;году, обязательно';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '60-80 % всей массы груза';
        $expected = '80&nbsp;%';
        $this->assertStringContainsString($expected, Typograph::format($original));

        $original = '60-80 ₽';
        $expected = '80&nbsp;';
        $this->assertStringContainsString($expected, Typograph::format($original));

        $original = '30 °С';
        $expected = '30&nbsp;';
        $this->assertStringContainsString($expected, Typograph::format($original));

        $original = '200 ГВт';
        $expected = '200&nbsp;ГВт';
        $this->assertEquals($expected, Typograph::format($original));
    }

    public function testNotAfterNumber()
    {
        $original = 'представлена в 2006 году';
        $expected = 'представлена в&nbsp;2006 году';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Собеседования состоятся 14-24 сентября';
        $expected = 'Собеседования состоятся 14-24 сентября';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'это было в 300 году';
        $expected = 'это было в&nbsp;300 году';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'это было в 300 году' . PHP_EOL . 'Тогда';
        $expected = 'это было в&nbsp;300 году' . PHP_EOL . 'Тогда';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'нужно успеть к 2050 году';
        $expected = 'нужно успеть к&nbsp;2050 году';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'нужно успеть к 2050 году' . PHP_EOL . 'Иначе';
        $expected = 'нужно успеть к&nbsp;2050 году' . PHP_EOL . 'Иначе';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '2 +';
        $expected = '2 +';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '1-2 метра';
        $expected = '1-2 метра';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '1/3 метра';
        $expected = '1/3 метра';
        $this->assertEquals($expected, Typograph::format($original));

        $original = '+5...+10 °С';
        $expected = '10 ';
        $this->assertStringContainsString($expected, Typograph::format($original));
    }

    public function testBeforeParticleWord()
    {
        $original = 'Съешь же ещё этих мягких французских булок';
        $expected = 'Съешь&nbsp;же ещё этих мягких французских булок';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Я бы подумал';
        $expected = 'Я&nbsp;бы подумал';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Было б чем гордиться';
        $expected = 'Было&nbsp;б чем гордиться';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'А был ли мальчик?';
        $expected = 'был&nbsp;ли мальчик?';
        $this->assertStringEndsWith($expected, Typograph::format($original));
    }

    public function testBeforeYear()
    {
        $original = '1941–1945 гг.';
        $expected = '1945&nbsp;гг.';
        $this->assertStringEndsWith($expected, Typograph::format($original));
    }

    public function testNotBeforeShortWord()
    {
        $original = 'Было дело, да';
        $expected = 'Было дело, да';
        $this->assertEquals($expected, Typograph::format($original));

        // Война и мир
        $original = 'вы уж не друг мой, вы уж не мой верный раб';
        $expected = 'вы&nbsp;уж не&nbsp;друг мой, вы&nbsp;уж не&nbsp;мой верный раб';
        $this->assertEquals($expected, Typograph::format($original));
    }

    public function testInitial()
    {
        $original = '(подраздел Ф. М. Достоевского)';
        $expected = '(подраздел Ф.&nbsp;М.&nbsp;Достоевского)';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Москву И. И. Иванов увидел впервые';
        $expected = 'Москву И.&nbsp;И. Иванов увидел впервые';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'клиент И. И. Иванов зарегистрировался';
        $expected = 'клиент И.&nbsp;И.&nbsp;Иванов зарегистрировался';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'В начале предложения И. А. Бунин писал';
        $expected = 'предложения И.&nbsp;А.&nbsp;Бунин писал';
        $this->assertStringEndsWith($expected, Typograph::format($original));

        $original = 'Обернуто скобками (П. П. Шмидт)';
        $expected = 'Обернуто скобками (П.&nbsp;П.&nbsp;Шмидт)';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Двойная фамилия: М. А. Волошин-Петров';
        $expected = 'Двойная фамилия: М.&nbsp;А.&nbsp;Волошин-Петров';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Инициалы после фамилии: Достоевский Ф. М.';
        $expected = 'Инициалы после фамилии: Достоевский&nbsp;Ф.&nbsp;М.';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'сначала Иванов И. И. рассказывал';
        $expected = 'сначала Иванов&nbsp;И.&nbsp;И. рассказывал';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'позже Иванов И. И. Петровича позабыл';
        $expected = 'позже Иванов И.&nbsp;И. Петровича позабыл';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'сказал Иванов И. И. Оказывается';
        $expected = 'сказал Иванов И.&nbsp;И. Оказывается';
        $this->assertEquals($expected, Typograph::format($original));

        $original = 'Лет 10 Иванов И. И. заблуждался';
        $expected = 'Лет&nbsp;10 Иванов&nbsp;И.&nbsp;И. заблуждался';
        $this->assertEquals($expected, Typograph::format($original));
    }

    public function testTags()
    {
        $original = 'Согласен с <strong>условиями</strong> пользовательского соглашения';
        $expected = 'Согласен с&nbsp;<strong>условиями</strong> пользовательского соглашения';
        $this->assertStringStartsWith($expected, Typograph::format($original));

        $original = 'Согласен с<strong> условиями</strong> пользовательского соглашения';
        $expected = 'Согласен с<strong>&nbsp;условиями</strong> пользовательского соглашения';
        $this->assertStringStartsWith($expected, Typograph::format($original));
    }
}
