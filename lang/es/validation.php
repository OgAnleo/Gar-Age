<?php

/*
| Spanish validation messages for the rules the app uses. Rules missing here
| fall back to the English messages (fallback locale).
*/

return [

    'accepted' => 'Debes aceptar :attribute.',
    'after_or_equal' => ':Attribute debe ser una fecha igual o posterior a :date.',
    'array' => ':Attribute debe ser una lista.',
    'before_or_equal' => ':Attribute debe ser una fecha igual o anterior a :date.',
    'between' => [
        'array' => ':Attribute debe tener entre :min y :max elementos.',
        'numeric' => ':Attribute debe estar entre :min y :max.',
        'string' => ':Attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => ':Attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña no es correcta.',
    'date' => ':Attribute no es una fecha válida.',
    'date_format' => ':Attribute no tiene el formato :format.',
    'digits' => ':Attribute debe tener :digits dígitos.',
    'distinct' => ':Attribute tiene un valor repetido.',
    'email' => ':Attribute no es un email válido.',
    'exists' => ':Attribute no es válido.',
    'in' => ':Attribute no es válido.',
    'integer' => ':Attribute debe ser un número entero.',
    'lowercase' => ':Attribute debe estar en minúsculas.',
    'max' => [
        'array' => ':Attribute no puede tener más de :max elementos.',
        'numeric' => ':Attribute no puede ser mayor que :max.',
        'string' => ':Attribute no puede tener más de :max caracteres.',
    ],
    'min' => [
        'array' => ':Attribute debe tener al menos :min elementos.',
        'numeric' => ':Attribute debe ser como mínimo :min.',
        'string' => ':Attribute debe tener al menos :min caracteres.',
    ],
    'numeric' => ':Attribute debe ser un número.',
    'password' => [
        'letters' => ':Attribute debe contener al menos una letra.',
        'mixed' => ':Attribute debe contener mayúsculas y minúsculas.',
        'numbers' => ':Attribute debe contener al menos un número.',
        'symbols' => ':Attribute debe contener al menos un símbolo.',
        'uncompromised' => 'Esa contraseña ha aparecido en una filtración de datos. Elige otra.',
    ],
    'regex' => ':Attribute no tiene un formato válido.',
    'required' => ':Attribute es obligatorio.',
    'required_if' => ':Attribute es obligatorio cuando :other es :value.',
    'same' => ':Attribute y :other deben coincidir.',
    'size' => [
        'array' => ':Attribute debe tener :size elementos.',
        'numeric' => ':Attribute debe ser :size.',
        'string' => ':Attribute debe tener :size caracteres.',
    ],
    'string' => ':Attribute debe ser un texto.',
    'unique' => 'Ese :attribute ya está en uso.',
    'url' => ':Attribute no es una URL válida.',

    'attributes' => [
        'business_name' => 'nombre del negocio',
        'code' => 'código',
        'current_password' => 'contraseña actual',
        'email' => 'email',
        'name' => 'nombre',
        'password' => 'contraseña',
        'recovery_code' => 'código de recuperación',
    ],

];
