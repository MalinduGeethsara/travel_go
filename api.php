<?php
header('Content-Type: application/json');
$dataFile = 'data.json';

// Create file if it doesn't exist
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

$jsonData = file_get_contents($dataFile);
$records = json_decode($jsonData, true);

// 1. READ (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $singleRecord = array_filter($records, function($record) use ($id) {
            return $record['id'] == $id;
        });
        echo json_encode(array_values($singleRecord));
    } else {
        echo json_encode($records);
    }
}

// 2. CREATE (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newRecord = [
        "id" => uniqid(),
        "title" => $_POST['title'] ?? '',
        "location" => $_POST['location'] ?? '',
        "category" => $_POST['category'] ?? '',
        "duration" => $_POST['duration'] ?? '',
        "price" => $_POST['price'] ?? '',
        "rating" => $_POST['rating'] ?? ''
    ];

    $records[] = $newRecord;
    file_put_contents($dataFile, json_encode($records, JSON_PRETTY_PRINT));
    echo json_encode(["status" => "success", "message" => "Record added successfully!"]);
}

// 3. UPDATE (PUT)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // PHP doesn't read PUT form data natively, so we read the raw JSON input
    $putData = json_decode(file_get_contents("php://input"), true);
    
    if (isset($putData['id'])) {
        $updated = false;
        foreach ($records as $key => $record) {
            if ($record['id'] == $putData['id']) {
                $records[$key]['title'] = $putData['title'];
                $records[$key]['location'] = $putData['location'];
                $records[$key]['category'] = $putData['category'];
                $records[$key]['duration'] = $putData['duration'];
                $records[$key]['price'] = $putData['price'];
                $records[$key]['rating'] = $putData['rating'];
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            file_put_contents($dataFile, json_encode($records, JSON_PRETTY_PRINT));
            echo json_encode(["status" => "success", "message" => "Record updated successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Record not found."]);
        }
    }
}

// 4. DELETE (DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $records = array_filter($records, function($record) use ($id) {
            return $record['id'] !== $id;
        });
        
        // Re-index array and save
        file_put_contents($dataFile, json_encode(array_values($records), JSON_PRETTY_PRINT));
        echo json_encode(["status" => "success", "message" => "Record deleted successfully!"]);
    }
}
?>