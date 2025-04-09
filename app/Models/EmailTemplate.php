<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Model\Translation;

class EmailTemplate extends Model
{
    use HasFactory;

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

//    public function getTitleAttribute($value){
//        if (count($this->translations) > 0) {
//            foreach ($this->translations as $translation) {
//                if ($translation['key'] == 'title') {
//                    return $translation['value'];
//                }
//            }
//        }
//
//        return $value;
//    }
//
//    public function getBodyAttribute($value){
//        if (count($this->translations) > 0) {
//            foreach ($this->translations as $translation) {
//                if ($translation['key'] == 'body') {
//                    return $translation['value'];
//                }
//            }
//        }
//
//        return $value;
//    }
//
//    public function getButtonNameAttribute($value){
//        if (count($this->translations) > 0) {
//            foreach ($this->translations as $translation) {
//                if ($translation['key'] == 'button_name') {
//                    return $translation['value'];
//                }
//            }
//        }
//
//        return $value;
//    }
//
//    public function getFooterTextAttribute($value){
//        if (count($this->translations) > 0) {
//            foreach ($this->translations as $translation) {
//                if ($translation['key'] == 'footer_text') {
//                    return $translation['value'];
//                }
//            }
//        }
//
//        return $value;
//    }
//
//    public function getCopyrightTextAttribute($value){
//        if (count($this->translations) > 0) {
//            foreach ($this->translations as $translation) {
//                if ($translation['key'] == 'copyright_text') {
//                    return $translation['value'];
//                }
//            }
//        }
//
//        return $value;
//    }
//
//    protected static function booted()
//    {
//        static::addGlobalScope('translate', function (Builder $builder) {
//            $builder->with(['translations' => function ($query) {
//                return $query->where('locale', app()->getLocale());
//            }]);
//        });
//    }
}
