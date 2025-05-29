<?php


function execute_query($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        echo "Error executing query: " . mysqli_error($conn);
        return false;
    }
    return $result;
}

function fetch_array($result) {
    return mysqli_fetch_assoc($result);
}

function num_rows($result) {
    return mysqli_num_rows($result);
}

function escape_string($conn, $string) {
    return mysqli_real_escape_string($conn, $string);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}


/**
 * Records a payment in the database.
 *
 * @param mysqli $conn The database connection object.
 * @param int $tenant_id The ID of the tenant making the payment.
 * @param int $property_id The ID of the property the payment is for.
 * @param string $payment_date The date of the payment (YYYY-MM-DD).
 * @param float $amount The amount paid.
 * @param string|null $description An optional description of the payment.
 * @return bool True on success, false on failure.
 */
function recordPayment(
    mysqli $conn,
    int $tenant_id,
    int $property_id,
    string $payment_date,
    float $amount,
    ?string $description
): bool {
    $payment_date = mysqli_real_escape_string($conn, $payment_date);
    $amount = floatval($amount); // Ensure amount is a float
    $description = $description ? mysqli_real_escape_string($conn, $description) : null;

    $sql = "INSERT INTO payments (tenant_id, property_id, payment_date, amount, description)
            VALUES ($tenant_id, $property_id, '$payment_date', $amount, " . ($description ? "'$description'" : "NULL") . ")";

    return mysqli_query($conn, $sql);
}




/**
 * Removes a tenant from a property and, optionally, deletes the associated user.
 *
 * @param mysqli $conn The database connection object.
 * @param int $tenant_id The ID of the tenant to remove.
 * @param bool $delete_user Whether to also delete the user account (optional, default false).
 * @return bool True on success, false on failure.
 */
function removeTenant(mysqli $conn, int $tenant_id, bool $delete_user = false): bool {
    //  Sanitize the tenant ID.
    $tenant_id = intval($tenant_id);

    //  Begin a transaction.  This ensures that either both the tenant
    //  is removed and user is deleted, or neither happens.
    mysqli_begin_transaction($conn);

    //  Remove the tenant from the tenants table.
    $delete_tenant_sql = "DELETE FROM tenants WHERE tenant_id = $tenant_id";
    $delete_tenant_result = mysqli_query($conn, $delete_tenant_sql);

    if (!$delete_tenant_result) {
        mysqli_rollback($conn);
        return false; //  Failed to remove tenant.
    }

    if ($delete_user) {
        //  Get the user_id associated with the tenant.
        $get_user_id_sql = "SELECT user_id FROM tenants WHERE tenant_id = $tenant_id";
        $get_user_id_result = mysqli_query($conn, $get_user_id_sql);

        if ($get_user_id_result && mysqli_num_rows($get_user_id_result) > 0) {
            $user_id_data = mysqli_fetch_assoc($get_user_id_result);
            $user_id_to_delete = intval($user_id_data['user_id']);

            //  Delete the user from the users table.
            $delete_user_sql = "DELETE FROM users WHERE user_id = $user_id_to_delete";
            $delete_user_result = mysqli_query($conn, $delete_user_sql);

            if (!$delete_user_result) {
                mysqli_rollback($conn);
                return false; //  Failed to delete user.
            }
        }
    }

    //  If we get here, everything was successful, so commit the transaction.
    mysqli_commit($conn);
    return true;
}





function fetch_all($result) {
    if (!$result) {
        return [];
    }
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}





?>