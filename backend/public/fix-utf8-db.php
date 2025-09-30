<?php
// Script to fix UTF-8 issues in database
require_once '../vendor/autoload.php';

// Load CodeIgniter configuration
$config = new \Config\Database();
$db = \Config\Database::connect();

echo "<h2>Database UTF-8 Fix Script</h2>";

try {
    // Check current database charset
    $result = $db->query("SHOW VARIABLES LIKE 'character_set_database'")->getResult();
    echo "<p>Database charset: " . ($result[0]->Value ?? 'Unknown') . "</p>";

    // Check companies table
    $companies = $db->table('companies')->get()->getResult();
    echo "<p>Found " . count($companies) . " companies</p>";

    foreach ($companies as $company) {
        echo "<h3>Company ID: {$company->id}</h3>";
        echo "<p>Original name: " . htmlspecialchars($company->company_name) . "</p>";

        // Check if company name has invalid UTF-8
        if (!mb_check_encoding($company->company_name, 'UTF-8')) {
            echo "<p style='color: red;'>❌ Invalid UTF-8 detected</p>";

            // Try to fix
            $fixedName = mb_convert_encoding($company->company_name, 'UTF-8', 'auto');
            if ($fixedName) {
                echo "<p>Fixed name: " . htmlspecialchars($fixedName) . "</p>";

                // Update database
                $db->table('companies')
                   ->where('id', $company->id)
                   ->update(['company_name' => $fixedName]);

                echo "<p style='color: green;'>✅ Updated successfully</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Could not convert</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ UTF-8 is valid</p>";
        }
        echo "<hr>";
    }

    echo "<p><strong>UTF-8 fix completed!</strong></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>