<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        $faker = Faker::create('pt_BR');

        return [
            'client_id' => null, // Definir ao usar
            'postal_code' => $faker->postcode(),
            'address' => $faker->streetAddress(),
            'address_number' => (string) $faker->numberBetween(1, 9999),
            'address_complement' => $faker->optional()->secondaryAddress(),
            'neighborhood' => $faker->streetName(),
            'city' => $faker->city(),
            'state' => Str::upper($faker->randomElement([
                'AC',
                'AL',
                'AP',
                'AM',
                'BA',
                'CE',
                'DF',
                'ES',
                'GO',
                'MA',
                'MT',
                'MS',
                'MG',
                'PA',
                'PB',
                'PR',
                'PE',
                'PI',
                'RJ',
                'RN',
                'RS',
                'RO',
                'RR',
                'SC',
                'SP',
                'SE',
                'TO',
            ])),
            'description' => $faker->randomElement([
                'Casa principal',
                'Apartamento',
                'Escritório',
                'Casa de praia',
                'Loja física',
                'Endereço de entrega',
                'Casa dos pais',
                'Cobrança',
                'Residencial',
                'Comercial',
                'Casa de campo',
                'Galpão',
                'Sala comercial',
                'Cobertura',
                'Casa de veraneio',
                'Escritório central',
                'Filial',
                'Depósito',
                'Showroom',
                'Ateliê',
                'Consultório',
                'Clínica',
                'Escola',
                'Academia',
                'Restaurante',
            ]),
            'status' => $faker->randomElement(['active', 'inactive']),
            'created_by_id' => User::factory(),
            'updated_by_id' => null,
        ];
    }
}
