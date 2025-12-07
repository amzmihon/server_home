<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Package;

class HostingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories
        $sharedHosting = Category::create([
            'name' => 'Shared Hosting',
            'slug' => 'shared-hosting',
            'description' => 'Affordable shared hosting plans',
            'icon' => '🌐',
            'order' => 1,
            'is_active' => true,
        ]);

        $vps = Category::create([
            'name' => 'VPS Hosting',
            'slug' => 'vps-hosting',
            'description' => 'High-performance VPS hosting',
            'icon' => '⚡',
            'order' => 2,
            'is_active' => true,
        ]);

        // Create features for Shared Hosting
        $storage = Feature::create([
            'category_id' => $sharedHosting->id,
            'name' => 'SSD Storage',
            'slug' => 'ssd-storage',
            'description' => 'SSD Storage space in GB',
            'type' => Feature::TYPE_NUMBER,
            'min_value' => 1,
            'max_value' => 500,
            'default_value' => '10',
            'base_price' => 0,
            'is_customizable' => true,
            'is_active' => true,
            'order' => 1,
        ]);

        $bandwidth = Feature::create([
            'category_id' => $sharedHosting->id,
            'name' => 'Monthly Bandwidth',
            'slug' => 'bandwidth',
            'description' => 'Monthly bandwidth limit in GB',
            'type' => Feature::TYPE_NUMBER,
            'min_value' => 10,
            'max_value' => 1000,
            'default_value' => '100',
            'base_price' => 0,
            'is_customizable' => true,
            'is_active' => true,
            'order' => 2,
        ]);

        $websites = Feature::create([
            'category_id' => $sharedHosting->id,
            'name' => 'Websites',
            'slug' => 'websites',
            'description' => 'Number of websites allowed',
            'type' => Feature::TYPE_NUMBER,
            'min_value' => 1,
            'max_value' => 100,
            'default_value' => '5',
            'base_price' => 0,
            'is_customizable' => true,
            'is_active' => true,
            'order' => 3,
        ]);

        $ssl = Feature::create([
            'category_id' => $sharedHosting->id,
            'name' => 'Free SSL Certificate',
            'slug' => 'free-ssl',
            'description' => 'Complimentary SSL/TLS certificate',
            'type' => Feature::TYPE_BOOLEAN,
            'default_value' => '1',
            'base_price' => 0,
            'is_customizable' => false,
            'is_active' => true,
            'order' => 4,
        ]);

        $backup = Feature::create([
            'category_id' => $sharedHosting->id,
            'name' => 'Daily Backup',
            'slug' => 'daily-backup',
            'description' => 'Automatic daily backups',
            'type' => Feature::TYPE_BOOLEAN,
            'default_value' => '1',
            'base_price' => 0,
            'is_customizable' => false,
            'is_active' => true,
            'order' => 5,
        ]);

        // Create packages for Shared Hosting
        $soloPackage = Package::create([
            'category_id' => $sharedHosting->id,
            'name' => 'Solo',
            'slug' => 'solo',
            'description' => 'Perfect for beginners',
            'base_price' => 99,
            'billing_cycle' => Package::BILLING_MONTHLY,
            'setup_fee' => 0,
            'is_popular' => false,
            'is_active' => true,
            'discount_percentage' => 50,
            'order' => 1,
        ]);

        $soloPackage->features()->attach([
            $storage->id => ['value' => '2', 'price_modifier' => 0, 'is_default' => true],
            $bandwidth->id => ['value' => '50', 'price_modifier' => 0, 'is_default' => true],
            $websites->id => ['value' => '1', 'price_modifier' => 0, 'is_default' => true],
            $ssl->id => ['value' => '1', 'price_modifier' => 0, 'is_default' => true],
            $backup->id => ['value' => '1', 'price_modifier' => 0, 'is_default' => true],
        ]);

        $infinityPackage = Package::create([
            'category_id' => $sharedHosting->id,
            'name' => 'Infinity',
            'slug' => 'infinity',
            'description' => 'Best for growing businesses',
            'base_price' => 199,
            'billing_cycle' => Package::BILLING_MONTHLY,
            'setup_fee' => 0,
            'is_popular' => true,
            'is_active' => true,
            'discount_percentage' => 50,
            'order' => 2,
        ]);

        $infinityPackage->features()->attach([
            $storage->id => ['value' => '5', 'price_modifier' => 0.5, 'is_default' => true],
            $bandwidth->id => ['value' => '100', 'price_modifier' => 0.2, 'is_default' => true],
            $websites->id => ['value' => '2', 'price_modifier' => 10, 'is_default' => true],
            $ssl->id => ['value' => '1', 'price_modifier' => 0, 'is_default' => true],
            $backup->id => ['value' => '1', 'price_modifier' => 0, 'is_default' => true],
        ]);

        $proPackage = Package::create([
            'category_id' => $sharedHosting->id,
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'For professionals',
            'base_price' => 349,
            'billing_cycle' => Package::BILLING_MONTHLY,
            'setup_fee' => 0,
            'is_popular' => false,
            'is_active' => true,
            'discount_percentage' => 50,
            'order' => 3,
        ]);

        $proPackage->features()->attach([
            $storage->id => ['value' => '10', 'price_modifier' => 1, 'is_default' => true],
            $bandwidth->id => ['value' => '500', 'price_modifier' => 0.5, 'is_default' => true],
            $websites->id => ['value' => '5', 'price_modifier' => 20, 'is_default' => true],
            $ssl->id => ['value' => '1', 'price_modifier' => 0, 'is_default' => true],
            $backup->id => ['value' => '1', 'price_modifier' => 0, 'is_default' => true],
        ]);

        // Create features for VPS
        $vpsRam = Feature::create([
            'category_id' => $vps->id,
            'name' => 'RAM',
            'slug' => 'vps-ram',
            'description' => 'RAM in GB',
            'type' => Feature::TYPE_DROPDOWN,
            'options' => ['1', '2', '4', '8', '16', '32'],
            'default_value' => '2',
            'base_price' => 0,
            'is_customizable' => true,
            'is_active' => true,
            'order' => 1,
        ]);

        $vpsCpu = Feature::create([
            'category_id' => $vps->id,
            'name' => 'CPU Cores',
            'slug' => 'vps-cpu',
            'description' => 'Number of CPU cores',
            'type' => Feature::TYPE_DROPDOWN,
            'options' => ['1', '2', '4', '8'],
            'default_value' => '2',
            'base_price' => 0,
            'is_customizable' => true,
            'is_active' => true,
            'order' => 2,
        ]);

        $vpsStorage = Feature::create([
            'category_id' => $vps->id,
            'name' => 'NVMe Storage',
            'slug' => 'vps-storage',
            'description' => 'SSD NVMe storage in GB',
            'type' => Feature::TYPE_DROPDOWN,
            'options' => ['20', '40', '80', '160', '320'],
            'default_value' => '40',
            'base_price' => 0,
            'is_customizable' => true,
            'is_active' => true,
            'order' => 3,
        ]);

        $ddos = Feature::create([
            'category_id' => $vps->id,
            'name' => 'DDoS Protection',
            'slug' => 'ddos-protection',
            'description' => 'Advanced DDoS protection',
            'type' => Feature::TYPE_BOOLEAN,
            'default_value' => '1',
            'base_price' => 0,
            'is_customizable' => false,
            'is_active' => true,
            'order' => 4,
        ]);

        // Create VPS packages
        $basicVps = Package::create([
            'category_id' => $vps->id,
            'name' => 'Basic',
            'slug' => 'basic-vps',
            'description' => 'Entry-level VPS',
            'base_price' => 499,
            'billing_cycle' => Package::BILLING_MONTHLY,
            'setup_fee' => 0,
            'is_popular' => false,
            'is_active' => true,
            'order' => 1,
        ]);

        $basicVps->features()->attach([
            $vpsRam->id => ['value' => '2', 'price_modifier' => 0, 'is_default' => true],
            $vpsCpu->id => ['value' => '2', 'price_modifier' => 0, 'is_default' => true],
            $vpsStorage->id => ['value' => '40', 'price_modifier' => 0, 'is_default' => true],
            $ddos->id => ['value' => '1', 'price_modifier' => 0, 'is_default' => true],
        ]);
    }
}
