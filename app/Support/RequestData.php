<?php

namespace App\Support;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Language;
use App\Models\PaymentSetting;
use App\Models\Setting;

/**
 * Request-scoped lookups shared by layouts and view composers.
 *
 * Values are deliberately not cached between requests: settings such as the
 * license status must remain fresh on every request.
 */
class RequestData
{
    private ?Setting $setting = null;

    private bool $settingLoaded = false;

    private ?PaymentSetting $paymentSetting = null;

    private bool $paymentSettingLoaded = false;

    private ?string $currencySymbol = null;

    private bool $currencySymbolLoaded = false;

    private $banners = null;

    private $activeCategories = null;

    private $activeLanguages = null;

    public function setting(): ?Setting
    {
        if (! $this->settingLoaded) {
            $this->setting = Setting::find(1);
            $this->settingLoaded = true;
        }

        return $this->setting;
    }

    public function paymentSetting(): ?PaymentSetting
    {
        if (! $this->paymentSettingLoaded) {
            $this->paymentSetting = PaymentSetting::find(1);
            $this->paymentSettingLoaded = true;
        }

        return $this->paymentSetting;
    }

    public function currencySymbol(): ?string
    {
        if ($this->currencySymbolLoaded) {
            return $this->currencySymbol;
        }

        $code = $this->setting()?->currency;
        $this->currencySymbol = $code
            ? Currency::where('code', $code)->first()?->symbol
            : null;
        $this->currencySymbolLoaded = true;

        return $this->currencySymbol;
    }

    public function banners()
    {
        return $this->banners ??= Banner::where('status', 1)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function activeCategories()
    {
        return $this->activeCategories ??= Category::where('status', 1)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function activeLanguages()
    {
        return $this->activeLanguages ??= Language::where('status', 1)->get();
    }
}
