<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yepteam\Typograph\Typograph;

require_once __DIR__ . '/../vendor/autoload.php';

final class WpShortcodeTest extends TestCase
{
    /**
     * @see https://codex.wordpress.org/Audio_Shortcode
     */
    public function testWpAudioShortcode()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $audio1 = '[audio src="audio-source.mp3"]';
        $audio2 = '[audio mp3="source.mp3" ogg="source.ogg" wav="source.wav"]';
        $this->assertSame($audio1, $typograph->format($audio1));
        $this->assertSame($audio2, $typograph->format($audio2));
    }

    /**
     * @see https://codex.wordpress.org/Caption_Shortcode
     */
    public function testWpCaptionShortcode()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $caption = '[caption id="attachment_6" align="alignright" width="300"]<img src="http://localhost/wp-content/uploads/2010/07/800px-Great_Wave_off_Kanagawa2-300x205.jpg" alt="Kanagawa" title="The Great Wave" width="300" height="205" class="size-medium wp-image-6" /> The Great Wave[/caption]';
        $this->assertSame($caption, $typograph->format($caption));
    }

    /**
     * @see https://codex.wordpress.org/Embed_Shortcode
     */
    public function testWpEmbedShortcode()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $embed1 = '[embed]https://www.youtube.com/watch?v=dQw4w9WgXcQ[/embed]';
        $embed2 = '[embed width="640" height="360"]https://www.youtube.com/watch?v=dQw4w9WgXcQ[/embed]';
        $this->assertSame($embed1, $typograph->format($embed1));
        $this->assertSame($embed2, $typograph->format($embed2));
    }

    /**
     * @see https://codex.wordpress.org/Gallery_Shortcode
     */
    public function testWpGalleryShortcode()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $gallery1 = '[gallery]';
        $gallery2 = '[gallery ids="729,732,731,720"]';
        $gallery3 = '[gallery order="DESC" orderby="ID"]';
        $this->assertSame($gallery1, $typograph->format($gallery1));
        $this->assertSame($gallery2, $typograph->format($gallery2));
        $this->assertSame($gallery3, $typograph->format($gallery3));
    }

    /**
     * @see https://codex.wordpress.org/Playlist_Shortcode
     */
    public function testWpPlaylistShortcode()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $playlist1 = '[playlist]';
        $playlist2 = '[playlist type="video" ids="123,456,789" style="dark"]';
        $this->assertSame($playlist1, $typograph->format($playlist1));
        $this->assertSame($playlist2, $typograph->format($playlist2));
    }

    /**
     * @see https://codex.wordpress.org/Video_Shortcode
     */
    public function testWpVideoShortcode()
    {
        $typograph = new Typograph(['entities' => 'named']);
        $video1 = '[video]';
        $video2 = '[video mp4="source.mp4" ogv="source.ogv" webm="source.webm"]';
        $this->assertSame($video1, $typograph->format($video1));
        $this->assertSame($video2, $typograph->format($video2));
    }
}
