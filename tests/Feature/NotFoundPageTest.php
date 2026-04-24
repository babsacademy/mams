<?php

test('it renders the branded custom 404 page', function () {
    $response = $this->get('/route-introuvable-prosmax');

    $response
        ->assertNotFound()
        ->assertSee('Erreur 404')
        ->assertSee('Retour à l', false)
        ->assertSee('Voir la boutique');
});
