<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Method CodeIgniter\\\\PHPStan\\\\Tests\\\\Type\\\\\\S+Test\\:\\:\\S+\\(\\) return type has no value type specified in iterable type iterable\\.$#',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
