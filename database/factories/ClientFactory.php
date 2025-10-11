<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $faker = Faker::create('pt_BR');

        $personType = $faker->randomElement(['individual', 'company']);

        $document = $personType === 'company'
            ? $faker->cnpj(false)
            : $faker->cpf(false);

        return [
            'name' => $faker->name(),
            'person_type' => $personType,
            'document' => $document,
            'observations' => $faker->optional()->randomElement([
                'Cliente preferencial - desconto de 10%',
                'Paga sempre em dia',
                'Contato apenas por e-mail',
                'Cliente VIP',
                'Possui contrato ativo',
                'Referenciado por João Silva',
                'Empresa em expansão',
                'Necessita de atendimento urgente',
                'Cliente desde 2020',
                'Observar prazo de entrega',
                'Cliente com histórico de atrasos',
                'Prefere contato por telefone',
                'Empresa do ramo de tecnologia',
                'Cliente internacional',
                'Possui filiais em outras cidades',
                'Cliente com potencial de crescimento',
                'Já fez pedidos grandes anteriormente',
                'Solicitou orçamento personalizado',
                'Cliente com necessidades especiais',
                'Empresa certificada ISO 9001',
                'Contato principal: Maria Santos',
                'Cliente com desconto progressivo',
                'Empresa familiar',
                'Cliente com contrato de manutenção',
                'Observar condições de pagamento',
            ]),
            'status' => $faker->randomElement(['active', 'inactive']),
            'contact_name' => $personType === 'company' ? $faker->name() : null,
            'contact_phone_primary' => $faker->phoneNumber(),
            'contact_phone_secondary' => $faker->optional()->phoneNumber(),
            'contact_email' => $faker->safeEmail(),
            'created_by_id' => User::factory(),
            'updated_by_id' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Client $client) {
            $faker = Faker::create('pt_BR');

            // Criar pelo menos um endereço para cada cliente
            $client->addresses()->create([
                'description' => fake()->randomElement([
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
                'status' => $faker->randomElement(['active', 'inactive']),
                'created_by_id' => $client->created_by_id,
            ]);
        });
    }
}
