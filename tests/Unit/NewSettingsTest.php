<?php declare(strict_types=1);

namespace AlecRabbit\Tests\Tools;

use AlecRabbit\Spinner\Core\Contracts\Frames;
use AlecRabbit\Spinner\Core\Contracts\SettingsInterface;
use AlecRabbit\Spinner\Core\Contracts\StylesInterface;
use AlecRabbit\Spinner\Settings\Settings;
use PHPUnit\Framework\TestCase;

class NewSettingsTest extends TestCase
{
    protected const PROCESSING = 'Processing';
    protected const COMPUTING = 'Computing';
    protected const MB_STRING_1 = 'ᚹädm漢字';

    /** @test */
    public function instance(): void
    {
        $settings = new Settings();
        $this->assertInstanceOf(Settings::class, $settings);
        $this->assertEquals(SettingsInterface::EMPTY, $settings->getMessage());
        $this->assertEquals(0.1, $settings->getInterval());
        $this->assertEquals(0, $settings->getErasingShift());
        $this->assertEquals('', $settings->getInlinePaddingStr());
        $this->assertEquals(SettingsInterface::ONE_SPACE_SYMBOL, $settings->getMessagePrefix());
        $this->assertEquals('', $settings->getMessageSuffix());
        $this->assertEquals(StylesInterface::DEFAULT_STYLES, $settings->getStyles());
        $this->assertEquals(SettingsInterface::DEFAULT_FRAMES, $settings->getFrames());
        $this->assertEquals(SettingsInterface::EMPTY, $settings->getSpacer());
        $this->assertEquals(0, $settings->getMessageErasingLen());

        $settings->setMessage(self::PROCESSING);
        $this->assertEquals(self::PROCESSING, $settings->getMessage());
        $this->assertEquals(10, $settings->getMessageErasingLen());
        $settings->setMessage(self::COMPUTING, 9);
        $this->assertEquals(self::COMPUTING, $settings->getMessage());
        $this->assertEquals(9, $settings->getMessageErasingLen());
        $settings->setMessage(self::MB_STRING_1);
        $this->assertEquals(self::MB_STRING_1, $settings->getMessage());
        $this->assertEquals(6, $settings->getMessageErasingLen());
    }

    /** @test */
    public function mergeEmpty(): void
    {
        $settings = new Settings();
        $newSettings = new Settings();
        $settings->merge($newSettings);
        $this->assertEquals(SettingsInterface::EMPTY, $settings->getMessage());
        $this->assertEquals(0.1, $settings->getInterval());
        $this->assertEquals(0, $settings->getErasingShift());
        $this->assertEquals('', $settings->getInlinePaddingStr());
        $this->assertEquals(SettingsInterface::ONE_SPACE_SYMBOL, $settings->getMessagePrefix());
        $this->assertEquals('', $settings->getMessageSuffix());
        $this->assertEquals(StylesInterface::DEFAULT_STYLES, $settings->getStyles());
        $this->assertEquals(SettingsInterface::DEFAULT_FRAMES, $settings->getFrames());
        $this->assertEquals(SettingsInterface::EMPTY, $settings->getSpacer());
        $this->assertEquals(0, $settings->getMessageErasingLen());
    }

    /** @test */
    public function mergeNotEmpty(): void
    {
        $settings = new Settings();
        $interval = 0.2;
        $message = 'message';
        $inlinePaddingStr = SettingsInterface::ONE_SPACE_SYMBOL;
        $messagePrefix = '-';
        $messageSuffix = '';
        $styles = [
            StylesInterface::SPINNER_STYLES =>
                [
                    StylesInterface::COLOR256 => StylesInterface::C_DARK,
                    StylesInterface::COLOR => StylesInterface::DISABLED,
                ],
            StylesInterface::MESSAGE_STYLES =>
                [
                    StylesInterface::COLOR256 => StylesInterface::C_DARK,
                    StylesInterface::COLOR => StylesInterface::DISABLED,
                ],
            StylesInterface::PERCENT_STYLES =>
                [
                    StylesInterface::COLOR256 => StylesInterface::C_DARK,
                    StylesInterface::COLOR => StylesInterface::DISABLED,
                ],
        ];
        $frames = Frames::DIAMOND;
        $spacer = SettingsInterface::ONE_SPACE_SYMBOL;

        $newSettings =
            (new Settings())
                ->setMessage($message)
                ->setInterval($interval)
                ->setInlinePaddingStr($inlinePaddingStr)
                ->setMessagePrefix($messagePrefix)
                ->setMessageSuffix($messageSuffix)
                ->setFrames($frames)
                ->setStyles($styles)
                ->setSpacer($spacer);
        $settings->merge($newSettings);
        $this->assertEquals($message, $settings->getMessage());
        $this->assertEquals($interval, $settings->getInterval());
        $this->assertEquals(1, $settings->getErasingShift());
        $this->assertEquals($inlinePaddingStr, $settings->getInlinePaddingStr());
        $this->assertEquals($messagePrefix, $settings->getMessagePrefix());
        $this->assertEquals($messageSuffix, $settings->getMessageSuffix());

        $this->assertEquals($frames, $settings->getFrames());
        $this->assertEquals($styles, $settings->getStyles());
        $this->assertEquals($spacer, $settings->getSpacer());
        $this->assertEquals(7, $settings->getMessageErasingLen());
    }
}