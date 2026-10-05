<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserBankLink;
use Illuminate\Support\Facades\Schema;

/**
 * Platform policy for NMB bank linking.
 *
 * Soft: withdrawals above threshold require a linked (any) NMB account.
 * Hard: loan disbursement requires a linked account (prefer verified).
 */
class NmbLinkPolicy
{
    /** TZS — withdrawals at or above this need a bank link */
    public static function withdrawalThreshold(): float
    {
        return (float) config('nmb.withdrawal_link_threshold', env('NMB_WITHDRAWAL_LINK_THRESHOLD', 50000));
    }

    public static function requireVerifiedForDisburse(): bool
    {
        return (bool) config('nmb.require_verified_for_disburse', env('NMB_REQUIRE_VERIFIED_DISBURSE', false));
    }

    public static function linksEnabled(): bool
    {
        return class_exists(UserBankLink::class) && Schema::hasTable('user_bank_links');
    }

    public static function primaryLink(?User $user): ?UserBankLink
    {
        if (!$user || !self::linksEnabled()) {
            return null;
        }

        return UserBankLink::where('user_id', $user->id)
            ->where('provider', 'nmb')
            ->orderByDesc('verified')
            ->orderByDesc('id')
            ->first();
    }

    public static function hasLink(?User $user): bool
    {
        return self::primaryLink($user) !== null;
    }

    public static function hasVerifiedLink(?User $user): bool
    {
        $link = self::primaryLink($user);

        return $link && $link->verified;
    }

    /**
     * @return array{ok:bool,message?:string,code?:int,link?:UserBankLink}
     */
    public static function assertCanWithdraw(?User $user, float $amount): array
    {
        if ($amount < self::withdrawalThreshold()) {
            return ['ok' => true];
        }

        if (!self::linksEnabled()) {
            return ['ok' => true]; // table missing — do not block platform
        }

        $link = self::primaryLink($user);
        if (!$link) {
            return [
                'ok' => false,
                'code' => 422,
                'message' => 'Withdrawals of TZS '.number_format(self::withdrawalThreshold()).
                    ' or more require a linked NMB account. Link one under Finance → NMB Bank, then retry.',
            ];
        }

        return ['ok' => true, 'link' => $link];
    }

    /**
     * @return array{ok:bool,message?:string,code?:int,link?:UserBankLink}
     */
    public static function assertCanDisburseLoan(?User $user): array
    {
        if (!self::linksEnabled()) {
            return ['ok' => true];
        }

        $link = self::primaryLink($user);
        if (!$link) {
            return [
                'ok' => false,
                'code' => 422,
                'message' => 'Farmer must link an NMB account before loan disbursement. Ask them to complete Finance → NMB Bank link.',
            ];
        }

        if (self::requireVerifiedForDisburse() && !$link->verified) {
            return [
                'ok' => false,
                'code' => 422,
                'message' => 'Farmer NMB account is linked but not verified. Admin must verify the bank link before disbursement.',
                'link' => $link,
            ];
        }

        return ['ok' => true, 'link' => $link];
    }
}
