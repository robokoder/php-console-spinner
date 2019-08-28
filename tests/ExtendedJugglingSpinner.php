<?php declare(strict_types=1);

namespace AlecRabbit\Tests\Spinner;

use AlecRabbit\Spinner\Core\Contracts\StylesInterface;
use AlecRabbit\Spinner\Core\Spinner;
use AlecRabbit\Spinner\JugglingSpinner;

class ExtendedJugglingSpinner extends JugglingSpinner
{
    protected const INTERVAL = 0.2;
    protected const FRAMES = ['1', '2', '3', '4',];
    protected const
        STYLES =
        [
            StylesInterface::SPINNER_STYLES =>
                [
                    StylesInterface::COLOR256 => StylesInterface::DISABLED,
                    StylesInterface::COLOR =>  [1, 2, 3, 4],
                ],
        ];
}