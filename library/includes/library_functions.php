<?php
function get_library_books($conn, $category_id = null, $search = '') {
    $sql = "SELECT b.*, c.name as category_name 
            FROM library_books b 
            LEFT JOIN library_categories c ON b.category_id = c.id 
            WHERE 1=1";
    if ($category_id) {
        $sql .= " AND b.category_id = " . intval($category_id);
    }
    if ($search) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (b.title LIKE '%$search%' OR b.author LIKE '%$search%' OR b.isbn LIKE '%$search%')";
    }
    $sql .= " ORDER BY b.created_at DESC";
    return mysqli_query($conn, $sql);
}
function get_library_categories($conn) {
    return mysqli_query($conn, "SELECT * FROM library_categories ORDER BY name ASC");
}
function get_user_borrows($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT b.*, bk.title, bk.author, bk.cover_image 
                                   FROM library_borrows b 
                                   JOIN library_books bk ON b.book_id = bk.id 
                                   WHERE b.user_id = ? 
                                   ORDER BY b.borrow_date DESC");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}
function get_library_stats($conn) {
    $stats = [];
    $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM library_books");
    $stats['total_books'] = mysqli_fetch_assoc($res)['total'] ?? 0;
    $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM library_borrows WHERE status = 'borrowed'");
    $stats['active_borrows'] = mysqli_fetch_assoc($res)['total'] ?? 0;
    $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM library_borrows WHERE status = 'pending'");
    $stats['pending_requests'] = mysqli_fetch_assoc($res)['total'] ?? 0;
    $res = mysqli_query($conn, "SELECT SUM(fine_amount) as total FROM library_borrows WHERE fine_amount > 0");
    $stats['total_fines'] = mysqli_fetch_assoc($res)['total'] ?? 0;
    return $stats;
}
function request_book($conn, $user_id, $book_id) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM library_borrows WHERE user_id = ? AND book_id = ? AND status IN ('pending', 'borrowed')");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $book_id);
    mysqli_stmt_execute($stmt);
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) return "You already have a pending or active request for this book.";
    $stmt = mysqli_prepare($conn, "SELECT available_copies FROM library_books WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $book_id);
    mysqli_stmt_execute($stmt);
    $book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$book || $book['available_copies'] <= 0) return "Book is currently out of stock.";
    $due_date = date('Y-m-d', strtotime('+14 days'));
    $stmt = mysqli_prepare($conn, "INSERT INTO library_borrows (book_id, user_id, due_date, status) VALUES (?, ?, ?, 'pending')");
    mysqli_stmt_bind_param($stmt, "iis", $book_id, $user_id, $due_date);
    if (mysqli_stmt_execute($stmt)) return true;
    return "Error processing request.";
}
function calculate_overdue_fines($conn) {
    $fine_per_day = 5.00;
    $sql = "UPDATE library_borrows 
            SET fine_amount = DATEDIFF(CURDATE(), due_date) * $fine_per_day, 
                status = 'overdue' 
            WHERE status = 'borrowed' AND due_date < CURDATE()";
    return mysqli_query($conn, $sql);
}
?>