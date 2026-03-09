<?php
require __DIR__ . '/../src/bootstrap.php';

$request = build_request();
$response = dispatch($request);

send_response($response);
