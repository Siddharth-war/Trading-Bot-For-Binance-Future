<?php

namespace App;

use App\Model\Branch;
use App\Model\ChefBranch;
use App\Model\CustomerAddress;
use App\Model\Order;
use App\Model\Wishlist;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'f_name', 'l_name', 'phone', 'email', 'password', 'point', 'is_active', 'user_type', 'refer_code', 'refer_by', 'language_code','group_id','credit_limit','branch_option','day_id','remaining_credit_limit',"business_name","vat_business","business_address"
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_phone_verified' => 'integer',
        'point' => 'integer',
    ];

    /* protected $appends = [ 'branch_id' ];*/

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'user_id');
    }

    public function chefBranch(): HasOne
    {
        return $this->hasOne(ChefBranch::class, 'user_id', 'id');
    }

    public static function get_chef_branch_name($chef)
    {
        $branch = DB::table('chef_branch')->where('user_id', $chef->id)->get();
        foreach ($branch as $value) {
            $branch_name = Branch::where('id', $value->branch_id)->get();
            foreach ($branch_name as $bn) {
                return $bn->name;
            }
        }
    }

    public function scopeOfType($query, $user_type)
    {
        if ($user_type != 'customer') {
            return $query->where('user_type', $user_type);
        }
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'user_id');
    }

    public function getImageFullPathAttribute($type = null): string
    {
        $image = $this->image ?? null;
        $path = asset('public/assets/admin/img/160x160/img1.jpg');

        if (!is_null($image) && Storage::disk('public')->exists('profile/' . $image)) {
            $path = asset('storage/app/public/profile/' . $image);
        }
        if ($this->user_type == 'kitchen'){
            if (!is_null($image) && Storage::disk('public')->exists('kitchen/' . $image)) {
                $path = asset('storage/app/public/kitchen/' . $image);
            }
        }
        return $path;
    }

}
