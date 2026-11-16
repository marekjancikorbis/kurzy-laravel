<?php

// Jen pravidla, která aplikace používá.
return [
    'required' => 'Pole :attribute je povinné.',
    'string' => 'Pole :attribute musí být text.',
    'integer' => 'Pole :attribute musí být celé číslo.',
    'boolean' => 'Pole :attribute musí být ano/ne.',
    'email' => 'Pole :attribute musí být platná e-mailová adresa.',
    'date' => 'Pole :attribute musí být platné datum.',
    'unique' => 'Hodnota pole :attribute už existuje.',
    'exists' => 'Vybraná hodnota pole :attribute neexistuje.',
    'in' => 'Vybraná hodnota pole :attribute není platná.',
    'min' => ['numeric' => 'Pole :attribute musí být alespoň :min.'],
    'max' => ['string' => 'Pole :attribute smí mít nejvýše :max znaků.'],

    'attributes' => [
        'name' => 'název/jméno',
        'code' => 'kód',
        'capacity' => 'kapacita',
        'starts_at' => 'termín',
        'email' => 'e-mail',
        'participant_id' => 'účastník',
        'status' => 'stav',
        'from' => 'od',
        'to' => 'do',
    ],
];
