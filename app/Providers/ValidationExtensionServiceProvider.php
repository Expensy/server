<?php

namespace App\Providers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Validator;

class ValidationExtensionServiceProvider extends ServiceProvider
{

  public function register() {
    // TODO: Implement register() method.
  }

  public function boot() {
    Validator::extend('unique_project_name', function ($attribute, $value, $parameters) {
      $connectedUser = Auth::user();
      $projects = $connectedUser->projects->all();

      if (count($projects) == 0) {
        return true;
      }

      return !collect($projects)->contains(function ($project) use ($value) {
        return $project->title == $value;
      });
    });

    Validator::extend('one_default_category', function ($attribute, $value, $parameters) {
      if ($value) {
        return true;
      }

      $project = Project::find($parameters[0]);
      $category = Project::find($parameters[0]);

      if (is_null($project) && is_null($category)) {
        return false;
      }

      $categories = !is_null($project) ?
        $project->categories->all() :
        $category->project->categories->all();

      return collect($categories)->contains(function ($category) use ($parameters) {
        if (isset($parameters[1])) {
          return $category->by_default == true && $category->id != $parameters[1];
        }

        return $category->by_default == true;
      });
    });

    Validator::extend('hex_color', function ($attribute, $value, $parameters) {
      return preg_match("/#[A-Fa-f0-9]{6}/", $value);
    });

    Validator::extend('currency', function ($attribute, $value, $parameters) {
      /**
       * From the Faker library
       * @link http://en.wikipedia.org/wiki/ISO_4217
       * On date of 2015-01-10
       */
      $currencyCode = [
        'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN',
        'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL',
        'BSD', 'BTC', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLF',
        'CLP', 'CNY', 'COP', 'CRC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP',
        'DZD', 'EEK', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL',
        'GGP', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK',
        'HTG', 'HUF', 'IDR', 'ILS', 'IMP', 'INR', 'IQD', 'IRR', 'ISK', 'JEP',
        'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD',
        'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTL', 'LVL', 'LYD',
        'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MTL', 'MUR',
        'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR',
        'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR',
        'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD',
        'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS',
        'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD',
        'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XAG', 'XAU', 'XCD',
        'XDR', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMK', 'ZMW', 'ZWL'
      ];

      return collect($currencyCode)->contains($value);
    });
  }
}
