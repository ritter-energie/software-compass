<?php

declare(strict_types=1);

use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;

$config = new IntlConfig(
    currentLocale: Locale::ENGLISH,
    fallbackLocale: Locale::ENGLISH,
);

$config->addTranslationMessageFile(Locale::ENGLISH, __DIR__ . '/../../resources/lang/messages.en.yaml');
$config->addTranslationMessageFile(Locale::GERMAN, __DIR__ . '/../../resources/lang/messages.de.yaml');

return $config;
