<?php

declare(strict_types=1);

class Currency {
    const CB_URL = "https://cbu.uz/uz/arkhiv-kursov-valyut/json/";

    public function exchange(float $uzs, string $currencyCode): float {
        $rate = $this->customCurrencies()[$currencyCode] ?? 1;
        return $uzs / $rate;
    }

    public function getCurrencyInfo(): array {
        $currencyInfo = file_get_contents(self::CB_URL);
        return json_decode($currencyInfo, true);
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