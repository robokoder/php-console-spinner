<?php /** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

use AlecRabbit\ConsoleColour\Themes;
use AlecRabbit\Spinner\Core\Adapters\EchoOutputAdapter;
use AlecRabbit\Spinner\Core\Contracts\SpinnerOutputInterface;
use AlecRabbit\Spinner\Core\Spinner;

require_once __DIR__ . '/__long_run_function.php';

/**
 * @param Spinner $s
 * @param Themes $theme
 * @param bool $inline
 * @param array $exampleMessages
 * @param SpinnerOutputInterface $output
 * @param bool $updateMessages
 */
function display(
    Spinner $s,
    Themes $theme,
    bool $inline,
    array $exampleMessages,
    SpinnerOutputInterface $output = null,
    bool $updateMessages = false
): void {
    $outputIsNull = null === $output;
    $output = $output ?? new EchoOutputAdapter();
    $output->write('-------------------------', true);
    $s->inline($inline);
    $emulatedMessages = scaleEmulatedMessages();
    $microseconds = (int)($s->interval() * 1000000);

    $output->write($theme->lightCyan('Example: '), true);
    foreach ($exampleMessages as $m) {
        $output->write($theme->lightCyan($m), true);

    }
    if (!$inline) {
        $output->write('', true);
    }

    if ($outputIsNull) {
        $output->write($s->begin());
    } else {
        $s->begin();
    }
    $currentMessage = null;
    for ($i = 0; $i < ITERATIONS; $i++) {
        usleep($microseconds); // Here you are doing your task
        if (array_key_exists($i, $emulatedMessages)) {
            // It's your job to echo erase sequence when needed
            if ($outputIsNull) {
                $output->write($s->erase());
            } else {
                $s->erase();
            }
            if ($inline) {
                $output->write('', true);
            }
            if(true === $updateMessages) {
                $currentMessage = $emulatedMessages[$i];
            }
            $output->write($theme->none($emulatedMessages[$i] . '...'));
            if (!$inline) {
                $output->write('', true);
            }
        }
        // It's your job to call spin() with approx. equal intervals
        // (each class has recommended interval for comfortable animation)
        $percent = $i / ITERATIONS;
        if ($outputIsNull) {
            $output->write($s->spin($percent, $currentMessage));
        } else {
            $s->spin($percent, $currentMessage);
        }
    }
    if ($outputIsNull) {
        $output->write($s->end());
    } else {
        $s->end();
    }
    if ($inline) {
        $output->write('', true);
    }
    $output->write($theme->none('Done!') . PHP_EOL, true);

    $output->write('', true);
}

/**
 * @return array
 */
function scaleEmulatedMessages(): array
{
    $c = ITERATIONS / 100;
    $simulated = [];
    foreach (MESSAGES as $key => $message) {
        $simulated[(int)$key * $c] = $message;
    }
    return $simulated;
}