# Race

The Race Coordinator.

### Usage

```php
$coordinator = new Ackintosh\Race\Coordinator();

$job = function (Ackintosh\Race\Agent $agent) {
    /*
     * We need some preparation before the race.
     */
    $cart = new Cart();
    $cart->add(new Product(mt_rand(1, 10));
    $cart->add(new Product(mt_rand(1, 10));
    
    /*
     * Notify the agent that preparation is finished, and the agent makes consensus on a timing for starting the race 
     * among agent which belongs coordinator.
     * The process stops working until the race starts.
     */
    $agent->ready();
    
    $cart->checkout();
};

/**
 * Fork processes which executes the job.
 */
for ($i = 0; $i < 10; $i++) {
    $coordinator->fork($job);
}

$coordinator->run();
```
