<?php declare(strict_types=1);

namespace AlecRabbit\Spinner;

use AlecRabbit\Spinner\Core\Contracts\Frames;
use AlecRabbit\Spinner\Core\Contracts\StylesInterface;
use AlecRabbit\Spinner\Core\Spinner;

class BlockSpinner extends Spinner
{
    protected const INTERVAL = 0.1;
    protected const SYMBOLS = Frames::BLOCK_VARIANT_1;
    protected const
        STYLES =
        [
            StylesInterface::SPINNER_STYLES =>
                [
                    StylesInterface::COLOR => StylesInterface::C_LIGHT_CYAN,
                ],
        ];
}