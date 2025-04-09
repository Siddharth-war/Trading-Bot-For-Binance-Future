<?php

namespace App\Observers;

use App\Models\LoginSetup;
use Illuminate\Support\Facades\Cache;

class LoginSetupObserver
{
    /**
     * Handle the LoginSetup "created" event.
     */
    public function created(LoginSetup $loginSetup): void
    {
        $this->refreshLoginSetupCache();
    }

    /**
     * Handle the LoginSetup "updated" event.
     */
    public function updated(LoginSetup $loginSetup): void
    {
        $this->refreshLoginSetupCache();
    }

    /**
     * Handle the LoginSetup "deleted" event.
     */
    public function deleted(LoginSetup $loginSetup): void
    {
        $this->refreshLoginSetupCache();
    }

    /**
     * Handle the LoginSetup "restored" event.
     */
    public function restored(LoginSetup $loginSetup): void
    {
        $this->refreshLoginSetupCache();
    }

    /**
     * Handle the LoginSetup "force deleted" event.
     */
    public function forceDeleted(LoginSetup $loginSetup): void
    {
        $this->refreshLoginSetupCache();
    }

    private function refreshLoginSetupCache()
    {
        Cache::forget(CACHE_LOGIN_SETUP_TABLE);
    }
}
