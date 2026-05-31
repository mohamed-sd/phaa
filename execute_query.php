<?php
function execute_query($query) {
    global $conn;
    if (!$conn->query($query)) {
        json_response(false, 'Database Error: ' . $conn->error);
    }
    return true;
}
