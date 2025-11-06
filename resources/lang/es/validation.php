<?php

return [

    'confirmed' => 'Las contraseñas no coinciden.',
    'email' => 'El :attribute debe ser un correo válido.',
    'required' => 'El :attribute es obligatorio.',
    'unique' => 'El :attribute ya ha sido registrado.',

    'min' => [
        'string' => 'La :attribute debe tener al menos :min caracteres.',
    ],

    // Opcional: para que muestre “contraseña” en lugar de “password”
    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
    ],
];
