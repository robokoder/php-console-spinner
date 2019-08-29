<?php declare(strict_types=1);

namespace AlecRabbit\Spinner\Core;

use AlecRabbit\Accessories\Circular;
use AlecRabbit\Cli\Tools\Core\TerminalStatic;
use AlecRabbit\Spinner\Core\Contracts\StylesInterface;
use const AlecRabbit\COLOR256_TERMINAL;
use const AlecRabbit\COLOR_TERMINAL;
use const AlecRabbit\ESC;
use const AlecRabbit\NO_COLOR_TERMINAL;

class Coloring
{
    /** @var Circular */
    protected $frameStyles;
    /** @var Circular */
    protected $messageStyles;
    /** @var Circular */
    protected $progressStyles;

    /**
     * Coloring constructor.
     * @param array $styles
     * @param mixed $color
     */
    public function __construct(array $styles, $color = null)
    {
        $styles = $this->mergeStyles($styles);
        $this->assertStyles($styles);
        // Styles defaults - NO color
        $this->frameStyles = $this->circularNoColor();
        $this->messageStyles = $this->circularNoColor();
        $this->progressStyles = $this->circularNoColor();
        // Reassign styles
        $this->assignStyles($styles, $this->refineColor($color));
    }

    /**
     * @param array $styles
     */
    protected function assertStyles(array $styles): void
    {
        foreach (StylesInterface::DEFAULT_STYLES as $index => $defaults) {
            if (!\array_key_exists($index, $styles)) {
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException(
                    'Styles array does not have [' . $index . '] key.'
                );
                // @codeCoverageIgnoreEnd
            }
            $keys = array_keys($defaults);
            foreach ($keys as $k) {
                if (!\array_key_exists($k, $styles[$index])) {
                    // @codeCoverageIgnoreStart
                    throw new \InvalidArgumentException(
                        'Styles array does not have [' . $index . '][' . $k . '] key.'
                    );
                    // @codeCoverageIgnoreEnd
                }
            }
        }
    }

    /**
     * @return Circular
     */
    protected function circularNoColor(): Circular
    {
        return new Circular(['%s',]);
    }

    /**
     * @param array $styles
     * @param mixed $color
     */
    protected function assignStyles(array $styles, $color): void
    {
        switch ($color) {
            case COLOR256_TERMINAL:
                $this->frameStyles = $this->circular256Color($styles[StylesInterface::SPINNER_STYLES]);
                $this->messageStyles = $this->circular256Color($styles[StylesInterface::MESSAGE_STYLES]);
                $this->progressStyles = $this->circular256Color($styles[StylesInterface::PERCENT_STYLES]);
                break;
            case COLOR_TERMINAL:
                $this->frameStyles = $this->circularColor($styles[StylesInterface::SPINNER_STYLES]);
                $this->messageStyles = $this->circularColor($styles[StylesInterface::MESSAGE_STYLES]);
                $this->progressStyles = $this->circularColor($styles[StylesInterface::PERCENT_STYLES]);
                break;
        }
    }

    /**
     * @param array $styles
     * @return Circular
     */
    protected function circular256Color(array $styles): Circular
    {
        return
            StylesInterface::DISABLED === $styles[StylesInterface::COLOR256] ?
                $this->circularColor($styles) :
                new Circular(
                    array_map(
                        /**
                         * @param string|array $value
                         * @return string
                         */
                        static function ($value): string {
                            if (\is_array($value)) {
                                [$fg,  $bg] = $value;
                                return ESC . "[38;5;{$fg};48;5;{$bg}m%s" . ESC . '[0m';
                            }
                            return ESC . "[38;5;{$value}m%s" . ESC . '[0m';
                        },
                        $styles[StylesInterface::COLOR256]
                    )
                );
    }

    /**
     * @param array $styles
     * @return array
     */
    protected function mergeStyles(array $styles): array
    {
        $defaultStyles = StylesInterface::DEFAULT_STYLES;
        $keys = array_keys($defaultStyles);
        foreach ($keys as $key) {
            if (\array_key_exists($key, $styles)) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $defaultStyles[$key] = array_merge(StylesInterface::DEFAULT_STYLES[$key], $styles[$key]);
            }
        }
        return $defaultStyles;
    }

    /**
     * @param array $styles
     * @return Circular
     */
    protected function circularColor(array $styles): Circular
    {
        return
            StylesInterface::DISABLED === $styles[StylesInterface::COLOR] ?
                $this->circularNoColor() :
                new Circular(
                    array_map(
                        /**
                         * @param string|array $value
                         * @return string
                         */
                        static function ($value): string {
                            if (\is_array($value)) {
                                $value = implode(';', $value);
                            }
                            return ESC . "[{$value}m%s" . ESC . '[0m';
                        },
                        $styles[StylesInterface::COLOR]
                    )
                );
    }

    /**
     * @param null|int $color
     * @return int
     */
    protected function refineColor(?int $color): int
    {
        // @codeCoverageIgnoreStart
        if (null === $color) {
            if (TerminalStatic::supportsColor()) {
                return
                    TerminalStatic::supports256Color() ?
                        COLOR256_TERMINAL :
                        COLOR_TERMINAL;
            }
            return NO_COLOR_TERMINAL;
        }
        // @codeCoverageIgnoreEnd
        return $color;
    }

    /**
     * @return Circular
     */
    public function getFrameStyles(): Circular
    {
        return $this->frameStyles;
    }

    /**
     * @return Circular
     */
    public function getMessageStyles(): Circular
    {
        return $this->messageStyles;
    }

    /**
     * @return Circular
     */
    public function getProgressStyles(): Circular
    {
        return $this->progressStyles;
    }
}
