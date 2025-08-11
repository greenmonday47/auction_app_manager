<?php

// Simple script to fix tokens_credited field
require_once 'system/bootstrap.php';

$db = \Config\Database::connect();

// Mark all approved transactions as tokens credited
$sql = "UPDATE transactions SET tokens_credited = 1 WHERE status = 'approved'";
$result = $db->query($sql);

echo "Fixed " . $db->affectedRows() . " transactions\n";

// Show current user tokens
$user = $db->query("SELECT tokens FROM users WHERE id = 2")->getRow();
echo "User tokens: " . $user->tokens . "\n"; 