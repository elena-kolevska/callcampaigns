<?php

use App\Customers\Customer;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ShowCustomerDataTest extends TestCase
{
    use DatabaseMigrations;
    use WithoutMiddleware;


    /** @test */
    public function view_endpoint_shows_customer_data()
    {
        $customer = factory(Customer::class)->create();

        $this->get('api/v1/customers/' . $customer->id);

        $this->seeJson([
                    'id' => $customer->id,
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'phone' => $customer->phone,

                ]);
    }
}
