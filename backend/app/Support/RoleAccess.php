<?php

namespace App\Support;

use App\Models\User;

/**
 * Column-based role matrix (users.role):
 * farmer | buyer | processor | provider | agrodealer | transporter | admin
 */
class RoleAccess
{
    public const FARMER = 'farmer';
    public const BUYER = 'buyer';
    public const PROCESSOR = 'processor';
    public const PROVIDER = 'provider';
    public const AGRODEALER = 'agrodealer';
    public const TRANSPORTER = 'transporter';
    public const ADMIN = 'admin';
    public const FINANCIER = 'financier';
    public const EDUCATOR = 'educator';

    public static function role(?User $user): string
    {
        return strtolower(trim((string) ($user->role ?? '')));
    }

    public static function isAdmin(?User $user): bool
    {
        return self::role($user) === self::ADMIN;
    }

    public static function canManageFarms(?User $user): bool
    {
        return in_array(self::role($user), [self::FARMER, self::ADMIN], true);
    }

    public static function canSellProduce(?User $user): bool
    {
        return in_array(self::role($user), [self::FARMER, self::PROCESSOR, self::ADMIN], true);
    }

    public static function canSellInputs(?User $user): bool
    {
        return in_array(self::role($user), [self::AGRODEALER, self::ADMIN], true);
    }

    /**
     * Machinery: farmers (owners) + admin.
     * Providers are intentionally excluded (services only).
     */
    public static function canSellMachinery(?User $user): bool
    {
        return in_array(self::role($user), [self::FARMER, self::ADMIN], true);
    }

    public static function canSellServices(?User $user): bool
    {
        return in_array(self::role($user), [self::PROVIDER, self::ADMIN], true);
    }

    public static function canBuy(?User $user): bool
    {
        return in_array(self::role($user), [
            self::BUYER, self::PROCESSOR, self::FARMER, self::ADMIN, self::TRANSPORTER,
        ], true);
    }

    public static function deny(string $message, int $code = 403)
    {
        abort($code, $message);
    }

    public static function assertAdmin(?User $user): void
    {
        if (! self::isAdmin($user)) {
            self::deny('Admin only. Your role: '.self::role($user));
        }
    }

    public static function assertCanSellProduce(?User $user): void
    {
        if (! self::canSellProduce($user)) {
            self::deny('Only farmers, processors (or admin) can create produce listings. Your role: '.self::role($user));
        }
    }

    public static function assertCanSellInputs(?User $user): void
    {
        if (! self::canSellInputs($user)) {
            self::deny('Only agrodealers (or admin) can create farm-input listings. Your role: '.self::role($user));
        }
    }

    public static function assertCanSellMachinery(?User $user): void
    {
        if (! self::canSellMachinery($user)) {
            self::deny(
                'Machinery listings are allowed for roles: farmer, admin. '.
                'Service providers cannot list machinery. Your role: '.self::role($user)
            );
        }
    }

    public static function assertCanSellServices(?User $user): void
    {
        if (! self::canSellServices($user)) {
            self::deny('Only service providers (or admin) can create services. Your role: '.self::role($user));
        }
    }

    public static function ownsOrAdmin(?User $user, $ownerId): bool
    {
        if (! $user) {
            return false;
        }

        return self::isAdmin($user) || (int) $user->id === (int) $ownerId;
    }

    public static function canManageFinance(?User $user): bool
    {
        return in_array(self::role($user), [self::FINANCIER, self::ADMIN], true);
    }

    public static function canManageKnowledge(?User $user): bool
    {
        return in_array(self::role($user), [self::EDUCATOR, self::ADMIN, self::PROVIDER], true);
    }
}
