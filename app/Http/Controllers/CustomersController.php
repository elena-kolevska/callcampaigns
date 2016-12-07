<?php

namespace App\Http\Controllers;

use App\Customers\Customer;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function show($id)
    {
        $customer = Customer::find($id);

        return [
            'id' => $customer->id,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'phone' => $customer->phone,
        ];
    }
}
