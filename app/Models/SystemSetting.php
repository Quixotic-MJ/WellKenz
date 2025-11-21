<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'system_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('setting_key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->setting_value, $setting->setting_type);
    }

    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string|null $description
     * @param bool $isPublic
     * @return static
     */
    public static function set($key, $value, $type = 'string', $description = null, $isPublic = false)
    {
        $setting = static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => static::encodeValue($value, $type),
                'setting_type' => $type,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );

        return $setting;
    }

    /**
     * Cast setting value based on type.
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'decimal':
                return (float) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            case 'string':
            default:
                return (string) $value;
        }
    }

    /**
     * Encode value based on type.
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    private static function encodeValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'json':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }

    /**
     * Get multiple settings by keys.
     *
     * @param array $keys
     * @return array
     */
    public static function getMany(array $keys)
    {
        $settings = static::whereIn('setting_key', $keys)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->setting_key] = static::castValue(
                $setting->setting_value,
                $setting->setting_type
            );
        }

        // Fill in defaults for missing keys
        foreach ($keys as $key) {
            if (!isset($result[$key])) {
                $result[$key] = static::getDefaultValue($key);
            }
        }

        return $result;
    }

    /**
     * Set multiple settings at once.
     *
     * @param array $settings
     * @return void
     */
    public static function setMany(array $settings)
    {
        foreach ($settings as $key => $data) {
            if (is_array($data)) {
                static::set($key, $data['value'], $data['type'] ?? 'string', $data['description'] ?? null, $data['is_public'] ?? false);
            } else {
                static::set($key, $data);
            }
        }
    }

    /**
     * Get default value for a setting key.
     *
     * @param string $key
     * @return mixed
     */
    private static function getDefaultValue($key)
    {
        $defaults = [
            'app_name' => 'WellKenz',
            'company_name' => 'WellKenz Bakery',
            'app_timezone' => 'Asia/Manila',
            'currency' => 'PHP',
            'low_stock_threshold' => 10,
            'default_lead_time' => 3,
            'business_hours_open' => '06:00',
            'business_hours_close' => '20:00',
            'tax_rate' => 0.12,
            'default_batch_size' => 100,
        ];

        return $defaults[$key] ?? null;
    }

    /**
     * Get public settings for client-side use.
     *
     * @return array
     */
    public static function getPublicSettings()
    {
        $settings = static::where('is_public', true)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->setting_key] = static::castValue(
                $setting->setting_value,
                $setting->setting_type
            );
        }

        return $result;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($setting) {
            $setting->updated_at = now();
        });
    }
}