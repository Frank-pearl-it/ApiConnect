<?php

namespace App\Services;

class MapService
{
    public static function mapCompanyToRelatie(array $company): array
    {
        return [
            // Basis
            'naam' => $company['companyName'] ?? null,
            'relatiecode' => $company['companyNumber'] ?? null, // or leave null if SnelStart auto-assigns
            'kvkNummer' => $company['companyNumber'] ?? null,
            'btwNummer' => $company['taxID'] ?? null,
            'nonactief' => isset($company['isActive']) ? !$company['isActive'] : false,

            // Contact
            'telefoon' => $company['phone'] ?? null,
            'mobieleTelefoon' => $company['alternatePhone1'] ?? null,
            'email' => $company['email'] ?? null, // Autotask "Email" if available
            'websiteUrl' => $company['webAddress'] ?? null,

            // Adres → vestigingsAdres
            'vestigingsAdres' => [
                'straat' => $company['address1'] ?? null,
                'huisnummer' => $company['address2'] ?? null, // if house number is separate, adjust mapping
                'postcode' => $company['postalCode'] ?? null,
                'plaats' => $company['city'] ?? null,
                'provincie' => $company['state'] ?? null,
                'land' => $company['countryID'] ?? null, // might need ISO translation
            ],

            // Correspondentieadres (fallback to same as vestiging)
            'correspondentieAdres' => [
                'straat' => $company['billingAddress1'] ?? $company['address1'] ?? null,
                'huisnummer' => $company['billingAddress2'] ?? $company['address2'] ?? null,
                'postcode' => $company['billToZipCode'] ?? $company['postalCode'] ?? null,
                'plaats' => $company['billToCity'] ?? $company['city'] ?? null,
                'provincie' => $company['billToState'] ?? $company['state'] ?? null,
                'land' => $company['billToCountryID'] ?? $company['countryID'] ?? null,
            ],

            // Extra info
            'memo' => $company['additionalAddressInformation'] ?? null,
        ];
    }
    public static function mapInvoiceToVerkoopboeking(array $invoice, array $snelstartRelatie): array
    {
        return [
            'factuurdatum' => $invoice['invoiceDateTime'] ?? null,
            'factuurnummer' => $invoice['invoiceNumber'] ?? null,
            'klant' => [
                'id' => $snelstartRelatie['id'], // fetched earlier via BTW or relatiecode
            ],
            'omschrijving' => $invoice['comments'] ?? 'Autotask factuur',
            'factuurbedrag' => $invoice['invoiceTotal'] ?? 0,
            'betalingstermijn' => $invoice['paymentTerm'] ?? 0,
            'boekingsregels' => array_map(function ($line) {
                return [
                    'omschrijving' => $line['description'] ?? 'Autotask regel',
                    'grootboek' => [
                        'id' => '00000000-0000-0000-0000-000000000000' // <-- map to correct grootboekrekening in SnelStart
                    ],
                    'bedrag' => $line['amount'] ?? 0,
                ];
            }, $invoice['lines'] ?? []),
            'btw' => [
                [
                    'btwPercentage' => 21, // map based on taxRegionName
                    'bedrag' => $invoice['totalTaxValue'] ?? 0,
                ]
            ],
        ];
    }


}