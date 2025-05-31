<?php

/**
 * Logs user activity in the activity_logs table using a prepared statement.
 *
 * @param mysqli $conn The database connection object.
 * @param int $user_id The ID of the user performing the action.
 * @param string $action A brief description of the action (e.g., 'Login', 'View Dashboard').
 * @param string|null $details More detailed information about the action.
 * @return bool True on success, false on failure.
 */
function logActivity(mysqli $conn, int $user_id, string $action, ?string $details = null): bool {
    // Ensure user_id is not 0 for logging, if a session user_id is available
    if ($user_id === 0 && isset($_SESSION['user_id'])) {
        $user_id = intval($_SESSION['user_id']);
    } elseif ($user_id === 0) {
        // If no user_id is available, log as an anonymous action or skip.
        // For this example, we'll log with user_id 0 if no session user_id is found.
        // Consider a dedicated 'anonymous' user_id or a different logging strategy for unauthenticated actions.
    }

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'N/A';

    $sql = "INSERT INTO activity_logs (user_id, action, ip_address, details)\n            VALUES (?, ?, ?, ?)";
    $types = "isss"; // integer, string, string, string

    return execute_prepared_query($conn, $sql, $types, [$user_id, $action, $ip_address, $details]);
}

/**
 * Records a new payment in the payments table.
 *
 * @param mysqli $conn The database connection object.
 * @param int $tenant_id The ID of the tenant making the payment.
 * @param int $property_id The ID of the property the payment is for.
 * @param string $payment_date The date of the payment (YYYY-MM-DD).
 * @param float $amount The amount of the payment.
 * @param string|null $description An optional description for the payment.
 * @return bool True on success, false on failure.
 */
function recordPayment(mysqli $conn, int $tenant_id, int $property_id, string $payment_date, float $amount, ?string $description = null): bool {
    // Start a transaction for atomicity
    mysqli_begin_transaction($conn);

    try {
        $sql = "INSERT INTO payments (tenant_id, property_id, payment_date, amount, description) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            error_log("Failed to prepare statement for recordPayment: " . mysqli_error($conn));
            mysqli_rollback($conn);
            return false;
        }

        // Bind parameters: tenant_id (i), property_id (i), payment_date (s), amount (d), description (s)
        mysqli_stmt_bind_param($stmt, "iisss", $tenant_id, $property_id, $payment_date, $amount, $description);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            mysqli_commit($conn);
            // Log the activity
            if (function_exists('logActivity')) {
                $user_id = $_SESSION['user_id'] ?? 0; // Get logged-in user ID
                logActivity($conn, $user_id, 'Recorded Payment', "Payment of $" . number_format($amount, 2) . " for Property ID: $property_id by Tenant ID: $tenant_id");
            }
            return true;
        } else {
            error_log("Failed to execute statement for recordPayment: " . mysqli_stmt_error($stmt));
            mysqli_rollback($conn);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception in recordPayment: " . $e->getMessage());
        mysqli_rollback($conn);
        return false;
    } finally {
        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
    }
}

/**
 * Executes a simple SQL query.
 *
 * @param mysqli $conn The database connection object.
 * @param string $sql The SQL query string.
 * @return mysqli_result|bool Returns the mysqli_result object on success for SELECT, SHOW, DESCRIBE or EXPLAIN queries. Returns TRUE for other successful queries. FALSE on failure.
 */
if (!function_exists('execute_query')) {
    function execute_query(mysqli $conn, string $sql) {
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            error_log("Database query error: " . mysqli_error($conn) . " in SQL: " . $sql);
        }
        return $result;
    }
}

/**
 * Executes a prepared statement.
 *
 * @param mysqli $conn The database connection object.
 * @param string $sql The SQL query with placeholders (e.g., SELECT * FROM users WHERE id = ?).
 * @param string $types A string containing one or more characters which specify the types for the corresponding bind variables (e.g., 's' for string, 'i' for integer, 'd' for double, 'b' for blob).
 * @param array $params An array of variables to bind to the placeholders.
 * @return mysqli_result|bool Returns the result set object on success for SELECT, SHOW, DESCRIBE or EXPLAIN queries. Returns TRUE for other successful queries. FALSE on failure.
 */
if (!function_exists('execute_prepared_query')) {
    function execute_prepared_query(mysqli $conn, string $sql, string $types, $params) {
        // Accept both array and variadic arguments for backward compatibility
        if (!is_array($params)) {
            $params = func_get_args();
            // Remove $conn, $sql, $types from the beginning
            $params = array_slice($params, 3);
        }
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            error_log("Failed to prepare statement: " . mysqli_error($conn) . " SQL: " . $sql);
            return false;
        }
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if ($result === false && mysqli_sqlstate($conn) !== '00000') {
                error_log("Error executing prepared statement: " . mysqli_stmt_error($stmt));
                mysqli_stmt_close($stmt);
                return false;
            }
            mysqli_stmt_close($stmt);
            return $result;
        } else {
            error_log("Error executing prepared statement: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return false;
        }
    }
}


/**
 * Fetches a single row from a mysqli result set as an associative array.
 *
 * @param mysqli_result|bool $result The mysqli result object.
 * @return array|null An associative array representing the row, or null if no rows.
 */
if (!function_exists('fetch_array')) {
    function fetch_array($result) {
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }
}

/**
 * Fetches all rows from a mysqli result set as an array of associative arrays.
 *
 * @param mysqli_result|bool $result The mysqli result object.
 * @return array An array of associative arrays, or an empty array if no rows.
 */
if (!function_exists('fetch_all')) {
    function fetch_all($result) {
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }
}

/**
 * Escapes a string for use in a SQL query.
 *
 * @param mysqli $conn The database connection object.
 * @param string $string The string to escape.
 * @return string The escaped string.
 */
if (!function_exists('escape_string')) {
    function escape_string(mysqli $conn, string $string): string {
        return mysqli_real_escape_string($conn, $string);
    }
}

// Existing functions from your provided functions.php
/**
 * Removes a tenant and optionally their associated user account.
 *
 * @param mysqli $conn The database connection object.
 * @param int $tenant_id The ID of the tenant to remove.
 * @param bool $delete_user True to also delete the associated user account, false otherwise.
 * @return bool True on success, false on failure.
 */
if (!function_exists('removeTenant')) {
    function removeTenant(mysqli $conn, int $tenant_id, bool $delete_user): bool {
        mysqli_begin_transaction($conn);

        // Get user_id associated with the tenant before deleting the tenant
        $user_id_to_delete = null;
        $tenant_name = '';
        $get_user_id_sql = "SELECT user_id, name FROM tenants WHERE tenant_id = ?";
        $stmt_get_user_id = mysqli_prepare($conn, $get_user_id_sql);
        if ($stmt_get_user_id) {
            mysqli_stmt_bind_param($stmt_get_user_id, 'i', $tenant_id);
            mysqli_stmt_execute($stmt_get_user_id);
            $result_get_user_id = mysqli_stmt_get_result($stmt_get_user_id);
            if ($row = mysqli_fetch_assoc($result_get_user_id)) {
                $user_id_to_delete = $row['user_id'];
                $tenant_name = $row['name'];
            }
            mysqli_stmt_close($stmt_get_user_id);
        }

        // Delete associated payments first to avoid foreign key constraints
        $delete_payments_sql = "DELETE FROM payments WHERE tenant_id = ?";
        $delete_payments_result = execute_prepared_query($conn, $delete_payments_sql, 'i', [$tenant_id]);
        if (!$delete_payments_result) {
            mysqli_rollback($conn);
            return false; // Failed to remove payments
        }

        // Delete associated complaints
        $delete_complaints_sql = "DELETE FROM complaints WHERE tenant_id = ?";
        $delete_complaints_result = execute_prepared_query($conn, $delete_complaints_sql, 'i', [$tenant_id]);
        if (!$delete_complaints_result) {
            mysqli_rollback($conn);
            return false; // Failed to remove complaints
        }

        // Delete the tenant from the tenants table
        $delete_tenant_sql = "DELETE FROM tenants WHERE tenant_id = ?";
        $delete_tenant_result = execute_prepared_query($conn, $delete_tenant_sql, 'i', [$tenant_id]);

        if (!$delete_tenant_result) {
            mysqli_rollback($conn);
            return false; // Failed to remove tenant.
        }

        $log_details = "Tenant ID: $tenant_id (Name: " . escape_string($conn, $tenant_name) . ") removed.";

        if ($delete_user && $user_id_to_delete) {
            // Delete the user from the users table using prepared statement
            $delete_user_sql = "DELETE FROM users WHERE user_id = ?";
            $delete_user_result = execute_prepared_query($conn, $delete_user_sql, 'i', [$user_id_to_delete]);

            if (!$delete_user_result) {
                mysqli_rollback($conn);
                return false; // Failed to delete user.
            }
            $log_details .= " Associated user ID: $user_id_to_delete also deleted.";
        }

        // If we get here, everything was successful, so commit the transaction.
        mysqli_commit($conn);
        logActivity($conn, $_SESSION['user_id'] ?? 0, 'Removed Tenant', $log_details);
        return true;
    }
}

/**
 * Redirects to a specified URL.
 *
 * @param string $url The URL to redirect to.
 */
if (!function_exists('redirect')) {
    function redirect(string $url): void {
        header("Location: " . $url);
        exit();
    }
}

/**
 * Returns the number of rows in a mysqli result set.
 *
 * @param mysqli_result|bool $result The mysqli result object.
 * @return int The number of rows, or 0 if not applicable.
 */
if (!function_exists('num_rows')) {
    function num_rows($result) {
        if ($result instanceof mysqli_result) {
            return mysqli_num_rows($result);
        }
        return 0;
    }
}


?>
