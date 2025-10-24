<?php

namespace Database\Factories;

use App\Services\HolidayService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'street' => fake()->streetAddress(),
            'house_number' => fake()->buildingNumber(),
            'city' => fake()->city(),
            'zip' => fake()->postcode(),
            'country' => fake()->country(),
            'federal_state' => collect(HolidayService::getRegionCodes('DE'))->random(),
            'country' => 'DE'
        ];
    }
}
