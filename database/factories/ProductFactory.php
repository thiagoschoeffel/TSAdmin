<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $faker = Faker::create('pt_BR');

        // Produtos de restaurante: marmitas, componentes, bebidas e sobremesas
        $products = [
            // Marmitas completas
            ['name' => 'Feijoada Completa', 'description' => 'Marmita com feijoada, arroz, couve refogada, laranja e farofa. Prato típico brasileiro.', 'price' => 32.00],
            ['name' => 'Arroz com Feijão', 'description' => 'Marmita com arroz branco, feijão carioca, bife acebolado e salada de alface.', 'price' => 22.00],
            ['name' => 'Moqueca de Peixe', 'description' => 'Marmita com moqueca de peixe, arroz e pirão. Deliciosa opção baiana.', 'price' => 38.00],
            ['name' => 'Strogonoff de Frango', 'description' => 'Marmita com strogonoff de frango, arroz e batata palha. Clássico brasileiro.', 'price' => 28.00],
            ['name' => 'Lasanha à Bolonhesa', 'description' => 'Marmita com lasanha de carne moída, molho bolonhesa e queijo gratinado.', 'price' => 30.00],
            ['name' => 'Churrasco Misto', 'description' => 'Marmita com picanha, linguiça, arroz, farofa e vinagrete.', 'price' => 42.00],
            ['name' => 'Baião de Dois', 'description' => 'Marmita com baião de dois, queijo coalho e carne seca. Prato nordestino.', 'price' => 26.00],
            ['name' => 'Frango com Quiabo', 'description' => 'Marmita com frango caipira, quiabo refogado e angu.', 'price' => 24.00],
            ['name' => 'Carne de Panela', 'description' => 'Marmita com carne de panela, legumes e batatas.', 'price' => 34.00],
            ['name' => 'Peixe Assado', 'description' => 'Marmita com peixe assado, legumes grelhados e arroz.', 'price' => 36.00],
            ['name' => 'Bobó de Camarão', 'description' => 'Marmita com bobó de camarão, arroz e farofa. Especialidade baiana.', 'price' => 45.00],
            ['name' => 'Vatapá', 'description' => 'Marmita com vatapá de camarão, arroz e caruru.', 'price' => 40.00],
            ['name' => 'Sarapatel', 'description' => 'Marmita com sarapatel, arroz e feijão. Prato goiano.', 'price' => 29.00],
            ['name' => 'Picanha na Chapa', 'description' => 'Marmita com picanha na chapa, batatas e salada.', 'price' => 44.00],
            ['name' => 'Costela Bovina', 'description' => 'Marmita com costela bovina assada, purê e legumes.', 'price' => 39.00],
            ['name' => 'Frango Xadrez', 'description' => 'Marmita com frango xadrez, arroz e legumes orientais.', 'price' => 27.00],
            ['name' => 'Escondidinho de Carne', 'description' => 'Marmita com escondidinho de carne seca, queijo e batata.', 'price' => 31.00],
            ['name' => 'Rabada', 'description' => 'Marmita com rabada cozida, arroz e tutu de feijão.', 'price' => 41.00],
            ['name' => 'Buchada de Bode', 'description' => 'Marmita com buchada de bode, arroz e farofa. Prato nordestino.', 'price' => 43.00],
            ['name' => 'Moela de Frango', 'description' => 'Marmita com moela de frango, angu e quiabo.', 'price' => 21.00],
            ['name' => 'Dobradinha', 'description' => 'Marmita com dobradinha, arroz e feijão.', 'price' => 37.00],
            ['name' => 'Tripas', 'description' => 'Marmita com tripas refogadas, arroz e vinagrete.', 'price' => 28.00],
            ['name' => 'Carne Louca', 'description' => 'Marmita com carne louca, arroz e salada.', 'price' => 33.00],
            ['name' => 'Galinhada', 'description' => 'Marmita com galinhada caipira, arroz e farofa.', 'price' => 25.00],
            ['name' => 'Canja de Galinha', 'description' => 'Marmita com canja de galinha, arroz e legumes.', 'price' => 20.00],
            // Componentes individuais
            ['name' => 'Arroz Branco', 'description' => 'Porção de arroz branco cozido.', 'price' => 6.00],
            ['name' => 'Feijão Carioca', 'description' => 'Porção de feijão carioca refogado.', 'price' => 5.00],
            ['name' => 'Farofa', 'description' => 'Farofa de mandioca crocante.', 'price' => 4.00],
            ['name' => 'Salada de Alface', 'description' => 'Salada fresca de alface, tomate e cebola.', 'price' => 7.00],
            ['name' => 'Bife Acebolado', 'description' => 'Bife de carne bovina acebolado.', 'price' => 15.00],
            ['name' => 'Frango Grelhado', 'description' => 'Peito de frango grelhado.', 'price' => 12.00],
            ['name' => 'Batata Frita', 'description' => 'Batatas fritas crocantes.', 'price' => 8.00],
            ['name' => 'Batata Palha', 'description' => 'Batata palha crocante.', 'price' => 5.00],
            ['name' => 'Purê de Batata', 'description' => 'Purê de batata cremoso.', 'price' => 6.00],
            ['name' => 'Legumes Grelhados', 'description' => 'Mix de legumes grelhados.', 'price' => 9.00],
            ['name' => 'Quiabo Refogado', 'description' => 'Quiabo refogado no óleo.', 'price' => 7.00],
            ['name' => 'Angu', 'description' => 'Angu de milho cremoso.', 'price' => 6.00],
            ['name' => 'Pirão', 'description' => 'Pirão de peixe.', 'price' => 5.00],
            ['name' => 'Couve Refogada', 'description' => 'Couve refogada com bacon.', 'price' => 6.00],
            ['name' => 'Vinagrete', 'description' => 'Vinagrete brasileiro.', 'price' => 5.00],
            ['name' => 'Queijo Coalho', 'description' => 'Queijo coalho grelhado.', 'price' => 8.00],
            ['name' => 'Laranja', 'description' => 'Laranja para acompanhar.', 'price' => 3.00],
            ['name' => 'Tutu de Feijão', 'description' => 'Tutu de feijão mole.', 'price' => 6.00],
            ['name' => 'Caruru', 'description' => 'Caruru baiano.', 'price' => 7.00],
            // Bebidas
            ['name' => 'Refrigerante 350ml', 'description' => 'Refrigerante de cola 350ml.', 'price' => 5.50],
            ['name' => 'Refrigerante 600ml', 'description' => 'Refrigerante de cola 600ml.', 'price' => 7.50],
            ['name' => 'Suco Natural de Laranja', 'description' => 'Suco de laranja natural 300ml.', 'price' => 6.50],
            ['name' => 'Suco Natural de Limão', 'description' => 'Suco de limão natural 300ml.', 'price' => 6.50],
            ['name' => 'Água Mineral', 'description' => 'Água mineral 500ml.', 'price' => 3.50],
            ['name' => 'Cerveja 350ml', 'description' => 'Cerveja pilsen 350ml.', 'price' => 6.00],
            ['name' => 'Chá Gelado', 'description' => 'Chá gelado de limão 300ml.', 'price' => 5.00],
            // Sobremesas
            ['name' => 'Pudim', 'description' => 'Pudim de leite caseiro.', 'price' => 7.00],
            ['name' => 'Mousse de Maracujá', 'description' => 'Mousse leve de maracujá.', 'price' => 8.00],
            ['name' => 'Brigadeiro', 'description' => 'Brigadeiro gourmet.', 'price' => 4.00],
            ['name' => 'Doce de Leite', 'description' => 'Doce de leite caseiro.', 'price' => 5.00],
            ['name' => 'Torta de Limão', 'description' => 'Torta de limão com merengue.', 'price' => 9.00],
        ];

        $product = $faker->randomElement($products);

        return [
            'name' => $product['name'],
            'code' => $faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'description' => $product['description'],
            'price' => $product['price'],
            'status' => $faker->randomElement(['active', 'inactive']),
            'created_by' => User::factory(),
            'updated_by' => null,
            'deleted_by' => null,
        ];
    }
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            $faker = Faker::create('pt_BR');

            // Componentes disponíveis com preços
            $componentes = [
                'Arroz Branco' => ['qty' => 1, 'price' => 6.00],
                'Feijão Carioca' => ['qty' => 0.5, 'price' => 5.00],
                'Farofa' => ['qty' => 0.2, 'price' => 4.00],
                'Salada de Alface' => ['qty' => 0.3, 'price' => 7.00],
                'Bife Acebolado' => ['qty' => 0.5, 'price' => 15.00],
                'Frango Grelhado' => ['qty' => 0.5, 'price' => 12.00],
                'Batata Frita' => ['qty' => 0.3, 'price' => 8.00],
                'Batata Palha' => ['qty' => 0.2, 'price' => 5.00],
                'Purê de Batata' => ['qty' => 0.4, 'price' => 6.00],
                'Legumes Grelhados' => ['qty' => 0.3, 'price' => 9.00],
                'Quiabo Refogado' => ['qty' => 0.2, 'price' => 7.00],
                'Angu' => ['qty' => 0.3, 'price' => 6.00],
                'Pirão' => ['qty' => 0.2, 'price' => 5.00],
                'Couve Refogada' => ['qty' => 0.2, 'price' => 6.00],
                'Vinagrete' => ['qty' => 0.2, 'price' => 5.00],
                'Queijo Coalho' => ['qty' => 0.1, 'price' => 8.00],
                'Laranja' => ['qty' => 0.5, 'price' => 3.00],
                'Tutu de Feijão' => ['qty' => 0.3, 'price' => 6.00],
                'Caruru' => ['qty' => 0.2, 'price' => 7.00],
            ];

            // Componentes específicos para cada marmita
            $marmitaComponents = [
                'Feijoada Completa' => ['Arroz Branco' => 1, 'Feijão Carioca' => 0.5, 'Farofa' => 0.2, 'Couve Refogada' => 0.2, 'Laranja' => 0.5],
                'Arroz com Feijão' => ['Arroz Branco' => 1, 'Feijão Carioca' => 0.5, 'Bife Acebolado' => 0.5, 'Salada de Alface' => 0.3],
                'Moqueca de Peixe' => ['Pirão' => 0.2, 'Arroz Branco' => 1],
                'Strogonoff de Frango' => ['Frango Grelhado' => 0.5, 'Arroz Branco' => 1, 'Batata Palha' => 0.2],
                'Lasanha à Bolonhesa' => [], // Prato único, sem componentes
                'Churrasco Misto' => ['Bife Acebolado' => 0.5, 'Arroz Branco' => 1, 'Farofa' => 0.2, 'Vinagrete' => 0.2],
                'Baião de Dois' => ['Queijo Coalho' => 0.1, 'Arroz Branco' => 1, 'Feijão Carioca' => 0.5],
                'Frango com Quiabo' => ['Frango Grelhado' => 0.5, 'Quiabo Refogado' => 0.2, 'Angu' => 0.3],
                'Carne de Panela' => ['Bife Acebolado' => 0.5, 'Legumes Grelhados' => 0.3, 'Batata Frita' => 0.3],
                'Peixe Assado' => ['Legumes Grelhados' => 0.3, 'Arroz Branco' => 1],
                'Bobó de Camarão' => ['Arroz Branco' => 1, 'Farofa' => 0.2],
                'Vatapá' => ['Arroz Branco' => 1, 'Caruru' => 0.2],
                'Sarapatel' => ['Arroz Branco' => 1, 'Feijão Carioca' => 0.5],
                'Picanha na Chapa' => ['Bife Acebolado' => 0.5, 'Batata Frita' => 0.3, 'Salada de Alface' => 0.3],
                'Costela Bovina' => ['Purê de Batata' => 0.4, 'Legumes Grelhados' => 0.3],
                'Frango Xadrez' => ['Frango Grelhado' => 0.5, 'Arroz Branco' => 1, 'Legumes Grelhados' => 0.3],
                'Escondidinho de Carne' => ['Queijo Coalho' => 0.1, 'Batata Frita' => 0.3],
                'Rabada' => ['Arroz Branco' => 1, 'Tutu de Feijão' => 0.3],
                'Buchada de Bode' => ['Arroz Branco' => 1, 'Farofa' => 0.2],
                'Moela de Frango' => ['Angu' => 0.3, 'Quiabo Refogado' => 0.2],
                'Dobradinha' => ['Arroz Branco' => 1, 'Feijão Carioca' => 0.5],
                'Tripas' => ['Arroz Branco' => 1, 'Vinagrete' => 0.2],
                'Carne Louca' => ['Bife Acebolado' => 0.5, 'Arroz Branco' => 1, 'Salada de Alface' => 0.3],
                'Galinhada' => ['Arroz Branco' => 1, 'Farofa' => 0.2],
                'Canja de Galinha' => ['Arroz Branco' => 1, 'Legumes Grelhados' => 0.3],
            ];

            // Lista de todas as marmitas
            $marmitas = array_keys($marmitaComponents);

            if (in_array($product->name, $marmitas)) {
                if (!empty($marmitaComponents[$product->name])) {
                    foreach ($marmitaComponents[$product->name] as $compName => $qty) {
                        $compPrice = $componentes[$compName]['price'] ?? 10.00;
                        $componentProduct = Product::where('name', $compName)->first() ?? Product::factory()->create([
                            'name' => $compName,
                            'description' => "Componente: {$compName}",
                            'price' => $compPrice,
                            'status' => 'active',
                            'created_by' => $product->created_by,
                        ]);

                        $product->components()->attach($componentProduct, [
                            'quantity' => $qty,
                        ]);
                    }
                }
            } elseif (in_array($product->name, array_keys($componentes))) {
                // Componentes individuais não precisam de subcomponentes
            } else {
                // Para bebidas e sobremesas, nada
            }
        });
    }
}
