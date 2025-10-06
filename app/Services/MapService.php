<?php

namespace App\Services;

class MapService
{
    public static function mapCompanyToRelatie(array $company): array
    {
        return [
            'naam' => $company['companyName'] ?? null,
            'relatiecode' => $company['companyNumber'] ?? null,
            'kvkNummer' => $company['companyNumber'] ?? null,
            'btwNummer' => $company['taxID'] ?? null,
            'nonactief' => isset($company['isActive']) ? !$company['isActive'] : false,

            'telefoon' => $company['phone'] ?? null,
            'mobieleTelefoon' => $company['alternatePhone1'] ?? null,
            'email' => $company['email'] ?? null,
            'websiteUrl' => $company['webAddress'] ?? null,

            'vestigingsAdres' => [
                'straat' => $company['address1'] ?? null,
                'huisnummer' => $company['address2'] ?? null,
                'postcode' => $company['postalCode'] ?? null,
                'plaats' => $company['city'] ?? null,
                'provincie' => $company['state'] ?? null,
                'land' => $company['countryID'] ?? null,
            ],

            'correspondentieAdres' => [
                'straat' => $company['billingAddress1'] ?? $company['address1'] ?? null,
                'huisnummer' => $company['billingAddress2'] ?? $company['address2'] ?? null,
                'postcode' => $company['billToZipCode'] ?? $company['postalCode'] ?? null,
                'plaats' => $company['billToCity'] ?? $company['city'] ?? null,
                'provincie' => $company['billToState'] ?? $company['state'] ?? null,
                'land' => $company['billToCountryID'] ?? $company['countryID'] ?? null,
            ],

            'memo' => $company['additionalAddressInformation'] ?? null,
        ];
    }

    public static function mapInvoiceToVerkoopboeking(array $invoice, string $snelstartRelatieId): array
    {
        $total = (float)($invoice['invoiceTotal'] ?? 0);
        $tax   = (float)($invoice['totalTaxValue'] ?? 0);
        $excl  = max(0, $total - $tax);

        $btwSoort = 'Overig';
        if ($total > 0) {
            $percentage = round(($tax / $total) * 100, 2);
            if ($percentage === 21.00) $btwSoort = 'Hoog';
            elseif ($percentage === 9.00) $btwSoort = 'Laag';
            elseif ($percentage === 0.00) $btwSoort = 'Geen';
        }

        $factuurdatum = $invoice['invoiceDateTime'] ?? null;
        if (preg_match('/Date\((\d+)\)/', $factuurdatum, $m)) {
            $factuurdatum = date('Y-m-d', $m[1] / 1000);
        }

        return [
            'externId' => (string)($invoice['id'] ?? ''),
            'factuurnummer' => $invoice['invoiceNumber'] ?? null,
            'factuurdatum' => $factuurdatum,
            'klant' => ['id' => $snelstartRelatieId],
            'omschrijving' => $invoice['comments'] ?? null,
            'factuurbedrag' => $total,
            'betalingstermijn' => $invoice['paymentTerm'] ?? null,
            'boekingsregels' => [[
                'omschrijving' => 'Autotask invoice line',
                'bedrag' => $excl,
                'btwSoort' => $btwSoort,
            ]],
            'btw' => [[
                'btwSoort' => $btwSoort,
                'btwBedrag' => $tax,
            ]],
        ];
    }
}
