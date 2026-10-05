<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\FarmerProfile;
use App\Models\BuyerProfile;
use App\Models\ProductListing;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * Hidden fields for API responses
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::created(function ($user) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('wallets')) {
                    \App\Models\Wallet::firstOrCreate(
                        ['user_id' => $user->id],
                        ['available_balance' => 0, 'pending_balance' => 0]
                    );
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Wallet auto-create skipped', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function farmerProfile()
    {
        return $this->hasOne(FarmerProfile::class);
    }

    public function buyerProfile()
    {
        return $this->hasOne(BuyerProfile::class);
    }

    public function listings()
    {
        return $this->hasMany(ProductListing::class, 'seller_id');
    }
    
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
    
    public function organisations()
{
    return $this->hasMany(
        OrganisationMember::class
    );
}

public function notifications()
{
    return $this->hasMany(Notification::class);
}

public function ownedOrganisations()
{
    return $this->hasMany(
        OrganisationMember::class
    )->where('is_owner', true);
}

public function assignedTasks()
{
    return $this->hasMany(TaskAssignment::class);
}

public function createdTasks()
{
    return $this->hasMany(Task::class,'created_by');
}

/**
 * Get all services offered by the provider.
 */
public function services(): HasMany
{
    return $this->hasMany(Service::class);
}

public function serviceBookings()
{
    return $this->hasMany(ServiceBooking::class, 'customer_id');
}

public function serviceJobs()
{
    return $this->hasMany(ServiceBooking::class, 'provider_id');
}

public function serviceQuotes()
{
    return $this->hasMany(ServiceQuote::class, 'customer_id');
}

public function receivedServiceQuotes()
{
    return $this->hasMany(ServiceQuote::class, 'provider_id');
}

public function providerProfile()
{
    return $this->hasOne(ProviderProfile::class);
}

public function shoppingCart()
{
    return $this->hasOne(ShoppingCart::class);
}

public function wishlist()
{
    return $this->hasMany(Wishlist::class);
}

public function subscriptions()
{
    return $this->hasMany(UserSubscription::class);
}

public function activeSubscription()
{
    return $this->hasOne(UserSubscription::class)
        ->where('status', 'active')
        ->latestOfMany();
}

public function hasActiveSubscription(): bool
{
    return $this->activeSubscription &&
           $this->activeSubscription->isActive();
}

public function currentPlan()
{
    return optional(
        $this->activeSubscription
    )->plan;
}

public function loanApplications()
{
    return $this->hasMany(
        LoanApplication::class
    );
}

public function loanRepayments()
{
    return $this->hasManyThrough(
        LoanRepayment::class,
        LoanApplication::class
    );
}


public function creditScore()
{
    return $this->hasOne(CreditScore::class);
}

public function farms()
{
    return $this->hasMany(
        Farm::class,
        'owner_id'
    );
}

}
