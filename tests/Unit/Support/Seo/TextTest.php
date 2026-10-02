<?php

use App\Support\Seo\Text;

it('picks the indefinite article by sound', function (string $state, string $article) {
    expect(Text::article($state))->toBe($article);
})->with([
    ['Alabama', 'an'],
    ['Ohio', 'an'],
    ['Idaho', 'an'],
    ['Iowa', 'an'],
    ['Illinois', 'an'],
    ['Indiana', 'an'],
    ['Arizona', 'an'],
    ['Oregon', 'an'],
    ['Oklahoma', 'an'],
    ['Utah', 'a'],
    ['Texas', 'a'],
    ['Hawaii', 'a'],
    ['New York', 'a'],
]);
