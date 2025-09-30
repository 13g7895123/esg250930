<?php

echo 'Cleaning up templates with UTF-8 issues...' . PHP_EOL;

try {
    $mysqli = new mysqli('mysql', 'esg_user', 'esg_pass', 'esg_db', 3306);

    if ($mysqli->connect_error) {
        throw new Exception('Connection failed: ' . $mysqli->connect_error);
    }

    // Set charset to handle UTF-8 properly
    $mysqli->set_charset("utf8mb4");

    echo 'Connected to database successfully!' . PHP_EOL;

    // Delete problematic templates
    $result = $mysqli->query('DELETE FROM risk_assessment_templates WHERE id IN (13, 14)');

    if ($result) {
        echo 'Successfully deleted problematic templates!' . PHP_EOL;

        // Show remaining templates
        $query = $mysqli->query('SELECT * FROM risk_assessment_templates');
        $templates = [];

        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $templates[] = $row;
            }
        }

        echo 'Remaining templates: ' . count($templates) . PHP_EOL;
        foreach ($templates as $template) {
            echo "ID: {$template['id']}, Name: {$template['version_name']}" . PHP_EOL;
        }
    } else {
        echo 'Failed to delete templates: ' . $mysqli->error . PHP_EOL;
    }

    $mysqli->close();

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}