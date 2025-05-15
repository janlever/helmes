<?php

function populateSectorsTable($conn) {
    $result = $conn->query("SELECT COUNT(*) as count FROM sectors");
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        return;
    }
    
    $html = file_get_contents('sectors.txt');
    
    $pattern = '/<option value="(\d+)">(.*?)<\/option>/s';
    preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("INSERT INTO sectors (id, name, parent_id) VALUES (?, ?, ?)");
        $levelParents = [];
        $previousLevel = 0;
        
        foreach ($matches as $match) {
            $id = $match[1];
            $rawName = $match[2];
            
            $nbspCount = substr_count($rawName, '&nbsp;');
            $level = $nbspCount / 4;
            
            $name = trim(strip_tags(html_entity_decode($rawName)));
            
            $parentId = null;
            if ($level > 0) {
                $parentLevel = $level - 1;
                if (isset($levelParents[$parentLevel])) {
                    $parentId = $levelParents[$parentLevel];
                }
            }
            
            $levelParents[$level] = $id;
            
            if ($level < $previousLevel) {
                for ($i = $level + 1; $i <= $previousLevel; $i++) {
                    unset($levelParents[$i]);
                }
            }
            
            $previousLevel = $level;
            
            $stmt->bind_param("isi", $id, $name, $parentId);
            $stmt->execute();
        }
        
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

function renderSectorsSelect($conn, $selectedSectors = []) {
    $result = $conn->query("SELECT id, name, parent_id FROM sectors ORDER BY id");
    
    $sectors = [];
    $tree = [];
    
    while ($row = $result->fetch_assoc()) {
        $sectors[$row['id']] = $row;
        $sectors[$row['id']]['children'] = [];
    }
    
    foreach ($sectors as $id => $sector) {
        if ($sector['parent_id'] === null) {
            $tree[$id] = &$sectors[$id];
        } else {
            if (isset($sectors[$sector['parent_id']])) {
                $sectors[$sector['parent_id']]['children'][$id] = &$sectors[$id];
            }
        }
    }
    
    $html = buildSectorOptions($tree, $selectedSectors);
    
    return $html;
}

function buildSectorOptions($sectors, $selectedSectors, $indent = "") {
    $html = "";
    
    foreach ($sectors as $sector) {
        $selected = in_array($sector['id'], $selectedSectors) ? "selected" : "";
        $html .= "<option value='{$sector['id']}' {$selected}>{$indent}{$sector['name']}</option>\n";
        
        if (!empty($sector['children'])) {
            $html .= buildSectorOptions($sector['children'], $selectedSectors, $indent . "&nbsp;&nbsp;&nbsp;&nbsp;");
        }
    }
    
    return $html;
}
?>