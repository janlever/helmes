<?php

function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

function validateFormData($name, $sectors, $termsAgreed) {
    $errors = [];

    if (empty($name)) {
        $errors[] = "Name is a required field";
    } elseif (strlen($name) > 255) {
        $errors[] = "Name length too long, cannot exceed 255 characters";
    }

    if (empty($sectors)) {
        $errors[] = "You have to select at least 1 sector";
    }

    if (!$termsAgreed) {
        $errors[] = "You have to agree to these terms";
    }

    return $errors;
}

function saveUserData($conn, $name, $sectors, $termsAgreed, $userId = null) {
    $conn->begin_transaction();
    
    try {
        if ($userId) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, agreed_to_terms = ? WHERE id = ?");
            $stmt->bind_param("sii", $name, $termsAgreed, $userId);
            $stmt->execute();
            
            $stmt = $conn->prepare("DELETE FROM users_sectors WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, agreed_to_terms) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $termsAgreed);
            $stmt->execute();
            $userId = $conn->insert_id;
        }
        
        if (!empty($sectors)) {
            $stmt = $conn->prepare("INSERT INTO users_sectors (user_id, sector_id) VALUES (?, ?)");
            
            foreach ($sectors as $sectorId) {
                $stmt->bind_param("ii", $userId, $sectorId);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        return $userId;
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error saving data: " . $e->getMessage();
        return false;
    }
}

function getUserData($conn, $userId) {
    $userData = [];
    
    $stmt = $conn->prepare("SELECT name, agreed_to_terms FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $userData = $row;
        
        $stmt = $conn->prepare("SELECT sector_id FROM users_sectors WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $userData['sectors'] = [];
        while ($row = $result->fetch_assoc()) {
            $userData['sectors'][] = $row['sector_id'];
        }
    }
    
    return $userData;
}
?>