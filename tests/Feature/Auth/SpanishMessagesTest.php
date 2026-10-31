<?php

use App\Models\User;

it('answers in Spanish', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong'])
        ->assertSessionHasErrors(['email' => 'El email o la contraseña no son correctos.']);

    $this->post(route('register.store'), [])
        ->assertSessionHasErrors(['name' => 'Nombre es obligatorio.', 'business_name' => 'Nombre del negocio es obligatorio.']);

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => 'Ana', 'email' => $user->email])
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Perfil actualizado.']);
});
