<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $personType = fake()->randomElement(['individual', 'company']);

        $document = $personType === 'company'
            ? fake()->numerify('########0001##')
            : fake()->numerify('###########');

        return [
            'name' => fake()->name(),
            'person_type' => $personType,
            'document' => $document,
            'observations' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['active', 'inactive']),
            'postal_code' => fake()->numerify('########'),
            'address' => fake()->streetAddress(),
            'address_number' => (string) fake()->numberBetween(1, 9999),
            'address_complement' => fake()->optional()->secondaryAddress(),
            'neighborhood' => fake()->streetName(),
            'city' => fake()->city(),
            'state' => Str::upper(fake()->randomElement([
                'AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO',
            ])),
            'contact_name' => $personType === 'company' ? fake()->name() : null,
            'contact_phone_primary' => fake()->numerify('###########'),
            'contact_phone_secondary' => fake()->optional()->numerify('###########'),
            'contact_email' => fake()->safeEmail(),
            'created_by_id' => User::factory(),
            'updated_by_id' => null,
        ];
    }
}
