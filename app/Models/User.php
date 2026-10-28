<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'role',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'review_qr_token',
        'api_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => 'integer',
        'api_token' => 'encrypted',
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(UserReview::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(UserAppointment::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(UserFaq::class);
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(UserGalleryImage::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(UserVideo::class);
    }

    public function newsItems(): HasMany
    {
        return $this->hasMany(UserNews::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(UserArticle::class);
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(UserBlog::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(UserService::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(UserPackage::class);
    }

    public function apiDomains(): HasMany
    {
        return $this->hasMany(UserApiDomain::class);
    }

    public function sliderItems(): HasMany
    {
        return $this->hasMany(UserSliderItem::class);
    }

    public function cmsPages(): HasMany
    {
        return $this->hasMany(UserCmsPage::class);
    }

    public function aboutUsPage(): HasOne
    {
        return $this->hasOne(UserAboutUsPage::class);
    }

    public function smtpSettings(): HasOne
    {
        return $this->hasOne(UserSmtpSetting::class);
    }

    public function socialMediaLinks(): HasMany
    {
        return $this->hasMany(UserSocialMediaLink::class);
    }

    public function websiteSettings(): HasOne
    {
        return $this->hasOne(UserWebsiteSetting::class);
    }
}
