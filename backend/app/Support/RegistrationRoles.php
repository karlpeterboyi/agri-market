<?php

namespace App\Support;

/**
 * Public registration catalogue — maps platform roles to capabilities,
 * onboarding requirements, and post-signup destinations.
 */
class RegistrationRoles
{
    /**
     * Roles that anyone may self-register for (admin is never public).
     */
    public static function publicRoles(): array
    {
        return [
            'farmer' => [
                'code' => 'farmer',
                'label_en' => 'Farmer / Livestock keeper',
                'label_sw' => 'Mkulima / Mfugaji',
                'description_en' => 'Sell produce & livestock, manage Farm ERP, apply for loans, receive payouts.',
                'description_sw' => 'Uza mazao na mifugo, simamia shamba, omba mikopo, pokea malipo.',
                'needs_organisation' => true,
                'organisation_type' => 'farm',
                'can_sell' => ['produce', 'livestock', 'machinery'],
                'can_buy' => true,
                'wallet' => true,
                'nmb_link_recommended' => true,
                'home' => '/farmer',
                'verification' => 'auto', // active immediately
            ],
            'buyer' => [
                'code' => 'buyer',
                'label_en' => 'Buyer / Trader',
                'label_sw' => 'Mnunuzi / Mfanyabiashara',
                'description_en' => 'Buy from marketplace, pay into escrow, track orders & logistics.',
                'description_sw' => 'Nunua sokoni, lipia escrow, fuatilia oda na usafirishaji.',
                'needs_organisation' => true,
                'organisation_type' => 'trading',
                'can_sell' => [],
                'can_buy' => true,
                'wallet' => true,
                'nmb_link_recommended' => true,
                'home' => '/buyer',
                'verification' => 'auto',
            ],
            'processor' => [
                'code' => 'processor',
                'label_en' => 'Processor / Aggregator',
                'label_sw' => 'Msindikaji / Mkusanyaji',
                'description_en' => 'Aggregate and process farm produce; buy in bulk and sell processed goods.',
                'description_sw' => 'Kusanya na sindika mazao; nunua kwa wingi na uza bidhaa zilizosindikwa.',
                'needs_organisation' => true,
                'organisation_type' => 'processor',
                'can_sell' => ['produce'],
                'can_buy' => true,
                'wallet' => true,
                'nmb_link_recommended' => true,
                'home' => '/processor',
                'verification' => 'auto',
            ],
            'agrodealer' => [
                'code' => 'agrodealer',
                'label_en' => 'Agro-dealer / Input supplier',
                'label_sw' => 'Muuzaji pembejeo',
                'description_en' => 'List seeds, fertilizer, chemicals and farm inputs for sale.',
                'description_sw' => 'Orodhesha mbegu, mbolea, dawa na pembejeo kwa mauzo.',
                'needs_organisation' => true,
                'organisation_type' => 'agrodealer',
                'can_sell' => ['inputs'],
                'can_buy' => true,
                'wallet' => true,
                'nmb_link_recommended' => true,
                'home' => '/agrodealer',
                'verification' => 'auto',
            ],
            'provider' => [
                'code' => 'provider',
                'label_en' => 'Service provider',
                'label_sw' => 'Mtoa huduma',
                'description_en' => 'Offer vet, extension, ploughing, spraying and other agri services.',
                'description_sw' => 'Toa huduma za daktari wa mifugo, ugani, kulima, kunyunyizia n.k.',
                'needs_organisation' => true,
                'organisation_type' => 'service',
                'can_sell' => ['services'],
                'can_buy' => false,
                'wallet' => true,
                'nmb_link_recommended' => true,
                'home' => '/provider',
                'verification' => 'auto',
            ],
            'transporter' => [
                'code' => 'transporter',
                'label_en' => 'Transporter / Logistics',
                'label_sw' => 'Msafirishaji',
                'description_en' => 'Accept delivery jobs from marketplace orders.',
                'description_sw' => 'Kubali kazi za usafirishaji kutoka oda za soko.',
                'needs_organisation' => true,
                'organisation_type' => 'transport',
                'can_sell' => [],
                'can_buy' => false,
                'wallet' => true,
                'nmb_link_recommended' => true,
                'home' => '/transporter',
                'verification' => 'auto',
            ],
            'financier' => [
                'code' => 'financier',
                'label_en' => 'Financial institution',
                'label_sw' => 'Taasisi ya fedha',
                'description_en' => 'Publish loan & insurance products; review and disburse applications.',
                'description_sw' => 'Chapisha mikopo na bima; kagua na toa fedha.',
                'needs_organisation' => true,
                'organisation_type' => 'financial_institution',
                'can_sell' => [],
                'can_buy' => false,
                'wallet' => true,
                'nmb_link_recommended' => true,
                'home' => '/finance/institution',
                'verification' => 'pending', // admin/KYC style
            ],
            'educator' => [
                'code' => 'educator',
                'label_en' => 'Educator / Research institution',
                'label_sw' => 'Mwalimu / Taasisi ya utafiti',
                'description_en' => 'Publish short courses, research and extension content.',
                'description_sw' => 'Chapisha kozi fupi, utafiti na maudhui ya ugani.',
                'needs_organisation' => true,
                'organisation_type' => 'research',
                'can_sell' => [],
                'can_buy' => false,
                'wallet' => false,
                'nmb_link_recommended' => false,
                'home' => '/knowledge/educator',
                'verification' => 'pending',
            ],
        ];
    }

    public static function codes(): array
    {
        return array_keys(self::publicRoles());
    }

    public static function definition(string $role): ?array
    {
        return self::publicRoles()[$role] ?? null;
    }
}
