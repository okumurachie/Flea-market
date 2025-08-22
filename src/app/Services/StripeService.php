<?php

namespace App\Services;

use Stripe\PaymentIntent;

class StripeService
{
    public function createKonbiniPayment(array $data)
    {
        return PaymentIntent::create($data);
    }
}
