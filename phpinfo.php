<?php

echo "<pre>";

echo "__DIR__ = " . __DIR__ . "\n";
echo "DOCUMENT_ROOT = " . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";
echo "SCRIPT_FILENAME = " . ($_SERVER['SCRIPT_FILENAME'] ?? '') . "\n";