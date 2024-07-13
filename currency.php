<?php

declare(strict_types=1);

class Currency {
    const CB_URL = "https://cbu.uz/uz/arkhiv-kursov-valyut/json/";

    public function exchange(float $uzs, string $currencyCode): float {
        $rate = $this->customCurrencies()[$currencyCode] ?? 1;
        return $uzs / $rate;
    }

    public function getCurrencyInfo(): array {
        $currencyInfo = @file_get_contents(self::CB_URL);
        if ($currencyInfo === false) {
            throw new Exception("Failed to fetch currency information.");
        }

        $data = json_decode($currencyInfo, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error decoding currency data: " . json_last_error_msg());
        }

        return $data;
    }

    public function customCurrencies(): array {
        $currencies = $this->getCurrencyInfo();
        $orderedCurrencies = [];
        foreach ($currencies as $currency) {
            $orderedCurrencies[$currency['Ccy']] = (float)$currency['Rate'];
        }
        return $orderedCurrencies;
    }
}
