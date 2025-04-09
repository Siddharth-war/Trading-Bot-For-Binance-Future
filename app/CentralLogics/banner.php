<?php

namespace App\CentralLogics;

use App\Model\Banner;

class BannerLogic
{
    public static function get_banners()
    {
        return Banner::latest()->get();
    }
}
