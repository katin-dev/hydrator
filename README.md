# PHP Hydrator

This hydrator allows you to easily hydrate value objects in you Domain Model. 

For example, you have `Product` entity and `Price` value object
```
class Product {
  private ?int $id = null;
  private string $name;
  private Price $price;
}

class Price {
  private int $value;
  private string $currency;
}
```

But you store it as flat array: 
```
$row = [
  "id" => 100,
  "name" => "Product Name",
  "cost" => 120,
  "currency" => "USD", 
]
``` 

Then you can hydrate and extract data like so: 
```
$rules = [
    new HydrationRule('int', 'id', 'id'),
    new HydrationRule('string', 'name', 'name'),
    new HydrationRule('convolution', 'price', '', [
        'className' => Price::class,
        'rules' => [
            'value' => new HydrationRule('int', 'value', 'cost'),
            'currency' => new HydrationRule('string', 'currency', 'currency'),
        ]
    ])
];

$hydrator = new Hydrator();
$product = $hydrator->hydrate($row, Product::class, $rules);
$row = $hydrator->extract($product, $rules);
```