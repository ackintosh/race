# Race

the race coordinator.

### Example

```php
$coordinator = new Ackintosh\Race\Coordinator();

$job = function (Ackintosh\Race\Agent $agent) {
    $cart = new Cart();
    $cart->add(new Product(mt_rand(1, 10));
    $cart->add(new Product(mt_rand(1, 10));
    
    $agent->ready();
    
    $cart->checkout();
};

for ($i = 0; $i < 10; $i++) {
    $coordinator->fork($job);
}

$coordinator->run();
```