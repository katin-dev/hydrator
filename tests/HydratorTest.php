<?php

namespace Test;

use App\HydrationRule;
use App\Hydrator;
use Doctrine\Instantiator\Instantiator;
use PHPUnit\Framework\TestCase;

class HydratorTest extends TestCase
{
    public function testHydrator()
    {
        $row = [
            'id'       => 1,
            'name'     => 'Sergey',
            'cost'     => 100,
            'currency' => 'USD',
            'actions' => json_encode([
                ['name' => 'action-1', 'value' => 'value-1'],
                ['name' => 'action-2', 'value' => 'value-2'],
            ])
        ];

        $hydrator = new Hydrator(new Instantiator());

        $rules = [
            'id' => new HydrationRule('int', 'id', 'id'),
            'name' => new HydrationRule('string', 'name', 'name'),
            'price' => new HydrationRule('convolution', 'price', '', [
                'className' => Price::class,
                'rules' => [
                    'value' => new HydrationRule('int', 'value', 'cost'),
                    'currency' => new HydrationRule('string', 'currency', 'currency'),
                ]
            ]),
            'actions' => new HydrationRule(HydrationRule::TYPE_JSON, 'actions', 'actions', [
                'is_collection' => true,
                'className' => Action::class,
                'rules' => [
                    'name'  => new HydrationRule('string', 'name', 'name'),
                    'value' => new HydrationRule('string', 'value', 'value'),
                ]
            ])
        ];

        /** @var Product $product */
        $product = $hydrator->hydrate($row, Product::class, $rules);
        $this->assertEquals(1, $product->getId());
        $this->assertEquals('Sergey', $product->getName());
        $this->assertEquals(new Price(100, 'USD'), $product->getPrice());
        $this->assertEquals(
            [new Action('action-1', 'value-1'), new Action('action-2', 'value-2')],
            $product->getActions()
        );

        var_dump($product);

        $row = $hydrator->extract($product, $rules);
        $this->assertEquals([
            'id'       => 1,
            'name'     => 'Sergey',
            'cost'     => 100,
            'currency' => 'USD',
            'actions' => json_encode([
                ['name' => 'action-1', 'value' => 'value-1'],
                ['name' => 'action-2', 'value' => 'value-2'],
            ])
        ], $row);

        print_r($row);
    }
}
