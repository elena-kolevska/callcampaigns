<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'id' => $faker->randomDigitNotNull,
        'name' => $faker->name,
        'company_id' => $faker->randomNumber,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Customers\Customer::class, function (Faker\Generator $faker) {
    $faker->addProvider(new Faker\Provider\en_US\Person($faker));
    $faker->addProvider(new Faker\Provider\en_US\PhoneNumber($faker));

    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'phone' => $faker->e164PhoneNumber,
        'company_id' => $faker->randomNumber,
    ];
});

$factory->define(App\Campaigns\Campaign::class, function (Faker\Generator $faker) {
    $faker->addProvider(new Faker\Provider\en_US\Company($faker));

    $company_id = $faker->randomNumber;
    $file_name = $faker->word;
    $call_options = [
        ['digit' => 1, 'message' => 'Press one to do something.'],
        ['digit' => 2, 'message' => 'Press two to do something else.'],
    ];

    return [
        'company_id' => $company_id,
        "name"=>"Campaign " . $faker->catchPhrase,
        "description"=>$faker->text,
        "message"=>$faker->catchPhrase,
        "options" =>json_encode($call_options),
        "locale"=>$faker->locale,
        "list_path_local" => "lists_{$company_id}/{$file_name}.csv",
        "list_path_remote" => "",
        "status" => 'importing'
    ];
});

