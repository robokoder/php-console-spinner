<?php declare(strict_types=1);

namespace AlecRabbit\Spinner\Core;

use AlecRabbit\Accessories\Circular;
use AlecRabbit\Accessories\Pretty;
use AlecRabbit\Cli\Tools\Cursor;
use AlecRabbit\Spinner\Core\Adapters\EchoOutputAdapter;
use AlecRabbit\Spinner\Core\Contracts\SettingsInterface;
use AlecRabbit\Spinner\Core\Contracts\SpinnerInterface;
use AlecRabbit\Spinner\Core\Contracts\SpinnerOutputInterface;
use AlecRabbit\Spinner\Core\Contracts\SpinnerSymbols;
use function AlecRabbit\typeOf;
use const AlecRabbit\ESC;

abstract class Spinner implements SpinnerInterface
{
//    protected const ERASING_SHIFT = SettingsInterface::DEFAULT_ERASING_SHIFT;
    protected const INTERVAL = SettingsInterface::DEFAULT_INTERVAL;
    protected const SYMBOLS = SpinnerSymbols::DIAMOND;
    protected const STYLES = [];

    /** @var string */
    protected $messageStr;
    /** @var string */
    protected $percentStr = '';
    /** @var string */
    protected $percentPrefix;
    /** @var string */
    protected $moveBackSequenceStr;
    /** @var string */
    protected $inlinePaddingStr;
    /** @var string */
    protected $eraseBySpacesStr;
    /** @var Style */
    protected $style;
    /** @var float */
    protected $interval;
    /** @var int */
    protected $erasingShift;
    /** @var Circular */
    protected $symbols;
    /** @var null|SpinnerOutputInterface */
    protected $output;

    /**
     * AbstractSpinner constructor.
     * @param mixed $settings
     * @param null|false|SpinnerOutputInterface $output
     * @param mixed $color
     */
    public function __construct($settings = null, $output = null, $color = null)
    {
        $this->output = $this->refineOutput($output);
        $settings = $this->refineSettings($settings);
        $this->interval = $settings->getInterval();
        $this->erasingShift = $settings->getErasingShift();
        $this->inlinePaddingStr = $settings->getInlinePaddingStr();
        $this->messageStr = $this->getMessageStr($settings);
        $this->updateProperties();
        $this->symbols = new Circular($settings->getSymbols());

        try {
            $this->style = new Style($settings->getStyles(), $color);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException(
                '[' . static::class . '] ' . $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * @param null|false|SpinnerOutputInterface $output
     * @return null|SpinnerOutputInterface
     */
    protected function refineOutput($output): ?SpinnerOutputInterface
    {
        $this->assertOutput($output);
        if (false === $output) {
            return null;
        }
        return $output ?? new EchoOutputAdapter();
    }

    /**
     * @param mixed $output
     */
    protected function assertOutput($output): void
    {
        if (null !== $output && false !== $output && !$output instanceof SpinnerOutputInterface) {
            $typeOrValue = true === $output ? 'true' : typeOf($output);
            throw new \InvalidArgumentException(
                'Incorrect $output param [null|false|SpinnerOutputInterface] expected "' . $typeOrValue . '" given.'
            );
        }
    }

    /**
     * @param mixed $settings
     * @return SettingsInterface
     */
    protected function refineSettings($settings): SettingsInterface
    {
        $this->assertSettings($settings);
        if (\is_string($settings)) {
            return
                $this->defaultSettings()->setMessage($settings);
        }
        return
            $settings ?? $this->defaultSettings();
    }

    /**
     * @param mixed $settings
     */
    protected function assertSettings($settings): void
    {
        if (null !== $settings && !\is_string($settings) && !$settings instanceof SettingsInterface) {
            throw new \InvalidArgumentException(
                'Instance of SettingsInterface or string expected ' . typeOf($settings) . ' given.'
            );
        }
    }

    /**
     * @return SettingsInterface
     */
    protected function defaultSettings(): SettingsInterface
    {
        return
            (new Settings())
                ->setInterval(static::INTERVAL)
                ->setSymbols(static::SYMBOLS)
                ->setStyles(static::STYLES);
    }

    /**
     * @param SettingsInterface $settings
     * @return string
     */
    protected function getMessageStr(SettingsInterface $settings): string
    {
        return $settings->getPrefix() . ucfirst($settings->getMessage()) . $settings->getSuffix();
    }

    protected function updateProperties(): void
    {
        $this->percentPrefix = $this->getPercentPrefix();
        $strLen =
            strlen($this->message()) + strlen($this->percent()) + strlen($this->inlinePaddingStr) + $this->erasingShift;
        $this->moveBackSequenceStr = ESC . "[{$strLen}D";
        $this->eraseBySpacesStr = str_repeat(SettingsInterface::ONE_SPACE_SYMBOL, $strLen);
    }

    /**
     * @return string
     */
    protected function getPercentPrefix(): string
    {
        if (strpos($this->messageStr, SettingsInterface::DEFAULT_SUFFIX)) {
            return SettingsInterface::ONE_SPACE_SYMBOL;
        }
        return SettingsInterface::EMPTY;
    }

    /**
     * @return string
     */
    protected function message(): string
    {
        return $this->messageStr;
    }

    /**
     * @return string
     */
    protected function percent(): string
    {
        return $this->percentStr;
    }

    /** {@inheritDoc} */
    public function getOutput(): ?SpinnerOutputInterface
    {
        return $this->output;
    }

    public function interval(): float
    {
        return $this->interval;
    }

    public function inline(bool $inline): SpinnerInterface
    {
        $this->inlinePaddingStr = $inline ? SettingsInterface::ONE_SPACE_SYMBOL : SettingsInterface::EMPTY;
        $this->updateProperties();
        return $this;
    }

    /** {@inheritDoc} */
    public function begin(?float $percent = null): string
    {
        if ($this->output) {
            $this->output->write(Cursor::hide());
            $this->spin($percent);
            return '';
        }
        return Cursor::hide() . $this->spin($percent);
    }

    /** {@inheritDoc} */
    public function spin(?float $percent = null, ?string $message = null): string
    {
        if (null !== $percent) {
            $this->updatePercent($percent);
        }
        if (null !== $message) {
            $this->updateMessage($message);
        }
        $str = $this->inlinePaddingStr .
            $this->style->spinner((string)$this->symbols->value()) .
            $this->style->message(
                $this->message()
            ) .
            $this->style->percent(
                $this->percent()
            ) .
            $this->moveBackSequenceStr;
        if ($this->output) {
            $this->output->write($str);
            return '';
        }
        return
            $str;
    }

    /**
     * @param float $percent
     */
    protected function updatePercent(float $percent): void
    {
        if (0 === (int)($percent * 1000) % 10) {
            $this->percentStr = Pretty::percent($percent, 0, $this->percentPrefix);
            $this->updateProperties();
        }
    }

    protected function updateMessage(string $message): void
    {
        if ($this->messageStr !== $message) {
            $this->messageStr = $message;
            $this->updateProperties();
        }
    }

    /** {@inheritDoc} */
    public function end(): string
    {
        if ($this->output) {
            $this->erase();
            $this->output->write(Cursor::show());
            return '';
        }
        return $this->erase() . Cursor::show();
    }

    /** {@inheritDoc} */
    public function erase(): string
    {
        $str = $this->eraseBySpacesStr . $this->moveBackSequenceStr;
        if ($this->output) {
            $this->output->write($str);
            return '';
        }
        return $str;
    }
}
