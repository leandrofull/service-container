<?php

namespace LeandroFull\Test;

require __DIR__ . '/../vendor/autoload.php';

final class Assert
{
    private static self $instance;

    private int $assertions = 0;

    private int $ok = 0;

    private string $file;

    private string $error;

    public static function init(): void
    {
        if (!isset(self::$instance)) self::$instance = new self();
    }

    private static function getInstance(): self
    {
        return self::$instance;
    }

    private static function storeResult(bool $boolean, string $msgWhenFailed): void
    {
        $instance = self::getInstance();
        $instance->assertions++;

        if (!$boolean) {
            $instance->error = $msgWhenFailed;
            Assert::printResults();
            exit;
        }
        
        $instance->ok++;
    }

    private static function debug(mixed $value): string
    {
        return var_export($value, true);
    }

    public static function setFileName(string $fileName): void
    {
        self::getInstance()->file = $fileName;
    }

    public static function same(mixed $expected, mixed $real): void
    {
        self::storeResult(
            $expected === $real,
            "Expected value: ".self::debug($expected).". Real Value: ".self::debug($real)
        );
    }

    public static function printResults(): void
    {
        $instance = self::getInstance();

        echo PHP_EOL;

        echo "\033[37mAssertions: {$instance->assertions}" . PHP_EOL;
        echo "Ok: {$instance->ok}" . PHP_EOL . PHP_EOL;

        if ($instance->assertions > $instance->ok) {
            echo "\033[31mFail\033[37m - ";
            echo $instance->error . PHP_EOL . PHP_EOL;
            echo "File: '{$instance->file}'" . PHP_EOL;
        } else {
            echo "\033[32mOK!\033[37m" . PHP_EOL;
        }
    }
}

Assert::init();
