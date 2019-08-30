<?php declare(strict_types=1);

namespace AlecRabbit\Spinner;

use AlecRabbit\Spinner\Core\Contracts\Frames;
use AlecRabbit\Spinner\Core\Contracts\StylesInterface;
use AlecRabbit\Spinner\Core\Spinner;

class SectorsSpinner extends Spinner
{
    protected const INTERVAL = 0.17;
    protected const FRAMES = Frames::SECTORS;
    protected const
        STYLES =
        [
            StylesInterface::FRAMES_STYLES =>
                [
                    StylesInterface::COLOR => StylesInterface::C_LIGHT_CYAN,
                ],
        ];
}
